<?php

namespace App\Filament\Resources\ProductionRequestResource\Pages;

use App\Enums\ProductionRequestStatus;
use App\Filament\Resources\ProductionRequestResource;
use App\Models\ProductionRequest;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon; // استخدم Carbon مباشرة
use App\Services\ProductionRequestWorkflow;
use App\Enums\{ProductionRequestPhase, PhaseStatus};

class ViewProductionTimeline extends Page
{
    protected static string $resource = ProductionRequestResource::class;
    protected static string $view     = 'filament.resources.production-request-resource.pages.view-production-timeline';
    protected static ?string $title   = 'معلومات الطلب التفصيلية';

    public ProductionRequest $record;

    /** بيانات الخط الزمني الجاهزة للعرض */
    public array $timeline = [];

    public static function canAccess(array $parameters = []): bool
    {
        return Auth::user()?->can('access_view_production_timeline') ?? false;
    }

    public function mount(ProductionRequest $record): void
    {
        $this->record = $record->load([
            'logs.causer',
            'client',
            'showroom',
            'files.department',
        ]);

        $this->timeline = $this->record->logs
            ->map(function ($log) {
                $at = $log->happened_at ?? $log->created_at;
                $atCarbon = $at instanceof Carbon ? $at : ($at ? Carbon::parse($at) : null);
                $note = $log->note ?? (is_array($log->data ?? null) ? ($log->data['note'] ?? null) : null);

                return [
                    'id'              => $log->id,
                    'user_name'       => $log->causer->name ?? '—',
                    'type'            => $log->type ?? '—',
                    'data'            => $log->data ?? [],
                    'note'            => $note ?? '—',
                    'at'              => $atCarbon?->toDateTimeString() ?? '—',
                    'at_human'        => $atCarbon?->diffForHumans() ?? '—',
                    'raw_happened_at' => $log->happened_at,
                    'raw_created_at'  => $log->created_at,
                ];
            })
            // رتب بحسب الوقت المتاح
            ->sortBy(fn ($row) => $row['at'] === '—' ? PHP_INT_MAX : strtotime($row['at']))
            ->values()
            ->all();
    }

    /**
     * تغيير الحالة العامة (status) + تسجيل log بنمط status_changed
     */
    protected function updateStatus(string $newValue, ?string $note): void
    {
        $current = (string) $this->record->status;

        if ($current !== $newValue) {
            $this->record->update(['status' => $newValue]);

            // جدول production_request_logs: type/data/note/causer_id/happened_at
            $this->record->logs()->create([
                'type'        => 'status_changed',
                'data'        => [
                    'from' => $current,
                    'to'   => $newValue,
                    'note' => $note,
                ],
                'note'        => $note
                    ?? 'تم تغيير الحالة إلى: ' . (ProductionRequestStatus::tryFrom($newValue)?->label() ?? $newValue),
                'causer_id'   => Auth::id(),
                'happened_at' => now(),
            ]);

            // أعد التحميل لتحديث الـ timeline فوراً
            $this->mount($this->record->fresh('logs.causer','client','showroom','files.department'));
        }
    }

    public function getHeaderActions(): array
    {
        return [
            // تحديث الحالة العامة
            Action::make('update_status')
                ->label('تحديث حالة الطلب')
                ->icon('heroicon-o-arrow-path')
                ->form([
                    Select::make('status')
                        ->label('الحالة')
                        ->options(ProductionRequestStatus::options())
                        ->default(fn () => (string) $this->record->status)
                        ->required()
                        ->reactive(),
                    Textarea::make('note')
                        ->label('ملاحظة')
                        ->nullable()
                        ->helperText('اختياري، سيُحفظ في الحقلين note و data.note'),
                ])
                ->action(function (array $data): void {
                    $this->updateStatus($data['status'], $data['note'] ?? null);

                    Notification::make()
                        ->title('تم تحديث الحالة بنجاح')
                        ->success()
                        ->send();
                }),

            // إرسال إلى مدير المصنع (اترك تسجيل اللوج للخدمة)
            Action::make('sendToFactory')
                ->label('إرسال إلى مدير المصنع')
                ->icon('heroicon-o-paper-airplane')
                ->visible(fn () =>
                    $this->record->current_phase === ProductionRequestPhase::ShowroomReview->value
                    && $this->record->phase_status === PhaseStatus::Approved->value
                )
                ->action(function () {
                    app(ProductionRequestWorkflow::class)->move(
                        $this->record,
                        ProductionRequestPhase::FactoryIntake,
                        PhaseStatus::Pending,
                        'factory_manager',
                        true
                    );

                    Notification::make()->success()->title('تم الإرسال إلى المصنع')->send();
                    $this->mount($this->record->fresh('logs.causer'));
                }),

            // تأكيد استلام المالك الحالي (الخدمة تسجل اللوج وتحدّث received_by_owner_at)
            Action::make('confirmReceipt')
                ->label('تأكيد استلامي')
                ->icon('heroicon-o-hand-thumb-up')
                ->visible(fn () => Auth::user()?->hasRole($this->record->current_owner_role))
                ->action(function () {
                    app(ProductionRequestWorkflow::class)->markReceived($this->record);

                    Notification::make()->success()->title('تم تأكيد الاستلام')->send();
                    $this->mount($this->record->fresh('logs.causer'));
                }),
        ];
    }
}
