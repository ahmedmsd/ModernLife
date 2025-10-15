<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Enums\TaskStatus;
use App\Filament\Resources\TaskResource;
use App\Models\ProductionTask;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Forms;
use Illuminate\Database\Eloquent\Builder;

class ActiveTasks extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = TaskResource::class;
    protected static ?string $title = 'عرض المهام الجارية';
    protected static string $view = 'filament.pages.blank';

    public static function canAccess(array $parameters = []): bool
    {
        return auth()->check();
    }

    protected function isFactoryManager(): bool
    {
        $u = auth()->user();
        return (bool)$u?->hasAnyRole(['factory_manager', 'admin', 'super-admin']);
    }

    protected function isDepartmentManager(): bool
    {
        $u = auth()->user();
        return (bool)$u?->hasRole('department_manager');
    }

    public function table(Table $table): Table
    {
        $user = auth()->user();
        $emp = $user?->employee;
        $deptId = $emp?->department_id;

        return $table
            ->heading(
                $this->isFactoryManager()
                    ? 'كل المهام الجارية'
                    : ($this->isDepartmentManager() ? 'المهام الجارية لقسمي' : 'المهام الجارية')
            )
            ->query(function () use ($deptId, $user): Builder {
                $q = ProductionTask::query()
                    ->whereNotIn('status', ['completed', 'closed', 'cancelled'])
                    ->with([
                        'project:id,project_name',
                        'department:dept_id,dept_name',
                        'employee:employee_id,employee_name',
                    ])
                    ->latest('created_at');

                // مدير المصنع / أدمن: يرى كل شيء
                if ($this->isFactoryManager()) {
                    return $q;
                }

                // مدير القسم: قسمه فقط
                if ($this->isDepartmentManager()) {
                    return $deptId ? $q->where('department_id', $deptId) : $q->whereRaw('1=0');
                }

                // باقي الأدوار: نتركها كما هي (كل المهام الجارية)
                return $q;
            })
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('project.project_name')->label('المشروع')->searchable()->wrap(),
                Tables\Columns\TextColumn::make('department.dept_name')->label('القسم')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('employee.employee_name')->label('المسؤول')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')->badge()
                    ->formatStateUsing(fn($state) => TaskStatus::fromScalar($state)?->ar() ?? '—')
                    ->color(fn($state) => TaskStatus::fromScalar($state)?->color() ?? 'gray'),
                Tables\Columns\TextColumn::make('due_date')->label('تاريخ التسليم')->date()->sortable()->placeholder('—'),
                Tables\Columns\TextColumn::make('created_at')->label('أُنشئت')->since()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'قيد الإنشاء',
                        'assigned' => 'مُسندة',
                        'acknowledged' => 'تأكيد الاستلام',
                        'in_progress' => 'قيد التنفيذ',
                        'blocked' => 'متوقفة',
                        'under_review' => 'قيد المراجعة',
                        'rework' => 'إعادة عمل',
                    ]),
                Tables\Filters\SelectFilter::make('department_id')
                    ->label('القسم')
                    ->relationship('department', 'dept_name'),
                Tables\Filters\SelectFilter::make('assigned_to_employee_id')
                    ->label('المسؤول')
                    ->relationship('employee', 'employee_name')
                    ->searchable(),
                Filter::make('period')
                    ->label('الفترة (تاريخ الإنشاء)')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('من')->native(false),
                        Forms\Components\DatePicker::make('to')->label('إلى')->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $from = $data['from'] ?? null;
                        $to = $data['to'] ?? null;
                        return $query
                            ->when($from, fn(Builder $q) => $q->whereDate('created_at', '>=', $from))
                            ->when($to, fn(Builder $q) => $q->whereDate('created_at', '<=', $to));
                    }),
            ])
            ->recordUrl(fn(ProductionTask $record) => TaskResource::getUrl('view', ['record' => $record]))
            ->actions([
                Tables\Actions\Action::make('view')->label('عرض')->icon('heroicon-o-eye')
                    ->url(fn(ProductionTask $record) => TaskResource::getUrl('view', ['record' => $record])),
            ])
            ->paginated([25, 50, 100])
            ->emptyStateHeading('لا توجد مهام جارية حالياً');
    }
}
