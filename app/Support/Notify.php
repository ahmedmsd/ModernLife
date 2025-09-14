<?php

namespace App\Support;

use App\Models\ProductionTask;
use App\Notifications\DepartmentTaskMail;
use Filament\Notifications\Notification as FNotification;
use Filament\Notifications\Actions\Action as FAction;

class Notify
{
    public static function departmentManager(ProductionTask $task, string $reason): void
    {
        $user = $task->department?->managerUser();
        if (! $user) return;

        // Filament bell
        $url = \App\Filament\Resources\ProjectResource::getUrl('view', ['record' => $task->project_id]);

        FNotification::make()
            ->title(match ($reason) {
                'created'    => 'مهمة جديدة لقسمك',
                'reassigned' => 'مهمة انتقلت إلى قسمك',
                'ownership'  => 'تم إسناد مهمة لك',
                default      => 'تنبيه مهمة',
            })
            ->body("المهمة: {$task->task_name} (#{$task->id}) • المشروع #{$task->project_id}")
            ->icon('heroicon-o-clipboard-document-check')
            ->success()
            ->actions([
                FAction::make('عرض المشروع')->button()->url($url),
            ])
            ->sendToDatabase($user);

        try {
            $user->notify(new DepartmentTaskMail($task, $reason));
        } catch (\Throwable $e) {
        }
    }
}
