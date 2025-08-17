<?php

namespace App\Filament\Pages;

use App\Models\ProductionTask;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use BackedEnum;

class FactoryManagerTaskReview extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string | BackedEnum | null $navigationIcon  = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'مراجعة المهام';
    protected static ?string $title           = 'مراجعة مهام الأقسام';
    protected static ?string $slug            = 'tasks/review';
    protected string  $view            = 'filament.pages.factory-manager-task-review';

    public static function canAccess(): bool
    {
        if (! auth()->check()) return false;

        $user = auth()->user();

        $isAdmin = (
            $user->id === 1
            || (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin','super-admin','owner']))
            || $user->can('super-admin')
            || $user->can('admin')
        );

        return $isAdmin || $user->can('factory_manager.review_tasks');
    }


    public static function shouldRegisterNavigation(): bool
    {
        return self::canAccess();
    }

    private function statusColor(string $state): string
    {
        return match ($state) {
            'completed'    => 'success',
            'in_progress'  => 'warning',
            'blocked'      => 'danger',
            'cancelled'    => 'danger',
            'under_review' => 'purple',
            'rework'       => 'pink',
            'acknowledged' => 'info',
            'assigned'     => 'primary',
            default        => 'gray',
        };
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('المهام المطلوب مراجعتها')
            ->query(fn (): Builder => ProductionTask::query()
                ->with(['project', 'department', 'employee.user'])
                ->where('status', 'under_review')
                ->latest('updated_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('project.project_name')
                    ->label('المشروع')
                    ->searchable()->wrap(),

                Tables\Columns\TextColumn::make('department.dept_name')
                    ->label('القسم')
                    ->badge()->color('info'),

                Tables\Columns\TextColumn::make('employee.employee_name')
                    ->label('الموظف'),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state) => $this->statusColor($state)),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('تاريخ التسليم المتوقع')
                    ->date()->placeholder('—'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->since(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('department_id')
                    ->label('القسم')
                    ->relationship('department', 'dept_name'),
                Tables\Filters\Filter::make('due_soon')
                    ->label('مستحقة خلال 7 أيام')
                    ->query(fn (Builder $q) => $q->whereDate('due_date', '<=', now()->addDays(7))),
            ])
            ->actions([
                // اعتماد = إكمال
                Action::make('approve')
                    ->label('اعتماد (إكمال)')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'under_review')
                    ->form([
                        Forms\Components\Textarea::make('note')
                            ->label('ملاحظة (اختياري)')
                            ->rows(3)
                            ->nullable(),
                        // (اختياري) إرفاق ملف تسليم نهائي:
                        // Forms\Components\FileUpload::make('deliverable')->label('ملف التسليم').->directory('projects/deliverables'),
                    ])
                    ->requiresConfirmation()
                    ->action(function (ProductionTask $record, array $data) {
                        $record->update([
                            'status' => 'completed',
                            'notes'  => trim(($record->notes ? $record->notes."\n" : '').'[Manager Approved] '.($data['note'] ?? '')),
                            // 'completed_at' => now(), // لو لديك عمود
                        ]);

                        // إشعار للموظف (بريد + داخلي)
                        if ($user = $record->employee?->user) {
                            $user->notify(new \App\Notifications\TaskReviewResultNotification($record, approved: true, managerNote: $data['note'] ?? null));
                        }

                        Notification::make()->title('تم اعتماد المهمة وإكمالها')->success()->send();
                    }),

                // إعادة عمل
                Action::make('request_rework')
                    ->label('طلب إعادة عمل')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn ($record) => $record->status === 'under_review')
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('سبب إعادة العمل')
                            ->rows(3)
                            ->required(),
                        Forms\Components\DatePicker::make('new_due_date')
                            ->label('موعد تسليم جديد (اختياري)')
                            ->native(false)
                            ->nullable(),
                    ])
                    ->requiresConfirmation()
                    ->action(function (ProductionTask $record, array $data) {
                        $record->update([
                            'status'   => 'rework',
                            'due_date' => $data['new_due_date'] ?? $record->due_date,
                            'notes'    => trim(($record->notes ? $record->notes."\n" : '').'[Rework] '.$data['reason']),
                            // 'reviewed_at' => now(), // لو لديك عمود زمني
                        ]);

                        if ($user = $record->employee?->user) {
                            $user->notify(new \App\Notifications\TaskReviewResultNotification(
                                $record, approved: false, managerNote: $data['reason'] ?? null
                            ));
                        }

                        Notification::make()->title('تم إرجاع المهمة لإعادة العمل')->warning()->send();
                    }),

                // فتح المشروع
                Action::make('open_project')
                    ->label('فتح المشروع')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (ProductionTask $record) => url("/admin/projects/{$record->project_id}/manage-tasks"))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                // اعتماد جماعي
                Tables\Actions\BulkAction::make('bulk_approve')
                    ->label('اعتماد جماعي')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($records) {
                        foreach ($records as $record) {
                            if ($record->status !== 'under_review') continue;
                            $record->update(['status' => 'completed']);
                            if ($user = $record->employee?->user) {
                                $user->notify(new \App\Notifications\TaskReviewResultNotification($record, approved: true));
                            }
                        }
                        Notification::make()->title('تم اعتماد المهام المحددة')->success()->send();
                    }),

                // إعادة عمل جماعي
                Tables\Actions\BulkAction::make('bulk_rework')
                    ->label('إعادة عمل جماعي')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->form([
                        Forms\Components\Textarea::make('reason')->label('سبب إعادة العمل')->required()->rows(3),
                    ])
                    ->requiresConfirmation()
                    ->action(function ($records, array $data) {
                        foreach ($records as $record) {
                            if ($record->status !== 'under_review') continue;
                            $record->update([
                                'status' => 'rework',
                                'notes'  => trim(($record->notes ? $record->notes."\n" : '').'[Rework] '.$data['reason']),
                            ]);
                            if ($user = $record->employee?->user) {
                                $user->notify(new \App\Notifications\TaskReviewResultNotification($record, approved: false, managerNote: $data['reason']));
                            }
                        }
                        Notification::make()->title('تم إرجاع المهام المحددة لإعادة العمل')->warning()->send();
                    }),
            ])
            ->emptyStateHeading('لا توجد مهام قيد المراجعة')
            ->emptyStateDescription('عند إرسال مهام للمراجعة ستظهر هنا.');
    }
}
