<?php

namespace App\Filament\Actions\Task\Completion;

use App\Models\ProductionTask;
use App\Services\Tasks\Workflow\CompletionWorkflowService;
use App\Support\Tasks\TaskPageHelper;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class UploadClientReceiptAction
{
    /**
     * Create the upload client receipt action
     *
     * @param ProductionTask $record
     * @param callable|null $redirectCallback
     * @return Action
     */
    public static function make(ProductionTask $record, ?callable $redirectCallback = null): Action
    {
        return Action::make('uploadClientReceipt')
            ->label('رفع سند استلام العميل وإكمال المهمة')
            ->icon('heroicon-o-arrow-up-on-square')
            ->color('success')
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
        
        return Auth::user()?->hasRole('quality_manager')
            && !$helper->isClosedOrCompleted($record)
            && $helper->hasLog($record, 'qa_approved_installation');
    }

    /**
     * Get the form schema
     *
     * @return array
     */
    protected static function getForm(): array
    {
        return [
            Forms\Components\FileUpload::make('client_receipt')
                ->label('سند استلام العميل')
                ->disk('public')
                ->directory(fn () => 'client-receipts/' . now()->format('Y/m'))
                ->downloadable()
                ->openable()
                ->acceptedFileTypes(['application/pdf', 'image/*'])
                ->maxSize(10240)
                ->required(),
            
            Forms\Components\DateTimePicker::make('actual_finished_at')
                ->label('تاريخ/وقت الانتهاء الفعلي للمهمة')
                ->native(false)
                ->default(now())
                ->required(),
            
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
        /** @var CompletionWorkflowService $workflow */
        $workflow = app(CompletionWorkflowService::class);
        
        $workflow->uploadClientReceiptAndComplete(
            $record,
            Arr::get($data, 'client_receipt'),
            Arr::get($data, 'actual_finished_at'),
            Arr::get($data, 'note')
        );

        Notification::make()
            ->success()
            ->title('اكتملت المهمة')
            ->send();
    }
}
