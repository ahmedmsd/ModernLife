<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesOrderResource\Pages;
use App\Filament\Resources\SalesOrderResource\RelationManagers;
use App\Models\SalesOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Support\Enums\FontWeight;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SalesOrderResource extends Resource
{
    protected static ?string $model = SalesOrder::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'ZOHO';
    protected static ?int $navigationSort = 4;
    protected static ?string $label = 'أمر بيع (Sales Order)';
    protected static ?string $pluralLabel = 'أوامر البيع (Sales Orders)';
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['client']);
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات أمر البيع')
                    ->schema([
                        Forms\Components\TextInput::make('so_number')
                            ->label('رقم الأمر')
                            ->disabled(),
                        Forms\Components\TextInput::make('subject')
                            ->label('الموضوع')
                            ->disabled(),
                        Forms\Components\Select::make('client_id')
                            ->label('العميل')
                            ->relationship('client', 'client_name')
                            ->disabled(),
                        Forms\Components\TextInput::make('status')
                            ->label('الحالة')
                            ->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('معلومات أمر البيع')
                    ->schema([
                        Infolists\Components\TextEntry::make('so_number')
                            ->label('رقم أمر البيع')
                            ->weight(FontWeight::Bold),
                        Infolists\Components\TextEntry::make('subject')
                            ->label('الموضوع'),
                        Infolists\Components\TextEntry::make('client.client_name')
                            ->label('العميل'),
                        Infolists\Components\TextEntry::make('status')
                            ->label('الحالة')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'Created' => 'gray',
                                'Approved' => 'success',
                                'Delivered' => 'info',
                                'Cancelled' => 'danger',
                                default => 'primary',
                            }),
                        Infolists\Components\TextEntry::make('total_amount')
                            ->label('المبلغ الإجمالي')
                            ->money('SAR'),
                    ])->columns(3),

                Infolists\Components\Section::make('بنود الطلب (أصناف الإنتاج)')
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
                                Infolists\Components\TextEntry::make('total')
                                    ->label('الإجمالي')
                                    ->money('SAR')
                                    ->weight(FontWeight::Bold),
                            ])->columns(4),
                    ]),

                Infolists\Components\Section::make('ملخص مالي')
                    ->schema([
                        Infolists\Components\TextEntry::make('sub_total')
                            ->label('المجموع الفرعي')
                            ->money('SAR'),
                        Infolists\Components\TextEntry::make('tax')
                            ->label('الضريبة')
                            ->money('SAR'),
                        Infolists\Components\TextEntry::make('adjustment')
                            ->label('التسوية')
                            ->money('SAR'),
                        Infolists\Components\TextEntry::make('total_amount')
                            ->label('المبلغ النهائي')
                            ->money('SAR')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->weight(FontWeight::Bold)
                            ->color('success'),
                    ])->columns(4),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('so_number')
                    ->label('رقم الأمر')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject')
                    ->label('الموضوع')
                    ->searchable(),
                Tables\Columns\TextColumn::make('client.client_name')
                    ->label('العميل')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('المبلغ الإجمالي')
                    ->money('SAR'),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Created' => 'gray',
                        'Approved' => 'success',
                        'Delivered' => 'info',
                        'Cancelled' => 'danger',
                        default => 'primary',
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListSalesOrders::route('/'),
            'create' => Pages\CreateSalesOrder::route('/create'),
            'view' => Pages\ViewSalesOrder::route('/{record}'),
            'edit' => Pages\EditSalesOrder::route('/{record}/edit'),
        ];
    }
}
