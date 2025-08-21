<?php

namespace App\Filament\Resources\ProductionRequestResource\Pages;

use App\Enums\{ProductionRequestPhase as Phase, PhaseStatus as S};
use App\Filament\Resources\ProductionRequestResource;
use App\Models\ProductionRequest;
use App\Services\ProductionRequestWorkflow;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;

class ReviewProductionRequest extends Page
{
    protected static string $resource = ProductionRequestResource::class;
    protected static string $view = 'filament.resources.production-request-resource.pages.review-request';
    protected static ?string $title = 'مراجعة طلب التصنيع';

    public ProductionRequest $record;

    public function mount(ProductionRequest $record): void
    {
        $this->record = $record->load(['client','project','logs','productionRequestFiles']);
    }

    public function getHeaderActions(): array
    {
        $cfg = config('production_workflow');
        $actions = [];

        foreach ($cfg['actions'] as $def) {
            if (! $this->shouldShow($def)) continue;

            $actions[] = $this->makeActionFromDefinition($def);
        }

        return $actions;
    }

    /* ---------------- Visibility & Role checks ---------------- */

    private function shouldShow(array $def): bool
    {
        // Role gate
        if (! $this->userAllowed($def['roles'] ?? [])) return false;

        // Phase/Status conditions
        $phase = $this->record->current_phase;
        $status = $this->record->phase_status;

        foreach (($def['when'] ?? []) as $cond) {
            if (isset($cond['phase'])      && $phase !== $cond['phase']) return false;
            if (isset($cond['phase_in'])   && !in_array($phase, (array)$cond['phase_in'], true)) return false;

            if (isset($cond['status'])     && $status !== $cond['status']) return false;
            if (isset($cond['status_in'])  && !in_array($status, (array)$cond['status_in'], true)) return false;
            if (isset($cond['status_not_in']) && in_array($status, (array)$cond['status_not_in'], true)) return false;
        }
        return true;
    }

    private function userAllowed(array $roles): bool
    {
        $user = Auth::user();
        if (! $user) return false;

        // admins can do everything
        if ($user->hasAnyRole(['admin', 'super-admin'])) return true;

        foreach ($roles as $r) {
            if ($r === '*owner*') {
                $owner = $this->record->current_owner_role;
                if ($owner && $user->hasRole($owner)) return true;
            } else {
                if ($user->hasRole($r)) return true;
            }
        }
        return false;
    }

    /* ---------------- Action builder ---------------- */

    private function makeActionFromDefinition(array $def): Action
    {
        $action = Action::make($def['key'])
            ->label($def['label'] ?? $def['key'])
            ->icon($def['icon'] ?? 'heroicon-o-bolt')
            ->color($def['color'] ?? 'primary')
            ->requiresConfirmation();

        $type = $def['type'] ?? 'status';

        if ($type === 'receive') {
            $action->action(function () {
                app(ProductionRequestWorkflow::class)->markReceived($this->record);
                Notification::make()->success()->title('تم تأكيد الاستلام')->send();
                $this->refreshRecord();
            });
        }
        elseif ($type === 'reject') {
            $action
                ->form([
                    \Filament\Forms\Components\Textarea::make('reason')->label('سبب الرفض')->required()->rows(3),
                ])
                ->action(function (array $data) {
                    app(ProductionRequestWorkflow::class)->reject($this->record, $data['reason'] ?? null);
                    Notification::make()->warning()->title('تم الرفض')->send();
                    $this->refreshRecord();
                });
        }
        elseif ($type === 'reject_and_back') {
            $action
                ->form([
                    \Filament\Forms\Components\Textarea::make('reason')->label('سبب الرفض')->required()->rows(3),
                ])
                ->action(function (array $data) use ($def) {
                    // 1) reject in current phase
                    app(ProductionRequestWorkflow::class)->reject($this->record, $data['reason'] ?? null);
                    // 2) move back to target (usually manufacturing/installation)
                    $to = $def['to'] ?? [];
                    app(ProductionRequestWorkflow::class)->move(
                        $this->record,
                        Phase::from($to['phase']),
                        S::from($to['status']),
                        $to['owner'] ?? null,
                        (bool)($to['touch_sent'] ?? false)
                    );
                    Notification::make()->warning()->title('تم الرفض والرجوع للمرحلة السابقة')->send();
                    $this->refreshRecord();
                });
        }
        elseif ($type === 'transition') {
            $action->action(function () use ($def) {
                $to = $def['to'] ?? [];
                app(ProductionRequestWorkflow::class)->move(
                    $this->record,
                    Phase::from($to['phase']),
                    S::from($to['status']),
                    $to['owner'] ?? null,
                    (bool)($to['touch_sent'] ?? false)
                );
                Notification::make()->success()->title('تم الإرسال للمرحلة التالية')->send();
                $this->refreshRecord();
            });
        }
        else { // 'status'
            $action->action(function () use ($def) {
                $toStatus = $def['to']['status'] ?? S::UnderReview->value;
                app(ProductionRequestWorkflow::class)->move(
                    $this->record,
                    Phase::from($this->record->current_phase),
                    S::from($toStatus),
                    $this->record->current_owner_role,
                    false
                );
                Notification::make()->success()->title('تم تحديث الحالة')->send();
                $this->refreshRecord();
            });
        }

        return $action;
    }

    private function refreshRecord(): void
    {
        $this->record->refresh()->load(['client','project','logs','productionRequestFiles']);
    }
}
