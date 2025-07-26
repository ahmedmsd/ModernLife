<?php

namespace App\Filament\Resources\ProductionRequestResource\Pages;

use App\Enums\ProductionRequestStatus;
use App\Filament\Resources\ProductionRequestResource;
use App\Models\ProductionRequest;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;

use Filament\Resources\Pages\Page;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class ViewProductionTimeline extends Page
{
    protected static string $resource = ProductionRequestResource::class;
    protected static string $view = 'filament.resources.production-request-resource.pages.view-production-timeline';
    protected static ?string $title = 'معلومات الطلب التفصيلية ';

    public ProductionRequest $record;

    public function mount(ProductionRequest $record): void
    {
        $this->record = $record->load([
            'logs.user',
            'client',
            'showroom',
            'files.department',
        ]);
    }

    public function getHeaderActions(): array
    {
        return [
            Action::make('update_status')
                ->label('تحديث حالة الطلب')
                ->icon('heroicon-o-arrow-path')
                ->form([
                    Select::make('status')
                        ->label('حالة الطلب')
                        ->options(ProductionRequestStatus::options())
                        ->default(
                            fn() => $this->record->status instanceof \BackedEnum
                                ? $this->record->status->value
                                : (string) $this->record->status
                        )
                        ->required()
                        ->reactive(), // للسماح بتحديث الفورم بناءً على الاختيار

                    Textarea::make('note')
                        ->label('سبب الرفض')
                        ->required(fn(callable $get) => $get('status') === ProductionRequestStatus::Rejected->value)
                        ->visible(fn(callable $get) => $get('status') === ProductionRequestStatus::Rejected->value),
                ])
                ->action(function (array $data): void {
                    $statusEnum = ProductionRequestStatus::from($data['status']);
                    $note = $data['note'] ?? null;

                    if (! $this->record->status instanceof ProductionRequestStatus || $this->record->status->value !== $statusEnum->value) {
                        $this->record->update(['status' => $statusEnum->value]);

                        $this->record->logs()->create([
                            'user_id' => Auth::id(),
                            'action' => $statusEnum->value,
                            'note' => $note ?? 'تم تغيير الحالة إلى: ' . $statusEnum->label(),
                            'action_at' => now(),
                        ]);
                    }

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
                    $newStatusValue = ProductionRequestStatus::Submitted->value;

                    $this->record->update([
                        'status' => $newStatusValue,
                    ]);

                    // $this->record->logs()->create([
                    //     'user_id' => Auth::id(),
                    //     'action' => $newStatusValue,
                    //     'note' => 'تم إرسال الطلب إلى مدير المصنع بواسطة المستخدم ' . Auth::user()?->name,
                    //     'action_at' => now(),
                    // ]);

                    Notification::make()
                        ->title('تم إرسال الطلب بنجاح')
                        ->success()
                        ->send();
                }),
        ];
    }
}
