<?php

namespace App\Filament\Widgets\Purchasing;

use App\Filament\Resources\TaskResource;
use App\Models\MaterialRequest;
use App\Models\ProductionTask;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class PurchasingOpenMaterialsRequests extends TableWidget
{
    protected static ?string $heading = 'طلباتي في المشتريات';
    protected static ?int $sort = 25;
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
                MaterialRequest::query()
                    ->with(['task.project','department','requestedBy'])
                    ->whereNull('provided_at')
                    ->whereIn('status', ['requested','approved'])
                    ->where(function ($q) use ($uid) {
                        $q->where('requested_by', $uid)
                            ->orWhere('provided_by', $uid)
                            ->orWhereHas('task', fn ($t) => $t->where('current_owner_user_id', $uid));
                    })
                    ->latest('id')
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#'),
                Tables\Columns\TextColumn::make('task.id')->label('المهمة'),
                Tables\Columns\TextColumn::make('department.dept_name')->label('القسم'),
                Tables\Columns\TextColumn::make('status')->label('الحالة')->badge(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('عرض')
                    ->icon('heroicon-o-eye')
                    ->url(fn (MaterialRequest $record): string =>
                    route('filament.admin.pages.purchasing.materials-requests')
                    )
            ])
            ->emptyStateHeading('لا توجد طلبات تخصّك.');
    }
}
