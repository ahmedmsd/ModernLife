<?php

namespace App\Filament\Actions\Task\Manufacturing;

use App\Models\ProductionTask;
use App\Services\Tasks\Workflow\ManufacturingWorkflowService;
use App\Support\Tasks\TaskPageHelper;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class ManufacturingAcknowledgeReworkAction
{
    /**
     * Create the manufacturing acknowledge rework action
     *
     * @param ProductionTask $record
     * @return Action
     */
    public static function make(ProductionTask $record): Action
    {
        return Action::make('manufacturingAcknowledgeRework')
            ->label('تأكيد استلام التصنيع (إعادة عمل)')
            ->icon('heroicon-o-clipboard-document-check')
            ->color('info')
            ->visible(fn() => static::isVisible($record))
            ->form(static::getForm())
            ->requiresConfirmation()
            ->action(fn(array $data) => static::handle($record, $data));
    }

    /**
     * Check if action should be visible
     *
     * @param ProductionTask $record
     * @return bool
     */
    protected static function isVisible(ProductionTask $record): bool
    {
        /** @var TaskPageHelper $helper */
        $helper = app(TaskPageHelper::class);
        
        return $helper->canManufacturingAcknowledgeRework($record, Auth::user());
    }

    /**
     * Get the form schema
     *
     * @return array
     */
    protected static function getForm(): array
    {
        return [
            Textarea::make('note')
                ->label('ملاحظات (اختياري)')
                ->rows(3),
        ];
    }

    /**
     * Handle the action
     *
     * @param ProductionTask $record
     * @param array $data
     * @return void
     */
    protected static function handle(ProductionTask $record, array $data): void
    {
        /** @var ManufacturingWorkflowService $workflow */
        $workflow = app(ManufacturingWorkflowService::class);
        
        $workflow->manufacturingAcknowledgeRework($record, $data['note'] ?? null);

        Notification::make()
            ->success()
            ->title('تم تأكيد استلام التصنيع (إعادة عمل)')
            ->send();

        $record->refresh();
    }
}
