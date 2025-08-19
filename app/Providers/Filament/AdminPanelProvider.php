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
use App\Filament\Resources\ProjectResource;
use Illuminate\Support\Facades\Auth;

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
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
                SystemSettings::class,
            ])
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
//                Widgets\AccountWidget::class,
//                Widgets\FilamentInfoWidget::class,
            ])
//            ->viteTheme('resources/css/filament-custom.css')

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

                $isAdmin = fn () => Auth::check() && (
                        Auth::user()->id === 1
                        || (method_exists(Auth::user(), 'hasAnyRole') && Auth::user()->hasAnyRole(['admin','super-admin','owner']))
                        || Auth::user()->can('super-admin')
                        || Auth::user()->can('admin')
                    );

                $hasEmployee = fn () => Auth::check() && Auth::user()?->employee !== null;

                $canReviewTasks = fn () => Auth::check() && (
                        Auth::user()->can('factory_manager.review_tasks')
                        || $isAdmin()
                    );

                return $builder
                    ->items([
                        NavigationItem::make('الصفحة الرئيسية')
                            ->url('/admin')
                            ->icon('heroicon-o-home')
                            ->sort(-1000)
                            ->isActiveWhen(fn () => request()->is('/')),
                    ])
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
                            ->label('المشروعات')
                            ->icon('heroicon-o-briefcase')
                            ->collapsed()
                            ->items([
                                NavigationItem::make('إدارة المشروعات')
                                    ->url(ProjectResource::getUrl()),
                            ])
                    )
                    ->group(
                        NavigationGroup::make()
                            ->label('المهام')
                            ->collapsible()
                            ->collapsed()
                            ->icon('heroicon-o-briefcase')
                            ->items([
                                NavigationItem::make('المهام المسندة إليّ')
                                    ->url(\App\Filament\Pages\AssignedTasks::getUrl())
                                    ->visible(fn () => $hasEmployee() || $isAdmin()),

                                NavigationItem::make('مراجعة المهام')
                                    ->url(\App\Filament\Pages\FactoryManagerTaskReview::getUrl())
                                    ->visible(fn () => $canReviewTasks()),
                            ])
                    )
                    ->group(
                        \Filament\Navigation\NavigationGroup::make()
                            ->label('المشتريات')
                            ->icon('heroicon-o-truck')
                            ->collapsed()
                            ->items([
                                \Filament\Navigation\NavigationItem::make('طلبات الخامات')
                                    ->url(\App\Filament\Pages\Purchasing\MaterialsRequests::getUrl())
                                    ->visible(fn () => \Illuminate\Support\Facades\Auth::user()?->hasAnyRole(['purchasing_manager','admin','super-admin'])),
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
