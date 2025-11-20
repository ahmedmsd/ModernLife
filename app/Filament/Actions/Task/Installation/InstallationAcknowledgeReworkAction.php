<?php

namespace App\Filament\Actions\Task\Installation;

use App\Models\ProductionTask;
use App\Services\Tasks\TaskWorkflowService;
use App\Support\Tasks\TaskPageHelper;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class InstallationAcknowledgeReworkAction
{
    public static function make(ProductionTask $record): Action
    {
        return Action::make('installationAcknowledgeRework')
            ->label('تأكيد استلام التركيب (إعادة عمل)')
            ->icon('heroicon-o-clipboard-document-check')
            ->color('info')
            ->visible(fn() => static::isVisible($record))
            ->form(static::getForm())
            ->requiresConfirmation()
            ->action(fn(array $data) => static::handle($record, $data));
    }

    protected static function isVisible(ProductionTask $record): bool
    {
        $helper = app(TaskPageHelper::class);
        return $helper->canInstallationAcknowledgeRework($record, Auth::user());
    }

    protected static function getForm(): array
    {
        return [
            Textarea::make('note')
                ->label('ملاحظات (اختياري)')
                ->rows(3),
        ];
    }

    protected static function handle(ProductionTask $record, array $data): void
    {
        $workflow = app(TaskWorkflowService::class);
        $workflow->installationAcknowledgeRework($record, $data['note'] ?? null);

        Notification::make()
            ->success()
            ->title('تم تأكيد استلام التركيب (إعادة عمل)')
            ->send();

        $record->refresh();
    }
}
