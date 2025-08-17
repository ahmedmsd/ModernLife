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

class AssignedTasks extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string | BackedEnum | null $navigationIcon  = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'مهامي';
    protected static ?string $title           = 'مهامي المسندة';
    protected static ?string $slug            = 'my-tasks';
    protected string  $view            = 'filament.pages.assigned-tasks';

    public static function canAccess(): bool
    {
        if (! auth()->check()) return false;

        $u = auth()->user();
        $isAdmin = (
            $u->id === 1
            || (method_exists($u, 'hasAnyRole') && $u->hasAnyRole(['admin','super-admin','owner']))
            || $u->can('super-admin') || $u->can('admin')
        );

        return $isAdmin || ($u->employee !== null);
    }

    protected function getEmployeeId(): ?int
    {
        return Auth::user()?->employee?->getKey();
    }

    private function statusColor(string $state): string
    {
        return match ($state) {
            'completed'    => 'success',
            'closed'       => 'success',
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
        $employeeId = $this->getEmployeeId();

        return $table
            ->heading('المهام المسندة إليّ')
            ->query(fn (): Builder => ProductionTask::query()
                ->with(['project', 'department'])
                ->when($employeeId, fn ($q) => $q->where('assigned_to_employee_id', $employeeId))
                ->latest('assigned_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('project.project_name')->label('المشروع')->searchable()->wrap(),
                Tables\Columns\TextColumn::make('department.dept_name')->label('القسم')->badge()->color('info')->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => $this->statusColor($state)),
        Tables\Columns\TextColumn::make('assigned_at')->label('تاريخ الإسناد')->dateTime('Y-m-d H:i')->sortable()->placeholder('—'),
                Tables\Columns\TextColumn::make('received_at')->label('تاريخ تأكيد الاستلام')->dateTime('Y-m-d H:i')->sortable()->placeholder('—'),
                Tables\Columns\TextColumn::make('due_date')->label('تاريخ التسليم المتوقع')->date()->placeholder('—'),
                Tables\Columns\TextColumn::make('assigned_budget')->label('الميزانية')->money('SAR')->placeholder('—'),
                Tables\Columns\TextColumn::make('notes')->label('ملاحظات')->limit(50)->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('الحالة')->options([
                    'pending'      => 'قيد الإنشاء',
                    'assigned'     => 'مُسندة',
                    'acknowledged' => 'تأكيد الاستلام',
                    'in_progress'  => 'قيد التنفيذ',
                    'blocked'      => 'متوقفة مؤقتًا',
                    'under_review' => 'قيد المراجعة',
                    'rework'       => 'إعادة عمل',
                    'completed'    => 'مكتملة',
                    'closed'       => 'مغلقة',
                    'cancelled'    => 'ملغاة',
                ]),
                Tables\Filters\Filter::make('due_soon')->label('تسليم خلال 7 أيام')
                    ->query(fn (Builder $q) => $q->whereDate('due_date', '<=', now()->addDays(7))),
            ])
            ->actions([
                Action::make('confirm_receipt')->label('تأكيد الاستلام')->icon('heroicon-o-check-circle')
                    ->visible(fn (ProductionTask $r) => in_array($r->status, ['pending','assigned'], true) && blank($r->received_at))
                    ->requiresConfirmation()
                    ->action(function (ProductionTask $r) {
                        $r->update([
                            'received_at' => now(),
                            'status'      => 'acknowledged',
                        ]);
                        Notification::make()->title('تم تأكيد استلام المهمة')->success()->send();
                    }),

                Action::make('start')->label('بدء التنفيذ')->icon('heroicon-o-play')
                    ->visible(fn (ProductionTask $r) => in_array($r->status, ['acknowledged','assigned','pending','rework'], true))
                    ->action(function (ProductionTask $r) {
                        $r->update([
                            'status'      => 'in_progress',
                            'received_at' => $r->received_at ?? now(),
                        ]);
                        Notification::make()->title('تم بدء تنفيذ المهمة')->success()->send();
                    }),

                Action::make('block')->label('إيقاف مؤقت')->icon('heroicon-o-pause-circle')->color('danger')
                    ->visible(fn (ProductionTask $r) => in_array($r->status, ['acknowledged','in_progress','rework'], true))
                    ->form([ Forms\Components\Textarea::make('reason')->label('سبب الإيقاف')->required()->rows(3) ])
                    ->requiresConfirmation()
                    ->action(function (ProductionTask $r, array $data) {
                        $r->update([
                            'status' => 'blocked',
                            'notes'  => trim(($r->notes ? $r->notes."\n" : '').'[Blocked] '.$data['reason']),
                        ]);
                        Notification::make()->title('تم إيقاف المهمة مؤقتًا')->warning()->send();
                    }),

                Action::make('resume')->label('استئناف')->icon('heroicon-o-play-circle')->color('success')
                    ->visible(fn (ProductionTask $r) => $r->status === 'blocked')
                    ->action(function (ProductionTask $r) {
                        $r->update(['status' => 'in_progress']);
                        Notification::make()->title('تم استئناف التنفيذ')->success()->send();
                    }),

                Action::make('submit_review')->label('إرسال للمراجعة')->icon('heroicon-o-paper-airplane')->color('primary')
                    ->visible(fn (ProductionTask $r) => $r->status === 'in_progress')
                    ->requiresConfirmation()
                    ->action(function (ProductionTask $r) {
                        $r->update(['status' => 'under_review']);
                        Notification::make()->title('تم إرسال المهمة للمراجعة')->success()->send();
                    }),

                Action::make('complete')->label('إكمال (سريع)')->icon('heroicon-o-check')->color('success')
                    ->visible(fn (ProductionTask $r) => in_array($r->status, ['under_review','in_progress'], true))
                    ->requiresConfirmation()
                    ->form([ Forms\Components\Textarea::make('completion_note')->label('ملاحظات الإكمال')->rows(3)->nullable() ])
                    ->action(function (ProductionTask $r, array $data) {
                        $r->update([
                            'status' => 'completed',
                            'notes'  => trim(($r->notes ? $r->notes."\n" : '').($data['completion_note'] ?? '')),
                        ]);
                        Notification::make()->title('تم إكمال المهمة')->success()->send();
                    }),

                Action::make('open_project')->label('فتح المشروع')->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (ProductionTask $r) => url("/admin/projects/{$r->project_id}/manage-tasks"))
                    ->openUrlInNewTab(),
            ])
            ->emptyStateHeading('لا توجد مهام مسندة حالياً')
            ->emptyStateDescription('عند إسناد مهام جديدة ستظهر هنا.');
    }
}
