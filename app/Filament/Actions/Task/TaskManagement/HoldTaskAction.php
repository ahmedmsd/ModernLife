<?php

namespace App\Filament\Actions\Task\TaskManagement;

use App\Models\ProductionTask;
use App\Services\Tasks\TaskTimerService;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;

class HoldTaskAction
{
    /**
     * Create the hold task action
     *
     * @param ProductionTask $record
     * @return Action
     */
    public static function make(ProductionTask $record): Action
    {
        return Action::make('hold')
            ->label('تعليق المهمة')
            ->icon('heroicon-o-pause-circle')
            ->color('warning')
            ->visible(fn() => static::isVisible($record))
            ->form(static::getForm())
            ->action(fn(array $data) => static::handle($record, $data));
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

        return !in_array($record->status, ['completed', 'closed'])
            && ($isDeptManagerAndOwner || $isPureFactoryManager);
    }

    /**
     * Get the form schema
     *
     * @return array
     */
    protected static function getForm(): array
    {
        return [
            Forms\Components\Select::make('type')
                ->label('نوع التعليق')
                ->options([
                    'awaiting_dependency' => 'بانتظار مهمة أخرى',
                    'awaiting_materials'  => 'بانتظار خامات',
                    'client_feedback'     => 'بانتظار رد العميل',
                    'other'               => 'أخرى',
                ])
                ->required(),
            
            Forms\Components\Select::make('related_task_id')
                ->label('المهمة المرتبطة')
                ->searchable()
                ->preload()
                ->options(
                    ProductionTask::query()
                        ->orderByDesc('id')
                        ->limit(200)
                        ->pluck('id', 'id')
                )
                ->visible(fn($get) => $get('type') === 'awaiting_dependency'),
            
            Forms\Components\Textarea::make('reason')
                ->label('السبب')
                ->rows(2),
            
            Forms\Components\Textarea::make('note')
                ->label('ملاحظة')
                ->rows(2),
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
        $data['created_by'] = auth()->id();
        
        /** @var TaskTimerService $timerService */
        $timerService = app(TaskTimerService::class);
        $timerService->startHold($record, $data);

        Notification::make()
            ->title('تم تعليق المهمة وإيقاف العدّ.')
            ->success()
            ->send();
    }
}
