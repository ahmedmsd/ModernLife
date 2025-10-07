<?php

namespace App\Providers\Filament;

use App\Filament\Pages\SystemSettings;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use App\Filament\Resources\CityResource;
use App\Filament\Resources\ClientResource;
use App\Filament\Resources\CountryResource;
use App\Filament\Resources\DepartmentCategoriesResource;
use App\Filament\Resources\DepartmentResource;
use App\Filament\Resources\EmployeeResource;
use App\Filament\Resources\PermissionResource;
use App\Filament\Resources\ProductionRequestResource;
use App\Filament\Resources\ProjectResource;
use App\Filament\Resources\RoleResource;
use App\Filament\Resources\ShowroomResource;
use App\Models\Project;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Widgets;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->authGuard('web')
            ->login()

            // protected static bool $shouldRegisterNavigation = false;
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')

            ->pages([
                Pages\Dashboard::class,
                SystemSettings::class,
            ])

            ->databaseNotifications()
            ->databaseNotificationsPolling('10s')

//            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                 \App\Filament\Widgets\MainStats::class,

                \App\Filament\Widgets\Sales\SalesInProgressRequests::class,
                \App\Filament\Widgets\Showroom\ShowroomManagerNeedsResponse::class,
                \App\Filament\Widgets\Factory\FactoryManagerCurrentRequests::class,
                \App\Filament\Widgets\Factory\FactoryManagerCurrentTasks::class,
                \App\Filament\Widgets\Department\DepartmentManagerCurrentTasks::class,
                \App\Filament\Widgets\Purchasing\PurchasingOpenMaterialsRequests::class,
                \App\Filament\Widgets\Quality\QualityManagerCurrentTasks::class,
                // \App\Filament\Widgets\Charts\ProductionTrendsChart::class,
                // \App\Filament\Widgets\Charts\TasksByDeptChart::class,
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
            ->plugins([
                FilamentShieldPlugin::make(),
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
                return $builder
                    ->items([
                        NavigationItem::make('الصفحة الرئيسية')
                            ->url('/admin')
                            ->icon('heroicon-o-home')
                            ->sort(-1000)
                            ->visible(fn () => \Filament\Pages\Dashboard::canAccess()),
                    ])

                    ->group(
                        NavigationGroup::make()->label('الطلبات')->icon('heroicon-o-rectangle-group')->collapsible()->collapsed()
                            ->items([
                                NavigationItem::make('إدارة طلبات التصنيع')
                                    ->url(\App\Filament\Resources\ProductionRequestResource::getUrl())
                                    ->visible(fn () => \App\Filament\Resources\ProductionRequestResource::canViewAny()),
                            ])
                    )

                    ->group(
                        NavigationGroup::make()->label('المشروعات')->icon('heroicon-o-briefcase')->collapsed()
                            ->items([
                                NavigationItem::make('المشروعات الحالية')
                                    ->url(\App\Filament\Resources\ProjectResource::getUrl('index').'?tableFilters[is_completed][value]=false')
                                    ->visible(fn () => \App\Filament\Resources\ProjectResource::canViewAny())
                                    ->badge(fn () => \App\Filament\Resources\ProjectResource::canViewAny()
                                        ? \App\Models\Project::where('status','!=','completed')->count()
                                        : null
                                    ),
                                NavigationItem::make('المشروعات المكتملة')
                                    ->url(\App\Filament\Resources\ProjectResource::getUrl('index').'?tableFilters[is_completed][value]=true')
                                    ->visible(fn () => \App\Filament\Resources\ProjectResource::canViewAny())
                                    ->badge(fn () => \App\Filament\Resources\ProjectResource::canViewAny()
                                        ? \App\Models\Project::where('status','completed')->count()
                                        : null
                                    ),
                            ])
                    )

                    ->group(
                        NavigationGroup::make()->label('المهام')->icon('heroicon-o-briefcase')->collapsible()->collapsed()
                            ->items([
                                NavigationItem::make('المهام المسندة إليّ')
                                    ->url(\App\Filament\Pages\AssignedTasks::getUrl())
                                    ->visible(fn () => \App\Filament\Pages\AssignedTasks::canAccess()),

                            ])
                    )

                    ->group(
                        NavigationGroup::make()->label('المشتريات')->icon('heroicon-o-truck')->collapsed()
                            ->items([
                                NavigationItem::make('طلبات الخامات')
                                    ->url(\App\Filament\Pages\Purchasing\MaterialsRequests::getUrl())
                                    ->visible(fn () => \App\Filament\Pages\Purchasing\MaterialsRequests::canAccess()),
                                NavigationItem::make('طلبات الخامات المُنجزة')
                                    ->url(\App\Filament\Pages\Purchasing\MaterialsRequestsDone::getUrl())
                                    ->visible(fn () => \App\Filament\Pages\Purchasing\MaterialsRequestsDone::canAccess()),
                            ])
                    )

                    ->group(
                        NavigationGroup::make()->label('التركيب')->icon('heroicon-o-truck')->collapsed()
                            ->items([
                                NavigationItem::make('تقويم التركيب ')
                                    ->url(\App\Filament\Pages\InstallationCalendar::getUrl())
                                    ->visible(fn () => \App\Filament\Pages\InstallationCalendar::canAccess()),
                            ])
                    )
                    ->group(
                        NavigationGroup::make()->label('الصيانة')->icon('heroicon-o-wrench-screwdriver')->collapsed()
                            ->items([
                                NavigationItem::make('طلبات الصيانة ')
                                    ->url(\App\Filament\Resources\MaintenanceRequestResource::getUrl())
                                    ->visible(fn () => \App\Filament\Resources\MaintenanceRequestResource::canViewAny()),
                            ])
                    )

                    ->group(
                        NavigationGroup::make()->label('الأقسام')->icon('heroicon-o-rectangle-group')->collapsible()->collapsed()
                            ->items([
                                NavigationItem::make('تصنيفات الأقسام')
                                    ->url(\App\Filament\Resources\DepartmentCategoriesResource::getUrl())
                                    ->visible(fn () => \App\Filament\Resources\DepartmentCategoriesResource::canViewAny()),
                                NavigationItem::make('الأقسام')
                                    ->url(\App\Filament\Resources\DepartmentResource::getUrl())
                                    ->visible(fn () => \App\Filament\Resources\DepartmentResource::canViewAny()),
                            ])
                    )

                    ->group(
                        NavigationGroup::make()->label('العملاء')->icon('heroicon-o-user-group')->collapsible()->collapsed()
                            ->items([
                                NavigationItem::make('إدارة العملاء')
                                    ->url(\App\Filament\Resources\ClientResource::getUrl())
                                    ->visible(fn () => \App\Filament\Resources\ClientResource::canViewAny()),
                            ])
                    )

                    ->group(
                        NavigationGroup::make()->label('الموظفين')->icon('heroicon-o-user-group')->collapsible()->collapsed()
                            ->items([
                                NavigationItem::make('إدارة الموظفين')
                                    ->url(\App\Filament\Resources\EmployeeResource::getUrl())
                                    ->visible(fn () => \App\Filament\Resources\EmployeeResource::canViewAny()),
                            ])
                    )

                    ->group(
                        NavigationGroup::make()->label('الإعدادات ')->icon('heroicon-o-cog')->collapsible()->collapsed()
                            ->items([
                                NavigationItem::make('إعدادات النظام')
                                    ->url(\App\Filament\Pages\SystemSettings::getUrl())
                                    ->visible(fn () => \App\Filament\Pages\SystemSettings::canAccess()),

                                NavigationItem::make('إدارة المعارض')
                                    ->url(\App\Filament\Resources\ShowroomResource::getUrl())
                                    ->visible(fn () => \App\Filament\Resources\ShowroomResource::canViewAny()),

                                NavigationItem::make('إدارة الدول')
                                    ->url(\App\Filament\Resources\CountryResource::getUrl())
                                    ->visible(fn () => \App\Filament\Resources\CountryResource::canViewAny()),

                                NavigationItem::make('إدارة المدن')
                                    ->url(\App\Filament\Resources\CityResource::getUrl())
                                    ->visible(fn () => \App\Filament\Resources\CityResource::canViewAny()),

                                NavigationItem::make('إدارة الأدوار')
                                    ->url(\App\Filament\Resources\RoleResource::getUrl())
                                    ->visible(fn () => \App\Filament\Resources\RoleResource::canViewAny())
                            ])
                    );
            });


    }
}
