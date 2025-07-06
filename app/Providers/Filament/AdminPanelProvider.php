<?php

namespace App\Providers\Filament;

use App\Helpers\NavigationHelper;
use App\Filament\Resources\DepartmentCategoriesResource;
use App\Filament\Resources\DepartmentResource;
use App\Filament\Resources\EmployeeResource;
use App\Filament\Resources\PermissionResource;
use App\Filament\Resources\RoleResource;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->navigationGroups([
                'إدارة النظام',
            ])
            ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
                $groups = [
                    [
                        'label' => 'الأقسام',
                        'icon' => 'heroicon-o-rectangle-group',
                        'items' => [
                            ['label' => 'تصنيفات الأقسام', 'url' => fn() => DepartmentCategoriesResource::getUrl()],
                            ['label' => 'الأقسام', 'url' => fn() => DepartmentResource::getUrl()],
                        ]
                    ],
                    [
                        'label' => 'الموظفين',
                        'icon' => 'heroicon-o-user-group',
                        'items' => [
                            ['label' => 'إدارة الموظفين', 'url' => fn() => EmployeeResource::getUrl()],
                        ]
                    ],
                    [
                        'label' => 'إدارة الصلاحيات',
                        'icon' => 'heroicon-o-shield-check',
                        'items' => [
                            ['label' => 'إدارة الأدوار', 'url' => fn() => RoleResource::getUrl()],
                            ['label' => 'إدارة الصلاحيات', 'url' => fn() => PermissionResource::getUrl()],
                        ]
                    ]
                ];

                foreach ($groups as $group) {
                    $builder->group(
                        NavigationGroup::make()
                            ->label($group['label'])
                            ->icon($group['icon'])
                            ->collapsible()
                            ->collapsed(true) // هذا السطر يضمن إغلاق المجموعات افتراضياً
                            ->items(array_map(
                                fn($item) => NavigationItem::make($item['label'])->url($item['url']),
                                $group['items']
                            ))
                    );
                }

                return $builder;
            });
    }
}
