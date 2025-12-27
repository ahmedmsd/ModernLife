<?php

namespace App\Providers\Filament;

use App\Filament\Pages\MyNotifications;
use App\Filament\Pages\SystemSettings;
use App\Filament\Widgets\Showroom\ShowroomManagerCurrentTasks;
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
            ->font('Cairo')
            // protected static bool $shouldRegisterNavigation = false;
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->brandLogo(fn () => asset('images/modern.png'))
            ->brandName('Modern Life')
            ->pages([
                Pages\Dashboard::class,
                SystemSettings::class,
            ])

            ->databaseNotifications()
            ->databaseNotificationsPolling('15s')

//            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                 \App\Filament\Widgets\MainStats::class,

                \App\Filament\Widgets\Sales\SalesInProgressRequests::class,
                \App\Filament\Widgets\Showroom\ShowroomManagerNeedsResponse::class,
                \App\Filament\Widgets\Showroom\ShowroomManagerCurrentTasks::class,
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
                        NavigationItem::make('تنبيهاتي')
                            ->icon('heroicon-o-bell')
                            ->url(fn () => \App\Filament\Pages\MyNotifications::getUrl())
                            ->visible(fn () => auth()->check())
                            ->badge(function (): ?string {
                                $user = auth()->user();

                                if (! $user instanceof \App\Models\User) {
                                    return null;
                                }

                                $count = $user->unreadNotifications()->count();

                                return $count > 0 ? (string) $count : null;
                            }),

                    ])

                    ->group(
                        NavigationGroup::make()->label('الطلبات')->icon('heroicon-o-rectangle-group')->collapsible()->collapsed()
                            ->items([
                                NavigationItem::make('طلبات التصنيع (الجارية)')
                                    ->url(\App\Filament\Resources\ProductionRequestResource::getUrl('index'))
                                    ->visible(fn () => \App\Filament\Resources\ProductionRequestResource::canViewAny())
                                    ->badge(fn () => \App\Filament\Resources\ProductionRequestResource::getActiveCount()),
                                NavigationItem::make('طلبات التصنيع (المكتملة)')
                                    ->url(\App\Filament\Resources\ProductionRequestResource::getUrl('completed'))
                                    ->visible(fn () => \App\Filament\Resources\ProductionRequestResource::canViewAny())
                                    ->badge(fn () => \App\Filament\Resources\ProductionRequestResource::getCompletedCount()),
                            ])
                    )

                    ->group(
                        NavigationGroup::make()
                            ->label('المشروعات')
                            ->icon('heroicon-o-briefcase')
                            ->collapsed()
                            ->items([
                                NavigationItem::make('المشروعات الحالية')
                                    ->url(\App\Filament\Resources\ProjectResource::getUrl('index') . '?tableFilters[is_completed][value]=false')
                                    ->visible(fn () => \App\Filament\Resources\ProjectResource::canViewAny())
                                    ->badge(function () {
                                        if (! \App\Filament\Resources\ProjectResource::canViewAny()) {
                                            return null;
                                        }

                                        $u = auth()->user();
                                        if (! $u) return null;

                                        $base = \App\Models\Project::query();

                                        if ($u->hasRole('department_manager', 'web') && $u->employee?->department_id) {
                                            $deptId = $u->employee->department_id;
                                            $base->whereHas('tasks', fn ($q) => $q->where('department_id', $deptId));
                                        }
                                        elseif ($u->hasRole('showroom_manager', 'web')) {
                                            $employeeId = $u->employee?->getKey();
                                            if (! $employeeId) {
                                                return 0;
                                            }
                                            $showroomIds = \App\Models\Showroom::query()
                                                ->where('manager_id', $employeeId)
                                                ->pluck('id');

                                            if ($showroomIds->isEmpty()) {
                                                return 0;
                                            }

                                            $base->whereHas('productionRequest', function ($qq) use ($showroomIds) {
                                                $qq->whereIn('showroom_id', $showroomIds);
                                            });
                                        }

                                        return (clone $base)
                                            ->where('status', '!=', 'completed')
                                            ->count();
                                    }),

                                NavigationItem::make('المشروعات المكتملة')
                                    ->url(\App\Filament\Resources\ProjectResource::getUrl('index') . '?tableFilters[is_completed][value]=true')
                                    ->visible(fn () => \App\Filament\Resources\ProjectResource::canViewAny())
                                    ->badge(function () {
                                        if (! \App\Filament\Resources\ProjectResource::canViewAny()) {
                                            return null;
                                        }

                                        $u = auth()->user();
                                        if (! $u) return null;

                                        $base = \App\Models\Project::query();

                                        if ($u->hasRole('department_manager', 'web') && $u->employee?->department_id) {
                                            $deptId = $u->employee->department_id;
                                            $base->whereHas('tasks', fn ($q) => $q->where('department_id', $deptId));
                                        } elseif ($u->hasRole('showroom_manager', 'web')) {
                                            $employeeId = $u->employee?->getKey();
                                            if (! $employeeId) {
                                                return 0;
                                            }
                                            $showroomIds = \App\Models\Showroom::query()
                                                ->where('manager_id', $employeeId)
                                                ->pluck('id');

                                            if ($showroomIds->isEmpty()) {
                                                return 0;
                                            }

                                            $base->whereHas('productionRequest', function ($qq) use ($showroomIds) {
                                                $qq->whereIn('showroom_id', $showroomIds);
                                            });
                                        }

                                        return (clone $base)
                                            ->where('status', 'completed')
                                            ->count();
                                    }),
                            ])
                    )


                    ->group(
                        NavigationGroup::make()->label('المهام')->icon('heroicon-o-briefcase')->collapsible()->collapsed()
                            ->items([

                                NavigationItem::make('عرض المهام (الجارية)')
                                    ->url(\App\Filament\Resources\TaskResource::getUrl('active'))
                                    ->visible(fn () => \App\Filament\Resources\TaskResource\Pages\ActiveTasks::canAccess())
                                    ->badge(fn () => \App\Filament\Resources\TaskResource::getActiveCount()),
                                NavigationItem::make('المهام المرفوضة')
                                    ->url(\App\Filament\Resources\TaskResource::getUrl('returned'))
                                    ->visible(fn () => \App\Filament\Resources\TaskResource\Pages\ReturnedToFactory::canAccess())
                                    ->badge(fn () => \App\Filament\Resources\TaskResource::getReturnedCount()),
                                NavigationItem::make('المهام المُنجزة')
                                    ->url(\App\Filament\Resources\TaskResource::getUrl('completed'))
                                    ->visible(fn () => \App\Filament\Resources\TaskResource\Pages\CompletedTasks::canAccess())
                                    ->badge(fn () => \App\Filament\Resources\TaskResource::getCompletedCount()),
                            ])
                    )

                    ->group(
                        NavigationGroup::make()->label('المشتريات')->icon('heroicon-o-truck')->collapsed()
                            ->items([
                                NavigationItem::make('طلبات الأقسام الخارجية')
                                    ->url(\App\Filament\Resources\DepartmentPurchaseRequestResource::getUrl())
                                    ->visible(fn () => \App\Filament\Resources\DepartmentPurchaseRequestResource::canViewAny()),
                                NavigationItem::make('طلبات الخامات')
                                    ->url(\App\Filament\Pages\Purchasing\MaterialsRequests::getUrl())
                                    ->visible(fn () => \App\Filament\Pages\Purchasing\MaterialsRequests::canAccess())
                                    ->badge(fn () => \App\Filament\Pages\Purchasing\MaterialsRequests::getNavigationBadge()),
                                NavigationItem::make('طلبات الخامات المُنجزة')
                                    ->url(\App\Filament\Pages\Purchasing\MaterialsRequestsDone::getUrl())
                                    ->visible(fn () => \App\Filament\Pages\Purchasing\MaterialsRequestsDone::canAccess())
                                    ->badge(fn () => \App\Filament\Pages\Purchasing\MaterialsRequestsDone::getNavigationBadge()),
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
                                NavigationItem::make('طلب صيانة (جاري)')
                                    ->url(\App\Filament\Resources\MaintenanceRequestResource::getUrl('index'))
                                    ->visible(fn () => \App\Filament\Resources\MaintenanceRequestResource::canViewAny())
                                    ->badge(fn () => \App\Filament\Resources\MaintenanceRequestResource::getActiveCount()),
                                NavigationItem::make('طلب صيانة (مكتمل)')
                                    ->url(\App\Filament\Resources\MaintenanceRequestResource::getUrl('completed'))
                                    ->visible(fn () => \App\Filament\Resources\MaintenanceRequestResource::canViewAny())
                                    ->badge(fn () => \App\Filament\Resources\MaintenanceRequestResource::getCompletedCount()),
                                NavigationItem::make('تقويم الصيانة ')
                                    ->url(\App\Filament\Pages\MaintenanceCalendar::getUrl())
                                    ->visible(fn () => \App\Filament\Pages\MaintenanceCalendar::canAccess()),
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
                        \Filament\Navigation\NavigationGroup::make()
                            ->label('التقارير')
                            ->icon('heroicon-o-chart-bar-square')
                            ->collapsible()
                            ->collapsed()
                            ->items([
                                 \Filament\Navigation\NavigationItem::make('تقرير المهام الجارية ')
                                ->url(\App\Filament\Pages\InProgressTasksReport::getUrl())
                                ->visible(fn () => \App\Filament\Pages\InProgressTasksReport::canAccess()),
                                \Filament\Navigation\NavigationItem::make('لوحة التقارير')
                                ->url(\App\Filament\Pages\Reports\PerformanceDashboard::getUrl())
                                ->visible(fn () => \App\Filament\Pages\Reports\PerformanceDashboard::canAccess()),
                                \Filament\Navigation\NavigationItem::make(' متابعة التأخيرات')
                                    ->url(\App\Filament\Pages\DelaysFollowUp::getUrl())
                                    ->visible(fn () => \App\Filament\Pages\DelaysFollowUp::canAccess()),


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
                                    ->url('/admin/shield/roles')
                                    ->visible(fn () => auth()->check() && auth()->user()->hasAnyRole(['admin','super-admin'], 'web')),

                            ])
                    );
            })
            ->renderHook(
                \Filament\View\PanelsRenderHook::HEAD_END,
                fn (): string => '
                    <style>
                        /* Base Layout - Remove Gaps */
                        .fi-sidebar-group,
                        .fi-sidebar-group-items,
                        .fi-sidebar-sub-group-items,
                        .fi-sidebar-item {
                            gap: 0 !important;
                        }

                        /* Item Buttons (Regular Links) */
                        .fi-sidebar-item-button {
                            padding-block: 5px !important;
                            min-height: auto !important;
                        }

                        /* Group Headers (Collapsible Parent Buttons) */
                        .fi-sidebar-group-button {
                            padding-block: 5px !important;
                            min-height: auto !important;
                            gap: 0.35rem !important;
                        }

                        /* Text Labels */
                        .fi-sidebar-item-label,
                        .fi-sidebar-group-label {
                            line-height: 1.3 !important;
                        }

                        /* Icons - Scale down to 20px to allow tighter rows */
                        .fi-sidebar-item-icon, 
                        .fi-sidebar-group-icon {
                            height: 1.35rem !important;
                            width: 1.35rem !important;
                        }

                        /* Sub-item specific adjustments */
                        .fi-sidebar-group-items > li,
                        .fi-sidebar-sub-group-items > li {
                             margin-top: 0 !important;
                        }
                    </style>
                ',
            );


    }
}
