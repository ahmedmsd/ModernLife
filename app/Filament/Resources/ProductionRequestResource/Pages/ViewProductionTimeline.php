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

class ViewProductionTimeline extends Page
{
    protected static string $resource = ProductionRequestResource::class;
    protected static string $view     = 'filament.resources.production-request-resource.pages.view-production-timeline';
    protected static ?string $title   = 'معلومات الطلب التفصيلية';

    public ProductionRequest $record;

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
    }

    protected function updateStatus(string $newValue, ?string $note): void
    {
        // نجلب الحالة الحالية كسلسلة نصية
        $current = (string) $this->record->status;

        if ($current !== $newValue) {
            // حدّث الحقل مباشرة بالقيمة الجديدة
            $this->record->update(['status' => $newValue]);
            // سجّل الحدث
            $this->record->logs()->create([
                'user_id'   => Auth::id(),
                'action'    => $newValue,
                'note'      => $note
                    ?? 'تم تغيير الحالة إلى: ' . ProductionRequestStatus::from($newValue)->label(),
                'action_at' => now(),
            ]);
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
                        // الخيارات من الـ enum
                        ->options(ProductionRequestStatus::options())
                        // الافتراضي: الحالة الحالية كنص
                        ->default(fn () => (string) $this->record->status)
                        ->required()
                        ->reactive(),

                    Textarea::make('note')
                        ->label('ملاحظة')
                        // شرطية الظهور والطلب فقط إذا اخترت "مرفوض"
                        ->required(fn ($get) => $get('status') === ProductionRequestStatus::REJECTED->value)
                        ->visible(fn  ($get) => $get('status') === ProductionRequestStatus::REJECTED->value),
                ])
                ->action(function (array $data): void {
                    // حدّث الحالة وسجّل الإشعار
                    $this->updateStatus($data['status'], $data['note'] ?? null);
                    Notification::make()
                        ->title('تم تحديث الحالة بنجاح')
                        ->success()
                        ->send();
                }),

            Action::make('send_to_manager')
                ->label('إرسال إلى مدير المصنع')
                ->icon('heroicon-o-paper-airplane')
                ->requiresConfirmation()
                ->action(function (): void {
                    $new = ProductionRequestStatus::SUBMITTED->value;
                    $this->record->update(['status' => $new]);
                    Notification::make()
                        ->title('تم إرسال الطلب بنجاح')
                        ->success()
                        ->send();
                }),

        ];
    }
}
