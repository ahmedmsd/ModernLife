<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Enums\TaskStatus;
use App\Filament\Resources\TaskResource;
use App\Models\ProductionTask;
use App\Models\Showroom;
use App\Models\User;
use App\Models\Department;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Forms;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ActiveTasks extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = TaskResource::class;
    protected static ?string $title = 'عرض المهام الجارية';
    protected static string $view = 'filament.pages.blank';

    protected function isAdminLike(): bool
    {
        $u = Auth::user();
        return (bool) $u?->hasAnyRole(['admin', 'super-admin', 'factory_manager']);
    }

    protected function isDepartmentManager(): bool
    {
        $u = Auth::user();
        return (bool) $u?->hasRole('department_manager');
    }

    protected function isShowroomManager(): bool
    {
        $u = Auth::user();
        return (bool) $u?->hasRole('showroom_manager');
    }

    protected function isQualityManager(): bool
    {
        $u = Auth::user();
        return (bool) $u?->hasRole('quality_manager');
    }

    public static function canAccess(array $parameters = []): bool
    {
        if (! Auth::check()) return false;
        $u = Auth::user();
        if ($u->hasAnyRole(['sales', 'purchasing'])) return false;
        return true;
    }

    public function table(Table $table): Table
    {
        $u = Auth::user();
        $deptId = null;
        $managedShowroomIds = [];
        $managedDeptIds = [];

        if ($u instanceof User) {
            $u->loadMissing('employee');
            $deptId = $u->employee?->department_id;

            $empId = $u->id;
            if ($empId) {
                $managedShowroomIds = Showroom::query()
                    ->where('manager_id', $empId)
                    ->pluck('id')
                    ->all();
            }

            $managedDeptIds = Department::query()
                ->where('manager_id', $u->id)
                ->pluck('dept_id')
                ->toArray();

            if (empty($managedDeptIds) && $deptId) {
                $managedDeptIds[] = $deptId;
            }
            // =======================================================
        }

        return $table
            ->heading(
                $this->isAdminLike() ? 'كل المهام الجارية'
                    : ($this->isQualityManager() ? 'مهام بانتظار الجودة'
                    : ($this->isShowroomManager() ? 'مهام المعرض الجارية'
                        : ($this->isDepartmentManager() ? 'مهام قسمي الجارية' : 'المهام الجارية')))
            )
            ->query(function () use ($managedDeptIds, $managedShowroomIds): Builder {
                $activeStatuses = [
                    'pending', 'assigned', 'received', 'in_progress',
                    'materials_wait', 'materials_prep', 'materials_done',
                    'waiting_production', 'under_review', 'approved', 'rejected', 'blocked',
                ];

                $q = ProductionTask::query()
                    ->whereNotIn('status', ['completed', 'closed', 'cancelled'])
                    ->with([
                        'project:id,project_name,production_request_id',
                        'project.productionRequest:id,showroom_id',
                        'project.productionRequest.showroom:id,name',
                        'department:dept_id,dept_name',
                        'assignedUser:id,name',
                    ])
                    ->latest('created_at');

                // 1) Admin-like => الكل
                if ($this->isAdminLike()) {
                    return $q;
                }

                // 2) Quality => المالك الحالي للجودة
                if ($this->isQualityManager()) {
                    return $q->where('current_owner_role', 'quality_manager');
                }

                // 3) Showroom Manager => مهام المعارض التي يديرها فعلاً
                if ($this->isShowroomManager()) {
                    if (! empty($managedShowroomIds)) {
                        return $q->whereHas('project.productionRequest', function (Builder $w) use ($managedShowroomIds) {
                            $w->whereIn('showroom_id', $managedShowroomIds);
                        });
                    }
                    return $q->whereRaw('1=0'); // لا يدير أي معرض
                }

                // 4) Department Manager => كل الأقسام التي يديرها المستخدم
                if ($this->isDepartmentManager()) {
                    if (! empty($managedDeptIds)) {
                        return $q->whereIn('department_id', $managedDeptIds);
                    }
                    return $q->whereRaw('1=0');
                }

                return $q->whereRaw('1=0');
            })
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('project.project_name')->label('المشروع')->searchable()->wrap(),
                Tables\Columns\TextColumn::make('project.productionRequest.showroom.name')
                    ->label('المعرض'),
                Tables\Columns\TextColumn::make('department.dept_name')->label('القسم')->searchable()->sortable(),

                Tables\Columns\TextColumn::make('assignedUser.name')
                    ->label('المسؤول')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')->badge()
                    ->formatStateUsing(fn ($state) => TaskStatus::fromScalar($state)?->ar() ?? '—')
                    ->color(fn ($state) => TaskStatus::fromScalar($state)?->color() ?? 'gray'),
                Tables\Columns\TextColumn::make('due_date')->label('تاريخ التسليم')->date()->sortable()->placeholder('—'),
                Tables\Columns\TextColumn::make('created_at')->label('أُنشئت')->since()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending'       => 'قيد الإنشاء',
                        'assigned'      => 'مُسندة',
                        'acknowledged'  => 'تأكيد الاستلام',
                        'in_progress'   => 'قيد التنفيذ',
                        'blocked'       => 'متوقفة',
                        'under_review'  => 'قيد المراجعة',
                        'rework'        => 'إعادة عمل',
                    ]),
                Tables\Filters\SelectFilter::make('department_id')
                    ->label('القسم')
                    ->relationship('department', 'dept_name')
                    ->visible(! $this->isDepartmentManager()),

                Tables\Filters\SelectFilter::make('assigned_to_user_id')
                    ->label('المسؤول')
                    ->relationship('assignedUser', 'name')
                    ->searchable()
                    ->visible(! $this->isDepartmentManager() && ! $this->isShowroomManager()),

                Filter::make('showroom_id')
                    ->label('المعرض')
                    ->form([
                        Forms\Components\Select::make('showroom_id')
                            ->label('اختر المعرض')
                            ->options(fn () => Showroom::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $sid = $data['showroom_id'] ?? null;
                        if (! $sid) return $query;
                        return $query->whereHas('project.productionRequest', function (Builder $w) use ($sid) {
                            $w->where('showroom_id', $sid);
                        });
                    })
                    ->visible(! $this->isShowroomManager()),

                Filter::make('period')
                    ->label('الفترة (تاريخ الإنشاء)')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('من')->native(false),
                        Forms\Components\DatePicker::make('to')->label('إلى')->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $from = $data['from'] ?? null;
                        $to   = $data['to']   ?? null;
                        return $query
                            ->when($from, fn (Builder $q) => $q->whereDate('created_at', '>=', $from))
                            ->when($to,   fn (Builder $q) => $q->whereDate('created_at', '<=', $to));
                    }),
            ])
            ->recordUrl(fn (ProductionTask $record) => TaskResource::getUrl('view', ['record' => $record]))
            ->actions([
                Tables\Actions\Action::make('view')->label('عرض')->icon('heroicon-o-eye')
                    ->url(fn (ProductionTask $record) => TaskResource::getUrl('view', ['record' => $record])),
            ])
            ->paginated([25, 50, 100])
            ->emptyStateHeading('لا توجد مهام جارية حالياً');
    }
}
