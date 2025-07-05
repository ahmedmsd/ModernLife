<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Spatie\Permission\Models\Role;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Filament\Resources\RoleResource\Pages;
use Spatie\Permission\Models\Permission;
use Filament\Forms\Components\Select;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel = 'الأدوار';
    protected static ?string $navigationGroup = 'إدارة الصلاحيات';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('اسم الدور')
                    ->required()
                    ->unique(ignoreRecord: true),

                Select::make('permissions')
                    ->label('الصلاحيات')
                    ->multiple()
                    ->relationship('permissions', 'name')
                    ->preload()
                    ->searchable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('اسم الدور')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('permissions.name')
                    ->label('الصلاحيات')
                    ->badge()
                    ->limit(3),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()->label('حذف الكل'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
