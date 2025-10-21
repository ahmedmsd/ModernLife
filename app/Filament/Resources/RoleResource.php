<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use App\Filament\Resources\RoleResource\Pages\CreateRole;
use App\Filament\Resources\RoleResource\Pages\EditRole;
use App\Filament\Resources\RoleResource\Pages\ListRoles;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\CheckboxList;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon  = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel = 'الأدوار';
    protected static ?string $navigationGroup = 'إدارة الصلاحيات';
    protected static bool $shouldRegisterNavigation = false;


    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return static::getModel()::query()->where('guard_name', 'web');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_role') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('بيانات الدور')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('اسم الدور')
                        ->required()
                        ->afterStateUpdated(fn ($state, $set) => $set('name', trim($state)))
                        ->unique(
                            table: Role::query()->getModel()->getTable(),
                            column: 'name',
                            ignoreRecord: true,
                            modifyRuleUsing: fn (Unique $rule) => $rule->where('guard_name', 'web')
                        ),
                    Forms\Components\TextInput::make('guard_name')
                        ->label('الحارس')
                        ->default('web')
                        ->disabled()
                        ->dehydrated()
                        ->visibleOn('create'),
                ])->columns(2),

            Section::make('الصلاحيات')
                ->description('صلاحيات مجمّعة: كل كيان في مجموعة مستقلّة + مجموعة للصفحات + مجموعة للودجتس.')
                ->schema(self::buildPermissionFieldsets())   // ← أهم سطر
                ->collapsible()
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('اسم الدور')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('permissions.name')->label('الصلاحيات')->badge()->limit(4),
                Tables\Columns\TextColumn::make('users_count')->label('عدد المستخدمين')->counts('users')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('تعديل')
                    ->visible(fn () =>
                        auth()->user()?->hasRole(config('filament-shield.super_admin.role_name', 'super-admin'))
                        || (auth()->user()?->can('update_role') ?? false)
                    ),
                Tables\Actions\DeleteAction::make()
                    ->label('حذف')
                    ->visible(fn () =>
                        auth()->user()?->hasRole(config('filament-shield.super_admin.role_name', 'super-admin'))
                        || (auth()->user()?->can('delete_role') ?? false)
                    ),
            ])
            ->paginated([10, 25, 50]);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'edit'   => EditRole::route('/{record}/edit'),
        ];
    }

    protected static function buildPermissionFieldsets(): array
    {
        $matrix = self::buildPermissionMatrix(); // ['resources'=>[entity=>[id=>label]], 'pages'=>[id=>label], 'widgets'=>[id=>label]]

        $fieldsets = [];

        // Resources: Fieldset لكل كيان
        foreach ($matrix['resources'] as $entity => $entityOptions) {
            $label = self::translateEntity($entity);

            $fieldsets[] = Fieldset::make($label)
                ->schema([
                    CheckboxList::make('permissions_map.resources.' . $entity)
                        ->label('الأفعال')
                        ->options($entityOptions)         // [id => 'عرض/إنشاء/..']
                        ->columns(2)
//                        ->searchable()
                        ->dehydrated()                    // حتى تصل القيم إلى $data
                        ->afterStateHydrated(function (CheckboxList $component, ?Role $record) use ($entityOptions) {
                            $component->state(self::selectedIdsForOptions($record, $entityOptions));
                        }),
                ])
                ->columns(1);
        }

        // Pages group
        if (! empty($matrix['pages'])) {
            $pagesOptions = $matrix['pages'];

            $fieldsets[] = Fieldset::make('الصفحات')
                ->schema([
                    CheckboxList::make('permissions_map.pages')
                        ->label('صلاحيات الصفحات')
                        ->options($pagesOptions)
                        ->columns(2)
//                        ->searchable()
                        ->dehydrated()
                        ->afterStateHydrated(function (CheckboxList $component, ?Role $record) use ($pagesOptions) {
                            $component->state(self::selectedIdsForOptions($record, $pagesOptions));
                        }),
                ])
                ->columns(1);
        }

        // Widgets group
        if (! empty($matrix['widgets'])) {
            $widgetsOptions = $matrix['widgets'];

            $fieldsets[] = Fieldset::make('الودجتس')
                ->schema([
                    CheckboxList::make('permissions_map.widgets')
                        ->label('صلاحيات الودجتس')
                        ->options($widgetsOptions)
                        ->columns(2)
//                        ->searchable()
                        ->dehydrated()
                        ->afterStateHydrated(function (CheckboxList $component, ?Role $record) use ($widgetsOptions) {
                            $component->state(self::selectedIdsForOptions($record, $widgetsOptions));
                        }),
                ])
                ->columns(1);
        }

        return $fieldsets;
    }


    protected static function buildPermissionMatrix(): array
    {
        $perms = Permission::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get(['id','name']);

        $resources = []; // entity => [id => label]
        $pages     = []; // id => label
        $widgets   = []; // id => label

        $actionMap = [
            'view_any_'     => 'عرض الكل',
            'view_'         => 'عرض',
            'create_'       => 'إنشاء',
            'update_'       => 'تعديل',
            'delete_'       => 'حذف',
            'force_delete_' => 'حذف نهائي',
            'restore_'      => 'استعادة',
            'replicate_'    => 'نسخ',
            'reorder_'      => 'إعادة ترتيب',
        ];

        foreach ($perms as $perm) {
            $name = $perm->name;

            // Pages: access_xxx
            if (str_starts_with($name, 'access_')) {
                $page  = str($name)->after('access_')->replace(['_', '-'], ' ')->toString();
                $pages[$perm->id] = 'وصول: ' . ucfirst($page);
                continue;
            }

            // Widgets: view_xxx_widget
            if (str_starts_with($name, 'view_') && str_ends_with($name, '_widget')) {
                $widget = str($name)->between('view_', '_widget')->replace(['_', '-'], ' ')->toString();
                $widgets[$perm->id] = 'عرض: ' . ucfirst($widget);
                continue;
            }

            // Resources: action + entity
            foreach ($actionMap as $prefix => $arAction) {
                if (str_starts_with($name, $prefix)) {
                    $entity = str($name)->after($prefix)->toString(); // client, project, ...
                    $resources[$entity] ??= [];
                    $resources[$entity][$perm->id] = $arAction;
                    continue 2;
                }
            }

            // Others (fallback into "عام" ككيان)
            $entity = '_general';
            $resources[$entity] ??= [];
            $pretty = ucfirst(str($name)->replace(['_', '-'], ' ')->toString());
            $resources[$entity][$perm->id] = $pretty;
        }

        ksort($resources, SORT_NATURAL | SORT_FLAG_CASE);

        if (isset($resources['_general'])) {
            $gen = $resources['_general'];
            unset($resources['_general']);
            $resources['صلاحيات عامة'] = $gen;
        }

        return [
            'resources' => $resources,
            'pages'     => $pages,
            'widgets'   => $widgets,
        ];
    }

    protected static function translateEntity(string $entity): string
    {
        $key = str($entity)->replace(['_', '-'], ' ')->trim()->toString();

        $map = [
            'client'            => 'العملاء',
            'project'           => 'المشاريع',
            'production request'=> 'طلبات التصنيع',
            'task'              => 'المهام',
            'material request'  => 'طلبات الخامات',
            'department'        => 'الأقسام',
            'user'              => 'المستخدمون',
            'role'              => 'الأدوار',
            'permission'        => 'الصلاحيات',
            'صلاحيات عامة'      => 'صلاحيات عامة',
        ];

        return $map[$key] ?? ucfirst($key);
    }

    protected static function selectedIdsForOptions(?Role $record, array $options): array
    {
        if (! $record) return [];
        $current = array_map('intval', $record->permissions()->pluck('id')->all());
        $available = array_map('intval', array_keys($options));
        return array_values(array_intersect($available, $current));
    }
}
