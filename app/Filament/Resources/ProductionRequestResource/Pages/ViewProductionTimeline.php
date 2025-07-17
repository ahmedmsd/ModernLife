<?php

namespace App\Filament\Resources\ProductionRequestResource\Pages;

use App\Filament\Resources\ProductionRequestResource;
use App\Models\ProductionRequest;
use Filament\Forms\Components\Select;
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
        $this->record = $record->load(['logs.user', 'client', 'showroom', 'files.department']);
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
                        ->options([
                            'قيد المراجعة' => 'قيد المراجعة',
                            'مقبول' => 'مقبول',
                            'مرفوض' => 'مرفوض',
                        ])
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $this->record->update([
                        'status' => $data['status'],
                    ]);

                    if ($user = Auth::user()) {
                        $this->record->logs()->create([
                            'user_id' => $user->id,
                            'action' => $data['status'],
                            'note' => 'تم تغيير حالة الطلب إلى ' . $data['status'],
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
                    $this->record->update(['status' => 'قيد المراجعة']);

                    if ($user = Auth::user()) {
                        $this->record->logs()->create([
                            'user_id' => $user->id,
                            'action' => 'تم إرسال الطلب إلى مدير المصنع',
                            'note' => 'بواسطة المستخدم ' . $user->name,
                            'action_at' => now(),
                        ]);
                    }

                    Notification::make()
                        ->success()
                        ->title('تم إرسال الطلب بنجاح')
                        ->send();
                }),
        ];
    }
}
