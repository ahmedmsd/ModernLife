<?php

namespace App\Filament\Resources\ProductionRequestResource\Pages;

use App\Enums\{ProductionRequestPhase as Phase, PhaseStatus as S};
use App\Filament\Resources\ProductionRequestResource;
use App\Models\ProductionRequest;
use App\Services\ProductionRequestWorkflow;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;

class ReviewProductionRequest extends Page
{
    protected static string $resource = ProductionRequestResource::class;
    protected static string $view = 'filament.resources.production-request-resource.pages.review-request';
    protected static ?string $title = 'مراجعة طلب التصنيع';

    public ProductionRequest $record;

    public function mount(ProductionRequest $record): void
    {
        $this->record = $record->load(['client', 'project', 'logs', 'productionRequestFiles', 'files']);
    }

    public function getHeaderActions(): array
    {
        $rid = (string) $this->record->getKey();
        $actions = [];

        // ==== 1) تأكيد الاستلام (pending للمالك الحالي) ====
        $actions[] = Action::make('confirmReceiptAction')
            ->label('تأكيد استلامي')
            ->icon('heroicon-o-hand-thumb-up')
            ->modalHeading("تأكيد الاستلام — طلب #{$rid}")
            ->extraAttributes(['wire:key' => "confirm-receipt-{$rid}"])
            ->visible(function () {
                if (! auth()->check()) return false;
                if ($this->record->project) return false;
                if ($this->record->phase_status !== S::Pending->value) return false;

                return match ($this->record->current_phase) {
                    Phase::ShowroomReview->value => auth()->user()->hasRole('showroom_manager'),
                    Phase::FactoryIntake->value  => auth()->user()->hasRole('factory_manager'),
                    default                      => false,
                };
            })
            ->action(function () {
                app(ProductionRequestWorkflow::class)->markReceived($this->record);
                Notification::make()->success()->title('تم تأكيد الاستلام')->send();
                $this->refreshRecord();
            })
            ->after(fn () => [
                $this->dispatch('close-modal', id: 'filament.actions.modal'),
                $this->resetErrorBag(),
                $this->resetValidation(),
            ]);

        // ==== 2) بدء مراجعة المعرض ====
        $actions[] = Action::make('startShowroomReviewAction')
            ->label('بدء مراجعة المعرض')
            ->icon('heroicon-o-play-circle')
            ->color('info')
            ->modalHeading("بدء مراجعة المعرض — طلب #{$rid}")
            ->extraAttributes(['wire:key' => "start-showroom-review-{$rid}"])
            ->visible(fn () =>
                auth()->check()
                && auth()->user()->hasRole('showroom_manager')
                && $this->record->current_phase === Phase::ShowroomReview->value
                && $this->record->phase_status === S::Received->value
            )
            ->action(function () {
                app(ProductionRequestWorkflow::class)->move(
                    $this->record, Phase::ShowroomReview, S::UnderReview, 'showroom_manager', false
                );
                Notification::make()->success()->title('تم بدء مراجعة المعرض')->send();
                $this->refreshRecord();
            })
            ->after(fn () => [
                $this->dispatch('close-modal', id: 'filament.actions.modal'),
                $this->resetErrorBag(),
                $this->resetValidation(),
            ]);

        // ==== 3) تحديد تكاليف الملفات ====
        $actions[] = Action::make('setFilesCostsAction')
            ->label('تحديد تكاليف الملفات')
            ->icon('heroicon-o-currency-dollar')
            ->color('warning')
            ->modalHeading("تكاليف الملفات — طلب #{$rid}")
            ->modalWidth('3xl')
            ->extraAttributes(['wire:key' => "set-files-costs-{$rid}"])
            ->visible(fn () =>
                auth()->check()
                && auth()->user()->hasRole('showroom_manager')
                && $this->record->current_phase === Phase::ShowroomReview->value
                && $this->record->phase_status === S::UnderReview->value
            )
            ->form(function () {
                $schema = [];
                $files = $this->record->files()->with('department')->get();
                foreach ($files as $f) {
                    $schema[] = \Filament\Forms\Components\Fieldset::make("ملف: " . ($f->file_name ?? basename($f->file_path)))
                        ->schema([
                            \Filament\Forms\Components\TextInput::make("cost_{$f->id}")
                                ->label('التكلفة التقديرية')->numeric()->minValue(0)->prefix('SAR')
                                ->default($f->estimated_cost)->required(),
                            \Filament\Forms\Components\Placeholder::make("dept_{$f->id}")
                                ->label('القسم')->content($f->department->dept_name ?? '—'),
                        ])->columns(2);
                }
                return $schema ?: [\Filament\Forms\Components\Placeholder::make('no_files')->content('لا توجد ملفات.')];
            })
            ->action(function (array $data) {
                foreach ($this->record->files as $f) {
                    $k = "cost_{$f->id}";
                    if (array_key_exists($k, $data)) {
                        $f->update(['estimated_cost' => $data[$k]]);
                    }
                }
                Notification::make()->success()->title('تم حفظ تكاليف الملفات')->send();
                $this->refreshRecord();
            })
            ->after(fn () => [
                $this->dispatch('close-modal', id: 'filament.actions.modal'),
                $this->resetErrorBag(),
                $this->resetValidation(),
            ]);

        // ==== 4) اعتماد المعرض والإرسال للمصنع ====
        $actions[] = Action::make('approveShowroomAndSendAction')
            ->label('اعتماد وإرسال للمصنع')
            ->icon('heroicon-o-paper-airplane')
            ->color('success')
            ->modalHeading("اعتماد المعرض — طلب #{$rid}")
            ->extraAttributes(['wire:key' => "approve-showroom-send-{$rid}"])
            ->visible(fn () =>
                auth()->check()
                && auth()->user()->hasRole('showroom_manager')
                && $this->record->current_phase === Phase::ShowroomReview->value
                && $this->record->phase_status === S::UnderReview->value
            )
            ->action(function () {
                if ($this->record->request_type === 'indirect') {
                    $missing = $this->record->files()->whereNull('estimated_cost')->count();
                    if ($missing > 0) {
                        Notification::make()->danger()
                            ->title('غير مكتمل')->body("حدد تكلفة جميع الملفات. ناقص: {$missing}")->send();
                        return;
                    }
                }
                app(ProductionRequestWorkflow::class)->move(
                    $this->record, Phase::FactoryIntake, S::Pending, 'factory_manager', true
                );
                Notification::make()->success()->title('تم الإرسال للمصنع')->send();
                $this->refreshRecord();
            })
            ->after(fn () => [
                $this->dispatch('close-modal', id: 'filament.actions.modal'),
                $this->resetErrorBag(),
                $this->resetValidation(),
            ]);

        // ==== 5) رفض من المعرض ====
        $actions[] = Action::make('rejectByShowroomAction')
            ->label('رفض')
            ->icon('heroicon-o-x-circle')->color('danger')
            ->modalHeading("رفض الطلب — #{$rid}")
            ->extraAttributes(['wire:key' => "reject-showroom-{$rid}"])
            ->form([
                \Filament\Forms\Components\Textarea::make('reason_showroom')
                    ->label('سبب الرفض')->required()->rows(3),
            ])
            ->visible(fn () =>
                auth()->check()
                && auth()->user()->hasRole('showroom_manager')
                && $this->record->current_phase === Phase::ShowroomReview->value
                && $this->record->phase_status === S::UnderReview->value
            )
            ->action(function (array $data) {
                $reason = $data['reason_showroom'] ?? null;
                app(ProductionRequestWorkflow::class)->reject($this->record, $reason);
                Notification::make()->warning()->title('تم الرفض')->send();
                $this->refreshRecord();
            })
            ->after(fn () => [
                $this->dispatch('close-modal', id: 'filament.actions.modal'),
                $this->resetErrorBag(),
                $this->resetValidation(),
            ]);

        // ==== 6) بدء مراجعة المصنع ====
        $actions[] = Action::make('startFactoryReviewAction')
            ->label('بدء مراجعة المصنع')
            ->icon('heroicon-o-play-circle')->color('info')
            ->modalHeading("بدء مراجعة المصنع — طلب #{$rid}")
            ->extraAttributes(['wire:key' => "start-factory-review-{$rid}"])
            ->visible(fn () =>
                auth()->check()
                && auth()->user()->hasRole('factory_manager')
                && $this->record->current_phase === Phase::FactoryIntake->value
                && $this->record->phase_status === S::Received->value
            )
            ->action(function () {
                app(ProductionRequestWorkflow::class)->move(
                    $this->record, Phase::FactoryIntake, S::UnderReview, 'factory_manager', false
                );
                Notification::make()->success()->title('تم بدء مراجعة المصنع')->send();
                $this->refreshRecord();
            })
            ->after(fn () => [
                $this->dispatch('close-modal', id: 'filament.actions.modal'),
                $this->resetErrorBag(),
                $this->resetValidation(),
            ]);

        // ==== 7) اعتماد المصنع وإنشاء المشروع ====
        $actions[] = Action::make('approveFactoryAction')
            ->label('اعتماد وإنشاء project & tasks')
            ->icon('heroicon-o-check-circle')->color('primary')
            ->requiresConfirmation()
            ->modalHeading("اعتماد المصنع — طلب #{$rid}")
            ->extraAttributes(['wire:key' => "approve-factory-{$rid}"])
            ->visible(fn () =>
                auth()->check()
                && auth()->user()->hasRole('factory_manager')
                && $this->record->current_phase === Phase::FactoryIntake->value
                && $this->record->phase_status === S::UnderReview->value
            )
            ->action(function () {
                $missing = $this->record->files()->whereNull('estimated_cost')->count();
                if ($missing > 0) {
                    Notification::make()->danger()
                        ->title('لا يمكن الإنشاء')->body("يوجد {$missing} ملف بدون تكلفة.")->send();
                    return;
                }
                app(ProductionRequestWorkflow::class)->approve($this->record);
                Notification::make()->success()->title('تم إنشاء المشروع والمهام')->send();
                $this->refreshRecord();
            })
            ->after(fn () => [
                $this->dispatch('close-modal', id: 'filament.actions.modal'),
                $this->resetErrorBag(),
                $this->resetValidation(),
            ]);

        // ==== 8) رفض المصنع ====
        $actions[] = Action::make('rejectByFactoryAction')
            ->label('رفض')
            ->icon('heroicon-o-x-circle')->color('danger')
            ->modalHeading("رفض الطلب — #{$rid}")
            ->extraAttributes(['wire:key' => "reject-factory-{$rid}"])
            ->form([
                \Filament\Forms\Components\Textarea::make('reason_factory')
                    ->label('سبب الرفض')->required()->rows(3),
            ])
            ->visible(fn () =>
                auth()->check()
                && auth()->user()->hasRole('factory_manager')
                && $this->record->current_phase === Phase::FactoryIntake->value
                && $this->record->phase_status === S::UnderReview->value
            )
            ->action(function (array $data) {
                $reason = $data['reason_factory'] ?? null;
                app(ProductionRequestWorkflow::class)->reject($this->record, $reason);
                Notification::make()->warning()->title('تم الرفض')->send();
                $this->refreshRecord();
            })
            ->after(fn () => [
                $this->dispatch('close-modal', id: 'filament.actions.modal'),
                $this->resetErrorBag(),
                $this->resetValidation(),
            ]);

        return $actions;
    }

    private function refreshRecord(): void
    {
        $this->record->refresh()->load(['client', 'project', 'logs', 'productionRequestFiles', 'files']);
    }
}
