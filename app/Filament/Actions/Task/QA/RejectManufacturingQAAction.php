<?php

namespace App\Filament\Actions\Task\QA;

use App\Models\ProductionTask;
use App\Services\Tasks\Workflow\ManufacturingWorkflowService;
use App\Support\Tasks\TaskPageHelper;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class RejectManufacturingQAAction
{
    public static function make(ProductionTask $record, ?callable $redirectCallback = null): Action
    {
        return Action::make('rejectManufacturingQA')
            ->label('رفض الجودة (بعد التصنيع)')
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
        return $helper->canRejectManufacturingQA($record, Auth::user());
    }

    protected static function getForm(): array
    {
        return [
            Textarea::make('note')
                ->label('سبب الرفض')
                ->rows(3)
                ->required(),
        ];
    }

    protected static function handle(ProductionTask $record, array $data): void
    {
        $workflow = app(ManufacturingWorkflowService::class);
        $workflow->rejectManufacturingQA($record, $data['note']);

        Notification::make()
            ->warning()
            ->title('تم رفض الجودة وأعيدت للتصنيع')
            ->send();
    }
}
