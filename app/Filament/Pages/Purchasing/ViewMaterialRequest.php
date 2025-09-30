<?php

namespace App\Filament\Pages\Purchasing;

use App\Models\MaterialRequest;
use Filament\Infolists\Infolist;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Components\{Section, Grid, TextEntry, IconEntry};
use Filament\Pages\Page;

class ViewMaterialRequest extends Page implements HasInfolists
{
    use InteractsWithInfolists;

    protected static ?string $navigationIcon  = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'تفاصيل طلب الخامات';
    protected static ?string $title           = 'تفاصيل طلب الخامات';
    protected static ?string $navigationGroup = 'المشتريات';

    protected static ?string $slug = 'purchasing/materials-requests/{record}';

    protected static string $view = 'filament.pages.purchasing.view-material-request';

    public MaterialRequest $record;

    public function mount(MaterialRequest $record): void
    {
        // حمّل العلاقات المهمة للعرض
        $this->record = $record->load([
            'task.project.productionRequest',
            'task.employee',
            'department',
            'requestedBy',
            'providedBy',
        ]);
    }

    public function getHeading(): string
    {
        return "طلب خامات #{$this->record->id}";
    }

    public static function shouldRegisterNavigation(): bool
    {
        // صفحة عرض تفصيلية لا يلزم ظهورها في القائمة
        return false;
    }

    /** خريطة الحالة إلى نص/لون */
    protected function statusLabel(?string $s): string
    {
        return match ($s) {
            'requested' => 'بانتظار اعتماد المشتريات',
            'approved'  => 'بانتظار التوريد',
            'fulfilled' => 'مورَّد',
            'cancelled' => 'ملغى',
            default     => '—',
        };
    }

    protected function statusColor(?string $s): string
    {
        return match ($s) {
            'requested' => 'warning',
            'approved'  => 'info',
            'fulfilled' => 'success',
            'cancelled' => 'gray',
            default     => 'secondary',
        };
    }

    /** Infolist الرئيسي */
    public function requestInfolist(Infolist $infolist): Infolist
    {
        $r = $this->record;

        return $infolist
            ->record($r)
            ->schema([
                Section::make('بيانات عامة')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('id')->label('#')->badge()->color('gray'),
                        TextEntry::make('department.dept_name')->label('القسم')->placeholder('—'),
                        TextEntry::make('task.id')->label('رقم المهمة')->placeholder('—'),
                        TextEntry::make('task.project.project_name')->label('المشروع')->placeholder('—'),

                        TextEntry::make('requestedBy.name')
                            ->label('مقدّم الطلب')
                            ->getStateUsing(fn () =>
                                ($r->requestedBy?->name)
                                ?? ($r->task?->employee?->employee_name)
                                ?? '—'
                            ),

                        TextEntry::make('requested_at')->label('تاريخ الطلب')->dateTime('Y-m-d H:i'),
                        TextEntry::make('expected_delivery_at')->label('موعد التوريد (متوقّع)')->dateTime('Y-m-d H:i'),
                        TextEntry::make('status')
                            ->label('الحالة')
                            ->badge()
                            ->color(fn ($state) => $this->statusColor($state))
                            ->formatStateUsing(fn ($state) => $this->statusLabel($state)),

                        TextEntry::make('note')->label('المطلوبات/ملاحظات')->columnSpanFull()->markdown(),
                    ]),

                Section::make('المشتريات')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('estimated_cost')->label('التكلفة التقديرية')->money('sar'),
                        TextEntry::make('actual_cost')->label('التكلفة الفعلية')->money('sar'),
                        TextEntry::make('po_number')->label('رقم الطلب/المرجع')->placeholder('—'),

                        TextEntry::make('po_file')
                            ->label('ملف PO')
                            ->formatStateUsing(fn ($state) => $state ? 'تنزيل' : '—')
                            ->url(fn ($state) => $state ? \Storage::url($state) : null, shouldOpenInNewTab: true)
                            ->icon(fn ($state) => $state ? 'heroicon-o-arrow-down-tray' : null),

                        TextEntry::make('provided_by.name')->label('مُنَفِّذ التوريد')->placeholder('—'),
                        TextEntry::make('provided_at')->label('تاريخ التوريد')->dateTime('Y-m-d H:i'),
                        TextEntry::make('created_at')->label('أُنشئ في')->dateTime('Y-m-d H:i'),
                        TextEntry::make('updated_at')->label('آخر تعديل')->dateTime('Y-m-d H:i'),
                    ]),

                Section::make('روابط سريعة')
                    ->schema([
                        IconEntry::make('project_link')
                            ->label('عرض المشروع')
                            ->icon('heroicon-o-arrow-top-right-on-square')
                            ->url(fn () => $r->task?->project
                                ? route('filament.admin.resources.projects.view', $r->task->project) : null,
                                shouldOpenInNewTab: true
                            )
                            ->helperText(fn () => $r->task?->project?->project_name ?? '—'),

                        IconEntry::make('task_link')
                            ->label('عرض المهمة')
                            ->icon('heroicon-o-arrow-top-right-on-square')
                            ->url(fn () => $r->task
                                ? route('filament.admin.resources.projects.view', $r->task->project) . '#tasks' : null,
                                shouldOpenInNewTab: true
                            )
                            ->helperText(fn () => $r->task?->id ? ('Task #' . $r->task->id) : '—'),

                        IconEntry::make('pr_link')
                            ->label('عرض طلب التصنيع')
                            ->icon('heroicon-o-arrow-top-right-on-square')
                            ->url(fn () => $r->task?->project?->productionRequest
                                ? route('filament.admin.resources.production-requests.view', $r->task->project->productionRequest) : null,
                                shouldOpenInNewTab: true
                            )
                            ->helperText(fn () => $r->task?->project?->productionRequest?->id
                                ? ('PR #' . $r->task->project->productionRequest->id) : '—'),
                    ])->columns(3),
            ]);
    }
}
