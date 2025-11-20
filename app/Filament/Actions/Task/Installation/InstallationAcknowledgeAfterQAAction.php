<?php

namespace App\Filament\Actions\Task\Installation;

use App\Models\ProductionTask;
use App\Services\Tasks\TaskWorkflowService;
use App\Support\Tasks\TaskPageHelper;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class InstallationAcknowledgeAfterQAAction
{
    public static function make(ProductionTask $record): Action
    {
        return Action::make('installationAcknowledgeAfterQAApprove')
            ->label('تأكيد استلام التركيب (بعد اعتماد جودة التصنيع)')
            ->icon('heroicon-o-clipboard-document-check')
            ->color('info')
            ->visible(fn() => static::isVisible($record))
            ->requiresConfirmation()
            ->action(fn() => static::handle($record));
    }

    protected static function isVisible(ProductionTask $record): bool
    {
        $helper = app(TaskPageHelper::class);
        return $helper->canInstallationAcknowledgeAfterQAApprove($record, Auth::user());
    }

    protected static function handle(ProductionTask $record): void
    {
        $workflow = app(TaskWorkflowService::class);
        $workflow->installationAcknowledge($record);

        Notification::make()
            ->success()
            ->title('تم تأكيد استلام قسم التركيب')
            ->send();
    }
}
