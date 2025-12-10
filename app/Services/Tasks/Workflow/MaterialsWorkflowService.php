<?php

namespace App\Services\Tasks\Workflow;

use App\Models\ProductionTask;
use App\Services\Tasks\Workflow\Concerns\HasTaskWorkflowHelpers;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MaterialsWorkflowService
{
    use HasTaskWorkflowHelpers;

    public function requestMaterials(ProductionTask $task, string $note, string $poFilePath): void
    {
        DB::transaction(function () use ($task, $note, $poFilePath) {
            \App\Models\MaterialRequest::create([
                'task_id'       => $task->id,
                'department_id' => $task->department_id,
                'requested_by'  => Auth::id(),
                'requested_at'  => now(),
                'status'        => 'requested',
                'note'          => $note,
                'po_file'       => $poFilePath,
            ]);

            $task->update(['status' => 'materials_wait']);
            $pmUserId = $this->resolvePurchasingManagerUserId($task);

            if ($pmUserId) {
                $this->setOwner(
                    $task,
                    'purchasing_manager',
                    userId: $pmUserId,
                    touchSent: true,
                    note: 'فتح طلب خامات — تحويل للمشتريات'
                );
            }
            $this->log($task, 'materials_request_opened', ['by' => Auth::id()]);
        });
    }

    public function purchasingReceive(ProductionTask $task, array $data): void
    {
        DB::transaction(function () use ($task, $data) {
            $mr = $task->materialRequests()->whereNull('provided_at')->latest()->firstOrFail();

            $mr->update([
                'po_number'            => $data['po_number']            ?? $mr->po_number,
                'estimated_cost'       => $data['estimated_cost']       ?? $mr->estimated_cost,
                'expected_delivery_at' => $data['expected_delivery_at'],
                'note'                 => trim(($mr->note ? $mr->note . "\n" : '') . ($data['note'] ?? '')),
                'status'               => 'approved',
                'approved_at'          => now(),
                'approved_by'          => Auth::id(),
            ]);

            $task->update(['status' => 'materials_prep']);

            $this->log($task, 'purchasing_ack', ['by' => Auth::id()]);
        });
    }

    public function materialsProvided(ProductionTask $task, float $actualCost, ?string $note = null, ?array $invoice = null): void
    {
        DB::transaction(function () use ($task, $actualCost, $note, $invoice) {
            $mr = $task->materialRequests()
                ->whereIn('status', ['approved','supplying']) // فقط الطلب “القابل للتوريد”
                ->orderByDesc('id')
                ->lockForUpdate()
                ->firstOrFail();

            $invoiceNo   = $invoice['invoice_no']   ?? null;
            $invoiceDate = isset($invoice['invoice_date']) && $invoice['invoice_date']
                ? \Illuminate\Support\Carbon::parse($invoice['invoice_date'])
                : null;
            $invoiceFile = $invoice['invoice_file'] ?? null;

            $mr->update([
                'actual_cost'  => $actualCost,
                'invoice_no'   => $invoiceNo   ?? $mr->invoice_no,
                'invoice_date' => $invoiceDate ?? $mr->invoice_date,
                'invoice_file' => $invoiceFile ?? $mr->invoice_file,
                'note'         => trim(($mr->note ? $mr->note . "\n" : '') . ($note ?? '')),
                'provided_by'  => Auth::id(),
                'provided_at'  => now(),
                'status'       => 'fulfilled',
            ]);

            $task->update(['status' => 'materials_done']);


            $this->log($task, 'materials_provided_note', [
                'mr_id'       => $mr->id,
                'actual_cost' => $actualCost,
                'note'        => $note ? trim($note) : null,
            ]);

        });
    }


    public function materialsReceivedOk(
        ProductionTask $task,
        ?string $start = null,
        ?string $end = null,
        ?string $install = null,
        ?string $note = null
    ): void {
        DB::transaction(function () use ($task, $start, $end, $install, $note) {
            $payload = ['status' => 'waiting_production'];
            if ($start)   $payload['planned_start_at']   = \Illuminate\Support\Carbon::parse($start);
            if ($end)     $payload['planned_end_at']     = \Illuminate\Support\Carbon::parse($end);
            if ($install) $payload['planned_install_at'] = \Illuminate\Support\Carbon::parse($install);
            $task->update($payload);

            $this->log($task, 'materials_received_ok', [
                'planned_start_at'   => optional($task->planned_start_at)->toDateTimeString(),
                'planned_end_at'     => optional($task->planned_end_at)->toDateTimeString(),
                'planned_install_at' => optional($task->planned_install_at)->toDateTimeString(),
                'note'               => $note ? trim($note) : null,
                'by'                 => Auth::id(),
            ]);

            $this->markOwnerReceived($task, 'استلام الخامات — جاهز لبدء التصنيع');

            $deptManagerId = $task->department?->manager_user_id
                ?? $task->department?->head_user_id
                ?? $task->assigned_to_employee?->user_id
                ?? null;

            if ($deptManagerId) {
                $this->setOwner(
                    $task,
                    'department_manager',
                    userId: $deptManagerId,
                    touchSent: false,
                    note: 'جاهز لبدء التصنيع بعد استلام الخامات'
                );
            }
        });
    }

    public function materialsReceivedPartialAllowStart(
        ProductionTask $task,
        ?string $start,
        ?string $end,
        ?string $install,
        ?string $note,
        ?string $missingItemsNote = null
    ): void {
        DB::transaction(function () use ($task, $start, $end, $install, $note, $missingItemsNote) {
            $this->openFollowupMaterialsRequest($task, $missingItemsNote);

            $payload = ['status' => 'waiting_production'];
            if ($start)   $payload['planned_start_at']   = \Illuminate\Support\Carbon::parse($start);
            if ($end)     $payload['planned_end_at']     = \Illuminate\Support\Carbon::parse($end);
            if ($install) $payload['planned_install_at'] = \Illuminate\Support\Carbon::parse($install);
            $task->update($payload);

            $this->log($task, 'materials_received_partial', [
                'allow_start'        => true,
                'planned_start_at'   => optional($task->planned_start_at)->toDateTimeString(),
                'planned_end_at'     => optional($task->planned_end_at)->toDateTimeString(),
                'planned_install_at' => optional($task->planned_install_at)->toDateTimeString(),
                'note'               => $note ? trim($note) : null,
                'missing'            => $missingItemsNote ? trim($missingItemsNote) : null,
                'by'                 => Auth::id(),
            ]);

            $this->markOwnerReceived($task, 'استلام جزئي (مع السماح بالبدء)');

            $deptManagerId = $task->department?->manager_user_id
                ?? $task->department?->head_user_id
                ?? $task->assigned_to_employee?->user_id
                ?? null;

            if ($deptManagerId) {
                $this->setOwner(
                    $task,
                    'department_manager',
                    userId: $deptManagerId,
                    touchSent: false,
                    note: 'استلام جزئي للخامات — يمكن بدء التصنيع'
                );
            }
        });
    }

    public function materialsReceivedPartialHold(
        ProductionTask $task,
        ?string $note,
        ?string $missingItemsNote = null
    ): void {
        DB::transaction(function () use ($task, $note, $missingItemsNote) {
            $this->openFollowupMaterialsRequest($task, $missingItemsNote);

            $task->update(['status' => 'on_hold']);

            $this->log($task, 'materials_received_partial', [
                'allow_start' => false,
                'note'        => $note ? trim($note) : null,
                'missing'     => $missingItemsNote ? trim($missingItemsNote) : null,
                'by'          => Auth::id(),
            ]);

            $this->setOwner($task, 'purchasing_manager', userId: $this->resolvePurchasingManagerUserId($task), touchSent: true, note: 'نواقص خامات — انتظار توريد تكميلي');
        });
    }

    public function materialsReceivedIssue(ProductionTask $task, ?string $note, ?string $issueDetails = null): void
    {
        DB::transaction(function () use ($task, $note, $issueDetails) {
            $mr = $task->materialRequests()
                ->whereIn('status', ['fulfilled','supplying','approved'])
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            if ($mr) {
                $mr->update(['status' => 'issue_reported']);
            }

            $task->update(['status' => 'on_hold']);
            $this->setOwner($task, 'purchasing_manager', userId: $this->resolvePurchasingManagerUserId($task), touchSent: true, note: 'مشكلة في الخامات — انتظار المعالجة');

            $this->log($task, 'materials_received_issue', [
                'mr_id'  => $mr?->id,
                'note'   => $note ? trim($note) : null,
                'issues' => $issueDetails ? trim($issueDetails) : null,
                'by'     => Auth::id(),
            ]);
        });
    }


    protected function openFollowupMaterialsRequest(ProductionTask $task, ?string $missingItemsNote): void
    {
        $prevMr = $task->materialRequests()->latest()->first();

        $mr = \App\Models\MaterialRequest::create([
            'task_id'       => $task->id,
            'department_id' => $task->department_id,
            'requested_by'  => Auth::id(),
            'requested_at'  => now(),
            'status'        => 'requested',
            'note'          => 'طلب تكميلي لبنود ناقصة.' . ($missingItemsNote ? ("\n\nالبنود الناقصة:\n" . trim($missingItemsNote)) : ''),
            'po_file'       => $prevMr?->po_file,
            'parent_id'     => $prevMr?->id, // يعتمد على الحقل الذي أضفته يدويًا
        ]);

        $this->log($task, 'materials_followup_opened', [
            'by'    => Auth::id(),
            'mr_id' => $mr->id,
        ]);
    }
}
