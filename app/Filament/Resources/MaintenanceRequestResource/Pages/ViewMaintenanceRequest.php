<?php

namespace App\Filament\Resources\MaintenanceRequestResource\Pages;

use App\Filament\Resources\MaintenanceRequestResource;
use App\Models\MaintenanceRequest;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewMaintenanceRequest extends ViewRecord
{
    protected static string $resource = MaintenanceRequestResource::class;

    protected function getHeaderActions(): array
    {
        $record = $this->getRecord();

        $actions[] = Actions\Action::make('add_comment')
            ->label('إضافة ملاحظة')
            ->icon('heroicon-o-chat-bubble-left-right')
            ->color('gray')
            ->visible(fn () => Auth::user()?->hasAnyRole(['sales','showroom_manager','factory_manager','admin','super-admin']) ?? false)
            ->form([
                Forms\Components\Textarea::make('note')
                    ->label('الملاحظة')
                    ->rows(4)
                    ->required()
                    ->maxLength(5000),
            ])
            ->action(function (\App\Models\MaintenanceRequest $record, array $data) {
                $record->comments()->create([
                    'user_id' => Auth::id(),
                    'note'    => (string) $data['note'],
                ]);

                app(\App\Services\MaintenanceNotifier::class)->notifyComment($record, (string) $data['note']);

                \Filament\Notifications\Notification::make()->success()->title('تمت إضافة الملاحظة')->send();
            });
        $actions[] = Actions\Action::make('confirm_receipt')
            ->label('تأكيد استلام الطلب')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->visible(function () use ($record) {
                return $record->status === 'new'
                    && empty($record->acknowledged_at)
                    && Auth::user()?->hasAnyRole(['factory_manager', 'admin', 'super-admin']);
            })
            ->form([
                Forms\Components\DateTimePicker::make('acknowledged_at')
                    ->label('تاريخ تأكيد الاستلام')
                    ->default(now())
                    ->readonly()
                    ->required(),

                Forms\Components\DatePicker::make('expected_start_at')
                    ->label('تاريخ بدء الصيانة المتوقع')
                    ->required(),
            ])
            ->action(function (MaintenanceRequest $record, array $data): void {
                $record->update([
                    'acknowledged_at'        => $data['acknowledged_at'],
                    'expected_start_at'      => $data['expected_start_at'],
                    'current_owner_role'     => 'factory_manager',
                    'current_owner_user_id'  => Auth::id(),
                    'received_by_owner_at'   => $data['acknowledged_at'] ?? now(),
                ]);
                app(\App\Services\MaintenanceNotifier::class)->notifyReceiptConfirmed($record);
                Notification::make()
                    ->success()
                    ->title('تم تأكيد استلام طلب الصيانة')
                    ->send();
            });

        $actions[] = Actions\Action::make('start_maintenance')
            ->label('بدء الصيانة')
            ->icon('heroicon-o-play')
            ->color('primary')
            ->visible(function () use ($record) {
                return $record->status === 'new'
                    && ! empty($record->acknowledged_at)
                    && Auth::user()?->hasAnyRole(['factory_manager', 'admin', 'super-admin']);
            })
            ->form([
                Forms\Components\DatePicker::make('actual_start_at')
                    ->label('تاريخ البدء الفعلي')
                    ->default(now())
                    ->required(),

                Forms\Components\DatePicker::make('expected_end_at')
                    ->label('تاريخ الانتهاء المتوقع')
                    ->required(),
            ])
            ->action(function (MaintenanceRequest $record, array $data): void {
                $record->update([
                    'actual_start_at'   => $data['actual_start_at'],
                    'expected_end_at'   => $data['expected_end_at'],
                    'status'            => 'in_progress',
                ]);
                app(\App\Services\MaintenanceNotifier::class)->notifyStarted($record);

                Notification::make()
                    ->success()
                    ->title('تم تسجيل بدء الصيانة')
                    ->send();
            });

        // 3) إنهاء الصيانة
        $actions[] = Actions\Action::make('end_maintenance')
            ->label('إنهاء الصيانة')
            ->icon('heroicon-o-check-badge')
            ->color('success')
            ->visible(function () use ($record) {
                return $record->status === 'in_progress'
                    && ! empty($record->actual_start_at)
                    && Auth::user()?->hasAnyRole(['factory_manager', 'admin', 'super-admin']);
            })
            ->form([
                Forms\Components\DatePicker::make('actual_end_at')
                    ->label('تاريخ الانتهاء الفعلي')
                    ->default(now())
                    ->required(),

                Forms\Components\FileUpload::make('client_confirmation_file')
                    ->label('ملف تأكيد العميل (اختياري)')
                    ->disk('public')
                    ->directory('maintenance-confirmations/' . now()->format('Y/m'))
                    ->visibility('public')
                    ->openable()
                    ->downloadable()
                    ->maxSize(25_000),
            ])
            ->action(function (MaintenanceRequest $record, array $data): void {
                $record->update([
                    'actual_end_at'            => $data['actual_end_at'],
                    'client_confirmation_file' => $data['client_confirmation_file'] ?? $record->client_confirmation_file,
                    'status'                   => 'completed',
                    'closed_at'                => now(),
                ]);
                app(\App\Services\MaintenanceNotifier::class)->notifyCompleted($record);

                Notification::make()
                    ->success()
                    ->title('تم إنهاء الصيانة وإغلاق الطلب')
                    ->send();
            });

        return $actions;
    }
}
