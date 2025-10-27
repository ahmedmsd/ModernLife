<?php

// app/Services/DprWorkflow.php
namespace App\Services;

use App\Models\DepartmentPurchaseRequest;
use App\Models\DepartmentPurchaseLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\DprStatusChanged;

class DprWorkflow
{
    public function log(DepartmentPurchaseRequest $dpr, string $action, ?string $note = null, array $data = []): void
    {
        DepartmentPurchaseLog::create([
            'request_id' => $dpr->id,
            'causer_id'  => Auth::id(),
            'action'     => $action,
            'data'       => $data,
            'note'       => $note,
            'created_at' => now(),
        ]);
    }

    public function setStatus(DepartmentPurchaseRequest $dpr, string $status, ?string $note = null): void
    {
        $dpr->status = $status;

        match ($status) {
            'submitted_to_factory' => $dpr->submitted_at = now(),
            'factory_approved'     => $dpr->factory_approved_at = now(),
            'factory_rejected'     => $dpr->factory_rejected_at = now(),
            'sent_to_purchasing'   => $dpr->sent_to_purchasing_at = now(),
            'purchased'            => $dpr->purchased_at = now(),
            'delivered'            => $dpr->delivered_at = now(),
            default                => null,
        };

        $dpr->save();
        $this->log($dpr, $status, $note);

        $recipients = collect();
        switch ($status) {
            case 'submitted_to_factory':
                $recipients = $this->usersWithRole('factory_manager');
                break;
            case 'factory_approved':
                $recipients = $this->usersWithRole('purchasing_manager')->merge([$dpr->requester]);
                break;
            case 'factory_rejected':
                $recipients = collect([$dpr->requester]);
                break;
            case 'sent_to_purchasing':
            case 'purchased':
            case 'delivered':
                $recipients = collect([$dpr->requester])->merge($this->usersWithRole('department_manager'));
                break;
        }

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients->unique('id')->all(), new DprStatusChanged($dpr, $status, $note));
        }
    }

    protected function usersWithRole(string $role)
    {
        return \App\Models\User::role($role)->get(); // Spatie
    }
}


