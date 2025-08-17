<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Spatie\Permission\Models\Role;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use App\Filament\Resources\RoleResource\Pages;
use Spatie\Permission\Models\Permission;
use Filament\Forms\Components\Select;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Auth\Authenticatable;
use Filament\Schemas\Schema;
use BackedEnum;
use UnitEnum;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel = 'الأدوار';
    protected static UnitEnum | string | null $navigationGroup = 'إدارة الصلاحيات';

    public static function form(Schema $schema): Schema
    {
        return $schema
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
            ])->columns(1);
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
//                Tables\Actions\DeleteAction::make()->label('حذف'),
            ]);

    }
    public static function shouldRegisterNavigation(): bool
    {
        return true; // Force show in navigation
    }
    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user && $user->can('manage-roles');
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
