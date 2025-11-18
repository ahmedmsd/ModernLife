<?php

namespace App\Filament\Widgets\Department;

use App\Filament\Resources\TaskResource;
use App\Models\Department;
use App\Models\ProductionTask;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class DepartmentManagerCurrentTasks extends TableWidget
{
    protected static ?string $heading = 'مهام قسمي (مدير القسم)';
    protected static ?int $sort = 20;
    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Auth::check() && Auth::user()->hasRole('department_manager');
    }

    public function table(Table $table): Table
    {
        $u = Auth::user();
        $managedDeptIds = [];
        if ($u instanceof User) {
            $managedDeptIds = Department::query()
                ->where('manager_id', $u->id)
                ->pluck('dept_id')
                ->toArray();

            $u->loadMissing('employee');
            $employeeDeptId = $u->employee?->department_id;
            if ($employeeDeptId && ! in_array($employeeDeptId, $managedDeptIds, true)) {
                $managedDeptIds[] = $employeeDeptId;
            }
        }

        $activeStatuses = [
            'pending', 'assigned', 'received', 'in_progress',
            'materials_wait', 'materials_prep', 'materials_done',
            'waiting_production', 'under_review', 'approved', 'rejected', 'blocked',
        ];

        $query = ProductionTask::query()
            ->with([
                'project:id,project_name,production_request_id',
                'project.productionRequest:id,showroom_id',
                'project.productionRequest.showroom:id,name',
                'department:dept_id,dept_name',
            ])
            ->when(! empty($managedDeptIds),
                fn (Builder $q) => $q->whereIn('department_id', $managedDeptIds),
                fn (Builder $q) => $q->whereRaw('1=0')
            )
            ->whereIn('status', $activeStatuses)
            ->latest('created_at');

        return $table
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('project.project_name')->label('المشروع')->searchable()->wrap(),
                Tables\Columns\TextColumn::make('project.productionRequest.showroom.name')->label('المعرض')->toggleable(),
                Tables\Columns\TextColumn::make('department.dept_name')->label('القسم')->sortable(),
                Tables\Columns\TextColumn::make('status')->label('الحالة')->badge(),
                Tables\Columns\TextColumn::make('due_date')->label('تاريخ التسليم')->date()->sortable()->placeholder('—'),
                Tables\Columns\TextColumn::make('created_at')->label('أُنشئت')->since()->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('عرض')
                    ->icon('heroicon-o-eye')
                    ->url(fn (ProductionTask $record): string =>
                    TaskResource::getUrl('view', ['record' => $record])
                    ),
            ])
            ->emptyStateHeading('لا توجد مهام لقسمك حالياً.');
    }
}
