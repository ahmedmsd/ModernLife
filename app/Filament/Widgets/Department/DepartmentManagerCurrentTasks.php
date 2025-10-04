<?php

namespace App\Filament\Widgets\Department;

use App\Filament\Resources\TaskResource;
use App\Models\ProductionTask;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class DepartmentManagerCurrentTasks extends TableWidget
{
    protected static ?string $heading = 'مهامي كمدير قسم';
    protected static ?int $sort = 24;
    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->check();
    }

    public function table(Table $table): Table
    {
        $uid = auth()->id();

        return $table
            ->query(
                ProductionTask::query()
                    ->with(['project','department'])
                    ->where('current_owner_user_id', $uid)
                    ->whereIn('status', [
                        'in_progress','materials_done','waiting_production','under_review'
                    ])
                    ->latest('id')
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#'),
                Tables\Columns\TextColumn::make('project.project_name')->label('المشروع'),
                Tables\Columns\TextColumn::make('department.dept_name')->label('القسم'),
                Tables\Columns\TextColumn::make('status')->label('الحالة')->badge(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('عرض')
                    ->icon('heroicon-o-eye')
                    ->url(fn (ProductionTask $record): string =>
                    TaskResource::getUrl('view', ['record' => $record])
                    )
            ])
            ->emptyStateHeading('لا توجد مهام تخصّك.');
    }
}
