<?php

namespace App\Support\Reports;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Database\Query\Builder;
use App\Enums\TaskStatus;

class ReportService
{
    public function __construct(protected ReportFilters $f) {}

    protected array $DONE = ['completed','closed','cancelled'];

    protected function baseTasks(): Builder
    {
        return DB::table('production_tasks as t')
            ->when($this->f->dateFrom,   fn($q) => $q->whereDate('t.created_at', '>=', $this->f->dateFrom))
            ->when($this->f->dateTo,     fn($q) => $q->whereDate('t.created_at', '<=', $this->f->dateTo))
            ->when($this->f->deptId,     fn($q) => $q->where('t.department_id', $this->f->deptId))
            ->when($this->f->employeeId, fn($q) => $q->where('t.assigned_to_employee_id', $this->f->employeeId))
            ->when($this->f->status,     fn($q) => $q->where('t.status', $this->f->status));
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
            ->where(function($q) {
                $q->where(function($qq){
                    $qq->whereNotNull('t.completed_at')
                        ->whereRaw('t.completed_at > t.planned_end_at');
                })->orWhere(function($qq){
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
            'labels' => $rows->pluck('d')->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))->all(),
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
        return [
            'labels' => $rows->pluck('status')->map(fn($s) => $s ?: 'غير محدد')->all(),
            'data'   => $rows->pluck('c')->all(),
        ];
    }

    public function topEmployeesChart(int $limit = 10): array
    {
        $rows = (clone $this->baseTasks())
            ->leftJoin('employees as e', 'e.employee_id', '=', 't.assigned_to_employee_id')
            ->selectRaw('COALESCE(e.employee_name, "غير محدد") as emp, SUM(t.status = "completed") as completed_cnt')
            ->groupBy('emp')
            ->orderByDesc('completed_cnt')
            ->limit($limit)
            ->get();

        return [
            'labels' => $rows->pluck('emp')->all(),
            'data'   => $rows->pluck('completed_cnt')->map(fn($v) => (int)$v)->all(),
        ];
    }
}
