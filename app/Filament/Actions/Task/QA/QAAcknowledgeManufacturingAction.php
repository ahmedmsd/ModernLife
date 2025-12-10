<?php

namespace App\Filament\Actions\Task\QA;

use App\Models\ProductionTask;
use App\Services\Tasks\Workflow\ManufacturingWorkflowService;
use App\Support\Tasks\TaskPageHelper;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class QAAcknowledgeManufacturingAction
{
    public static function make(ProductionTask $record): Action
    {
        return Action::make('qaAcknowledgeManufacturing')
            ->label('تأكيد استلام الجودة (بعد التصنيع)')
            ->icon('heroicon-o-inbox-arrow-down')
            ->color('primary')
            ->visible(fn() => static::isVisible($record))
            ->requiresConfirmation()
            ->action(fn() => static::handle($record));
    }

    protected static function isVisible(ProductionTask $record): bool
    {
        $helper = app(TaskPageHelper::class);
        return $helper->canQaAcknowledgeManufacturing($record, Auth::user());
    }

    protected static function handle(ProductionTask $record): void
    {
        $workflow = app(ManufacturingWorkflowService::class);
        $workflow->qaAcknowledgeManufacturing($record, $data['note'] ?? null);

        Notification::make()
            ->success()
            ->title('تم تأكيد استلام الجودة')
            ->send();

        $record->refresh();
    }
}
