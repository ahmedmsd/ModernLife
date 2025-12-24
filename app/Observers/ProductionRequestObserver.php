<?php

namespace App\Observers;

use App\Models\ProductionRequest;
use App\Models\User;
use App\Notifications\oldPReq\ProductionRequestCreated;
use App\Services\ProductionRequestWorkflow;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Facades\Notification;

class ProductionRequestObserver
{

    public function created(ProductionRequest $pr): void
    {
        app(ProductionRequestWorkflow::class)->start($pr->fresh());

//        $pr = app(ProductionRequestWorkflow::class)->start($pr->fresh());
//
//        $recipients = $this->recipientsOnCreateQuery($pr)
//            ->get()
//            ->unique('id')
//            ->values();
//
//        if ($recipients->isNotEmpty()) {
//            Notification::send($recipients, new ProductionRequestCreated($pr));
//        }
    }


    protected function recipientsOnCreateQuery(ProductionRequest $pr): EloquentBuilder
    {
        $guard = 'web';

        if (($pr->request_type ?? null) === 'direct') {
            return User::query()->role('factory_manager', $guard);
        }

        if ($pr->showroom?->manager?->user) {
            return User::query()->whereKey($pr->showroom->manager->user->getKey());
        }

        $q = User::query()->role('showroom_manager', $guard);

        if ($pr->showroom_id) {
            $q->whereHas('managedShowrooms', fn ($sub) => $sub->where('id', $pr->showroom_id));
        }

        return $q;
    }
}
