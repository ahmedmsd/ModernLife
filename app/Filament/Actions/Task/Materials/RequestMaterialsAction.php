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

class RequestMaterialsAction
{
    /**
     * Create the request materials action
     *
     * @param ProductionTask $record
     * @param callable|null $redirectCallback
     * @return Action
     */
    public static function make(ProductionTask $record, ?callable $redirectCallback = null): Action
    {
        return Action::make('request_materials')
            ->label('طلب خامات')
            ->icon('heroicon-o-truck')
            ->color('info')
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
        
        return $helper->canRequestMaterials($record, Auth::user());
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
                ->label('ملاحظات / تفاصيل المطلوب')
                ->rows(3)
                ->required(),
            
            Forms\Components\FileUpload::make('po_file')
                ->label('ملف أمر الشراء (PO) المُعتمد من مدير المصنع')
                ->disk('public')
                ->directory('purchase_orders/' . now()->format('Y/m'))
                ->acceptedFileTypes(['application/pdf', 'image/*'])
                ->maxSize(20_480)
                ->openable()
                ->downloadable()
                ->moveFiles()
                ->visibility('public')
                ->required(),
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
        
        $workflow->requestMaterials($record, $data['note'], $data['po_file']);

        Notification::make()
            ->success()
            ->title('تم إرسال طلب الخامات')
            ->send();
    }
}
