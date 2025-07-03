<?php
namespace App\Filament\Resources;

use App\Filament\Resources\DepartmentResource\Pages;
use App\Models\Department;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;


class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    protected static ?string $navigationIcon = 'heroicon-o-office-building';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('dept_name')
                    ->label('Department Name')
                    ->required()
                    ->maxLength(100),

                Forms\Components\TextInput::make('dept_code')
                    ->label('Department Code')
                    ->maxLength(20),


                Forms\Components\Select::make('parent_dept_id')
                    ->label('Parent Department')
                    ->nullable(),

                Forms\Components\TextInput::make('location')
                    ->label('Location')
                    ->maxLength(100),

                Forms\Components\TextInput::make('phone_extension')
                    ->label('Phone Extension')
                    ->maxLength(10),

                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->nullable(),

                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),

                Forms\Components\ColorPicker::make('color_code')
                    ->label('Color Code')
                    ->default('#3498db'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('dept_name')->label('Department Name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('dept_code')->label('Code')->sortable(),
                Tables\Columns\BooleanColumn::make('is_active')->label('Active'),
                Tables\Columns\TextColumn::make('factory.name')->label('Factory')->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // DepartmentManagersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDepartments::route('/'),
            'create' => Pages\CreateDepartment::route('/create'),
            'edit' => Pages\EditDepartment::route('/{record}/edit'),
        ];
    }
}
