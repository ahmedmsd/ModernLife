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
    protected static string $view     = 'filament.resources.production-request-resource.pages.review-request';
    protected static ?string $title   = 'مراجعة طلب التصنيع';

    /** @var ProductionRequest */
    public ProductionRequest $record;

    public function mount(ProductionRequest $record): void
    {
        $this->record = $record->load(['client', 'project', 'logs','logs.causer', 'productionRequestFiles', 'files']);
    }

    public function logTitle($log): string
    {
        $t = (string) $log->type;
        $phase  = data_get($log->data, 'phase');
        $status = data_get($log->data, 'status');
        $ownerR = data_get($log->data, 'owner_role');

        return match ($t) {
            'created'      => "تم إنشاء الطلب",
            'received'     => "تأكيد الاستلام ({$ownerR})",
            'under_review' => "بدء المراجعة ({$ownerR})",
            'moved'        => "نقل الطلب إلى {$ownerR} — {$phase} / {$status}",
            'approved'     => "تم الاعتماد — {$phase} / {$status}",
            'rejected'     => "تم الرفض — {$phase} / {$status}",
            default        => "عملية: {$t}",
        };
    }


    public function logIcon($log): string
    {
        return match ((string) $log->type) {
            'created'      => 'heroicon-o-plus-circle',
            'received'     => 'heroicon-o-inbox-arrow-down',
            'under_review' => 'heroicon-o-eye',
            'moved'        => 'heroicon-o-arrow-right-circle',
            'approved'     => 'heroicon-o-check-circle',
            'rejected'     => 'heroicon-o-x-circle',
            default        => 'heroicon-o-information-circle',
        };
    }


    public function logColor($log): string
    {
        return match ((string) $log->type) {
            'approved'     => 'success',
            'rejected'     => 'danger',
            'moved'        => 'info',
            'received'     => 'primary',
            'under_review' => 'warning',
            'created'      => 'gray',
            default        => 'gray',
        };
    }

    public function getHeaderActions(): array
    {
        $rid = (string) $this->record->getKey();
        $actions = [];

        // 1) تأكيد الاستلام
        $actions[] = Action::make('confirmReceiptAction')
            ->label('تأكيد استلامي')
            ->icon('heroicon-o-hand-thumb-up')
            ->modalHeading("تأكيد الاستلام — طلب #{$rid}")
            ->modalDescription('هل أنت متأكد من تأكيد استلام هذا الطلب؟')
            ->requiresConfirmation()
            ->modalSubmitActionLabel('تأكيد')
            ->modalCancelActionLabel('إلغاء')
            ->closeModalByClickingAway(false)
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
                $this->record->refresh()->load(['client', 'project', 'logs', 'productionRequestFiles', 'files']);
            });

        $actions[] = Action::make('startShowroomReviewAction')
            ->label('بدء مراجعة المعرض')
            ->icon('heroicon-o-play-circle')
            ->color('info')
            ->modalHeading("بدء مراجعة المعرض — طلب #{$rid}")
            ->modalDescription('سيتم بدء عملية المراجعة للمعرض.')
            ->requiresConfirmation()
            ->modalSubmitActionLabel('بدء المراجعة')
            ->modalCancelActionLabel('إلغاء')
            ->closeModalByClickingAway(false)
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
                $this->record->refresh()->load(['client', 'project', 'logs', 'productionRequestFiles', 'files']);
            });

        // 3) تحديد تكاليف الملفات
        $actions[] = Action::make('setFilesCostsAction')
            ->label('تحديد تكاليف الملفات')
            ->icon('heroicon-o-currency-dollar')
            ->color('warning')
            ->modalHeading("تكاليف الملفات — طلب #{$rid}")
            ->modalWidth('3xl')
            ->modalSubmitActionLabel('حفظ التكاليف')
            ->modalCancelActionLabel('إلغاء')
            ->closeModalByClickingAway(false)
            ->visible(fn () =>
                auth()->check()
                && auth()->user()->hasRole('showroom_manager')
                && $this->record->current_phase === Phase::ShowroomReview->value
                && $this->record->phase_status === S::UnderReview->value
            )
            ->mountUsing(function () {
                // إعادة تحميل البيانات في كل مرة
                $this->record->refresh()->load(['files.department']);
                return null;
            })
            ->form(function () {
                $schema = [];
                // إعادة تحميل الملفات في كل مرة
                $files = $this->record->files()->with('department')->get();

                foreach ($files as $f) {
                    $schema[] = \Filament\Forms\Components\Fieldset::make("file_fieldset_{$f->id}")
                        ->label("ملف: " . ($f->file_name ?? basename($f->file_path)))
                        ->schema([
                            \Filament\Forms\Components\TextInput::make("cost_{$f->id}")
                                ->label('التكلفة التقديرية')
                                ->numeric()
                                ->minValue(0)
                                ->prefix('SAR')
                                ->default($f->estimated_cost)
                                ->required()
                                ->key("cost_{$f->id}_" . time()), // مفتاح فريد لكل مرة
                            \Filament\Forms\Components\Placeholder::make("dept_{$f->id}")
                                ->label('القسم')
                                ->content($f->department->dept_name ?? '—'),
                        ])->columns(2);
                }

                return $schema ?: [
                    \Filament\Forms\Components\Placeholder::make('no_files')->content('لا توجد ملفات.')
                ];
            })
            ->action(function (array $data) {
                $this->record->refresh()->load(['files']);
                foreach ($this->record->files as $f) {
                    $k = "cost_{$f->id}";
                    if (array_key_exists($k, $data)) {
                        $f->update(['estimated_cost' => $data[$k]]);
                    }
                }
                Notification::make()->success()->title('تم حفظ تكاليف الملفات')->send();
                $this->record->refresh()->load(['client', 'project', 'logs', 'productionRequestFiles', 'files']);
            });

        // 4) اعتماد المعرض → إرسال للمصنع
        $actions[] = Action::make('approveShowroomAndSendAction')
            ->label('اعتماد وإرسال للمصنع')
            ->icon('heroicon-o-paper-airplane')
            ->color('success')
            ->modalHeading("اعتماد المعرض — طلب #{$rid}")
            ->modalDescription('سيتم اعتماد المراجعة وإرسال الطلب للمصنع.')
            ->requiresConfirmation()
            ->modalSubmitActionLabel('اعتماد وإرسال')
            ->modalCancelActionLabel('إلغاء')
            ->closeModalByClickingAway(false)
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
                        Notification::make()
                            ->danger()
                            ->title('غير مكتمل')
                            ->body("حدد تكلفة جميع الملفات. ناقص: {$missing}")
                            ->send();
                        $this->halt();
                    }
                }

                app(ProductionRequestWorkflow::class)->move(
                    $this->record, Phase::FactoryIntake, S::Pending, 'factory_manager', true
                );

                Notification::make()->success()->title('تم الإرسال للمصنع')->send();
                $this->record->refresh()->load(['client', 'project', 'logs', 'productionRequestFiles', 'files']);
            });

        // 5) رفض من المعرض
        $actions[] = Action::make('rejectByShowroomAction')
            ->label('رفض')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->modalHeading("رفض الطلب — #{$rid}")
            ->modalSubmitActionLabel('رفض الطلب')
            ->modalCancelActionLabel('إلغاء')
            ->closeModalByClickingAway(false)
            ->form([
                \Filament\Forms\Components\Textarea::make('reason_showroom')
                    ->label('سبب الرفض')
                    ->required()
                    ->rows(3)
                    ->key('reason_showroom_' . time()), // مفتاح فريد
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
                $this->record->refresh()->load(['client', 'project', 'logs', 'productionRequestFiles', 'files']);
            });

        // 6) بدء مراجعة المصنع
        $actions[] = Action::make('startFactoryReviewAction')
            ->label('بدء مراجعة المصنع')
            ->icon('heroicon-o-play-circle')
            ->color('info')
            ->modalHeading("بدء مراجعة المصنع — طلب #{$rid}")
            ->modalDescription('سيتم بدء عملية المراجعة للمصنع.')
            ->requiresConfirmation()
            ->modalSubmitActionLabel('بدء المراجعة')
            ->modalCancelActionLabel('إلغاء')
            ->closeModalByClickingAway(false)
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
                $this->record->refresh()->load(['client', 'project', 'logs', 'productionRequestFiles', 'files']);
            });

        // 7) اعتماد المصنع (إنشاء مشروع ومهام)
        $actions[] = Action::make('approveFactoryAction')
            ->label('اعتماد وإنشاء المشروع والمهام')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->modalHeading("اعتماد المصنع — طلب #{$rid}")
            ->modalDescription('سيتم إنشاء المشروع والمهام المرتبطة.')
            ->requiresConfirmation()
            ->modalSubmitActionLabel('اعتماد وإنشاء')
            ->modalCancelActionLabel('إلغاء')
            ->closeModalByClickingAway(false)
            ->visible(fn () =>
                auth()->check()
                && auth()->user()->hasRole('factory_manager')
                && $this->record->current_phase === Phase::FactoryIntake->value
                && $this->record->phase_status === S::UnderReview->value
            )
            ->action(function () {
                $missing = $this->record->files()->whereNull('estimated_cost')->count();
                if ($missing > 0) {
                    Notification::make()
                        ->danger()
                        ->title('لا يمكن الإنشاء')
                        ->body("يوجد {$missing} ملف بدون تكلفة.")
                        ->send();
                    $this->halt();
                }

                app(ProductionRequestWorkflow::class)->approve($this->record);

                Notification::make()->success()->title('تم إنشاء المشروع والمهام')->send();
                $this->record->refresh()->load(['client', 'project', 'logs', 'productionRequestFiles', 'files']);
            });

        // 8) رفض المصنع
        $actions[] = Action::make('rejectByFactoryAction')
            ->label('رفض')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->modalHeading("رفض الطلب — #{$rid}")
            ->modalSubmitActionLabel('رفض الطلب')
            ->modalCancelActionLabel('إلغاء')
            ->closeModalByClickingAway(false)
            ->form([
                \Filament\Forms\Components\Textarea::make('reason_factory')
                    ->label('سبب الرفض')
                    ->required()
                    ->rows(3)
                    ->key('reason_factory_' . time()), // مفتاح فريد
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
                $this->record->refresh()->load(['client', 'project', 'logs', 'productionRequestFiles', 'files']);
            });

        return $actions;
    }
}
