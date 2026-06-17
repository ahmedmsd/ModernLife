<?php

namespace App\Filament\Actions\Task\Manufacturing;

use App\Models\ProductionTask;
use App\Models\TaskLog;
use App\Services\Tasks\Workflow\ManufacturingWorkflowService;
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
        /** @var \App\Support\Tasks\TaskPageHelper $helper */
        $helper = app(\App\Support\Tasks\TaskPageHelper::class);
        return $helper->canStartProduction($record, auth()->user());
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
        $workflow = app(ManufacturingWorkflowService::class);
        $workflow->startProduction($record, $data['started_at'], $data['note'] ?? null);

        Notification::make()
            ->success()
            ->title('تم بدء التصنيع')
            ->send();

        $record->refresh();
    }
}
