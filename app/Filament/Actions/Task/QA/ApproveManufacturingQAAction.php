<?php

namespace App\Filament\Actions\Task\QA;

use App\Models\ProductionTask;
use App\Services\Tasks\Workflow\ManufacturingWorkflowService;
use App\Support\Tasks\TaskPageHelper;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class ApproveManufacturingQAAction
{
    public static function make(ProductionTask $record, ?callable $redirectCallback = null): Action
    {
        return Action::make('approveManufacturingQA')
            ->label('اعتماد الجودة (بعد التصنيع)')
            ->icon('heroicon-o-check-badge')
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
        return $helper->canApproveManufacturingQA($record, Auth::user());
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
        $workflow = app(ManufacturingWorkflowService::class);
        $workflow->approveManufacturingQA($record, $data['note'] ?? null);

        Notification::make()
            ->success()
            ->title('تم اعتماد الجودة وتحويل المهمة للتركيب')
            ->send();
    }
}
