<?php

namespace App\Services;

use App\Models\MaintenanceRequest;
use App\Models\User;
use App\Notifications\MaintenanceRequestStatusChanged;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class MaintenanceNotifier
{
    /** إنشاء طلب جديد → تنبيه مدير المصنع */
    public function notifyNewRequest(MaintenanceRequest $request): void
    {
        $actor       = Auth::user();
        $recipients  = $this->factoryManagers();

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send(
            $recipients,
            new MaintenanceRequestStatusChanged(
                request: $request,
                action: 'new_request',
                actor:  $actor,
            )
        );
    }

    /** تأكيد استلام الطلب من مدير المصنع */
    public function notifyReceiptConfirmed(MaintenanceRequest $request): void
    {
        $actor      = Auth::user();
        $recipient  = $this->createdByOrOwner($request);

        if (! $recipient) {
            return;
        }

        $recipient->notify(
            new MaintenanceRequestStatusChanged(
                request: $request,
                action: 'receipt_confirmed',
                actor:  $actor,
            )
        );
    }

    /** بدء الصيانة الفعلي */
    public function notifyStarted(MaintenanceRequest $request): void
    {
        $actor      = Auth::user();
        $recipient  = $this->createdByOrOwner($request);

        if (! $recipient) {
            return;
        }

        $recipient->notify(
            new MaintenanceRequestStatusChanged(
                request: $request,
                action: 'started',
                actor:  $actor,
            )
        );
    }

    /** إنهاء الصيانة وإغلاق الطلب */
    public function notifyCompleted(MaintenanceRequest $request): void
    {
        $actor = Auth::user();

        $targets = collect();

        if ($created = $this->createdBy($request)) {
            $targets->push($created);
        }

        if ($owner = $this->currentOwner($request)) {
            $targets->push($owner);
        }

        $targets = $targets->unique('id')->filter();

        if ($targets->isEmpty()) {
            return;
        }

        Notification::send(
            $targets,
            new MaintenanceRequestStatusChanged(
                request: $request,
                action: 'completed',
                actor:  $actor,
            )
        );
    }

    /** إضافة ملاحظة */
    public function notifyComment(MaintenanceRequest $request, string $note): void
    {
        $actor   = Auth::user();

        $targets = collect();

        if ($created = $this->createdBy($request)) {
            $targets->push($created);
        }

        if ($owner = $this->currentOwner($request)) {
            $targets->push($owner);
        }

        // لا ترسل الإشعار لنفس الشخص الذي كتب الملاحظة
        $targets = $targets
            ->filter()
            ->unique('id')
            ->reject(fn (User $u) => $actor && $u->id === $actor->id);

        if ($targets->isEmpty()) {
            return;
        }

        Notification::send(
            $targets,
            new MaintenanceRequestStatusChanged(
                request: $request,
                action: 'note_added',
                actor:  $actor,
                extra:  ['note' => $note],
            )
        );
    }

    /* ================== Helpers ================== */

    /** جميع المستخدمين الذين لهم دور factory_manager */
    protected function factoryManagers(): Collection
    {
        return User::role('factory_manager')->get();
    }

    protected function createdBy(MaintenanceRequest $request): ?User
    {
        if (! empty($request->created_by) && method_exists($request, 'createdByUser')) {
            return $request->createdByUser;
        }

        return null;
    }

    protected function currentOwner(MaintenanceRequest $request): ?User
    {
        if (! empty($request->current_owner_user_id)) {
            return User::find($request->current_owner_user_id);
        }

        return null;
    }

    protected function createdByOrOwner(MaintenanceRequest $request): \Illuminate\Contracts\Auth\Authenticatable
    {
        return $this->createdBy($request) ?? $this->currentOwner($request) ?? Auth::user();
    }
}
