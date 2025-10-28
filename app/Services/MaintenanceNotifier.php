<?php


namespace App\Services;

use App\Models\MaintenanceRequest;
use App\Models\User;
use App\Notifications\MaintenanceRequestStatusChanged;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class MaintenanceNotifier
{
    /** إنشاء طلب جديد → factory_manager فقط */
    public function notifyNewRequest(MaintenanceRequest $request): void
    {
        $this->sendToFactoryManagers($request, 'new_request');
    }

    /** إغلاق الطلب → صاحب الطلب فقط */
    public function notifyCompletedToOwner(MaintenanceRequest $request, ?string $note = null): void
    {
        if ($owner = $this->resolveOwner($request)) {
            Notification::send([$owner], new MaintenanceRequestStatusChanged($request, 'completed', $note));
        }
    }

    public function notifyComment(MaintenanceRequest $request, string $note): void
    {
        $actor = Auth::user();
        if ($this->isFactoryManager($actor)) {
            if ($owner = $this->resolveOwner($request, fallbackToActor: false)) {
                Notification::send([$owner], new MaintenanceRequestStatusChanged($request, 'note_added', $note));
            }
            return;
        }
        $this->sendToFactoryManagers($request, 'note_added', $note);
    }

    public function notifyStatusChange(MaintenanceRequest $request, string $action, ?string $note = null): void
    {
        $actor = Auth::user();
        if ($this->isFactoryManager($actor)) {
            if ($owner = $this->resolveOwner($request, fallbackToActor: false)) {
                Notification::send([$owner], new MaintenanceRequestStatusChanged($request, $action, $note));
            }
            return;
        }
        $this->sendToFactoryManagers($request, $action, $note);
    }

    /* ================= Helpers ================= */

    protected function sendToFactoryManagers(MaintenanceRequest $request, string $action, ?string $note = null): void
    {
        $recipients = User::role('factory_manager')->get();
        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new MaintenanceRequestStatusChanged($request, $action, $note));
        }
    }

    protected function resolveOwner(MaintenanceRequest $request, bool $fallbackToActor = true): ?\Illuminate\Contracts\Auth\Authenticatable
    {
        if (method_exists($request, 'createdByUser') && $request->createdByUser) {
            return $request->createdByUser;
        }
        // 2) current_owner_user_id
        if (!empty($request->current_owner_user_id)) {
            return User::find($request->current_owner_user_id);
        }
        return $fallbackToActor ? Auth::user() : null;
    }

    protected function isFactoryManager(?User $user): bool
    {
        return $user?->hasRole('factory_manager') ?? false;
    }
}
