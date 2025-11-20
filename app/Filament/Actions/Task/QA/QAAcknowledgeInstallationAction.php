<?php

namespace App\Filament\Actions\Task\QA;

use App\Models\ProductionTask;
use App\Services\Tasks\TaskWorkflowService;
use App\Support\Tasks\TaskPageHelper;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class QAAcknowledgeInstallationAction
{
    public static function make(ProductionTask $record): Action
    {
        return Action::make('qaAcknowledgeInstallation')
            ->label('تأكيد استلام الجودة (التركيب)')
            ->icon('heroicon-o-inbox-arrow-down')
            ->color('info')
            ->visible(fn() => static::isVisible($record))
            ->requiresConfirmation()
            ->action(fn() => static::handle($record));
    }

    protected static function isVisible(ProductionTask $record): bool
    {
        $helper = app(TaskPageHelper::class);
        return $helper->canQaAcknowledgeInstallation($record, Auth::user());
    }

    protected static function handle(ProductionTask $record): void
    {
        $workflow = app(TaskWorkflowService::class);
        $workflow->qaAcknowledgeInstallation($record);

        Notification::make()
            ->success()
            ->title('تم تأكيد استلام الجودة للتركيب')
            ->send();
    }
}
