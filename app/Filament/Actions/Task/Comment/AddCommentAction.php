<?php

namespace App\Filament\Actions\Task\Comment;

use App\Filament\Resources\TaskResource;
use App\Models\ProductionTask;
use App\Models\TaskComment;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;

class AddCommentAction
{
    /**
     * Create the add comment action
     *
     * @param ProductionTask $record
     * @return Action
     */
    public static function make(ProductionTask $record): Action
    {
        return Action::make('addComment')
            ->label('تعليق سريع')
            ->icon('heroicon-m-chat-bubble-left-right')
            ->form(static::getForm())
            ->action(fn(array $data) => static::handle($record, $data));
    }

    /**
     * Get the form schema for the action
     *
     * @return array
     */
    protected static function getForm(): array
    {
        return [
            Textarea::make('body')
                ->label('نص التعليق')
                ->required()
                ->autosize(),
            
            FileUpload::make('attachments')
                ->label('مرفقات (اختياري)')
                ->multiple()
                ->directory('task-comments')
                ->downloadable()
                ->openable(),
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
        // Create the comment
        $comment = TaskComment::create([
            'task_id'     => $record->id,
            'user_id'     => auth()->id(),
            'body'        => $data['body'],
            'attachments' => isset($data['attachments']) ? array_values((array) $data['attachments']) : null,
        ]);

        // Load relationships for notification
        $record->loadMissing([
            'department.managerUser',
            'project.productionRequest.showroom.manager',
        ]);

        // Build task URL
        $taskUrl = TaskResource::getUrl('view', ['record' => $record]);

        // Collect recipients
        $recipients = static::getNotificationRecipients($record);

        // Send notifications
        if ($recipients->isNotEmpty()) {
            Notification::make()
                ->title("تعليق جديد على المهمة #{$record->id}")
                ->icon('heroicon-m-chat-bubble-left-right')
                ->body(\Illuminate\Support\Str::limit(strip_tags((string) $data['body']), 180))
                ->actions([
                    NotificationAction::make('عرض المهمة')
                        ->button()
                        ->url($taskUrl)
                        ->openUrlInNewTab(),
                ])
                ->sendToDatabase($recipients);
        }

        // Success notification
        Notification::make()
            ->title('تم إضافة التعليق')
            ->success()
            ->send();
    }

    /**
     * Get users who should be notified
     *
     * @param ProductionTask $record
     * @return \Illuminate\Support\Collection
     */
    protected static function getNotificationRecipients(ProductionTask $record): \Illuminate\Support\Collection
    {
        $recipients = collect();

        // Task creator
        if ($record->created_by && ($user = User::find($record->created_by))) {
            $recipients->push($user);
        }

        // Current owner
        if ($record->current_owner_user_id && ($owner = User::find($record->current_owner_user_id))) {
            $recipients->push($owner);
        }

        // Department manager
        $deptManagerUser = optional(optional($record->department)->manager)->user;
        if ($deptManagerUser) {
            $recipients->push($deptManagerUser);
        }

        // Showroom manager
        $showroomManagerUser = optional(
            optional(
                optional($record->project)->productionRequest
            )->showroom
        )->manager?->user;

        if ($showroomManagerUser) {
            $recipients->push($showroomManagerUser);
        }

        // Factory managers
        $factoryManagers = User::role('factory_manager')->get();
        if ($factoryManagers->isNotEmpty()) {
            $recipients = $recipients->merge($factoryManagers);
        }

        // Filter and deduplicate
        return $recipients
            ->filter()
            ->unique('id')
            ->reject(fn ($user) => (int) $user->id === (int) auth()->id())
            ->values();
    }
}
