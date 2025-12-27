<?php

namespace App\Filament\Pages\Purchasing;

use App\Models\MaterialRequest;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Infolists;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Storage;

class MaterialsRequestsDone extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon  = 'heroicon-o-check-badge';
    protected static ?string $navigationLabel = 'طلبات خامات (منجزة)';
    protected static ?string $title           = 'طلبات خامات منجزة';
    protected static ?string $navigationGroup = 'المشتريات';
    protected static string $view = 'filament.pages.purchasing.materials-requests-done';

    public static function canAccess(): bool
    {
        $u = Auth::user();
        return $u && $u->hasAnyRole(['super-admin','admin', 'purchasing_manager', 'factory_manager','department_manager']);
    }

    public static function getNavigationBadge(): ?string
    {
        $q = static::baseQuery();
        if (Auth::user()?->hasRole('department_manager')) {
            $q->where('requested_by', Auth::id());
        }
        return (string) $q->count();
    }

    protected static function baseQuery(): Builder
    {
        return MaterialRequest::query()
            ->where(function ($q) {
                $q->where('status', 'fulfilled')
                    ->orWhereNotNull('provided_at');
            })
            ->where('status', '!=', 'partially_fulfilled');
    }

    public function table(Table $table): Table
    {
        $query = static::baseQuery()
            ->when(
                Auth::user()?->hasRole('department_manager'),
                fn (Builder $q) => $q->where('requested_by', Auth::id())
            )
            ->with([
                'task',
                'department',
                'requestedBy',
                'approvedBy',
                'providedBy',
            ]);

        return $table
            ->query($query)
            ->columns([
                TextColumn::make('id')->label('رقم #')->sortable()->searchable(),
                
                TextColumn::make('status')->label('الحالة')->badge()
                    ->color(fn ($state) => $state === 'fulfilled' ? 'success' : 'gray')
                    ->formatStateUsing(fn ($state) => $state === 'fulfilled' ? 'مُنفّذ' : $state),
                
                TextColumn::make('department.dept_name')->label('القسم')->toggleable()->searchable(),
                
                // Client Only (Wrapped)
                TextColumn::make('task.project.client.client_name')
                    ->label('العميل')
                    ->sortable()
                    ->searchable()
                    ->wrap()
                    ->toggleable(),

                // Requested By (Wrapped)
                TextColumn::make('requestedBy.name')->label('أنشأه')->searchable()->wrap()->toggleable(),
                
                TextColumn::make('po_file')
                    ->label('أمر الشراء')
                    ->formatStateUsing(fn($state) => $state ? 'تحميل' : '—')
                    ->badge()
                    ->color(fn($state) => $state ? 'primary' : 'gray')
                    ->url(fn($record) => $record->po_file && Storage::disk('public')->exists($record->po_file)
                        ? Storage::disk('public')->url($record->po_file)
                        : null
                    )
                    ->openUrlInNewTab()
                    ->tooltip('تحميل ملف أمر الشراء إن وُجد'),

                // Provided By (Wrapped)
                TextColumn::make('providedBy.name')->label('صرفها')->wrap()->toggleable(),
                
                // Provided At (Condensed)
                TextColumn::make('provided_at')
                    ->label('تاريخ الصرف')
                    ->formatStateUsing(fn ($state) => $state ? '<div class="flex flex-col gap-0 leading-tight"><span>' . \Carbon\Carbon::parse($state)->format('Y-m-d') . '</span><span class="text-xs text-gray-500">' . \Carbon\Carbon::parse($state)->format('H:i') . '</span></div>' : '—')
                    ->html()
                    ->sortable(),

                TextColumn::make('actual_cost')->label('التكلفة')->money('sar', true)->sortable(),
                
                TextColumn::make('invoice_no')->label('فاتورة #')->toggleable(),
                
                TextColumn::make('invoice_date')->label('تاريخ الفاتورة')->date('Y-m-d')->toggleable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('')
                    ->icon('heroicon-o-eye')
                    ->url(fn (MaterialRequest $record): string => ViewMaterialRequest::getUrl(['record' => $record]))
                    ->tooltip('عرض التفاصيل'),
            ])
            ->filters([
                Tables\Filters\Filter::make('provided_range')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('من'),
                        Forms\Components\DatePicker::make('to')->label('إلى'),
                    ])
                    ->query(function (Builder $q, array $data) {
                        if (!empty($data['from'])) $q->whereDate('provided_at', '>=', $data['from']);
                        if (!empty($data['to']))   $q->whereDate('provided_at', '<=', $data['to']);
                    }),
            ])
            ->defaultSort('provided_at', 'desc');
    }
}
