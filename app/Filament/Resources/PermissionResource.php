<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Spatie\Permission\Models\Permission;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use App\Filament\Resources\PermissionResource\Pages;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Facades\Filament;
use Illuminate\Support\Str;
use Filament\Schemas\Schema;
use BackedEnum;
use UnitEnum;

class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-key';
    protected static ?string $navigationLabel = 'الصلاحيات';
    protected static UnitEnum | string | null $navigationGroup = 'إدارة الصلاحيات';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('اسم الصلاحية')
                    ->required()
                    ->unique(ignoreRecord: true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم الصلاحية')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()->label('حذف الكل'),
            ])
            ->headerActions([
                Action::make('syncPermissions')
                    ->label('مزامنة الصلاحيات')
                    ->color('success')
                    ->icon('heroicon-o-arrow-path')
                    ->action(function () {
                        $resources = Filament::getResources();
                        $newPermissions = [];

                        foreach ($resources as $resource) {
                            $baseName = class_basename($resource);
                            $baseKey = Str::snake($baseName);

                            foreach (['view', 'create', 'edit', 'delete'] as $prefix) {
                                $permissionName = "{$prefix}_{$baseKey}";

                                if (!Permission::where('name', $permissionName)->exists()) {
                                    Permission::create([
                                        'name' => $permissionName,
                                        'guard_name' => 'web',
                                    ]);

                                    $newPermissions[] = $permissionName;
                                }
                            }
                        }

                        $customPages = [
                            'manage_project_tasks',
                            'view_project',
                            'review_production_request',
                            'view_production_timeline',
                        ];

                        foreach ($customPages as $page) {
                            $permissionName = "access_{$page}";

                            if (!Permission::where('name', $permissionName)->exists()) {
                                Permission::create([
                                    'name' => $permissionName,
                                    'guard_name' => 'web',
                                ]);

                                $newPermissions[] = $permissionName;
                            }
                        }

                        if (count($newPermissions)) {
                            Notification::make()
                                ->title('تمت إضافة صلاحيات جديدة')
                                ->body('تمت إضافة ' . count($newPermissions) . ' صلاحية جديدة.')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('لا توجد صلاحيات جديدة')
                                ->body('كل الصلاحيات موجودة بالفعل.')
                                ->info()
                                ->send();
                        }
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPermissions::route('/'),
            'create' => Pages\CreatePermission::route('/create'),
            'edit' => Pages\EditPermission::route('/{record}/edit'),
        ];
    }
}
