<?php

namespace App\Filament\Actions\Task\QA;

use App\Models\ProductionTask;
use App\Services\Tasks\Workflow\InstallationWorkflowService;
use App\Support\Tasks\TaskPageHelper;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class RejectInstallationQAAction
{
    public static function make(ProductionTask $record, ?callable $redirectCallback = null): Action
    {
        return Action::make('rejectInstallationQA')
            ->label('رفض الجودة (التركيب)')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->visible(fn() => static::isVisible($record))
            ->form(static::getForm())
            ->requiresConfirmation()
            ->action(function (array $data) use ($record, $redirectCallback) {
                static::handle($record, $data);
                if ($redirectCallback) {
                    return $redirectCallback();
                }
            });
    }

    protected static function isVisible(ProductionTask $record): bool
    {
        $helper = app(TaskPageHelper::class);
        return $helper->canRejectInstallationQA($record, Auth::user());
    }

    protected static function getForm(): array
    {
        return [
            Textarea::make('reason')
                ->label('سبب الرفض')
                ->rows(3)
                ->required(),
        ];
    }

    protected static function handle(ProductionTask $record, array $data): void
    {
        $workflow = app(InstallationWorkflowService::class);
        $workflow->rejectInstallationQA($record, $data['reason']);

        Notification::make()
            ->warning()
            ->title('تم رفض الجودة وأُعيدت المهمة للتركيب')
            ->send();
    }
}
