<?php

namespace App\Providers\Filament;

use App\Filament\Pages\SystemSettings;
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

            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
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
            ->plugins([])
            ->authMiddleware([
                Authenticate::class,
            ])

            ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
                $u = Auth::user();
                $hasRole = fn(array $roles) => method_exists($u, 'hasAnyRole') ? ($u?->hasAnyRole($roles) ?? false) : false;
                $can     = fn(string $perm) => $u?->can($perm) ?? false;
                $isSuper = fn() => $hasRole(['super-admin','admin','owner']);

                $hasEmployee = fn (): bool => $u?->employee !== null;

                $canReviewTasks = fn (): bool => $can('factory_manager.review_tasks') || $hasRole(['factory_manager','admin','super-admin','owner']);

                $canManageSettings = fn (): bool => $can('manage_system_settings') || $hasRole(['admin','super-admin','owner','it_manager']);

                return $builder
                    ->items([
                        NavigationItem::make('الصفحة الرئيسية')
                            ->url('/admin')
                            ->icon('heroicon-o-home')
                            ->sort(-1000)
                            ->isActiveWhen(fn () => request()->is('admin') || request()->is('admin/*')),
                    ])

                    // الطلبات
                    ->group(
                        NavigationGroup::make()
                            ->label('الطلبات')
                            ->collapsible()
                            ->collapsed()
                            ->icon('heroicon-o-rectangle-group')
                            ->items([
                                NavigationItem::make('إدارة طلبات التصنيع')
                                    ->url(ProductionRequestResource::getUrl())
                                    ->visible(fn () => $isSuper() || $can('view_production_request_resource')),
                            ])
                    )

                    // المشروعات
                    ->group(
                        NavigationGroup::make()
                            ->label('المشروعات')
                            ->icon('heroicon-o-briefcase')
                            ->collapsed()
                            ->items([
                                NavigationItem::make('المشروعات الحالية')
                                    ->url(ProjectResource::getUrl('index') . '?tableFilters[is_completed][value]=false')
                                    ->badge(fn () => Project::query()->where('status', '!=', 'completed')->count())
                                    ->visible(fn () => $isSuper() || $can('view_project_resource')),
                                NavigationItem::make('المشروعات المكتملة')
                                    ->url(ProjectResource::getUrl('index') . '?tableFilters[is_completed][value]=true')
                                    ->badge(fn () => Project::query()->where('status', 'completed')->count())
                                    ->visible(fn () => $isSuper() || $can('view_project_resource')),
                            ])
                    )

                    // المهام
                    ->group(
                        NavigationGroup::make()
                            ->label('المهام')
                            ->collapsible()
                            ->collapsed()
                            ->icon('heroicon-o-briefcase')
                            ->items([
                                NavigationItem::make('المهام المسندة إليّ')
                                    ->url(\App\Filament\Pages\AssignedTasks::getUrl())
                                    ->visible(fn () =>
                                        $hasEmployee()
                                        || $can('view_assigned_tasks')
                                        || $can('view_any_task')
                                        || $hasRole(['admin','super-admin'])
                                    ),

                                NavigationItem::make('مراجعة المهام')
                                    ->url(\App\Filament\Pages\FactoryManagerTaskReview::getUrl())
                                    ->visible(fn () => $canReviewTasks()),
                            ])
                    )

                    // المشتريات
                    ->group(
                        NavigationGroup::make()
                            ->label('المشتريات')
                            ->icon('heroicon-o-truck')
                            ->collapsed()
                            ->items([
                                NavigationItem::make('طلبات الخامات')
                                    ->url(\App\Filament\Pages\Purchasing\MaterialsRequests::getUrl())
                                    ->visible(fn () =>
                                        $can('view_material_requests')
                                        || $hasRole(['purchasing_manager','admin','super-admin'])
                                    ),
                            ])
                    )

                    // الأقسام
                    ->group(
                        NavigationGroup::make()
                            ->label('الأقسام')
                            ->collapsible()
                            ->collapsed()
                            ->icon('heroicon-o-rectangle-group')
                            ->items([
                                NavigationItem::make('تصنيفات الأقسام')
                                    ->url(DepartmentCategoriesResource::getUrl())
                                    ->visible(fn () => $isSuper() || $can('view_department_categories_resource')),

                                NavigationItem::make('الأقسام')
                                    ->url(DepartmentResource::getUrl())
                                    ->visible(fn () => $isSuper() || $can('view_department_resource')),
                            ])
                    )

                    // العملاء
                    ->group(
                        NavigationGroup::make()
                            ->label('العملاء')
                            ->collapsible()
                            ->icon('heroicon-o-user-group')
                            ->collapsed()
                            ->items([
                                NavigationItem::make('إدارة العملاء')
                                    ->url(ClientResource::getUrl())
                                    ->visible(fn () => $isSuper() || $can('view_client_resource')),
                            ])
                    )

                    // الموظفون
                    ->group(
                        NavigationGroup::make()
                            ->label('الموظفين')
                            ->collapsible()
                            ->icon('heroicon-o-user-group')
                            ->collapsed()
                            ->items([
                                NavigationItem::make('إدارة الموظفين')
                                    ->url(EmployeeResource::getUrl())
                                    ->visible(fn () => $isSuper() || $can('view_employee_resource')),
                            ])
                    )

                    // الإعدادات
                    ->group(
                        NavigationGroup::make()
                            ->label('الإعدادات ')
                            ->collapsible()
                            ->icon('heroicon-o-cog')
                            ->collapsed()
                            ->items([
                                NavigationItem::make('إعدادات النظام')
                                    ->url(SystemSettings::getUrl())
                                    ->visible(fn () => $canManageSettings()),

                                NavigationItem::make('إدارة المعارض')
                                    ->url(ShowroomResource::getUrl())
                                    ->visible(fn () => $isSuper() || $can('view_showroom_resource')),

                                NavigationItem::make('إدارة الدول')
                                    ->url(CountryResource::getUrl())
                                    ->visible(fn () => $isSuper() || $can('view_country_resource')),

                                NavigationItem::make('إدارة المدن')
                                    ->url(CityResource::getUrl())
                                    ->visible(fn () => $isSuper() || $can('view_city_resource')),
                                NavigationItem::make('إدارة الأدوار')
                                    ->url(RoleResource::getUrl())
                                    ->visible(fn () => $isSuper() || $can('view_roles_resource')),

                                NavigationItem::make('إدارة الصلاحيات')
                                    ->url(PermissionResource::getUrl())
                                    ->visible(fn () => $isSuper() || $can('view_premission_resource')),
                            ])
                    );
            });
    }
}
