<?php

namespace App\Filament\Actions\Task\Installation;

use App\Models\ProductionTask;
use App\Services\Tasks\Workflow\InstallationWorkflowService;
use App\Support\Tasks\TaskPageHelper;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class StartInstallationAction
{
    public static function make(ProductionTask $record): Action
    {
        return Action::make('startInstallation')
            ->label('بدء التركيب')
            ->icon('heroicon-o-wrench-screwdriver')
            ->color('info')
            ->visible(fn() => static::isVisible($record))
            ->form(static::getForm())
            ->requiresConfirmation()
            ->action(fn(array $data) => static::handle($record, $data));
    }

    protected static function isVisible(ProductionTask $record): bool
    {
        $helper = app(TaskPageHelper::class);
        return $helper->canStartInstallation($record, Auth::user());
    }

    protected static function getForm(): array
    {
        return [
            Forms\Components\DateTimePicker::make('started_at')
                ->label('تاريخ/وقت البدء')
                ->default(now())
                ->required(),
            
            Textarea::make('note')
                ->label('ملاحظات (اختياري)')
                ->rows(3),
        ];
    }

    protected static function handle(ProductionTask $record, array $data): void
    {
        $workflow = app(InstallationWorkflowService::class);
        $workflow->startInstallation($record, $data['started_at'], $data['note'] ?? null);

        Notification::make()
            ->success()
            ->title('تم بدء التركيب')
            ->send();

        $record->refresh();
    }
}
