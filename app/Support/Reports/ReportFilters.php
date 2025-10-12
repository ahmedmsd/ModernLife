<?php

namespace App\Support\Reports;

use Carbon\Carbon;

class ReportFilters
{
    public ?string $dateFrom;
    public ?string $dateTo;
    public ?int $branchId;
    public ?int $deptId;
    public ?int $employeeId;
    public ?string $status;

    public function __construct(array $q)
    {
        $this->dateFrom   = $q['date_from']   ?? null;
        $this->dateTo     = $q['date_to']     ?? null;
        $this->branchId   = isset($q['branch_id']) ? (int)$q['branch_id'] : null; // إن وجِد لديك
        $this->deptId     = isset($q['dept_id']) ? (int)$q['dept_id'] : null;
        $this->employeeId = isset($q['employee_id']) ? (int)$q['employee_id'] : null;
        $this->status     = $q['status'] ?? null;

        if (!$this->dateFrom || !$this->dateTo) {
            $this->dateTo   = Carbon::now()->toDateString();
            $this->dateFrom = Carbon::now()->subDays(30)->toDateString();
        }
    }

    public static function fromRequest(): self
    {
        return new self(request()->query());
    }
}
