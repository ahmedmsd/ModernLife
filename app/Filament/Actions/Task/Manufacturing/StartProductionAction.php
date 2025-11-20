<?php

namespace App\Filament\Actions\Task\Manufacturing;

use App\Models\ProductionTask;
use App\Models\TaskLog;
use App\Services\Tasks\TaskWorkflowService;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;

class StartProductionAction
{
    public static function make(ProductionTask $record): Action
    {
        return Action::make('start_production')
            ->label('بدء التصنيع')
            ->icon('heroicon-o-play')
            ->color('info')
            ->visible(fn() => static::isVisible($record))
            ->form(static::getForm())
            ->requiresConfirmation()
            ->action(fn(array $data) => static::handle($record, $data));
    }

    protected static function isVisible(ProductionTask $record): bool
    {
        $u = auth()->user();
        if (!$u || !$u->hasRole('department_manager', 'web')) return false;

        if (($record->current_owner_role ?? null) !== 'department_manager') return false;

        $status = strtolower((string) ($record->status ?? ''));
        if (!in_array($status, ['waiting_production', 'rework'], true)) return false;

        $anchor = TaskLog::query()
            ->where('task_id', $record->id)
            ->where('type', 'manufacturing_ack_rework')
            ->orderByRaw('COALESCE(happened_at, created_at) DESC, id DESC')
            ->first();

        if (!$anchor) {
            $anchor = TaskLog::query()
                ->where('task_id', $record->id)
                ->where(function ($q) {
                    $q->where('type', 'materials_received_ok')
                        ->orWhere(function ($q2) {
                            $q2->where('type', 'materials_received_partial')
                                ->where('data->allow_start', true);
                        })
                        ->orWhere('type', 'planning_hint_set');
                })
                ->orderByRaw('COALESCE(happened_at, created_at) DESC, id DESC')
                ->first();
        }

        if (!$anchor) return false;

        $anchorTime = $anchor->happened_at ?? $anchor->created_at;
        $anchorId   = $anchor->id;

        $startedAfter = TaskLog::query()
            ->where('task_id', $record->id)
            ->where('type', 'manufacturing_started')
            ->where(function ($q) use ($anchorTime, $anchorId) {
                $q->whereRaw('COALESCE(happened_at, created_at) > ?', [$anchorTime])
                    ->orWhere(function ($q2) use ($anchorTime, $anchorId) {
                        $q2->whereRaw('COALESCE(happened_at, created_at) = ?', [$anchorTime])
                            ->where('id', '>', $anchorId);
                    });
            })
            ->exists();

        return !$startedAfter;
    }

    protected static function getForm(): array
    {
        return [
            Forms\Components\DateTimePicker::make('started_at')
                ->label('تاريخ البدء')
                ->default(now())
                ->required(),
            Forms\Components\Textarea::make('note')
                ->label('ملاحظة')
                ->rows(2),
        ];
    }

    protected static function handle(ProductionTask $record, array $data): void
    {
        $workflow = app(TaskWorkflowService::class);
        $workflow->startProduction($record, $data['started_at'], $data['note'] ?? null);

        Notification::make()
            ->success()
            ->title('تم بدء التصنيع')
            ->send();

        $record->refresh();
    }
}
