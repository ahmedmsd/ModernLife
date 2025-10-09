<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use App\Models\ProductionTask;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;

class ActiveTasks extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = TaskResource::class;
    protected static ?string $title   = 'عرض المهام الجارية';
    protected static string $view     = 'filament.pages.blank';

//    public static function canAccess(array $parameters = []): bool
//    {
//        return auth()->check();
//    }

    public static function canViewAny(): bool
    {
        return auth()->check();
    }

    private function statusAr(?string $s): string
    {
        return match ($s) {
            'pending'       => 'قيد الإنشاء',
            'assigned'      => 'مُسندة',
            'acknowledged'  => 'تأكيد الاستلام',
            'in_progress'   => 'قيد التنفيذ',
            'blocked'       => 'متوقفة',
            'under_review'  => 'قيد المراجعة',
            'rework'        => 'إعادة عمل',
            'draft'         => 'مسودة',
            default         => ($s ?: '—'),
        };
    }

    private function roleAr(?string $r): string
    {
        return match ($r) {
            'factory_manager'       => 'مدير المصنع',
            'department_manager'    => 'مدير القسم',
            'quality_manager'       => 'مدير الجودة',
            'installation_manager'  => 'مسؤول التركيب',
            'purchasing_manager'    => 'مدير المشتريات',
            default                 => ($r ?: '—'),
        };
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('كل المهام الجارية')
            ->query(
                ProductionTask::query()
                    ->whereNotIn('status', ['completed','closed','cancelled'])
                    ->with([
                        'project:id,project_name',
                        'department:dept_id,dept_name',
                        'employee:employee_id,employee_name',
                    ])
                    ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END, due_date asc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('project.project_name')->label('المشروع')->searchable()->wrap(),
                Tables\Columns\TextColumn::make('department.dept_name')->label('القسم')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('employee.employee_name')->label('المسؤول')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('status')->label('الحالة')->badge()
                    ->formatStateUsing(fn (?string $s) => $this->statusAr($s))
                    ->color(fn (?string $s) => match ($s) {
                        'pending','draft'            => 'gray',
                        'assigned','acknowledged'    => 'warning',
                        'in_progress'                => 'info',
                        'blocked','rework'           => 'purple',
                        'under_review'               => 'cyan',
                        default                      => 'secondary',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('current_owner_role')
                    ->label('المالك الحالي')
                    ->formatStateUsing(fn (?string $r) => $this->roleAr($r))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('planned_start_at')->label('بداية (متوقّعة)')
                    ->dateTime('Y-m-d')->toggleable(),
                Tables\Columns\TextColumn::make('planned_end_at')->label('نهاية (متوقّعة)')
                    ->dateTime('Y-m-d')->toggleable(),
                Tables\Columns\TextColumn::make('due_date')->label('تاريخ التسليم')->date('Y-m-d')
                    ->color(fn ($d) => $d && now()->gt($d) ? 'danger' : 'secondary')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('أُنشئت')->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('الحالة')->options([
                    'pending'=>'قيد الإنشاء','assigned'=>'مُسندة','acknowledged'=>'تأكيد الاستلام',
                    'in_progress'=>'قيد التنفيذ','blocked'=>'متوقفة','under_review'=>'قيد المراجعة','rework'=>'إعادة عمل','draft'=>'مسودة',
                ]),
                Tables\Filters\SelectFilter::make('department_id')->label('القسم')
                    ->relationship('department','dept_name'),
                Tables\Filters\SelectFilter::make('assigned_to_employee_id')->label('المسؤول')
                    ->relationship('employee','employee_name'),
            ])
            ->actions([
                Tables\Actions\Action::make('view')->label('عرض')->icon('heroicon-o-eye')
                    ->url(fn(ProductionTask $r) => \App\Filament\Resources\TaskResource::getUrl('view', ['record'=>$r]))
                    ->openUrlInNewTab(),
            ])
            ->paginated([25, 50, 100]);
    }
}
