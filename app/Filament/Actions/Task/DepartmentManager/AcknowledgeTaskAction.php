<?php

namespace App\Filament\Actions\Task\DepartmentManager;

use App\Models\ProductionTask;
use App\Services\Tasks\TaskWorkflowService;
use App\Support\Tasks\TaskPageHelper;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class AcknowledgeTaskAction
{
    /**
     * Create the acknowledge task action
     *
     * @param ProductionTask $record
     * @param callable|null $redirectCallback
     * @return Action
     */
    public static function make(ProductionTask $record, ?callable $redirectCallback = null): Action
    {
        return Action::make('acknowledge')
            ->label('تأكيد استلام المهمة (مدير القسم)')
            ->icon('heroicon-o-hand-thumb-up')
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
        
        return $helper->canDeptAcknowledge($record, Auth::user());
    }

    /**
     * Get the form schema
     *
     * @return array
     */
    protected static function getForm(): array
    {
        return [
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
        
        $workflow->deptAcknowledge($record, $data['note'] ?? null);

        Notification::make()
            ->success()
            ->title('تم تأكيد الاستلام')
            ->send();
    }
}
