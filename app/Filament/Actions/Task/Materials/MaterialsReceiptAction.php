<?php

namespace App\Filament\Actions\Task\Materials;

use App\Models\ProductionTask;
use App\Services\Tasks\TaskWorkflowService;
use App\Support\Tasks\TaskPageHelper;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class MaterialsReceiptAction
{
    /**
     * Create the materials receipt action
     *
     * @param ProductionTask $record
     * @param callable|null $redirectCallback
     * @return Action
     */
    public static function make(ProductionTask $record, ?callable $redirectCallback = null): Action
    {
        return Action::make('materials_receipt')
            ->label('تسجيل استلام الخامات (مدير القسم)')
            ->icon('heroicon-o-archive-box')
            ->color('success')
            ->visible(fn() => static::isVisible($record))
            ->form(static::getForm($record))
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
        
        return $helper->canMaterialsReceivedOk($record, Auth::user());
    }

    /**
     * Get the form schema
     *
     * @param ProductionTask $record
     * @return array
     */
    protected static function getForm(ProductionTask $record): array
    {
        return [
            Forms\Components\Select::make('receipt_type')
                ->label('حالة الاستلام')
                ->options([
                    'ok'             => 'استلام كلي (جاهز لبدء التصنيع)',
                    'partial_allow'  => 'استلام جزئي — مع السماح ببدء التصنيع',
                    'partial_hold'   => 'استلام جزئي — إيقاف حتى استكمال النواقص',
                    'issue'          => 'استلام به مشكلة — إيقاف وتحويل للمشتريات',
                ])
                ->native(false)
                ->required()
                ->reactive()
                ->afterStateUpdated(function (Get $get, Set $set, ?string $state) use ($record) {
                    $needPlan = in_array($state, ['ok', 'partial_allow'], true);

                    if (!$needPlan) {
                        return;
                    }

                    $currentStart   = optional($record->planned_start_at)->toDateString();
                    $currentEnd     = optional($record->planned_end_at)->toDateString();
                    $currentInstall = optional($record->planned_install_at)->toDateString();

                    if (!$get('planned_start') && $currentStart)   $set('planned_start', $currentStart);
                    if (!$get('planned_end') && $currentEnd)       $set('planned_end', $currentEnd);
                    if (!$get('planned_install') && $currentInstall) $set('planned_install', $currentInstall);
                }),

            Forms\Components\DatePicker::make('planned_start')
                ->label('بداية التصنيع (متوقعة)')
                ->native(false)
                ->visible(fn (Get $get) => in_array($get('receipt_type'), ['ok', 'partial_allow'], true))
                ->required(fn (Get $get) => in_array($get('receipt_type'), ['ok', 'partial_allow'], true))
                ->default(optional($record->planned_start_at)->toDateString())
                ->afterStateHydrated(function (Get $get, Set $set, $state) use ($record) {
                    if (!$state && in_array($get('receipt_type'), ['ok', 'partial_allow'], true)) {
                        $val = optional($record->planned_start_at)->toDateString();
                        if ($val) $set('planned_start', $val);
                    }
                }),

            Forms\Components\DatePicker::make('planned_end')
                ->label('نهاية التصنيع (متوقعة)')
                ->native(false)
                ->visible(fn (Get $get) => in_array($get('receipt_type'), ['ok', 'partial_allow'], true))
                ->required(fn (Get $get) => in_array($get('receipt_type'), ['ok', 'partial_allow'], true))
                ->default(optional($record->planned_end_at)->toDateString())
                ->afterStateHydrated(function (Get $get, Set $set, $state) use ($record) {
                    if (!$state && in_array($get('receipt_type'), ['ok', 'partial_allow'], true)) {
                        $val = optional($record->planned_end_at)->toDateString();
                        if ($val) $set('planned_end', $val);
                    }
                }),

            Forms\Components\DatePicker::make('planned_install')
                ->label('موعد التركيب (متوقع)')
                ->native(false)
                ->visible(fn (Get $get) => in_array($get('receipt_type'), ['ok', 'partial_allow'], true))
                ->required(fn (Get $get) => in_array($get('receipt_type'), ['ok', 'partial_allow'], true))
                ->default(optional($record->planned_install_at)->toDateString())
                ->afterStateHydrated(function (Get $get, Set $set, $state) use ($record) {
                    if (!$state && in_array($get('receipt_type'), ['ok', 'partial_allow'], true)) {
                        $val = optional($record->planned_install_at)->toDateString();
                        if ($val) $set('planned_install', $val);
                    }
                }),

            Forms\Components\Textarea::make('note')
                ->label('ملاحظات (اختياري)')
                ->rows(3)
                ->maxLength(1000),

            Forms\Components\Textarea::make('missing_items')
                ->label('تفاصيل البنود الناقصة')
                ->rows(3)
                ->visible(fn (Get $get) => in_array($get('receipt_type'), ['partial_allow', 'partial_hold'], true)),

            Forms\Components\Textarea::make('issue_details')
                ->label('تفاصيل المشكلة')
                ->rows(3)
                ->visible(fn (Get $get) => $get('receipt_type') === 'issue'),
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
        $type = $data['receipt_type'];

        // Validate dates for ok/partial_allow types
        $needPlan = in_array($type, ['ok', 'partial_allow'], true);
        if ($needPlan) {
            $start = Carbon::parse($data['planned_start']);
            $end   = Carbon::parse($data['planned_end']);
            $ins   = Carbon::parse($data['planned_install']);

            if ($end->lt($start) || $ins->lt($end)) {
                Notification::make()
                    ->danger()
                    ->title('تسلسل التواريخ غير صحيح')
                    ->body('يجب أن تكون نهاية التصنيع بعد بدايته، وموعد التركيب بعد نهاية التصنيع.')
                    ->send();
                return;
            }
        }

        /** @var TaskWorkflowService $workflow */
        $workflow = app(TaskWorkflowService::class);

        switch ($type) {
            case 'ok':
                $workflow->materialsReceivedOk(
                    $record,
                    $data['planned_start'],
                    $data['planned_end'],
                    $data['planned_install'],
                    $data['note'] ?? null
                );
                Notification::make()
                    ->success()
                    ->title('تم الاستلام الكلي — المهمة بانتظار بدء التصنيع')
                    ->send();
                break;

            case 'partial_allow':
                $workflow->materialsReceivedPartialAllowStart(
                    $record,
                    $data['planned_start'],
                    $data['planned_end'],
                    $data['planned_install'],
                    $data['note'] ?? null,
                    $data['missing_items'] ?? null
                );
                Notification::make()
                    ->success()
                    ->title('استلام جزئي (السماح بالبدء) — تم فتح طلب تكميلي')
                    ->send();
                break;

            case 'partial_hold':
                $workflow->materialsReceivedPartialHold(
                    $record,
                    $data['note'] ?? null,
                    $data['missing_items'] ?? null
                );
                Notification::make()
                    ->warning()
                    ->title('استلام جزئي — المهمة موقوفة حتى استكمال النواقص')
                    ->send();
                break;

            case 'issue':
                $workflow->materialsReceivedIssue(
                    $record,
                    $data['note'] ?? null,
                    $data['issue_details'] ?? null
                );
                Notification::make()
                    ->warning()
                    ->title('استلام به مشكلة — تم تحويل المهمة للمشتريات لمعالجة المشكلة')
                    ->send();
                break;
        }
    }
}
