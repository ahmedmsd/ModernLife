<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class EmployeesByDepartmentDoughnut extends ChartWidget
{
    protected static ?string $heading = 'توزيع الموظفين حسب الأقسام';
    protected static ?string $maxHeight = '320px';
    protected int|string|array $columnSpan = ['default' => 1, 'lg' => 1];
    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $rows = DB::table('departments as d')
            ->leftJoin('employees as e', 'e.department_id', '=', 'd.dept_id')
            ->selectRaw('d.dept_name, COUNT(e.employee_id) as employees_count')
            ->groupBy('d.dept_id','d.dept_name')
            ->orderByDesc('employees_count')
            ->get();

        $labels = $rows->pluck('dept_name')->all();
        $data   = $rows->pluck('employees_count')->map(fn($v) => (int)$v)->all();

        if (empty($data) || array_sum($data) === 0) {
            $labels = ['لا بيانات'];
            $data   = [1];
        }

        return [
            'datasets' => [[ 'label' => 'عدد الموظفين', 'data' => $data ]],
            'labels'   => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => ['position' => 'bottom'],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
