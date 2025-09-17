<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CountryResource\Pages;
use App\Models\Country;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Forms\Components\Actions\Action;
use Filament\Notifications\Notification;

class CountryResource extends Resource
{
    protected static ?string $model = Country::class;
    protected static ?string $navigationGroup = 'الإعدادات الجغرافية';
    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';
    protected static ?string $navigationLabel = 'الدول';
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $label = 'إدارة الدول';
    protected static ?string $pluralLabel = 'الدول';
    protected static ?string $modelLabel = 'دولة';
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('اسم الدولة')
                ->required(),
            Forms\Components\TextInput::make('code')
                ->label('رمز الدولة')
                ->maxLength(5),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('الدولة')->searchable(),
                 Tables\Columns\TextColumn::make('cities_count')->counts('cities')->label('عدد المدن')->sortable(),
                Tables\Columns\TextColumn::make('code')->label('الرمز')->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->action(function (Country $record) {
                        if ($record->cities()->count() > 0) {
                            Notification::make()
                                ->title('لا يمكن حذف الدولة')
                                ->body('هذه الدولة مرتبطة بمدن ولا يمكن حذفها.')
                                ->danger()
                                ->send();
                            return;
                        }

                        $record->delete();
                        Notification::make()
                            ->title('تم الحذف بنجاح')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCountries::route('/'),

        ];
    }
}
