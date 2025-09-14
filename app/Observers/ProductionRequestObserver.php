<?php

namespace App\Observers;

use App\Models\ProductionRequest;
use App\Models\ProductionRequestLog;
use App\Models\User;
use App\Notifications\ProductionRequestCreated;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use App\Services\ProductionRequestWorkflow;
use Illuminate\Support\Facades\Notification;
use Filament\Notifications\Notification as FNotification;
use Filament\Notifications\Actions\Action as FAction;

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

        $recipients = collect();

        if ($pr->request_type === 'direct') {
            $recipients = User::role('factory_manager')->get();
        } else { // indirect
            $manager = $pr->showroom?->manager;
            if ($manager) {
                $recipients = collect([$manager]);
            }
        }

        if ($recipients instanceof Collection && $recipients->isNotEmpty()) {
            $pr  = $pr ?? $pr;
            $url = \App\Filament\Resources\ProductionRequestResource::getUrl('review', ['record' => $pr->getKey()]);
            $title = $pr->request_type === 'direct' ? 'طلب تصنيع مباشر' : 'طلب تصنيع غير مباشر';
            $body  = 'رقم الطلب: #' . $pr->id . ($pr->project_name ? ' • ' . $pr->project_name : '');

            FNotification::make()
                ->title($title)
                ->body($body)
                ->icon('heroicon-o-briefcase')
                ->success()
                ->actions([
                    FAction::make('عرض الطلب')->button()->url($url),
                ])
                ->sendToDatabase($recipients);
        }
    }

    public function updated(ProductionRequest $pr): void
    {
        $workflowFields = [
            'current_phase', 'phase_status',
            'current_owner_role', 'current_owner_user_id',
            'sent_to_owner_at', 'received_by_owner_at',
        ];

        $hasTransition = collect($workflowFields)->some(fn ($f) => $pr->wasChanged($f));
        if ($hasTransition) {
            \App\Models\ProductionRequestLog::create([
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
                'causer_id'   => \Illuminate\Support\Facades\Auth::id(),
                'happened_at' => now(),
            ]);
        }

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

        $contentChanged = collect($contentFields)->some(fn ($f) => $pr->wasChanged($f));

        if ($contentChanged) {
            app(\App\Services\ProductionRequestWorkflow::class)->routeForReReview($pr);
            return;
        }

        $becameApprovedByFactory =
            $pr->wasChanged('phase_status')
            && ((string) $pr->phase_status === 'approved')
            && ((string) $pr->current_owner_role === 'factory_manager');

        if ($becameApprovedByFactory) {
            app(\App\Services\ProductionRequestWorkflow::class)->approve($pr);
            return;
        }
    }


    /** لوج حذف */
    public function deleting(\App\Models\ProductionRequest $pr): void
    {
        \App\Models\ProductionRequestLog::create([
            'production_request_id' => $pr->id,
            'type'        => 'deleted',
            'data'        => null,
            'note'        => 'تم حذف الطلب',
            'causer_id'   => \Illuminate\Support\Facades\Auth::id(),
            'happened_at' => now(),
        ]);
    }
}
