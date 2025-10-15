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

class CompletedTasks extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = TaskResource::class;
    protected static ?string $title = 'المهام المكتملة';
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
                    ? 'كل المهام المكتملة'
                    : ($this->isDepartmentManager() ? 'المهام المكتملة لقسمي' : 'المهام المكتملة')
            )
            ->query(function () use ($deptId): Builder {
                $q = ProductionTask::query()
                    ->whereIn('status', ['completed', 'closed'])
                    ->with([
                        'project:id,project_name',
                        'department:dept_id,dept_name',
                        'employee:employee_id,employee_name',
                    ])
                    ->latest('completed_at');

                if ($this->isFactoryManager()) {
                    return $q;
                }

                if ($this->isDepartmentManager()) {
                    return $deptId ? $q->where('department_id', $deptId) : $q->whereRaw('1=0');
                }

                return $q;
            })
            ->defaultSort('completed_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('project.project_name')->label('المشروع')->searchable()->wrap(),
                Tables\Columns\TextColumn::make('department.dept_name')->label('القسم')->sortable(),
                Tables\Columns\TextColumn::make('employee.employee_name')->label('المسؤول')->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')->badge()
                    ->formatStateUsing(fn($state) => TaskStatus::fromScalar($state)?->ar() ?? '—')
                    ->color(fn($state) => TaskStatus::fromScalar($state)?->color() ?? 'gray'),
                Tables\Columns\TextColumn::make('created_at')->label('أُنشئت')->since()->sortable(),
                Tables\Columns\TextColumn::make('completed_at')->label('أُكتملت')->since()->sortable()->placeholder('—'),
                Tables\Columns\TextColumn::make('cycle_time')
                    ->label('مدة الإنجاز')
                    ->state(function (ProductionTask $r) {
                        if (!$r->created_at || !$r->completed_at) return '—';
                        $mins = $r->created_at->diffInMinutes($r->completed_at);
                        $days = intdiv($mins, 1440);
                        $hours = intdiv($mins % 1440, 60);
                        $m = $mins % 60;
                        $parts = [];
                        if ($days) $parts[] = $days . ' يوم';
                        if ($hours) $parts[] = $hours . ' ساعة';
                        if ($m || (!$days && !$hours)) $parts[] = $m . ' دقيقة';
                        return implode(' و ', $parts);
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('department_id')
                    ->label('القسم')
                    ->relationship('department', 'dept_name'),
                Tables\Filters\SelectFilter::make('assigned_to_employee_id')
                    ->label('المسؤول')
                    ->relationship('employee', 'employee_name')
                    ->searchable(),
                Filter::make('period')
                    ->label('الفترة (تاريخ الإكمال)')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('من')->native(false),
                        Forms\Components\DatePicker::make('to')->label('إلى')->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $from = $data['from'] ?? null;
                        $to = $data['to'] ?? null;
                        return $query
                            ->when($from, fn(Builder $q) => $q->whereDate('completed_at', '>=', $from))
                            ->when($to, fn(Builder $q) => $q->whereDate('completed_at', '<=', $to));
                    }),
            ])
            ->recordUrl(fn(ProductionTask $record) => TaskResource::getUrl('view', ['record' => $record]))
            ->actions([
                Tables\Actions\Action::make('view')->label('عرض')->icon('heroicon-o-eye')
                    ->url(fn(ProductionTask $record) => TaskResource::getUrl('view', ['record' => $record])),
            ])
            ->paginated([25, 50, 100])
            ->emptyStateHeading('لا توجد مهام مكتملة ضمن الشروط الحالية');
    }
}
