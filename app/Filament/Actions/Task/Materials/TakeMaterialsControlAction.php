<?php

namespace App\Filament\Actions\Task\Materials;

use App\Models\ProductionTask;
use App\Support\Tasks\TaskPageHelper;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class TakeMaterialsControlAction
{
    public static function make(ProductionTask $record, ?callable $redirectCallback = null): Action
    {
        return Action::make('take_materials_control')
            ->label('استعادة ملكية المهمة (بدء الاستلام)')
            ->icon('heroicon-o-hand-raised')
            ->color('warning')
            ->visible(fn() => static::isVisible($record))
            ->requiresConfirmation()
            ->modalHeading('استعادة التحكم بالمهمة')
            ->modalDescription('سيتم إعادة ملكية المهمة إليك لتمكنيك من تسجيل استلام الخامات المتوفرة والبدء في التصنيع.')
            ->action(function () use ($record, $redirectCallback) {
                $u = Auth::user();
                
                $record->update([
                    'current_owner_role'    => 'department_manager',
                    'current_owner_user_id' => $u->id,
                    'sent_to_owner_at'      => now(),
                    'received_by_owner_at'  => now(),
                ]);

                Notification::make()
                    ->success()
                    ->title('تم استعادة ملكية المهمة')
                    ->body('يمكنك الآن تسجيل استلام الخامات وبدء العمل.')
                    ->send();
                
                if ($redirectCallback) {
                    return $redirectCallback();
                }
            });
    }

    protected static function isVisible(ProductionTask $record): bool
    {
        $u = Auth::user();
        if (!$u || !$u->hasRole('department_manager')) {
            return false;
        }

        // Only visible if NOT the owner
        if ($record->current_owner_role === 'department_manager') {
            return false;
        }

        // Only meaningful if it's currently with purchasing or quality (though usually purchasing for materials)
        if ($record->current_owner_role !== 'purchasing_manager') {
            return false;
        }

        // Only if there are material requests (meaningful context)
        return $record->materialRequests()->exists();
    }
}
