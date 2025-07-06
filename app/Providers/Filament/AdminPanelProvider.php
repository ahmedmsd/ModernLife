<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

use App\Filament\Resources\DepartmentCategoriesResource;
use App\Filament\Resources\DepartmentResource;
use App\Filament\Resources\EmployeeResource;
use App\Filament\Resources\UserGroupResource;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use App\Filament\Resources\RoleResource;
use App\Filament\Resources\PermissionResource;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->resources([
                RoleResource::class,
                PermissionResource::class,
                ])
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
                return $builder
                    ->group(
                        NavigationGroup::make()
                            ->label('الأقسام')
                            ->collapsible()
                            ->items([
                                NavigationItem::make('تصنيفات الأقسام')
                                    ->icon('heroicon-o-rectangle-group')
                                    ->url(DepartmentCategoriesResource::getUrl()),
                                     NavigationItem::make('الأقسام')
                                    ->icon('heroicon-o-rectangle-group')
                                    ->url(DepartmentResource::getUrl()),
                            ])
                    )
                    ->group(
                        NavigationGroup::make()
                            ->label('الموظفين')
                            ->collapsible()
                            ->items([
                                NavigationItem::make('إدارة الموظفين ')
                                    ->icon('heroicon-o-rectangle-group')
                                    ->url(EmployeeResource::getUrl()),
                            ])
                    )
                    ->group(
                        NavigationGroup::make()
                            ->label('إدارة الصلاحيات')
                            ->collapsible()
                            ->items([
                                NavigationItem::make('إدارة الأدوار')
                                    ->icon('heroicon-o-shield-check')
                                    ->url(RoleResource::getUrl()),

                                NavigationItem::make('إدارة الصلاحيات')
                                    ->icon('heroicon-o-key')
                                    ->url(PermissionResource::getUrl()),
                            ])
                    );
            });
    }
}
