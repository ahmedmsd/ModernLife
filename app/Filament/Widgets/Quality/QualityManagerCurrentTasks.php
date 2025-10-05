<?php

namespace App\Filament\Widgets\Quality;

use App\Filament\Resources\TaskResource;
use App\Models\ProductionTask;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class QualityManagerCurrentTasks extends TableWidget
{
    protected static ?string $heading = 'مهامي في الجودة';
    protected static ?int $sort = 26;
    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $u = auth()->user();
        if (! $u) return false;

        if (method_exists($u, 'can') && $u->can('view_widget_quality_manager_current_tasks')) {
            return true;
        }

        return $u->hasAnyRole(['quality_manager','admin','super-admin']);
    }

    public function table(Table $table): Table
    {
        $uid = auth()->id();

        return $table
            ->query(
                \App\Models\ProductionTask::query()
                    ->with(['project','department'])
                    ->where('current_owner_role', 'quality_manager')
                    ->whereIn('status', [
                        'pending','in_progress','under_review',
                        'materials_wait','materials_prep','materials_done',
                        'waiting_production','on_hold',
                        'quality_check','quality_after_manufacture','quality_after_installation',
                    ])
                    ->latest('id')
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('project.project_name')->label('المشروع')->searchable(),
                Tables\Columns\TextColumn::make('department.dept_name')->label('القسم')->toggleable(),
                Tables\Columns\TextColumn::make('status')->label('الحالة')->badge(),
                Tables\Columns\TextColumn::make('due_date')->label('تاريخ التسليم')->date()->sortable(),
                Tables\Columns\TextColumn::make('sent_to_owner_at')->label('أُرسلت لي')->since()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('عرض')
                    ->icon('heroicon-o-eye')
                    ->url(fn (ProductionTask $record) => TaskResource::getUrl('view', ['record' => $record])),
            ])
            ->emptyStateHeading('لا توجد مهام جودة حالية.');
    }
}
