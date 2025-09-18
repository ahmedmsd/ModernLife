<?php

namespace App\Observers;

use App\Models\ProductionRequest;
use App\Models\ProductionRequestLog;
use App\Models\User;
use App\Notifications\ProductionRequestCreated;
use App\Notifications\ProductionRequestStatusChanged;
use App\Notifications\ProductionRequestUpdated;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class ProductionRequestObserver
{
    public function created(ProductionRequest $pr): void
    {


        ProductionRequestLog::create([
            'production_request_id' => $pr->id,
            'type'        => 'created',
            'data'        => [
                'phase'      => $pr->current_phase,
                'status'     => $pr->phase_status,
                'owner_role' => $pr->current_owner_role,
                'owner_user' => $pr->current_owner_user_id,
            ],
            'note'        => 'تم إنشاء الطلب',
            'causer_id'   => Auth::id(),
            'happened_at' => now(),
        ]);

        $recipients = $this->recipientsOnCreate($pr);

        if ($recipients->isNotEmpty()) {
            $this->send($recipients, new ProductionRequestCreated($pr));
        }
    }

    public function updated(ProductionRequest $pr): void
    {
        $workflowFields = [
            'current_phase',
            'phase_status',
            'current_owner_role',
            'current_owner_user_id',
            'sent_to_owner_at',
            'received_by_owner_at',
        ];

        $hasTransition = collect($workflowFields)->some(fn ($f) => $pr->wasChanged($f));

        if ($hasTransition) {
            // لوج الانتقال
            ProductionRequestLog::create([
                'production_request_id' => $pr->id,
                'type'        => 'transition',
                'data'        => [
                    'from' => [
                        'phase'      => $pr->getOriginal('current_phase'),
                        'status'     => $pr->getOriginal('phase_status'),
                        'owner_role' => $pr->getOriginal('current_owner_role'),
                        'owner_user' => $pr->getOriginal('current_owner_user_id'),
                        'sent_at'    => $pr->getOriginal('sent_to_owner_at'),
                        'recv_at'    => $pr->getOriginal('received_by_owner_at'),
                    ],
                    'to' => [
                        'phase'      => $pr->current_phase,
                        'status'     => $pr->phase_status,
                        'owner_role' => $pr->current_owner_role,
                        'owner_user' => $pr->current_owner_user_id,
                        'sent_at'    => $pr->sent_to_owner_at,
                        'recv_at'    => $pr->received_by_owner_at,
                    ],
                ],
                'note'        => sprintf(
                    'Phase: %s → %s | Status: %s → %s | Owner: %s → %s',
                    $pr->getOriginal('current_phase') ?? '—',
                    $pr->current_phase ?? '—',
                    $pr->getOriginal('phase_status') ?? '—',
                    $pr->phase_status ?? '—',
                    $pr->getOriginal('current_owner_role') ?? '—',
                    $pr->current_owner_role ?? '—',
                ),
                'causer_id'   => Auth::id(),
                'happened_at' => now(),
            ]);

            // إشعار تغيّر الحالة (يشمل الرفض مع السبب)
            if ($pr->wasChanged('phase_status')) {
                $from   = (string) $pr->getOriginal('phase_status');
                $to     = (string) $pr->phase_status;
                $reason = null;

                if ($to === 'rejected') {
                    $reason = $pr->rejection_reason
                        ?? $pr->status_note
                        ?? $pr->reject_reason
                        ?? null;
                }

                $recipients = $this->recipientsForCurrentOwner($pr);
                if ($recipients->isNotEmpty()) {
                    $this->send($recipients, new ProductionRequestStatusChanged($pr, $from, $to, $reason));
                }
            }

            // موافقة المصنع بعد الانتقال
            $becameApprovedByFactory =
                $pr->wasChanged('phase_status')
                && ((string) $pr->phase_status === 'approved')
                && ((string) $pr->current_owner_role === 'factory_manager');

            if ($becameApprovedByFactory) {
                app(\App\Services\ProductionRequestWorkflow::class)->approve($pr);
            }

            return; // انتهى فرع الانتقال
        }

        // تعديل محتوى بدون انتقال
        $contentFields = [
            'client_id',
            'project_name',
            'request_type',
            'showroom_id',
            'agreement_file',
            'project_description',
            'description',
            'price',
            'budget_cap',
        ];
        $contentChanged = collect($contentFields)->filter(fn ($f) => $pr->wasChanged($f));

        if ($contentChanged->isNotEmpty()) {
            app(\App\Services\ProductionRequestWorkflow::class)->routeForReReview($pr);

            $recipients = $this->recipientsForCurrentOwner($pr->fresh());
            if ($recipients->isNotEmpty()) {
                $this->send($recipients, new ProductionRequestUpdated($pr->fresh(), $contentChanged->values()->all()));
            }
        }
    }

    public function deleting(ProductionRequest $pr): void
    {
        ProductionRequestLog::create([
            'production_request_id' => $pr->id,
            'type'        => 'deleted',
            'data'        => null,
            'note'        => 'تم حذف الطلب',
            'causer_id'   => Auth::id(),
            'happened_at' => now(),
        ]);
    }

    /* ---------------------- Recipients helpers ---------------------- */

    protected function recipientsOnCreate(ProductionRequest $pr): Collection
    {
        if (($pr->request_type ?? null) === 'direct') {
            return User::role('factory_manager')->get();
        }

        // مدير المعرض كـ User
        if ($pr->showroom?->manager?->user) {
            return collect([$pr->showroom->manager->user]);
        }

        if (method_exists(User::class, 'showrooms') && $pr->showroom_id) {
            return User::role('showroom_manager')
                ->whereHas('showrooms', fn ($q) => $q->whereKey($pr->showroom_id))
                ->get();
        }

        return User::role('showroom_manager')->get();
    }

    protected function recipientsForCurrentOwner(ProductionRequest $pr): Collection
    {
        if ($pr->current_owner_user_id) {
            $u = User::find($pr->current_owner_user_id);
            return $u ? collect([$u]) : collect();
        }

        $role = (string) ($pr->current_owner_role ?? '');

        if ($role === 'factory_manager') {
            return User::role('factory_manager')->get();
        }

        if ($role === 'showroom_manager') {
            if ($pr->showroom?->manager?->user) {
                return collect([$pr->showroom->manager->user]); // Employee->user
            }

            if (method_exists(User::class, 'showrooms') && $pr->showroom_id) {
                return User::role('showroom_manager')
                    ->whereHas('showrooms', fn ($q) => $q->whereKey($pr->showroom_id))
                    ->get();
            }

            return User::role('showroom_manager')->get();
        }

        return collect();
    }

    protected function normalizeRecipients(Collection $recipients): Collection
    {
        // يحوّل أي Employee إلى User مرتبط، ويحذف المكررات
        return $recipients
            ->map(function ($r) {
                if ($r instanceof User) return $r;
                if (is_object($r) && method_exists($r, 'user') && $r->user) {
                    return $r->user;
                }
                return null;
            })
            ->filter()
            ->unique('id')
            ->values();
    }

    protected function send(
        \Illuminate\Support\Collection $recipients,
        \Illuminate\Notifications\Notification $notification,
        bool $allowSelf = true // ← السماح بإشعار الذات افتراضيًا
    ): void {
        $actorId = \Illuminate\Support\Facades\Auth::id();

        $recipients = $this->normalizeRecipients($recipients)
            ->unique('id')
            ->values();

        if (! $allowSelf && $actorId) {
            $recipients = $recipients->where('id', '!=', $actorId);
        }

        if ($recipients->isEmpty()) {
            \Log::debug('PR notify skipped: empty recipients', [
                'notification' => class_basename($notification),
                'actor' => $actorId,
            ]);
            return;
        }

        \Illuminate\Support\Facades\Notification::send($recipients, $notification);
    }
}
