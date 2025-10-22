<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Enums\TaskStatus;
use App\Filament\Resources\TaskResource;
use App\Models\ProductionTask;
use App\Models\Showroom;
use App\Models\User;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Forms;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class CompletedTasks extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = TaskResource::class;
    protected static ?string $title = 'المهام المكتملة';
    protected static string $view = 'filament.pages.blank';

    protected function isAdminLike(): bool
    {
        $u = Auth::user();
        return (bool)$u?->hasAnyRole(['admin', 'super-admin', 'factory_manager']);
    }

    protected function isDepartmentManager(): bool
    {
        $u = Auth::user();
        return (bool)$u?->hasRole('department_manager');
    }

    protected function isShowroomManager(): bool
    {
        $u = Auth::user();
        return (bool)$u?->hasRole('showroom_manager');
    }

    protected function isQualityManager(): bool
    {
        $u = Auth::user();
        return (bool)$u?->hasRole('quality_manager');
    }

    public static function canAccess(array $parameters = []): bool
    {
        if (!Auth::check()) return false;

        $u = Auth::user();
        if ($u->hasAnyRole(['sales', 'purchasing_manager'])) {
            return false;
        }

        return true;
    }

    public function table(Table $table): Table
    {
        $u = Auth::user();
        $deptId = null;
        $managedShowroomIds = [];

        if ($u instanceof User) {
            $u->loadMissing('employee');
            $deptId = $u->employee?->department_id;

            $empId = $u->employee?->employee_id;
            if ($empId) {
                $managedShowroomIds = Showroom::query()
                    ->where('manager_id', $empId)
                    ->pluck('id')
                    ->all();
            }
        }

        return $table
            ->heading(
                $this->isAdminLike() ? 'كل المهام المكتملة'
                    : ($this->isQualityManager() ? 'كل المهام المكتملة'
                    : ($this->isShowroomManager() ? 'مهام المعارض التي أديرها (مكتملة)'
                        : ($this->isDepartmentManager() ? 'مهام قسمي المكتملة' : 'المهام المكتملة')))
            )
            ->query(function () use ($deptId, $managedShowroomIds): Builder {
                $q = ProductionTask::query()
                    ->whereIn('status', ['completed', 'closed'])
                    ->with([
                        'project:id,project_name,production_request_id',
                        'project.productionRequest:id,showroom_id',
                        'project.productionRequest.showroom:id,name',
                        'department:dept_id,dept_name',
                        'employee:employee_id,employee_name',
                    ])
                    // ->orderByRaw('COALESCE(completed_at, updated_at, created_at) DESC');
                    ->latest('completed_at');

                // 1) Admin-like => الكل
                if ($this->isAdminLike()) {
                    return $q;
                }

                // 2) Quality Manager => الكل (لا يوجد needs action هنا)
                if ($this->isQualityManager()) {
                    return $q;
                }

                // 3) Showroom Manager => مهام معارضي
                if ($this->isShowroomManager()) {
                    if (!empty($managedShowroomIds)) {
                        return $q->whereHas('project.productionRequest', function (Builder $w) use ($managedShowroomIds) {
                            $w->whereIn('showroom_id', $managedShowroomIds);
                        });
                    }
                    return $q->whereRaw('1=0'); // لا يدير أي معرض
                }

                // 4) Department Manager => قسمه فقط
                if ($this->isDepartmentManager()) {
                    return $deptId
                        ? $q->where('department_id', $deptId)
                        : $q->whereRaw('1=0');
                }

                // أدوار أخرى: لا شيء
                return $q->whereRaw('1=0');
            })
            ->defaultSort('completed_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('project.project_name')->label('المشروع')->searchable()->wrap(),
                Tables\Columns\TextColumn::make('project.productionRequest.showroom.name')
                    ->label('المعرض'),
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
                    ->relationship('department', 'dept_name')
                    ->visible(!$this->isDepartmentManager()),
                Tables\Filters\SelectFilter::make('assigned_to_employee_id')
                    ->label('المسؤول')
                    ->relationship('employee', 'employee_name')
                    ->searchable()
                    ->visible(!$this->isDepartmentManager() && !$this->isShowroomManager()),
                // فلتر المعرض عبر السلسلة Task -> Project -> ProductionRequest -> showroom_id
                Filter::make('showroom_id')
                    ->label('المعرض')
                    ->form([
                        Forms\Components\Select::make('showroom_id')
                            ->label('اختر المعرض')
                            ->options(fn() => Showroom::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $sid = $data['showroom_id'] ?? null;
                        if (!$sid) return $query;
                        return $query->whereHas('project.productionRequest', function (Builder $w) use ($sid) {
                            $w->where('showroom_id', $sid);
                        });
                    })
                    ->visible(!$this->isShowroomManager()),
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
