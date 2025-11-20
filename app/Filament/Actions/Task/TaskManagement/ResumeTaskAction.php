<?php

namespace App\Filament\Actions\Task\TaskManagement;

use App\Models\ProductionTask;
use App\Services\Tasks\TaskTimerService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class ResumeTaskAction
{
    /**
     * Create the resume task action
     *
     * @param ProductionTask $record
     * @return Action
     */
    public static function make(ProductionTask $record): Action
    {
        return Action::make('resume')
            ->label('استئناف المهمة')
            ->icon('heroicon-o-play-circle')
            ->color('success')
            ->visible(fn() => static::isVisible($record))
            ->action(fn() => static::handle($record));
    }

    /**
     * Check if action should be visible
     *
     * @param ProductionTask $record
     * @return bool
     */
    protected static function isVisible(ProductionTask $record): bool
    {
        if (!auth()->check()) {
            return false;
        }

        $user = auth()->user();

        $isDeptManagerAndOwner =
            $user->hasRole('department_manager')
            && $record->current_owner_role === 'department_manager'
            && (int) $record->current_owner_user_id === (int) $user->id;

        $isPureFactoryManager =
            $user->hasRole('factory_manager')
            && $user->getRoleNames()->count() === 1;

        return in_array($record->status, ['on_hold', 'blocked'])
            && ($isDeptManagerAndOwner || $isPureFactoryManager);
    }

    /**
     * Handle the action
     *
     * @param ProductionTask $record
     * @return void
     */
    protected static function handle(ProductionTask $record): void
    {
        /** @var TaskTimerService $timerService */
        $timerService = app(TaskTimerService::class);
        $timerService->endHold($record, 'Manual resume');

        Notification::make()
            ->title('تم استئناف المهمة وتشغيل العدّ.')
            ->success()
            ->send();
    }
}
