<?php

namespace App\Filament\Pages;

use App\Models\ProductionTask;
use App\Models\Showroom;
use App\Models\TaskLog;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class InstallationCalendar extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-calendar';
    protected static ?string $navigationLabel = 'تقويم التركيب';
    protected static ?string $title           = 'جدول مواعيد التركيب';
    protected static ?string $slug            = 'installation-calendar';
    protected static ?int    $navigationSort  = 40;

    protected static string $view = 'filament.pages.installation-calendar';

    public static function canAccess(): bool
    {
        return auth()->check()
            && auth()->user()->hasAnyRole([
                'admin',
                'super-admin',
                'factory_manager',
                'showroom_manager',
                'department_manager',
                'sales',
                'quality_manager',
            ]);
    }

    public ?string $from = null;
    public ?string $to   = null;

    public ?int    $rescheduleTaskId = null;
    public ?string $newInstallDate   = null;
    public ?string $rescheduleNote   = null;

    protected function baseQueryForTable(): Builder
    {
        $startAt = $this->from ? Carbon::parse($this->from)->startOfDay() : now()->startOfMonth();
        $endAt   = $this->to   ? Carbon::parse($this->to)->endOfDay()     : now()->endOfMonth();

        $q = ProductionTask::query()
            ->with([
                'project.client',
                'project.productionRequest.showroom',
                'department',
            ])
            ->whereNotNull('planned_install_at')
            ->whereBetween('planned_install_at', [$startAt, $endAt])
            ->whereNotIn('status', ['completed', 'closed', 'cancelled']);

        return $this->applyRoleScope($q, 'production_tasks');
    }

    public function groupedByDate(): array
    {
        $tasks = $this->baseQueryForTable()
            ->orderBy('planned_install_at')
            ->get();

        return $tasks->groupBy(fn ($t) => Carbon::parse($t->planned_install_at)->toDateString())
            ->map(fn ($group) => $group->values()->all())
            ->toArray();
    }

    public function openRescheduleModal(int $taskId): void
    {
        $task = ProductionTask::with(['project.productionRequest.showroom'])->findOrFail($taskId);

        if (! $this->canReschedule($task)) {
            throw ValidationException::withMessages([
                'newInstallDate' => 'ليست لديك الصلاحية لتعديل موعد هذه المهمة.',
            ]);
        }

        $this->rescheduleTaskId = $task->id;
        $this->newInstallDate   = optional($task->planned_install_at)->toDateString();
        $this->rescheduleNote   = null;

        $this->dispatch('open-modal', id: 'reschedule-modal');
    }

    public function saveReschedule(): void
    {
        $this->validate([
            'rescheduleTaskId' => ['required', 'integer', 'exists:production_tasks,id'],
            'newInstallDate'   => ['required', 'date', 'after_or_equal:today'],
        ], [], [
            'newInstallDate' => 'تاريخ التركيب الجديد',
        ]);

        $task = ProductionTask::with(['project.productionRequest.showroom'])->findOrFail($this->rescheduleTaskId);

        if (! $this->canReschedule($task)) {
            throw ValidationException::withMessages([
                'newInstallDate' => 'ليست لديك الصلاحية لتعديل موعد هذه المهمة.',
            ]);
        }

        $old = optional($task->planned_install_at)->toDateString();
        $task->planned_install_at = Carbon::parse($this->newInstallDate)->startOfDay();
        $task->save();

        TaskLog::create([
            'task_id'     => $task->id,
            'type'        => 'installation_rescheduled',
            'data'        => [
                'old_date' => $old,
                'new_date' => $this->newInstallDate,
                'reason'   => $this->rescheduleNote,
            ],
            'causer_id'   => auth()->id(),
            'happened_at' => now(),
            'note'        => 'تعديل موعد التركيب',
        ]);

        $this->dispatch('close-modal', id: 'reschedule-modal');
        $this->dispatch('notify', type: 'success', title: 'تم', body: 'تم تحديث موعد التركيب بنجاح.');
        $this->reset(['rescheduleTaskId', 'newInstallDate', 'rescheduleNote']);
    }

    protected function canReschedule(ProductionTask $task): bool
    {
        $u = auth()->user();
        if (! $u) {
            return false;
        }

        // الأدمن ومدير المصنع يمكنهم التعديل
        if ($u->hasAnyRole(['admin', 'super-admin', 'factory_manager'])) {
            return true;
        }

        // مدير المعرض: فقط لو كانت المهمة ضمن أحد معارضه
//        if ($u->hasRole('showroom_manager')) {
//            $employeeId = $u->employee?->getKey();
//            if (! $employeeId) {
//                return false;
//            }
//
//            $showroomIds = Showroom::query()
//                ->where('manager_id', $employeeId)
//                ->pluck('id');
//
//            $taskShowroomId = $task->project?->productionRequest?->showroom_id;
//
//            return $taskShowroomId && $showroomIds->contains($taskShowroomId);
//        }

        // بقية الأدوار: عرض فقط
        return false;
    }

    protected function applyRoleScope(Builder $q, string $tasksTable = 'production_tasks'): Builder
    {
        $u = auth()->user();
        if (! $u) {
            return $q->whereRaw('1=0');
        }

        if ($u->hasAnyRole(['admin', 'super-admin', 'factory_manager'])) {
            return $q;
        }

        if ($u->hasRole('department_manager') && $u->employee?->department_id) {
            $q->where($tasksTable . '.department_id', $u->employee->department_id);
        }

        if ($u->hasRole('showroom_manager')) {
            $employeeId = $u->id;
            if (! $employeeId) {
                return $q->whereRaw('1=0');
            }

            $showroomIds = Showroom::query()
                ->where('manager_id', $employeeId)
                ->pluck('id');

            if ($showroomIds->isEmpty()) {
                return $q->whereRaw('1=0');
            }

            $q->whereHas('project.productionRequest', function (Builder $qq) use ($showroomIds) {
                $qq->whereIn('showroom_id', $showroomIds);
            });
        }

        if ($u->hasRole('sales')) {
            $q->whereHas('project.productionRequest', function (Builder $qq) use ($u) {
                $qq->where('created_by', $u->id);
            });
        }

        return $q;
    }
}
