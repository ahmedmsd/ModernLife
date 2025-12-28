<?php

namespace App\Filament\Widgets\Purchasing;

use App\Filament\Pages\Purchasing\MaterialsRequests;
use App\Models\MaterialRequest;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class PurchasingOpenMaterialsRequests extends TableWidget
{
    protected static ?string $heading = 'طلبات خامات قيد المعالجة';
    protected static ?int $sort = 25;
    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->hasRole('purchasing_manager', 'web');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                MaterialRequest::query()
                    ->with(['task.project.client', 'department', 'requestedBy'])
                    ->whereNull('provided_at')
                    ->whereIn('status', ['requested', 'approved'])
                    ->latest('id')
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('task.project.client.client_name')
                    ->label('العميل / المشروع')
                    ->description(fn (MaterialRequest $record) => $record->task?->project?->project_name ?? '—')
                    ->searchable(['production_tasks.project_name', 'client_name']),
                Tables\Columns\TextColumn::make('task.id')->label('المهمة')->sortable(),
                Tables\Columns\TextColumn::make('department.dept_name')->label('القسم'),
                Tables\Columns\TextColumn::make('requestedBy.name')->label('مقدّم الطلب')->placeholder('—'),
                Tables\Columns\TextColumn::make('created_at')->label('تاريخ الطلب'),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'requested' => 'بانتظار اعتماد المشتريات',
                        'approved'  => 'بانتظار التوريد',
                        default     => '—',
                    })
                    ->color(fn (?string $state) => match ($state) {
                        'requested' => 'warning',
                        'approved'  => 'info',
                        default     => 'secondary',
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('review')
                    ->label('مراجعة الطلب')
                    ->icon('heroicon-o-eye')
                    ->url(fn (MaterialRequest $record): string =>
                    \App\Filament\Pages\Purchasing\ViewMaterialRequest::getUrl(['record' => $record])
                    ),
            ])
            ->emptyStateHeading('لا توجد طلبات خامات حالية.');
    }
}
