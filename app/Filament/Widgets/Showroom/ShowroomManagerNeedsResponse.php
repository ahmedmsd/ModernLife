<?php

namespace App\Filament\Widgets\Showroom;

use App\Filament\Resources\ProductionRequestResource;
use App\Models\ProductionRequest;
use App\Models\Showroom;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ShowroomManagerNeedsResponse extends TableWidget
{
    protected static ?string $heading = 'طلبات بانتظار ردي (المعرض)';
    protected static ?int $sort = 21;
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
            $empId = $u->employee?->employee_id;

            if ($empId) {
                $managedShowroomIds = Showroom::query()
                    ->where('manager_id', $empId)
                    ->pluck('id')
                    ->all();
            }
        }

        $awaitingPhases = ['awaiting_showroom', 'showroom_review'];

        return $table
            ->query(
                ProductionRequest::query()
                    ->with([
                        'creator:id,name',
                        'client:id,client_name',
                        'project:id,project_name,production_request_id',
                        'productionRequest',
                        'showroom:id,name',
                    ])
                    ->whereIn('showroom_id', $managedShowroomIds ?: [-1])
                    ->whereIn('current_phase', $awaitingPhases)
                    ->latest('id')
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('project.project_name')->label('المشروع')->wrap()->toggleable(),
                Tables\Columns\TextColumn::make('showroom.name')->label('المعرض')->toggleable(),
                Tables\Columns\TextColumn::make('client.client_name')->label('العميل')->toggleable(),
                Tables\Columns\TextColumn::make('creator.name')->label('بواسطة')->toggleable(),
                Tables\Columns\TextColumn::make('phase_status')->label('الحالة')->badge(),
                Tables\Columns\TextColumn::make('created_at')->label('أُنشئ')->since()->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('review')
                    ->label('مراجعة')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->url(fn (ProductionRequest $record): string =>
                    ProductionRequestResource::getUrl('review', ['record' => $record])
                    ),
            ])
            ->emptyStateHeading('لا توجد طلبات بانتظارك حالياً.');
    }
}
