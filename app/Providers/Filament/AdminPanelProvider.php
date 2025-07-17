<?php

namespace App\Providers\Filament;

use App\Filament\Pages\SystemSettings;
use App\Filament\Resources\CityResource;
use App\Filament\Resources\ClientResource;
use App\Filament\Resources\CountryResource;
use App\Filament\Resources\ShowroomResource;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
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

use App\Filament\Resources\DepartmentCategoriesResource;
use App\Filament\Resources\DepartmentResource;
use App\Filament\Resources\EmployeeResource;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use App\Filament\Resources\RoleResource;
use App\Filament\Resources\PermissionResource;
use App\Filament\Resources\ProductionRequestResource;
use App\Filament\Resources\SystemSettingResource;

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
                SystemSettings::class,
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
                            ->label('الطلبات')
                            ->collapsible()
                            ->collapsed()
                            ->icon('heroicon-o-rectangle-group')
                            ->items([
                                NavigationItem::make('إدارة طلبات التصنيع')
                                    ->url(ProductionRequestResource::getUrl()),
                            ])
                    )    
                ->group(
                        NavigationGroup::make()
                            ->label('الأقسام')
                            ->collapsible()
                            ->collapsed()
                            ->icon('heroicon-o-rectangle-group')
                            ->items([
                                NavigationItem::make('تصنيفات الأقسام')
                                    ->url(DepartmentCategoriesResource::getUrl()),
                                NavigationItem::make('الأقسام')
                                    ->url(DepartmentResource::getUrl()),
                            ])
                    )
                    ->group(
                        NavigationGroup::make()
                            ->label('العملاء')
                            ->collapsible()
                            ->icon('heroicon-o-user-group')
                            ->collapsed()
                            ->items([
                                NavigationItem::make('إدارة العملاء')
                                    ->url(ClientResource::getUrl()),
                            ])
                    )
                    ->group(
                        NavigationGroup::make()
                            ->label('الموظفين')
                            ->collapsible()
                            ->icon('heroicon-o-user-group')
                            ->collapsed()
                            ->items([
                                NavigationItem::make('إدارة الموظفين')
                                    ->url(EmployeeResource::getUrl()),
                            ])
                    )
                    ->group(
                        NavigationGroup::make()
                            ->label('الإعدادات ')
                            ->collapsible()
                            ->icon('heroicon-o-cog')
                            ->collapsed()
                            ->items([
                                // NavigationItem::make('إدارة إعدادات النظام')
                                //     ->url(SystemSettingResource::getUrl()),
                                NavigationItem::make('إعدادات النظام')
                                    ->url(SystemSettings::getUrl()),
                                NavigationItem::make('إدارة المعارض')
                                    ->url(ShowroomResource::getUrl()),
                                NavigationItem::make('إدارة الدول')
                                    ->url(CountryResource::getUrl()),
                                NavigationItem::make('إدارة المدن')
                                    ->url(CityResource::getUrl()),
                                NavigationItem::make('إدارة الأدوار')
                                    ->url(RoleResource::getUrl()),

                                NavigationItem::make('إدارة الصلاحيات')
                                    ->url(PermissionResource::getUrl()),
                            ])
                    );
            });
    }
}
