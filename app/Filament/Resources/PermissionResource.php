<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermissionResource\Pages\CreatePermission;
use App\Filament\Resources\PermissionResource\Pages\EditPermission;
use App\Filament\Resources\PermissionResource\Pages\ListPermissions;
use Filament\Forms;
use Filament\Tables;
use Spatie\Permission\Models\Permission;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Filament\Resources\PermissionResource\Pages;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Facades\Filament;
use Illuminate\Support\Str;

class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;
    protected static ?string $navigationIcon = 'heroicon-o-key';
    protected static ?string $navigationLabel = 'الصلاحيات';
    protected static ?string $navigationGroup = 'إدارة الصلاحيات';
    protected static bool $shouldRegisterNavigation = false;
    public static function form(Form $form): Form
    {
        return $form
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
            'index' => ListPermissions::route('/'),
            'create' => CreatePermission::route('/create'),
            'edit' => EditPermission::route('/{record}/edit'),
        ];
    }
}
