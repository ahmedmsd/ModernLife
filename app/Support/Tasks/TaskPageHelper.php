<?php

namespace App\Support\Tasks;

use App\Models\ProductionTask;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Carbon;

class TaskPageHelper
{
    /* ===== Normalization ===== */
    public function norm(?string $v): ?string
    {
        return is_null($v) ? null : strtolower(trim($v));
    }

    public function normalizeStatus(mixed $s): ?string
    {
        if (is_array($s)) $s = $s['status'] ?? $s['to'] ?? $s['from'] ?? null;
        if ($s instanceof \BackedEnum) $s = $s->value;
        if ($s === null) return null;
        $s = (string) $s;

        return match ($s) {
            'assigned'     => 'pending',
            'acknowledged' => 'received',
            'blocked'      => 'on_hold',
            'closed'       => 'completed',
            default        => $s,
        };
    }

    public function statusVal(ProductionTask $t): ?string
    {
        return $this->normalizeStatus($t->status);
    }

    public function statusAr(?string $val): ?string
    {
        if ($val === null) return null;
        $val = $this->normalizeStatus($val);
        return match ($val) {
            'pending'            => 'بانتظار التأكيد',
            'received'           => 'تم الاستلام',
            'waiting_production' => 'بانتظار التصنيع',
            'under_review'       => 'قيد المراجعة',
            'approved'           => 'معتمد',
            'rejected'           => 'مرفوض',
            'rework'             => 'مطلوب إعادة تنفيذ',
            'in_progress'        => 'قيد التنفيذ',
            'materials_wait'     => 'بانتظار اعتماد المشتريات',
            'materials_prep'     => 'جارٍ تجهيز الخامات',
            'materials_done'     => 'تم توفير الخامات',
            'on_hold'            => 'متوقفة مؤقتًا',
            'completed'          => 'مكتملة',
            'cancelled'          => 'ملغاة',
            default              => $val,
        };
    }

    public function statusColor(?string $val): string
    {
        $val = $this->normalizeStatus($val);
        return match ($val) {
            'pending'            => 'warning',
            'received'           => 'info',
            'waiting_production' => 'warning',
            'under_review'       => 'cyan',
            'approved'           => 'success',
            'rejected'           => 'danger',
            'rework'             => '#f97316',
            'in_progress'        => 'primary',
            'materials_wait'     => 'warning',
            'materials_prep'     => 'primary',
            'materials_done'     => 'success',
            'on_hold'            => 'gray',
            'completed'          => 'success',
            'cancelled'          => 'gray',
            default              => 'secondary',
        };
    }

    public function statusHex(?string $status): string
    {
        $status = $this->normalizeStatus($status);
        return match ($status) {
            'pending'            => '#f59e0b',
            'received'           => '#3b82f6',
            'waiting_production' => '#f59e0b',
            'under_review'       => '#06b6d4',
            'approved'           => '#10b981',
            'rejected'           => '#ef4444',
            'in_progress'        => '#0ea5e9',
            'materials_wait'     => '#f59e0b',
            'materials_prep'     => '#0ea5e9',
            'materials_done'     => '#22c55e',
            'on_hold'            => '#6b7280',
            'completed'          => '#22c55e',
            'cancelled'          => '#9ca3af',
            default              => '#6b7280',
        };
    }

    /* ===== Ownership ===== */
    public function ownerRole(ProductionTask $t): ?string
    {
        return $this->norm($t->current_owner_role);
    }
    public function ownerUserId(ProductionTask $t): ?int
    {
        return $t->current_owner_user_id;
    }
    public function ownerIs(ProductionTask $t, string $role): bool
    {
        return $this->ownerRole($t) === $this->norm($role);
    }

    public function userHasAnyRole(?Authenticatable $u, array $roles): bool
    {
        if (!$u) return false;
        if (method_exists($u, 'hasAnyRole')) return (bool) $u->hasAnyRole($roles);
        try {
            $names = method_exists($u, 'roles') ? $u->roles->pluck('name')->map(fn($n)=>strtolower(trim($n)))->all() : [];
            return (bool) array_intersect($names, array_map('strtolower', $roles));
        } catch (\Throwable) { return false; }
    }

    /* ===== Button visibility guards ===== */
    public function canDeptAcknowledge(ProductionTask $t, ?Authenticatable $u): bool
    {
        $statusOk       = in_array($this->statusVal($t), ['pending','assigned'], true);
        $ownerRoleOk    = $this->ownerIs($t, 'department_manager');
        $notReceivedYet = blank($t->received_at);
        $userIsOwner    = !$this->ownerUserId($t) || $this->ownerUserId($t) === ($u?->id);
        return $this->userHasAnyRole($u, ['department_manager','admin','super-admin'])
            && $statusOk && $ownerRoleOk && $notReceivedYet && $userIsOwner;
    }

    public function canRequestMaterials(ProductionTask $t, ?Authenticatable $u): bool
    {
        return $this->userHasAnyRole($u, ['department_manager','admin','super-admin'])
            && ($this->statusVal($t) === 'received' || $this->statusVal($t) === 'on_hold')
            && !$this->hasOpenMaterialsRequest($t)
            && $this->ownerIs($t, 'department_manager');
    }

    public function canPurchasingReceive(ProductionTask $t, ?Authenticatable $u): bool
    {
        if (!$this->userHasAnyRole($u, ['purchasing_manager','admin','super-admin'])) return false;
        if ($this->statusVal($t) !== 'materials_wait') return false;
        if (!$this->ownerIs($t, 'purchasing_manager')) return false;

        $mr = $t->materialRequests()->whereNull('provided_at')->latest()->first();
        return $mr && ($mr->status === 'requested' || $mr->status === null);
    }
    public function canMaterialsProvided(ProductionTask $t, ?Authenticatable $u): bool
    {
        if (! $this->userHasAnyRole($u, ['purchasing_manager','admin','super-admin'])) return false;
        if ($this->statusVal($t) !== 'materials_wait') return false;
        if (! $this->ownerIs($t, 'purchasing_manager')) return false;

        return $t->materialRequests()
            ->whereNull('provided_at')
            ->where('status', 'approved')
            ->exists();
    }

    public function canMaterialsReceivedOk(ProductionTask $t, ?Authenticatable $u): bool
    {
        return $this->userHasAnyRole($u, ['department_manager','admin','super-admin'])
            && $this->statusVal($t) === 'materials_done'
            && $this->ownerIs($t, 'department_manager');
    }


    public function canProductionAcknowledge(ProductionTask $t, ?Authenticatable $u): bool
    {
        return false;
    }


    public function canStartProduction(ProductionTask $t, ?Authenticatable $u): bool
    {
        return $this->userHasAnyRole($u, ['department_manager','admin','super-admin'])
            && ($this->statusVal($t) === 'waiting_production' || $this->statusVal($t) === 'rework')
            && $this->ownerIs($t, 'department_manager')
            && ! $this->hasLog($t, 'manufacturing_started');
    }
    public function canFinishManufacturing(\App\Models\ProductionTask $t, ?\Illuminate\Contracts\Auth\Authenticatable $u): bool
    {
        if ($this->isClosedOrCompleted($t)) {
            return false;
        }

        // أدوار مسموح لها
        if (! $this->userHasAnyRole($u, ['department_manager','admin','super-admin'])) {
            return false;
        }

        // يجب أن يكون المالك الحالي هو مدير القسم (ما لم تكن عندك سياسة أخرى)
        if (! $this->ownerIs($t, 'department_manager')) {
            return false;
        }

        // الحالة يجب أن تكون in_progress
        if ($this->statusVal($t) !== 'in_progress') {
            return false;
        }

        // لازم يكون في manufacturing_started سابق
        if (! $this->hasLog($t, 'manufacturing_started')) {
            return false;
        }

        // منع التكرار: لا بد من عدم وجود manufacturing_sent_to_qa بعد آخر manufacturing_started
        $lastStart = \App\Models\TaskLog::query()
            ->where('task_id', $t->id)
            ->where('type', 'manufacturing_started')
            ->orderByRaw('COALESCE(happened_at, created_at) DESC, id DESC')
            ->first();

        if (! $lastStart) {
            return false;
        }

        $stAt = $lastStart->happened_at ?? $lastStart->created_at;

        $sentAfter = \App\Models\TaskLog::query()
            ->where('task_id', $t->id)
            ->where('type', 'manufacturing_sent_to_qa')
            ->where(function ($q) use ($stAt, $lastStart) {
                $q->whereRaw('COALESCE(happened_at, created_at) > ?', [$stAt])
                    ->orWhere(function ($q2) use ($stAt, $lastStart) {
                        $q2->whereRaw('COALESCE(happened_at, created_at) = ?', [$stAt])
                            ->where('id', '>', $lastStart->id);
                    });
            })
            ->exists();

        return ! $sentAfter;
    }


    /* ===== Logs / Requests helpers ===== */
    public function hasLog(ProductionTask $t, string $type): bool
    {
        $logs = $t->relationLoaded('logs') ? $t->logs : $t->logs()->get();
        return $logs->contains(fn ($l) => $l->type === $type);
    }

    public function hasOpenMaterialsRequest(ProductionTask $t): bool
    {
        return $t->materialRequests()
            ->whereNull('provided_at')
            ->whereIn('status', ['requested','approved'])
            ->exists();
    }

    public function isClosedOrCompleted(ProductionTask $t): bool
    {
        $s = (string) $t->status;
        return in_array($s, ['completed', 'closed', 'cancelled'], true);
    }

    /* ===== Stage durations (render) ===== */
    public function buildStageDurations(ProductionTask $task): array
    {
        $logs = $task->logs()->orderBy('happened_at')->get(['type','data','happened_at','created_at']);

        $start = $task->created_at ? Carbon::parse($task->created_at)
            : ($logs->first()?->happened_at ? Carbon::parse($logs->first()->happened_at) : now());

        $endRaw = $task->completed_at ?? $logs->last()?->happened_at ?? now();
        $end    = $endRaw instanceof Carbon ? $endRaw : Carbon::parse($endRaw);

        $firstChange = $logs->firstWhere('type', 'status_changed');
        $initial     = is_array($firstChange?->data ?? null) ? ($this->normalizeStatus($firstChange->data['from'] ?? null)) : null;
        $current     = $initial ?? $this->normalizeStatus($task->status) ?? 'pending';

        $cursor  = $start->clone();
        $seconds = [];

        $add = function (?string $status, Carbon $from, Carbon $to) use (&$seconds) {
            $status = $status ?: 'unknown';
            $delta  = max(0, $from->diffInSeconds($to));
            $seconds[$status] = ($seconds[$status] ?? 0) + $delta;
        };

        foreach ($logs as $log) {
            $tRaw = $log->happened_at ?? $log->created_at;
            if (!$tRaw) continue;
            $t = $tRaw instanceof Carbon ? $tRaw : Carbon::parse($tRaw);
            if ($t->lessThan($cursor)) continue;

            $add($current, $cursor, $t);
            $cursor = $t->clone();

            if ($log->type === 'status_changed' && is_array($log->data ?? null)) {
                $to = $this->normalizeStatus($log->data['to'] ?? null);
                if ($to) $current = $to;
            }
        }

        if ($end->greaterThan($cursor)) $add($current, $cursor, $end);

        $total = array_sum($seconds);

        $order = [
            'pending','received','materials_wait','materials_prep','materials_done',
            'waiting_production','in_progress','under_review','approved',
            'rejected','on_hold','completed','cancelled','unknown'
        ];

        $rows = collect($seconds)->map(fn($sec,$status)=>[
            'status'=>$status, 'label'=>$this->statusAr($status) ?? $status, 'seconds'=>$sec,
        ])->sortBy(fn($row)=>($i=array_search($row['status'],$order,true))===false?999:$i)->values()
            ->map(function($row) use($total){
                $row['human']   = $row['seconds']>0? Carbon::now()->subSeconds($row['seconds'])->diffForHumans(null,true):'0 ث';
                $row['percent'] = $total>0? round($row['seconds']*100/$total,1):0.0;
                return $row;
            })->all();

        return compact('rows','total','start','end');
    }

    public function renderStageDurationsHtml(ProductionTask $task): string
    {
        $stats = $this->buildStageDurations($task);
        $rows  = $stats['rows'];
        $totalH = $stats['start']->diffForHumans($stats['end'], true);
        $start  = $stats['start']->format('Y-m-d H:i');
        $end    = $stats['end']->format('Y-m-d H:i');

        ob_start(); ?>
        <div class="w-full">
            <div class="rounded-xl border bg-white/80 dark:bg-gray-900/70 shadow-sm">
                <div class="px-4 py-3 border-b bg-gray-50/60 dark:bg-gray-800/60 rounded-t-xl">
                    <div class="flex flex-wrap items-center gap-3 text-sm">
                        <div class="font-semibold">الإجمالي منذ الإنشاء حتى الإغلاق/الآن:</div>
                        <div class="px-2 py-0.5 rounded-full bg-gray-900 text-white text-xs dark:bg-white dark:text-gray-900">
                            <?= e($totalH) ?>
                        </div>
                        <div class="ms-auto text-xs text-gray-500 dark:text-gray-400">
                            من <?= e($start) ?> إلى <?= e($end) ?>
                        </div>
                    </div>
                </div>
                <div class="px-4 py-6">
                    <div class="overflow-x-auto">
                        <table class="w-full table-auto text-sm rtl:text-right">
                            <thead class="bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                            <tr>
                                <th class="px-3 py-2 text-right">المرحلة</th>
                                <th class="px-3 py-2 text-right">المدة</th>
                                <th class="px-3 py-2 text-right">النسبة</th>
                                <th class="px-3 py-2 text-right">تقدّم</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <?php foreach ($rows as $r):
                                $hex     = $this->statusHex($r['status'] ?? null);
                                $label   = $r['label'] ?? ($r['status'] ?? '—');
                                $human   = $r['human'] ?? '—';
                                $percent = isset($r['percent']) ? (float)$r['percent'] : 0.0;
                                ?>
                                <tr class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-900 dark:even:bg-gray-800">
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-2">
                                            <span class="inline-block w-2.5 h-2.5 rounded-full" style="background-color: <?= e($hex) ?>;"></span>
                                            <span class="px-2 py-0.5 rounded text-white text-xs" style="background-color: <?= e($hex) ?>;">
                                                <?= e($label) ?>
                                            </span>
                                        </span>
                                    </td>
                                    <td class="px-3 py-2"><?= e($human) ?></td>
                                    <td class="px-3 py-2"><?= e(number_format($percent, 1)) ?>%</td>
                                    <td class="px-3 py-2 w-64">
                                        <div class="w-full h-2 rounded bg-gray-200 dark:bg-gray-700 overflow-hidden">
                                            <div class="h-2 rounded" style="width: <?= e(max(0,min(100,$percent))) ?>%; background-color: <?= e($hex) ?>;"></div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php return (string) ob_get_clean();
    }

    /* ===== Navigation helpers ===== */
    public function parentTasksUrl(?Authenticatable $u, ProductionTask $t): string
    {
        if ($u && method_exists($u, 'hasAnyRole') && $u->hasAnyRole(['super-admin','admin','project_manager'])) {
            return $t->project_id ? url("/admin/projects/{$t->project_id}/manage-tasks") : url('/admin/tasks');
        }
        return url('/admin/my-tasks');
    }

    public function parentTasksLabel(?Authenticatable $u): string
    {
        return ($u && method_exists($u,'hasAnyRole') && $u->hasAnyRole(['super-admin','admin','factory_manager']))
            ? 'مهام المشروع' : 'مهامي';
    }
}
