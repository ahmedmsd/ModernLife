<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CityResource\Pages;
use App\Filament\Resources\CityResource\Pages\ListCities;
use App\Models\City;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;

class CityResource extends Resource
{
    protected static ?string $model = City::class;
    protected static ?string $navigationGroup = 'الإعدادات الجغرافية';
    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    protected static ?string $navigationLabel = 'المدن';
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $label = 'إدارة المدن';
    protected static ?string $pluralLabel = 'المدن';
    protected static ?string $modelLabel = 'مدينة';

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('اسم المدينة')
                ->required(),

            Forms\Components\Select::make('country_id')
                ->label('الدولة')
                ->relationship('country', 'name')
                ->searchable()
                ->preload()
                ->createOptionForm([
                    Forms\Components\TextInput::make('name')->label('اسم الدولة')->required(),
                    Forms\Components\TextInput::make('code')->label('رمز الدولة')->maxLength(5),
                ])
                ->createOptionAction(fn (Action $action) => $action->modal()) // modal
                ->editOptionForm([
                    Forms\Components\TextInput::make('name')->label('اسم الدولة')->required(),
                    Forms\Components\TextInput::make('code')->label('رمز الدولة')->maxLength(5),
                ])
                ->editOptionAction(fn (Action $action) => $action->modal()), // modal
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('المدينة')->searchable(),
                Tables\Columns\TextColumn::make('country.name')->label('الدولة'),
            ])
            ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCities::route('/'),
        ];
    }
}
