<?php

namespace App\Support\Reports;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\TaskStatus;

class ReportService
{
    public function __construct(protected ReportFilters $f) {}

    protected array $DONE = ['completed','closed','cancelled'];

    protected function baseTasks(): Builder
    {
        return \App\Models\ProductionTask::query()
            ->from('production_tasks as t')
            ->when($this->f->date_from,   fn ($q, $v) => $q->whereDate('t.created_at', '>=', $v))
            ->when($this->f->date_to,     fn ($q, $v) => $q->whereDate('t.created_at', '<=', $v))
            ->when($this->f->dept_id,     fn ($q, $v) => $q->where('t.department_id', $v))
            ->when($this->f->branch_id,   fn ($q, $v) => $q->whereExists(function($eq) use ($v) {
                $eq->select(DB::raw(1))
                   ->from('projects as p')
                   ->join('showrooms as s', 's.id', '=', 'p.showroom_id')
                   ->whereColumn('p.id', 't.project_id')
                   ->where('s.id', $v);
            }))
            ->when($this->f->employee_id, fn ($q, $v) => $q->where('t.assigned_to_user_id', $v))
            ->when($this->f->status,      fn ($q, $v) => $q->where('t.status', $v));
    }

    public function kpis(): array
    {
        $base = $this->baseTasks();

        $total = (clone $base)->count();

        $completed = (clone $base)
            ->where('t.status', 'completed')
            ->count();

        $wip = (clone $base)
            ->whereNotIn('t.status', $this->DONE)
            ->count();

        $delayed = (clone $base)
            ->whereNotNull('t.planned_end_at')
            ->where(function ($q) {
                $q->where(function ($qq) {
                    $qq->whereNotNull('t.completed_at')
                        ->whereRaw('t.completed_at > t.planned_end_at');
                })->orWhere(function ($qq) {
                    $qq->whereNull('t.completed_at')
                        ->whereRaw('NOW() > t.planned_end_at');
                });
            })
            ->count();

        $avgHours = (clone $base)
            ->whereNotNull('t.assigned_at')
            ->whereNotNull('t.completed_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, t.assigned_at, t.completed_at)) as h')
            ->value('h');

        $completedWithDue = (clone $base)
            ->where('t.status', 'completed')
            ->whereNotNull('t.due_date')
            ->count();

        $onTimeCompleted = (clone $base)
            ->where('t.status', 'completed')
            ->whereNotNull('t.due_date')
            ->whereRaw("t.completed_at <= DATE_ADD(DATE(t.due_date), INTERVAL 1 DAY) - INTERVAL 1 SECOND")
            ->count();

        $slaRate = $completedWithDue
            ? round(($onTimeCompleted / $completedWithDue) * 100, 1)
            : 0.0;

        return [
            'total'        => $total,
            'completion'   => $total ? round(($completed / $total) * 100, 1) : 0,
            'avg_duration' => $avgHours ? round($avgHours, 1) : 0,
            'delay_rate'   => $total ? round(($delayed / $total) * 100, 1) : 0,
            'wip'          => $wip,
            'sla_rate'     => $slaRate,
        ];
    }

    public function tasksByDepartment(): array
    {
        $rows = (clone $this->baseTasks())
            ->leftJoin('departments as d', 'd.dept_id', '=', 't.department_id')
            ->selectRaw('COALESCE(d.dept_name, "غير محدد") as dept, COUNT(t.id) as c')
            ->groupBy('dept')
            ->orderByDesc('c')
            ->limit(12)
            ->get();

        return [
            'labels' => $rows->pluck('dept')->all(),
            'data'   => $rows->pluck('c')->all(),
        ];
    }

    public function completionTrend(): array
    {
        $rows = (clone $this->baseTasks())
            ->where('t.status', 'completed')
            ->whereNotNull('t.completed_at')
            ->selectRaw('DATE(t.completed_at) as d, COUNT(t.id) as c')
            ->groupBy('d')
            ->orderBy('d')
            ->get();

        return [
            'labels' => $rows->pluck('d')->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'))->all(),
            'data'   => $rows->pluck('c')->all(),
        ];
    }

    public function statusDistribution(): array
    {
        $rows = (clone $this->baseTasks())
            ->selectRaw('t.status, COUNT(t.id) as c')
            ->groupBy('t.status')
            ->orderByDesc('c')
            ->get();

        $labels = $rows->pluck('status')->map(fn ($s) => $this->humanizeStatus($s))->all();

        return [
            'labels' => $labels,
            'data'   => $rows->pluck('c')->all(),
        ];
    }

    protected function humanizeStatus(?string $status): string
    {
        return match ($status) {
            'pending'            => 'قيد الانتظار',
            'assigned'           => 'مُسندة',
            'received'           => 'مستلمة',
            'under_review'       => 'تحت المراجعة',
            'approved'           => 'معتمدة',
            'rejected'           => 'مرفوضة',
            'in_progress'        => 'قيد التنفيذ',
            'materials_wait'     => 'انتظار خامات',
            'materials_prep'     => 'تحضير خامات',
            'materials_done'     => 'خامات مكتملة',
            'on_hold'            => 'متوقفة',
            'completed'          => 'مكتملة',
            'cancelled'          => 'ملغاة',
            'waiting_production' => 'انتظار تصنيع',
            default              => $status ?: 'غير محدد',
        };
    }

    public function topEmployeesChart(int $limit = 10): array
    {
        $rows = (clone $this->baseTasks())
            ->leftJoin('users as u', 'u.id', '=', 't.assigned_to_user_id')
            ->selectRaw('COALESCE(u.name, "غير محدد") as user_name, SUM(t.status = "completed") as completed_cnt')
            ->groupBy('user_name')
            ->orderByDesc('completed_cnt')
            ->limit($limit)
            ->get();

        return [
            'labels' => $rows->pluck('user_name')->all(),
            'data'   => $rows->pluck('completed_cnt')->map(fn ($v) => (int) $v)->all(),
        ];
    }
}
