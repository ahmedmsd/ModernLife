<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShowroomResource\Pages;
use App\Models\Showroom;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use BackedEnum;
use UnitEnum;
use Filament\Schemas\Schema;

class ShowroomResource extends Resource
{
    protected static ?string $model = Showroom::class;
    protected static UnitEnum | string | null $navigationGroup = 'إدارة المعارض';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationLabel = 'المعارض';
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $label = 'إدارة المعارض';
    protected static ?string $pluralLabel = ' المعارض';
    protected static ?string $modelLabel = 'معرض';
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Forms\Components\TextInput::make('name')
                ->label('اسم المعرض')
                ->required(),

            Forms\Components\Textarea::make('address')
                ->label('العنوان'),

            Forms\Components\Select::make('city_id')
                ->label('المدينة')
                ->relationship('city', 'name')
                ->searchable()
                ->preload()
                ->required(),

            Forms\Components\TextInput::make('phone')
                ->label('رقم الهاتف'),

            Forms\Components\TextInput::make('email')
                ->label('البريد الإلكتروني')
                ->email(),

            Forms\Components\Select::make('manager_id')
                ->label('مدير المعرض')
                ->relationship('manager', 'employee_name')
                ->searchable()
                ->preload(),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('اسم المعرض')->searchable(),
                Tables\Columns\TextColumn::make('city.name')->label('المدينة'),
                Tables\Columns\TextColumn::make('phone')->label('الهاتف'),
                Tables\Columns\TextColumn::make('manager.employee_name')->label('مدير المعرض'),
            ])
            ->actions([
                // Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShowrooms::route('/'),
            'create' => Pages\CreateShowroom::route('/create'),
            'edit' => Pages\EditShowroom::route('/{record}/edit'),
            'view' => Pages\ViewShowroom::route('/{record}'),
        ];
    }
}
