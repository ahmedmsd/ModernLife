<?php

namespace App\Filament\Actions\Task\Installation;

use App\Models\ProductionTask;
use App\Services\Tasks\TaskWorkflowService;
use App\Support\Tasks\TaskPageHelper;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class FinishInstallationAction
{
    public static function make(ProductionTask $record, ?callable $redirectCallback = null): Action
    {
        return Action::make('finishInstallationAndSendQA')
            ->label('إنهاء التركيب وإرسال للجودة')
            ->icon('heroicon-o-paper-airplane')
            ->color('success')
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
        return $helper->canFinishInstallationToQA($record, Auth::user());
    }

    protected static function getForm(): array
    {
        return [
            Forms\Components\DateTimePicker::make('finished_at')
                ->label('تاريخ/وقت الإنهاء')
                ->default(now())
                ->required(),
            
            Textarea::make('note')
                ->label('ملاحظات (اختياري)')
                ->rows(3),
        ];
    }

    protected static function handle(ProductionTask $record, array $data): void
    {
        $workflow = app(TaskWorkflowService::class);
        $workflow->finishInstallationToQA($record, $data['finished_at'], $data['note'] ?? null);

        Notification::make()
            ->success()
            ->title('تم إرسال التركيب للجودة')
            ->send();
    }
}
