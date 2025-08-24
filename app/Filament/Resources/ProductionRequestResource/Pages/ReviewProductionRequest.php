<?php

namespace App\Filament\Resources\ProductionRequestResource\Pages;

use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\TextInput;
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
        $actions = [];

        // تأكيد استلام المالك الحالي
        $actions[] = Action::make('confirmReceipt')
            ->label('تأكيد استلامي')
            ->icon('heroicon-o-hand-thumb-up')
            ->visible(fn () => Auth::user()?->hasRole($this->record->current_owner_role))
            ->action(function () {
                app(ProductionRequestWorkflow::class)->markReceived($this->record);
                Notification::make()->success()->title('تم تأكيد الاستلام')->send();
                $this->refreshRecord();
            });

        $actions[] = Action::make('setFilesCosts')
            ->label('تحديد تكاليف الملفات')
            ->icon('heroicon-o-currency-dollar')
            ->color('warning')
            ->visible(fn () =>
                Auth::user()?->hasRole('showroom_manager') &&
                $this->record->current_phase === Phase::ShowroomReview->value
            )
            ->form(function () {
                // نبني حقولًا ديناميكية لكل ملف
                $schema = [];
                $files = $this->record->files()->with('department')->get();
                foreach ($files as $f) {
                    $schema[] = Fieldset::make("ملف: ".($f->file_name ?? basename($f->file_path)))
                        ->schema([
                            TextInput::make("cost_{$f->id}")
                                ->label('التكلفة التقديرية')
                                ->numeric()
                                ->minValue(0)
                                ->prefix('SAR')
                                ->default($f->estimated_cost)
                                ->required(),
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
                $files = $this->record->files()->get();
                $changed = 0;

                foreach ($files as $f) {
                    $key = "cost_{$f->id}";
                    if (array_key_exists($key, $data)) {
                        $val = $data[$key];
                        if ($f->estimated_cost != $val) {
                            $f->update(['estimated_cost' => $val]);
                            $changed++;
                        }
                    }
                }

                // Log وصفي
                $this->record->logs()->create([
                    'type'        => 'files_costs_set',
                    'data'        => [
                        'files'   => $files->map(fn($f)=>[
                            'id'=>$f->id,
                            'name'=>$f->file_name ?? basename($f->file_path ?? ''),
                            'estimated_cost'=>$f->estimated_cost,
                        ])->values()->all(),
                    ],
                    'note'        => "تم تحديث تكاليف الملفات (عدد: {$changed})",
                    'causer_id'   => Auth::id(),
                    'happened_at' => now(),
                ]);

                \Filament\Notifications\Notification::make()
                    ->success()->title('تم حفظ تكاليف الملفات')->send();

                $this->refreshRecord();
            });

        // (المعرض) اعتماد وإرسال للمصنع (Pending)
        $actions[] = Action::make('approveShowroomAndSend')
            ->label('اعتماد وإرسال للمصنع')
            ->icon('heroicon-o-paper-airplane')
            ->color('success')
            ->visible(fn () =>
                Auth::user()?->hasRole('showroom_manager') &&
                $this->record->current_phase === Phase::ShowroomReview->value &&
                in_array($this->record->phase_status, [S::Pending->value, S::UnderReview->value, S::Approved->value], true)
            )
            ->action(function () {
                app(ProductionRequestWorkflow::class)->move(
                    $this->record,
                    Phase::FactoryIntake,
                    S::Pending,
                    'factory_manager',
                    true
                );
                Notification::make()->success()->title('تم اعتماد الطلب وإرساله للمصنع')->send();
                $this->refreshRecord();
            });

        // (المصنع) اعتماد وإنشاء المشروع ثم الانتقال لإسناد الأقسام
        $actions[] = Action::make('approveFactory')
            ->label('اعتماد وإنشاء project & tasks')
            ->icon('heroicon-o-check-circle')
            ->color('primary')
            ->visible(fn () =>
                Auth::user()?->hasRole('factory_manager') &&
                $this->record->current_phase === Phase::FactoryIntake->value &&
                in_array($this->record->phase_status, [S::Pending->value, S::Received->value, S::UnderReview->value], true)
            )
            ->requiresConfirmation()
            ->action(function () {
                app(ProductionRequestWorkflow::class)->approve($this->record);
                Notification::make()->success()->title('تم اعتماد المصنع وإنشاء المشروع وربط المهام')->send();
                $this->refreshRecord();
            });

        // رفض (بأي من الدورين)
        $actions[] = Action::make('reject')
            ->label('رفض')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->form([
                \Filament\Forms\Components\Textarea::make('reason')->label('سبب الرفض')->required(),
            ])
            ->visible(fn () =>
            in_array($this->record->current_owner_role, ['showroom_manager','factory_manager'])
            )
            ->action(function (array $data) {
                app(ProductionRequestWorkflow::class)->reject($this->record, $data['reason']);
                Notification::make()->warning()->title('تم رفض الطلب')->send();
                $this->refreshRecord();
            });

        return $actions;
    }

    private function refreshRecord(): void
    {
        $this->record->refresh()->load(['client','project','logs','productionRequestFiles','files']);
    }
}
