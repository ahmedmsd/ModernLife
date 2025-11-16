<?php


namespace App\Filament\Widgets\Showroom;

use App\Filament\Resources\TaskResource;
use App\Models\ProductionTask;
use App\Models\Showroom;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ShowroomManagerCurrentTasks extends TableWidget
{
    protected static ?string $heading = 'مهام معارضي (جارية)';
    protected static ?int $sort = 22;
    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Auth::check() && Auth::user()->hasRole('showroom_manager');
    }

    public function table(Table $table): Table
    {
        $u = Auth::user();
        $managedShowroomIds = [];

        if ($u instanceof User) {
            $u->loadMissing('employee');
            $empId = $u->id;

            if ($empId) {
                $managedShowroomIds = Showroom::query()
                    ->where('manager_id', $empId)
                    ->pluck('id')
                    ->all();
            }
        }

        $activeStatuses = [
            'pending', 'assigned', 'received', 'in_progress',
            'materials_wait', 'materials_prep', 'materials_done',
            'waiting_production', 'under_review', 'approved', 'rejected', 'blocked',
        ];

        return $table
            ->query(
                ProductionTask::query()
                    ->with([
                        'project:id,project_name,production_request_id',
                        'project.productionRequest:id,showroom_id',
                        'project.productionRequest.showroom:id,name',
                        'department:dept_id,dept_name',
                        'employee:employee_id,employee_name',
                    ])
                    ->whereIn('status', $activeStatuses)
                    ->whereHas('project.productionRequest', function (Builder $w) use ($managedShowroomIds) {
                        $w->whereIn('showroom_id', $managedShowroomIds ?: [-1]);
                    })
                    ->latest('created_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('project.project_name')->label('المشروع')->searchable()->wrap(),
                Tables\Columns\TextColumn::make('project.productionRequest.showroom.name')->label('المعرض')->toggleable(),
                Tables\Columns\TextColumn::make('department.dept_name')->label('القسم')->sortable(),
                Tables\Columns\TextColumn::make('employee.employee_name')->label('المسؤول')->sortable(),
                Tables\Columns\TextColumn::make('status')->label('الحالة')->badge(),
                Tables\Columns\TextColumn::make('due_date')->label('تاريخ التسليم')->date()->sortable()->placeholder('—'),
                Tables\Columns\TextColumn::make('created_at')->label('أُنشئت')->since()->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('عرض')
                    ->icon('heroicon-o-eye')
                    ->url(fn(ProductionTask $record): string => TaskResource::getUrl('view', ['record' => $record])
                    ),
            ])
            ->emptyStateHeading('لا توجد مهام جارية لمعاريعك حالياً.');
    }
}
