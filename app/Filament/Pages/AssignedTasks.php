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
use App\Support\Filament\HasShieldAccess;

class AssignedTasks extends Page implements HasTable
{
    use InteractsWithTable;
    use HasShieldAccess;

    protected static ?string $navigationIcon  = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'مهامي';
    protected static ?string $title           = 'مهامي المسندة';
    protected static ?string $slug            = 'my-tasks';
    protected static string  $view            = 'filament.pages.assigned-tasks';

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
            // 👇 يجعل الصف قابلاً للنقر لفتح صفحة عرض المهمة
            ->recordUrl(fn (ProductionTask $r) => route('filament.admin.resources.tasks.view', $r))
            ->recordAction(null)
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
                Tables\Columns\TextColumn::make('estimated_cost')->label('الميزانية')->money('SAR')->placeholder('—')->visible(fn () => ! auth()->user()?->hasRole('department_manager')),
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
                Tables\Actions\Action::make('viewTask')
                    ->label('عرض')
                    ->icon('heroicon-m-eye')
                    ->url(fn($record) => route('filament.admin.resources.tasks.view', $record)),

                Action::make('open_project')->label('فتح المشروع')->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (ProductionTask $r) => url("/admin/projects/{$r->project_id}/manage-tasks"))
                    ->openUrlInNewTab(),
            ])
            ->emptyStateHeading('لا توجد مهام مسندة حالياً')
            ->emptyStateDescription('عند إسناد مهام جديدة ستظهر هنا.');
    }
}
