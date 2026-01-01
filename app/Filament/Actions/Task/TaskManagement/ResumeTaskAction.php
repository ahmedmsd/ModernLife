<?php

namespace App\Filament\Actions\Task\TaskManagement;

use App\Models\ProductionTask;
use App\Services\Tasks\TaskTimerService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

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

        // Action is only available if task is on hold or blocked
        if (! in_array($record->status, ['on_hold', 'blocked'])) {
            return false;
        }

        $user = auth()->user();

        // Admin can always resume
        if ($user->hasAnyRole(['admin', 'super-admin'])) {
            return true;
        }

        // 1. Allow the user who put it on hold to resume it (Fallback)
        $activeHold = $record->holds()
            ->whereNull('ended_at')
            ->latest('started_at')
            ->first();



        if ($activeHold && (int) $activeHold->created_by === (int) $user->id) {
            return true;
        }

        // Handle case where status is on_hold but NO record found (Broken state)
        // Allow Dept Manager or Factory Manager to rescue it
        if (!$activeHold && in_array($record->status, ['on_hold', 'blocked'])) {
             if ($user->hasAnyRole(['department_manager', 'factory_manager'])) {
                 return true;
             }
        }

        // 2. Standard Role/Ownership checks
        $isDeptManagerAndOwner =
            $user->hasRole('department_manager')
            && $record->current_owner_role === 'department_manager';

        $isPureFactoryManager =
            $user->hasRole('factory_manager')
            && $user->getRoleNames()->count() === 1;

        return ($isDeptManagerAndOwner || $isPureFactoryManager);
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

        if (auth()->user()->hasRole('department_manager')) {
            $updated = $record->update([
                'current_owner_role'    => 'department_manager',
                'current_owner_user_id' => auth()->id(),
            ]);
        }

        Notification::make()
            ->title('تم استئناف المهمة وتشغيل العدّ.')
            ->success()
            ->send();

        $record->refresh();
    }
}
