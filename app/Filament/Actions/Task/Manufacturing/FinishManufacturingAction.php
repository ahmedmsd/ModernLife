<?php

namespace App\Filament\Actions\Task\Manufacturing;

use App\Models\ProductionTask;
use App\Models\TaskLog;
use App\Services\Tasks\TaskWorkflowService;
use App\Support\Tasks\TaskPageHelper;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class FinishManufacturingAction
{
    /**
     * Create the finish manufacturing action
     *
     * @param ProductionTask $record
     * @param callable|null $redirectCallback
     * @return Action
     */
    public static function make(ProductionTask $record, ?callable $redirectCallback = null): Action
    {
        return Action::make('finish_manufacturing_send_to_qa')
            ->label('إنهاء التصنيع وإرسال للجودة')
            ->icon('heroicon-o-check-badge')
            ->color('success')
            ->visible(fn() => static::isVisible($record))
            ->form(static::getForm())
            ->requiresConfirmation()
            ->action(function (array $data) use ($record, $redirectCallback) {
                if (static::handle($record, $data) && $redirectCallback) {
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
        
        return $helper->canFinishManufacturing($record, Auth::user());
    }

    /**
     * Get the form schema
     *
     * @return array
     */
    protected static function getForm(): array
    {
        return [
            Forms\Components\DateTimePicker::make('actual_finished_at')
                ->label('تاريخ/وقت الانتهاء الفعلي')
                ->native(false)
                ->default(now())
                ->required(),
            
            Forms\Components\Textarea::make('note')
                ->label('ملاحظات (اختياري)')
                ->rows(3),
        ];
    }

    /**
     * Handle the action
     *
     * @param ProductionTask $record
     * @param array $data
     * @return bool
     */
    protected static function handle(ProductionTask $record, array $data): bool
    {
        $finished = Carbon::parse($data['actual_finished_at']);

        $startField = $record->actual_start_at ?? $record->started_at ?? null;

        if ($startField) {
            $started = $startField instanceof Carbon
                ? $startField
                : Carbon::parse($startField);
        } else {
            $startLog = TaskLog::query()
                ->where('task_id', $record->id)
                ->where('type', 'manufacturing_started')
                ->orderByRaw('COALESCE(happened_at, created_at) DESC, id DESC')
                ->first();

            $started = $startLog
                ? (($startLog->data['started_at'] ?? null)
                    ? Carbon::parse($startLog->data['started_at'])
                    : ($startLog->happened_at ?? $startLog->created_at))
                : null;
        }

        if ($started && $finished->lt($started)) {
            Notification::make()
                ->danger()
                ->title('تاريخ الانتهاء أقدم من تاريخ البدء')
                ->body('يرجى ضبط وقت/تاريخ الانتهاء ليكون بعد وقت البدء.')
                ->send();
            return false;
        }

        /** @var TaskWorkflowService $workflow */
        $workflow = app(TaskWorkflowService::class);
        
        $workflow->finishManufacturingAndSendToQA(
            $record,
            $data['actual_finished_at'],
            $data['note'] ?? null
        );

        Notification::make()
            ->success()
            ->title('تم إنهاء التصنيع وإرسال المهمة للجودة')
            ->send();

        return true;
    }
}
