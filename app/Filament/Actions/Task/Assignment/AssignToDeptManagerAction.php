<?php

namespace App\Filament\Actions\Task\Assignment;

use App\Models\ProductionTask;
use App\Models\User;
use App\Services\Tasks\Workflow\AssignmentWorkflowService;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class AssignToDeptManagerAction
{
    /**
     * Create the assign to department manager action
     *
     * @param ProductionTask $record
     * @param callable|null $redirectCallback
     * @return Action
     */
    public static function make(ProductionTask $record, ?callable $redirectCallback = null): Action
    {
        return Action::make('assign_to_dept_manager')
            ->label('إسناد لمدير القسم')
            ->icon('heroicon-o-user-plus')
            ->visible(fn() => static::isVisible($record))
            ->form(static::getForm($record))
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
        $user = Auth::user();

        return $user?->hasAnyRole(['factory_manager', 'admin', 'super-admin'])
            && blank($record->assigned_to_user_id);
    }

    /**
     * Get the form schema
     *
     * @param ProductionTask $record
     * @return array
     */
    protected static function getForm(ProductionTask $record): array
    {
        return [
            Forms\Components\Select::make('user_id')
                ->label('المسؤول')
                ->searchable()
                ->preload()
                ->options(function () use ($record) {
                    $deptId = $record->department_id;

                    return User::query()
                        ->role('department_manager')
                        // Uncomment if you want to filter by department
                        ->when($deptId, function ($q) use ($deptId) {
                            $q->whereHas('employee', fn($q2) => $q2->where('department_id', $deptId));
                        })
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->toArray();
                })
                ->required(),

            Forms\Components\DatePicker::make('due_date')
                ->label('تاريخ التسليم المتوقع')
                ->required(),
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
        /** @var AssignmentWorkflowService $workflow */
        $workflow = app(AssignmentWorkflowService::class);

        $workflow->assignToDeptManager(
            $record,
            (int) $data['user_id'],
            $data['due_date'],
            null
        );

        Notification::make()
            ->success()
            ->title('تم الإسناد')
            ->send();
    }
}
