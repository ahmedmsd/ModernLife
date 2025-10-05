<?php

namespace App\Filament\Widgets\Sales;

use App\Filament\Resources\ProductionRequestResource;
use App\Models\ProductionRequest;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class SalesInProgressRequests extends TableWidget
{
    protected static ?string $heading = 'طلباتي تحت الإجراء (المبيعات)';
    protected static ?int $sort = 20;
    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->check()
            && auth()->user()->hasRole('sales', 'web');
    }

    public function table(Table $table): Table
    {
        $uid = auth()->id();
        $terminal = ['completed','cancelled','rejected','approved_final'];

        return $table
            ->query(
                ProductionRequest::query()
                    ->with(['project','client'])
                    ->whereNotIn('phase_status', $terminal)
                    ->where(function ($q) use ($uid) {
                        $q->where('created_by', $uid)
                            ->orWhere('current_owner_user_id', $uid);
                    })
                    ->latest('id')
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('project.project_name')->label('المشروع'),
                Tables\Columns\TextColumn::make('client.client_name')->label('العميل'),
                Tables\Columns\TextColumn::make('phase_status')->label('الحالة')->badge(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('عرض')
                    ->icon('heroicon-o-eye')
                    ->url(fn (ProductionRequest $record): string =>
                    ProductionRequestResource::getUrl('review', ['record' => $record])
                    ),
            ])
            ->emptyStateHeading('لا توجد طلبات تخصّك حالياً.');
    }
}
