<?php

namespace App\Filament\Widgets\Factory;

use App\Filament\Resources\ProductionRequestResource;
use App\Filament\Resources\TaskResource;
use App\Models\MaterialRequest;
use App\Models\ProductionRequest;
use App\Models\ProductionTask;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class FactoryManagerCurrentTasks extends TableWidget
{
    protected static ?string $heading = 'مهامي الجارية (المصنع)';
    protected static ?int $sort = 23;
    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->check()
            && (auth()->user()->hasAnyRole(['factory_manager'], 'web'));
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
                        'in_progress','materials_wait','materials_prep','returned_to_factory',
                        'materials_done','waiting_production','under_review'
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
                    ->url(fn ($record) => TaskResource::getUrl('view', ['record' => $record->getKey()]))

            ])
            ->emptyStateHeading('لا توجد مهام تخصّك.');
    }
}
