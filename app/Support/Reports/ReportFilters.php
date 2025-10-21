<?php

namespace App\Support\Reports;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class ReportFilters
{
    public array $data = [];

    public function __construct(array $data = [])
    {
        $this->data = $this->sanitize($data);
    }

    public static function fromArray(array $data = []): self
    {
        return new self($data);
    }

    public static function fromRequest(?Request $request = null): self
    {
        $r = $request ?: request();

        $data = [
            'date_from'   => $r->input('date_from'),
            'date_to'     => $r->input('date_to'),
            'branch_id'   => $r->input('branch_id'),
            'dept_id'     => $r->input('dept_id'),
            'employee_id' => $r->input('employee_id'),
            'status'      => $r->input('status'),
        ];

        return new self($data);
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function apply(Builder $query, string $tasksTable = 'production_tasks'): Builder
    {
        $d = $this->data;

        if (! empty($d['date_from']) && ! empty($d['date_to'])) {
            $query->whereBetween($tasksTable . '.created_at', [
                $d['date_from']->toDateTimeString(),
                $d['date_to']->toDateTimeString(),
            ]);
        } else {
            $query
                ->when($d['date_from'] ?? null, fn ($q, $v) => $q->where($tasksTable . '.created_at', '>=', $v->toDateTimeString()))
                ->when($d['date_to'] ?? null,   fn ($q, $v) => $q->where($tasksTable . '.created_at', '<=', $v->toDateTimeString()));
        }


        return $query
            ->when($d['branch_id']   ?? null, fn ($q, $v) => $q->where('showroom_id', $v))
            ->when($d['dept_id']     ?? null, fn ($q, $v) => $q->where('department_id', $v))
            ->when($d['employee_id'] ?? null, fn ($q, $v) => $q->where('assigned_to_employee_id', $v))
            ->when($d['status']      ?? null, fn ($q, $v) => $q->where($tasksTable . '.status', $v));
    }

    /* ===================== Helpers ===================== */

    private function sanitize(array $data): array
    {
        $out = [];

        $out['date_from']   = $this->parseDate($data['date_from'] ?? null)?->startOfDay();
        $out['date_to']     = $this->parseDate($data['date_to'] ?? null)?->endOfDay();
        $out['branch_id']   = $this->nullIfEmpty($data['branch_id']   ?? null);
        $out['dept_id']     = $this->nullIfEmpty($data['dept_id']     ?? null);
        $out['employee_id'] = $this->nullIfEmpty($data['employee_id'] ?? null);
        $out['status']      = $this->nullIfEmpty($data['status']      ?? null);

        return $out;
    }

    private function parseDate($v): ?Carbon
    {
        if (empty($v)) return null;
        if ($v instanceof Carbon) return $v;

        try { return Carbon::parse($v); }
        catch (\Throwable) { return null; }
    }

    private function nullIfEmpty($v)
    {
        return ($v === '' || $v === false) ? null : $v;
    }

    /* ========= BC helpers: accessors & magic properties ========= */

    public function dateFrom(): ?Carbon   { return $this->data['date_from']   ?? null; }
    public function dateTo(): ?Carbon     { return $this->data['date_to']     ?? null; }
    public function branchId(): ?int      { return $this->data['branch_id']   ?? null; }
    public function deptId(): ?int        { return $this->data['dept_id']     ?? null; }
    public function employeeId(): ?int    { return $this->data['employee_id'] ?? null; }
    public function status(): ?string     { return $this->data['status']      ?? null; }

    public function __get(string $name)
    {
        $snake = $this->camelToSnake($name);
        return $this->data[$snake] ?? null;
    }

    private function camelToSnake(string $name): string
    {
        $snake = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $name));
        return $snake;
    }
}
