<?php

namespace App\Filament\Actions\Task\QA;

use App\Models\ProductionTask;
use App\Services\Tasks\Workflow\InstallationWorkflowService;
use App\Support\Tasks\TaskPageHelper;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class ApproveInstallationQAAction
{
    public static function make(ProductionTask $record): Action
    {
        return Action::make('approveInstallationQA')
            ->label('اعتماد الجودة (بعد التركيب)')
            ->icon('heroicon-o-check-badge')
            ->color('success')
            ->visible(fn() => static::isVisible($record))
            ->form(static::getForm())
            ->requiresConfirmation()
            ->action(fn(array $data) => static::handle($record, $data));
    }

    protected static function isVisible(ProductionTask $record): bool
    {
        $helper = app(TaskPageHelper::class);
        return $helper->canApproveInstallationQA($record, Auth::user());
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
        $workflow = app(InstallationWorkflowService::class);
        $workflow->approveInstallationQA($record, $data['note'] ?? null);

        Notification::make()
            ->success()
            ->title('تم اعتماد الجودة لما بعد التركيب')
            ->send();
    }
}
