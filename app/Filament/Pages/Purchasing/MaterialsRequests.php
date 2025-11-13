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

class MaterialsRequests extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon  = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'طلبات خامات (جارية)';
    protected static ?string $title           = 'طلبات خامات قيد المعالجة';
    protected static ?string $navigationGroup = 'المشتريات';
    protected static string $view = 'filament.pages.purchasing.materials-requests';

    public static function canAccess(): bool
    {
        $u = Auth::user();
        return $u && $u->hasAnyRole(['super-admin','admin', 'purchasing_manager', 'factory_manager', 'department_manager']);
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
            ->whereNull('provided_at')
            ->whereIn('status', ['requested', 'approved']);
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
                TextColumn::make('id')->label('رقم')->sortable()->searchable(),
                TextColumn::make('status')->label('الحالة')->badge()
                    ->color(fn (string $state) => match ($state) {
                        'requested' => 'warning',
                        'approved'  => 'success',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'requested' => 'قيد الطلب',
                        'approved'  => 'معتمد',
                        default     => $state,
                    }),
                TextColumn::make('department.dept_name')->label('القسم')->toggleable()->searchable(),
                TextColumn::make('task_id')->label('مهمة #')->sortable()->toggleable(),
                TextColumn::make('task.project.client.client_name')->label(' العميل')->sortable()->toggleable(),
                TextColumn::make('requestedBy.name')->label('أنشأه')->searchable(),
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
                TextColumn::make('requested_at')->label('تاريخ الطلب')->dateTime()->sortable(),
                TextColumn::make('expected_delivery_at')->label('تسليم متوقع')->dateTime()->sortable(),
                TextColumn::make('estimated_cost')->label('التكلفة المتوقعة')->money('sar', true)->sortable(),
                TextColumn::make('note')->label('ملاحظة')->limit(40)->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'requested' => 'قيد الطلب',
                        'approved'  => 'معتمد',
                    ]),
                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('من'),
                        Forms\Components\DatePicker::make('to')->label('إلى'),
                    ])
                    ->query(function (Builder $q, array $data) {
                        if (!empty($data['from'])) $q->whereDate('requested_at', '>=', $data['from']);
                        if (!empty($data['to']))   $q->whereDate('requested_at', '<=', $data['to']);
                    }),
            ])
            ->defaultSort('requested_at', 'desc');
    }
}
