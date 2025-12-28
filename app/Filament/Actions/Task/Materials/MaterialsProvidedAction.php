<?php

namespace App\Filament\Actions\Task\Materials;

use App\Models\ProductionTask;
use App\Services\Tasks\Workflow\MaterialsWorkflowService;
use App\Support\Tasks\TaskPageHelper;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class MaterialsProvidedAction
{
    /**
     * Create the materials provided action
     *
     * @param ProductionTask $record
     * @param callable|null $redirectCallback
     * @return Action
     */
    public static function make(ProductionTask $record, ?callable $redirectCallback = null): Action
    {
        return Action::make('materials_provided')
            ->label('تأكيد توفر الخامات')
            ->icon('heroicon-o-archive-box')
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
        
        return $helper->canMaterialsProvided($record, Auth::user());
    }

    /**
     * Get the form schema
     *
     * @return array
     */
    protected static function getForm(): array
    {
        return [
            Forms\Components\TextInput::make('actual_cost')
                ->label('قيمة الشراء الفعلية')
                ->numeric()
                ->required(),
            
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
        /** @var MaterialsWorkflowService $workflow */
        $workflow = app(MaterialsWorkflowService::class);
        
        $workflow->materialsProvided(
            $record,
            (float) $data['actual_cost'],
            $data['note'] ?? null
        );

        Notification::make()
            ->success()
            ->title('تم توفير الخامات')
            ->send();
    }
}
