<?php


namespace App\Observers;

use App\Models\Employee;

class EmployeeObserver
{
    public function created(Employee $employee): void
    {
        if ($employee->user && filled($employee->employee_name)) {
            $employee->user->forceFill(['name' => $employee->employee_name])->saveQuietly();
        }
    }

    public function updated(Employee $employee): void
    {
        $nameChanged = $employee->wasChanged('employee_name');
        $userChanged = $employee->wasChanged('user_id');

        if (($nameChanged || $userChanged) && $employee->user && filled($employee->employee_name)) {
            $employee->user->forceFill(['name' => $employee->employee_name])->saveQuietly();
        }
    }
}
