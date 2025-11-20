<?php

namespace App\Filament\Actions\Task\DepartmentManager;

use App\Models\ProductionTask;
use App\Services\Tasks\TaskWorkflowService;
use App\Support\Tasks\TaskPageHelper;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class RejectToFactoryAction
{
    /**
     * Create the reject to factory action
     *
     * @param ProductionTask $record
     * @param callable|null $redirectCallback
     * @return Action
     */
    public static function make(ProductionTask $record, ?callable $redirectCallback = null): Action
    {
        return Action::make('deptRejectToFactory')
            ->label('رفض المهمة وإعادتها للمصنع')
            ->icon('heroicon-o-arrow-uturn-left')
            ->color('danger')
            ->visible(fn() => static::isVisible($record))
            ->form(static::getForm())
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
        
        return $helper->canDeptReject($record, Auth::user());
    }

    /**
     * Get the form schema
     *
     * @return array
     */
    protected static function getForm(): array
    {
        return [
            Forms\Components\Textarea::make('reason')
                ->label('سبب الإعادة')
                ->required()
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
        
        $workflow->deptRejectToFactory($record, (string) $data['reason']);

        Notification::make()
            ->title('تمت إعادة المهمة إلى مدير المصنع')
            ->success()
            ->send();
    }
}
