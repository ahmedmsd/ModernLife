<?php

namespace App\Filament\Resources\ProductionRequestResource\Pages;

use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Placeholder;
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
        $this->record = $record->load(['client','project','logs','productionRequestFiles','files']);
    }

    public function getHeaderActions(): array
    {
        $rid = (string) $this->record->getKey();
        $actions = [];

        // ==== 1) تأكيد الاستلام (يظهر فقط عند pending) ====
        $actions[] = Action::make('confirmReceiptAction')
            ->label('تأكيد استلامي')
            ->icon('heroicon-o-hand-thumb-up')
            ->modalHeading("تأكيد الاستلام — طلب #{$rid}")
            ->extraAttributes(['wire:key' => "confirm-receipt-{$rid}"])
            ->visible(fn () =>
                auth()->user()?->hasRole($this->record->current_owner_role)
                && $this->record->phase_status === S::Pending->value // <-- القيد الأهم
            )
            ->action(function () {
                app(ProductionRequestWorkflow::class)->markReceived($this->record);
                \Filament\Notifications\Notification::make()->success()->title('تم تأكيد الاستلام')->send();
                $this->refreshRecord();
            })
            ->after(fn () => [$this->resetErrorBag(), $this->resetValidation()]);

        // ==== 2) بدء مراجعة المعرض (يظهر فقط عند received وفي مرحلة المعرض) ====
        $actions[] = Action::make('startShowroomReviewAction')
            ->label('بدء مراجعة المعرض')
            ->icon('heroicon-o-play-circle')
            ->color('info')
            ->modalHeading("بدء مراجعة المعرض — طلب #{$rid}")
            ->extraAttributes(['wire:key' => "start-showroom-review-{$rid}"])
            ->visible(fn () =>
                auth()->user()?->hasRole('showroom_manager')
                && $this->record->current_phase === Phase::ShowroomReview->value
                && $this->record->phase_status === S::Received->value
            )
            ->action(function () {
                app(\App\Services\ProductionRequestWorkflow::class)->move(
                    $this->record, Phase::ShowroomReview, S::UnderReview, 'showroom_manager', false
                );
                \Filament\Notifications\Notification::make()->success()->title('تم بدء مراجعة المعرض')->send();
                $this->refreshRecord();
            })
            ->after(fn () => [$this->resetErrorBag(), $this->resetValidation()]);

        // ==== 3) تحديد تكاليف الملفات (أثناء مراجعة المعرض فقط) ====
        $actions[] = Action::make('setFilesCostsAction')
            ->label('تحديد تكاليف الملفات')
            ->icon('heroicon-o-currency-dollar')
            ->color('warning')
            ->modalHeading("تكاليف الملفات — طلب #{$rid}")
            ->modalWidth('3xl')
            ->extraAttributes(['wire:key' => "set-files-costs-{$rid}"])
            ->visible(fn () =>
                auth()->user()?->hasRole('showroom_manager')
                && $this->record->current_phase === Phase::ShowroomReview->value
                && $this->record->phase_status === S::UnderReview->value // <-- فقط أثناء المراجعة
            )
            ->form(function () {
                $schema = [];
                $files = $this->record->files()->with('department')->get();
                foreach ($files as $f) {
                    $schema[] = \Filament\Forms\Components\Fieldset::make("ملف: ".($f->file_name ?? basename($f->file_path)))
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
                    if (array_key_exists($k, $data)) $f->update(['estimated_cost' => $data[$k]]);
                }
                \Filament\Notifications\Notification::make()->success()->title('تم حفظ تكاليف الملفات')->send();
                $this->refreshRecord();
            })
            ->after(fn () => [$this->resetErrorBag(), $this->resetValidation()]);

        // ==== 4) اعتماد المعرض والإرسال للمصنع (فقط أثناء under_review) ====
        $actions[] = Action::make('approveShowroomAndSendAction')
            ->label('اعتماد وإرسال للمصنع')
            ->icon('heroicon-o-paper-airplane')
            ->color('success')
            ->modalHeading("اعتماد المعرض — طلب #{$rid}")
            ->extraAttributes(['wire:key' => "approve-showroom-send-{$rid}"])
            ->visible(fn () =>
                auth()->user()?->hasRole('showroom_manager')
                && $this->record->current_phase === Phase::ShowroomReview->value
                && $this->record->phase_status === S::UnderReview->value
            )
            ->action(function () {
                if ($this->record->request_type === 'indirect') {
                    $missing = $this->record->files()->whereNull('estimated_cost')->count();
                    if ($missing > 0) {
                        \Filament\Notifications\Notification::make()->danger()
                            ->title('غير مكتمل')->body("حدد تكلفة جميع الملفات. ناقص: {$missing}")->send();
                        return;
                    }
                }
                app(\App\Services\ProductionRequestWorkflow::class)->move(
                    $this->record, Phase::FactoryIntake, S::Pending, 'factory_manager', true
                );
                \Filament\Notifications\Notification::make()->success()->title('تم الإرسال للمصنع')->send();
                $this->refreshRecord();
            })
            ->after(fn () => [$this->resetErrorBag(), $this->resetValidation()]);

        // ==== 5) رفض من المعرض أثناء المراجعة فقط ====
        $actions[] = Action::make('rejectByShowroomAction')
            ->label('رفض')
            ->icon('heroicon-o-x-circle')->color('danger')
            ->modalHeading("رفض الطلب — #{$rid}")
            ->extraAttributes(['wire:key' => "reject-showroom-{$rid}"])
            ->form([\Filament\Forms\Components\Textarea::make('reason')->label('سبب الرفض')->required()->rows(3)])
            ->visible(fn () =>
                auth()->user()?->hasRole('showroom_manager')
                && $this->record->current_phase === Phase::ShowroomReview->value
                && $this->record->phase_status === S::UnderReview->value
            )
            ->action(function (array $data) {
                app(\App\Services\ProductionRequestWorkflow::class)->reject($this->record, $data['reason']);
                \Filament\Notifications\Notification::make()->warning()->title('تم الرفض')->send();
                $this->refreshRecord();
            });

        // ==== 6) بدء مراجعة المصنع (يظهر فقط عند received في المصنع) ====
        $actions[] = Action::make('startFactoryReviewAction')
            ->label('بدء مراجعة المصنع')
            ->icon('heroicon-o-play-circle')->color('info')
            ->modalHeading("بدء مراجعة المصنع — طلب #{$rid}")
            ->extraAttributes(['wire:key' => "start-factory-review-{$rid}"])
            ->visible(fn () =>
                auth()->user()?->hasRole('factory_manager')
                && $this->record->current_phase === Phase::FactoryIntake->value
                && $this->record->phase_status === S::Received->value
            )
            ->action(function () {
                app(\App\Services\ProductionRequestWorkflow::class)->move(
                    $this->record, Phase::FactoryIntake, S::UnderReview, 'factory_manager', false
                );
                \Filament\Notifications\Notification::make()->success()->title('تم بدء مراجعة المصنع')->send();
                $this->refreshRecord();
            })
            ->after(fn () => [$this->resetErrorBag(), $this->resetValidation()]);

        // ==== 7) اعتماد المصنع وإنشاء المشروع (فقط أثناء under_review في المصنع) ====
        $actions[] = Action::make('approveFactoryAction')
            ->label('اعتماد وإنشاء project & tasks')
            ->icon('heroicon-o-check-circle')->color('primary')
            ->requiresConfirmation()
            ->modalHeading("اعتماد المصنع — طلب #{$rid}")
            ->extraAttributes(['wire:key' => "approve-factory-{$rid}"])
            ->visible(fn () =>
                auth()->user()?->hasRole('factory_manager')
                && $this->record->current_phase === Phase::FactoryIntake->value
                && $this->record->phase_status === S::UnderReview->value
            )
            ->action(function () {
                $missing = $this->record->files()->whereNull('estimated_cost')->count();
                if ($missing > 0) {
                    \Filament\Notifications\Notification::make()->danger()
                        ->title('لا يمكن الإنشاء')->body("يوجد {$missing} ملف بدون تكلفة.")->send();
                return;
            }
                app(\App\Services\ProductionRequestWorkflow::class)->approve($this->record);
                \Filament\Notifications\Notification::make()->success()->title('تم إنشاء المشروع والمهام')->send();
                $this->refreshRecord();
            })
            ->after(fn () => [$this->resetErrorBag(), $this->resetValidation()]);

        // ==== 8) رفض المصنع أثناء المراجعة فقط ====
        $actions[] = Action::make('rejectByFactoryAction')
            ->label('رفض')
            ->icon('heroicon-o-x-circle')->color('danger')
            ->modalHeading("رفض الطلب — #{$rid}")
            ->extraAttributes(['wire:key' => "reject-factory-{$rid}"])
            ->form([\Filament\Forms\Components\Textarea::make('reason')->label('سبب الرفض')->required()->rows(3)])
            ->visible(fn () =>
                auth()->user()?->hasRole('factory_manager')
                && $this->record->current_phase === Phase::FactoryIntake->value
                && $this->record->phase_status === S::UnderReview->value
            )
            ->action(function (array $data) {
                app(\App\Services\ProductionRequestWorkflow::class)->reject($this->record, $data['reason']);
                \Filament\Notifications\Notification::make()->warning()->title('تم الرفض')->send();
                $this->refreshRecord();
            });

        return $actions;
    }


    private function refreshRecord(): void
    {
        $this->record->refresh()->load(['client','project','logs','productionRequestFiles','files']);
    }
}
