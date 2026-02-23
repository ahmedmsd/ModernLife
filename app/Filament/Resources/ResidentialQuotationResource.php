<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ResidentialQuotationResource\Pages;
use App\Models\Quotation;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Support\Enums\FontWeight;

class ResidentialQuotationResource extends Resource
{
    protected static ?string $model = Quotation::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationGroup = 'ZOHO';
    protected static ?int $navigationSort = 3;
    protected static ?string $label = 'عرض فردي (Residential)';
    protected static ?string $pluralLabel = 'عروض فردية (Residential)';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات العرض')
                    ->schema([
                        Forms\Components\TextInput::make('quote_number')
                            ->label('رقم العرض')
                            ->disabled(),
                        Forms\Components\TextInput::make('customer_name')
                            ->label('اسم العميل (Zoho)')
                            ->disabled(),
                        Forms\Components\TextInput::make('contract_type')
                            ->label('نوع العقد (Contract Type)')
                            ->disabled(),
                        Forms\Components\Select::make('client_id')
                            ->label('العميل')
                            ->relationship('client', 'client_name')
                            ->disabled(),
                        Forms\Components\TextInput::make('quote_stage')
                            ->label('المرحلة')
                            ->disabled(),
                        Forms\Components\TextInput::make('zoho_module')
                            ->label('نوع العرض (Type)')
                            ->disabled(),
                    ])->columns(3),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('معلومات عرض السعر')
                    ->schema([
                        Infolists\Components\TextEntry::make('quote_number')
                            ->label('رقم العرض')
                            ->weight(FontWeight::Bold),
                        Infolists\Components\TextEntry::make('customer_name')
                            ->label('اسم العميل (Zoho)'),
                        Infolists\Components\TextEntry::make('contract_type')
                            ->label('نوع العقد (Contract Type)')
                            ->badge()
                            ->color('warning'),
                        Infolists\Components\TextEntry::make('quote_stage')
                            ->label('المرحلة')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'Draft' => 'gray',
                                'Negotiation' => 'warning',
                                'Delivered' => 'info',
                                'On Hold' => 'danger',
                                'Confirmed', 'Closed Won' => 'success',
                                'Closed Lost' => 'danger',
                                default => 'primary',
                            }),
                        Infolists\Components\TextEntry::make('total_amount')
                            ->label('المبلغ الإجمالي')
                            ->money('SAR'),
                        Infolists\Components\TextEntry::make('zoho_module')
                            ->label('نوع العرض (Type)')
                            ->badge()
                            ->color('info'),
                    ])->columns(4),

                Infolists\Components\Section::make('الأصناف والأسعار')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('items')
                            ->label('الأصناف')
                            ->schema([
                                Infolists\Components\TextEntry::make('product_name')
                                    ->label('المنتج'),
                                Infolists\Components\TextEntry::make('quantity')
                                    ->label('الكمية'),
                                Infolists\Components\TextEntry::make('unit_price')
                                    ->label('سعر الوحدة')
                                    ->money('SAR'),
                                Infolists\Components\TextEntry::make('discount')
                                    ->label('الخصم')
                                    ->money('SAR'),
                                Infolists\Components\TextEntry::make('tax')
                                    ->label('الضريبة')
                                    ->money('SAR'),
                                Infolists\Components\TextEntry::make('total')
                                    ->label('الإجمالي')
                                    ->money('SAR')
                                    ->weight(FontWeight::Bold),
                            ])->columns(6),
                    ]),

                Infolists\Components\Section::make('تفاصيل مالية إضافية')
                    ->schema([
                        Infolists\Components\TextEntry::make('sub_total')
                            ->label('المجموع الفرعي')
                            ->money('SAR'),
                        Infolists\Components\TextEntry::make('tax')
                            ->label('إجمالي الضريبة')
                            ->money('SAR'),
                        Infolists\Components\TextEntry::make('adjustment')
                            ->label('التسوية')
                            ->money('SAR'),
                        Infolists\Components\TextEntry::make('discount')
                            ->label('إجمالي الخصم')
                            ->money('SAR'),
                        Infolists\Components\TextEntry::make('total_amount')
                            ->label('المبلغ الصافي')
                            ->money('SAR')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->weight(FontWeight::Bold)
                            ->color('success'),
                    ])->columns(5),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('quote_number')
                    ->label('Quotation / Client')
                    ->description(fn (Quotation $record): string => $record->customer_name ?? '—')
                    ->searchable(['quote_number', 'customer_name'])
                    ->sortable()
                    ->weight(FontWeight::Bold),
                Tables\Columns\TextColumn::make('contract_date_combined')
                    ->label('Contract / Type / Date')
                    ->getStateUsing(fn (Quotation $record) => ($record->contract_type === 'New Contract' ? 'عقد جديد' : 'أعمال إضافية') . ' (' . $record->contract_type . ')')
                    ->description(fn (Quotation $record): string => $record->created_at ? $record->created_at->format('d-M-Y') : '—')
                    ->wrap(),
                Tables\Columns\TextColumn::make('quote_stage')
                    ->label('Status / Sales')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Draft' => 'gray',
                        'Negotiation' => 'warning',
                        'Delivered' => 'info',
                        'On Hold' => 'danger',
                        'Confirmed', 'Closed Won' => 'success',
                        'Closed Lost' => 'danger',
                        default => 'primary',
                    })
                    ->description(fn (Quotation $record): string => $record->sales_person ?? '—'),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total (inc VAT)')
                    ->money('SAR')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('print_zoho_quote')
                    ->label('Quotation')
                    ->button()
                    ->color('gray')
                    ->url(fn (Quotation $record) => $record->quotation_pdf_url)
                    ->openUrlInNewTab()
                    ->visible(fn (Quotation $record) => !empty($record->quotation_pdf_url)),
                Tables\Actions\Action::make('print_zoho_contract')
                    ->label('Contract')
                    ->button()
                    ->color('gray')
                    ->url(fn (Quotation $record) => $record->contract_pdf_url)
                    ->openUrlInNewTab()
                    ->visible(fn (Quotation $record) => !empty($record->contract_pdf_url)),
                Tables\Actions\Action::make('create_production_request')
                    ->label('إنشاء طلب تصنيع')
                    ->icon('heroicon-o-plus-circle')
                    ->color('primary')
                    ->action(function (Quotation $record) {
                        return redirect()->route('filament.admin.resources.production-requests.create', [
                            'quotation_id' => $record->id,
                            'client_id' => $record->client_id,
                            'project_name' => str_replace(['Commercial - ', 'Residential - '], '', $record->quote_number . ' - ' . $record->customer_name),
                        ]);
                    })
                    ->visible(fn (Quotation $record) => $record->productionRequest === null),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('zoho_module', 'ZohoCreator_ModernLife')
            ->where('contract_type', 'Residential');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListResidentialQuotations::route('/'),
            'create' => Pages\CreateResidentialQuotation::route('/create'),
            'view' => Pages\ViewResidentialQuotation::route('/{record}'),
            'edit' => Pages\EditResidentialQuotation::route('/{record}/edit'),
        ];
    }
}
