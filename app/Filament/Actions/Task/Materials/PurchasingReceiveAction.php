<?php

namespace App\Filament\Actions\Task\Materials;

use App\Models\ProductionTask;
use App\Services\Tasks\TaskWorkflowService;
use App\Support\Tasks\TaskPageHelper;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class PurchasingReceiveAction
{
    /**
     * Create the purchasing receive action
     *
     * @param ProductionTask $record
     * @param callable|null $redirectCallback
     * @return Action
     */
    public static function make(ProductionTask $record, ?callable $redirectCallback = null): Action
    {
        return Action::make('purchasing_receive')
            ->label('تأكيد استلام طلب الخامات (المشتريات)')
            ->icon('heroicon-o-check-badge')
            ->color('primary')
            ->visible(fn() => static::isVisible($record))
            ->form(static::getForm())
            ->requiresConfirmation()
            ->action(function (array $data) use ($record, $redirectCallback) {
                static::handle($record, $data);
                
                if ($redirectCallback) {
                    return $redirectCallback();
                }
            });
    }

    /**
     * Check if action should be visible
     *
     * @param ProductionTask $record
     * @return bool
     */
    protected static function isVisible(ProductionTask $record): bool
    {
        /** @var TaskPageHelper $helper */
        $helper = app(TaskPageHelper::class);
        
        return $helper->canPurchasingReceive($record, Auth::user());
    }

    /**
     * Get the form schema
     *
     * @return array
     */
    protected static function getForm(): array
    {
        return [
            Forms\Components\TextInput::make('po_number')
                ->label('رقم الطلب/المرجع'),
            
            Forms\Components\DateTimePicker::make('expected_delivery_at')
                ->label('موعد التوريد المتوقع')
                ->required(),
            
            Forms\Components\TextInput::make('estimated_cost')
                ->label('التكلفة المتوقعة')
                ->numeric(),
            
            Textarea::make('note')
                ->label('ملاحظات (اختياري)')
                ->rows(3),
        ];
    }

    /**
     * Handle the action
     *
     * @param ProductionTask $record
     * @param array $data
     * @return void
     */
    protected static function handle(ProductionTask $record, array $data): void
    {
        /** @var TaskWorkflowService $workflow */
        $workflow = app(TaskWorkflowService::class);
        
        $workflow->purchasingReceive($record, $data);

        Notification::make()
            ->success()
            ->title('تم تسجيل استلام طلب الخامات')
            ->send();
    }
}
