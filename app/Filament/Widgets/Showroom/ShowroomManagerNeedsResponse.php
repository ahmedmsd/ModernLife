<?php

namespace App\Filament\Widgets\Showroom;

use App\Filament\Resources\ProductionRequestResource;
use App\Models\ProductionRequest;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class ShowroomManagerNeedsResponse extends TableWidget
{
    protected static ?string $heading = 'طلبات بانتظار ردي (المعرض)';
    protected static ?int $sort = 21;
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
                ProductionRequest::query()
                    ->with(['project','client','showroom'])
                    ->where('current_owner_user_id', $uid)
                    ->whereIn('current_phase', ['awaiting_showroom','showroom_review'])
                    ->latest('id')
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#'),
                Tables\Columns\TextColumn::make('project.project_name')->label('المشروع'),
                Tables\Columns\TextColumn::make('client.client_name')->label('العميل'),
                Tables\Columns\TextColumn::make('phase_status')->label('الحالة')->badge(),
            ])
            ->actions([
                Tables\Actions\Action::make('review')
                    ->label('مراجعة')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->url(fn (ProductionRequest $record): string =>
                    ProductionRequestResource::getUrl('review', ['record' => $record])
                    ),
            ])
            ->emptyStateHeading('لا توجد طلبات بانتظارك.');
    }
}
