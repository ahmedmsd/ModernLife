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
use Illuminate\Support\Carbon;
use App\Services\ProductionRequestWorkflow;
use App\Enums\{ProductionRequestPhase, PhaseStatus};

class ViewProductionTimeline extends Page
{
    protected static string $resource = ProductionRequestResource::class;
    protected static string $view     = 'filament.resources.production-request-resource.pages.view-production-timeline';
    protected static ?string $title   = 'معلومات الطلب التفصيلية';

    public ProductionRequest $record;


    public array $timeline = [];

    public static function canAccess(array $parameters = []): bool
    {
        return Auth::user()?->can('access_view_production_timeline') ?? false;
    }

    public function mount(ProductionRequest $record): void
    {
        $this->record = $record->load([
            'logs.user',
            'client',
            'showroom',
            'files.department',
        ]);

        $this->timeline = $this->record->logs
            ->map(function ($log) {
                // اختر أول وقت متاح: action_at ثم happened_at ثم created_at
                $at = $log->action_at ?? $log->happened_at ?? $log->created_at;

                // حوّل إلى Carbon عند الحاجة
                $atCarbon = $at instanceof Carbon ? $at : ($at ? Carbon::parse($at) : null);

                return [
                    'id'          => $log->id,
                    'user_name'   => $log->user->name ?? '—',
                    'action'      => $log->action ?? ($log->type ?? '—'),
                    'note'        => $log->note ?? ($log->data['note'] ?? null) ?? '—',
                    // قيم آمنة للعرض في الـ Blade بدون رمي أخطاء:
                    'at'          => $atCarbon?->toDateTimeString() ?? '—',
                    'at_human'    => $atCarbon?->diffForHumans() ?? '—',
                    // احتفظ بالأصل إن احتجته
                    'raw_action_at' => $log->action_at,
                    'raw_happened_at' => $log->happened_at,
                    'raw_created_at'  => $log->created_at,
                ];
            })
            // رتّب زمنيًا باستخدام أول وقت متاح
            ->sortBy(fn ($row) => $row['at'] === '—' ? PHP_INT_MAX : strtotime($row['at']))
            ->values()
            ->all();
    }

    /**
     * تحديث الحالة + تسجيل لوج بوقت action_at مضمون.
     */
    protected function updateStatus(string $newValue, ?string $note): void
    {
        $current = (string) $this->record->status;

        if ($current !== $newValue) {
            $this->record->update(['status' => $newValue]);

            $this->record->logs()->create([
                'user_id'   => Auth::id(),
                'action'    => $newValue,
                'note'      => $note
                    ?? 'تم تغيير الحالة إلى: ' . ProductionRequestStatus::from($newValue)->label(),
                'action_at' => now(), // ⬅️ مهم لتفادي null
            ]);

            // حدّث الـ timeline مباشرة بعد الإضافة
            $this->mount($this->record->fresh('logs.user','client','showroom','files.department'));
        }
    }

    public function getHeaderActions(): array
    {
        return [
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
                        ->required(fn ($get) => $get('status') === ProductionRequestStatus::REJECTED->value)
                        ->visible(fn  ($get) => $get('status') === ProductionRequestStatus::REJECTED->value),
                ])
                ->action(function (array $data): void {
                    $this->updateStatus($data['status'], $data['note'] ?? null);

                    Notification::make()
                        ->title('تم تحديث الحالة بنجاح')
                        ->success()
                        ->send();
                }),

            Action::make('sendToFactory')
                ->label('إرسال إلى مدير المصنع')
                ->icon('heroicon-o-paper-airplane')
                ->visible(fn($record)=> $record->current_phase === 'showroom_review' && $record->phase_status === 'approved')
                ->action(function($record){
                    app(ProductionRequestWorkflow::class)
                        ->move($record, ProductionRequestPhase::FactoryIntake, PhaseStatus::Pending, 'factory_manager', true);
                    \Filament\Notifications\Notification::make()->success()->title('تم الإرسال إلى المصنع')->send();
                }),

        Action::make('confirmReceipt')
            ->label('تأكيد استلامي')
            ->icon('heroicon-o-hand-thumb-up')
            ->visible(fn($record)=> auth()->user()?->hasRole($record->current_owner_role))
            ->action(function($record){
                app(ProductionRequestWorkflow::class)->markReceived($record);
                \Filament\Notifications\Notification::make()->success()->title('تم تأكيد الاستلام')->send();
            }),
        ];
    }
}
