<?php

namespace App\Filament\Resources;

use App\Models\DepartmentCategories;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Enums\FiltersLayout;

class DepartmentCategoriesResource extends Resource
{
    protected static ?string $model = DepartmentCategories::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $modelLabel = 'تصنيف';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('category_name')
                    ->required()
                    ->label('اسم التصنيف')
                    ->maxLength(50)
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('description')
                    ->maxLength(65535)
                    ->label('وصف التصنيف')
                    ->columnSpanFull(),

                Forms\Components\ColorPicker::make('color_code')
                    ->default('#95a5a6')
                    ->label('لون التصنيف')
                    ->hex(),

                Forms\Components\TextInput::make('icon')
                    ->maxLength(50)
                    ->label('أيقونة التصنيف')
                    ->hint('Use Heroicons or other icon class names'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->filtersLayout(FiltersLayout::AboveContentCollapsible)
            ->paginated([10, 25, 50, 100])
            ->deferLoading()
            ->columns([
                Tables\Columns\TextColumn::make('category_name')
                    ->label('اسم التصنيف')
                    ->searchable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('وصف التصنيف')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\ColorColumn::make('color_code')
                    ->copyable()
                    ->label('لون التصنيف')
                    ->copyMessage('Color code copied'),

                Tables\Columns\IconColumn::make('icon')
                    ->label('أيقونة التصنيف')
                    ->icon(fn(string $state): string => $state),
            ])
            ->filters([
                //
            ])

            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading('تعديل التصنيف'),

                Tables\Actions\DeleteAction::make(),
            ])

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\DepartmentCategoriesResource\Pages\ListDepartmentCategories::route('/'),
        ];
    }
}
