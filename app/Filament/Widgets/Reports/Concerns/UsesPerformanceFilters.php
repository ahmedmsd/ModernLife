<?php

namespace App\Filament\Widgets\Reports\Concerns;

use Livewire\Attributes\On;

trait UsesPerformanceFilters
{
    public array $filters = [
        'date_from'   => null,
        'date_to'     => null,
        'branch_id'   => null,
        'dept_id'     => null,
        'employee_id' => null,
        'status'      => null,
    ];

    #[On('pd.filters.updated')]
    public function onFiltersUpdated(array $filters): void
    {
        $this->filters = array_merge($this->filters, $filters ?? []);
        $this->dispatch('$refresh');
    }

    protected function applyCommonFilters($query)
    {
        return $query
            ->when($this->filters['date_from'], fn($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($this->filters['date_to'],   fn($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->when($this->filters['branch_id'], fn($q, $v) => $q->whereHas('project.showroom', fn($qq)=>$qq->where('id', $v)))
            ->when($this->filters['dept_id'],   fn($q, $v) => $q->where('department_id', $v))
            ->when($this->filters['employee_id'], fn($q, $v) => $q->where('assigned_to_user_id', $v))
            ->when($this->filters['status'],    fn($q, $v) => $q->where('status', $v));
    }

    protected function palette(int $n): array
    {
        $base = [
            [59,130,246],  // blue-500
            [16,185,129],  // emerald-500
            [245,158,11],  // amber-500
            [236,72,153],  // pink-500
            [99,102,241],  // indigo-500
            [249,115,22],  // orange-500
            [14,165,233],  // sky-500
            [34,197,94],   // green-500
            [168,85,247],  // purple-500
            [239,68,68],   // red-500
        ];
        $bg = $border = [];
        for ($i=0; $i<$n; $i++) {
            $rgb = $base[$i % count($base)];
            $bg[]     = "rgba({$rgb[0]},{$rgb[1]},{$rgb[2]},0.25)";
            $border[] = "rgba({$rgb[0]},{$rgb[1]},{$rgb[2]},0.9)";
        }
        return ['bg'=>$bg, 'border'=>$border];
    }
}
