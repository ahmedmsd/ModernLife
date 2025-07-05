<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserGroupResource\Pages;
use App\Models\UserGroup;
use App\Models\Permission;
use App\Models\Employee;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables\Table;

class UserGroupResource extends Resource
{
    protected static ?string $model = UserGroup::class;

    protected static ?string $navigationIcon = 'heroicon-o-lock-closed';
    protected static ?string $navigationLabel = 'مجموعات المستخدمين';
    protected static ?string $pluralModelLabel = 'مجموعات المستخدمين';
    protected static ?string $modelLabel = 'مجموعة مستخدمين';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('group_name')
                ->label('اسم المجموعة')
                ->required()
                ->maxLength(255),

            Forms\Components\Textarea::make('description')
                ->label('الوصف')
                ->maxLength(500),

            Forms\Components\CheckboxList::make('permissions')
                ->label('الصلاحيات المرتبطة')
                ->relationship('permissions', 'name')
                ->columns(2),

            Forms\Components\CheckboxList::make('employees')
                ->label('الموظفون المنضمون')
                ->relationship('employees', 'employee_name')
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('group_name')
                    ->label('اسم المجموعة')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('الوصف')
                    ->limit(40)
                    ->wrap(),

                Tables\Columns\TextColumn::make('employees_count')
                    ->label('عدد الموظفين')
                    ->counts('employees'),

                Tables\Columns\TextColumn::make('permissions_count')
                    ->label('عدد الصلاحيات')
                    ->counts('permissions'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUserGroups::route('/'),
            'create' => Pages\CreateUserGroup::route('/create'),
            'edit'   => Pages\EditUserGroup::route('/{record}/edit'),
        ];
    }
}
