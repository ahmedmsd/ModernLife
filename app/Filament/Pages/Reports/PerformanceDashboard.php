<?php

namespace App\Filament\Pages\Reports;

use App\Filament\Widgets\Reports\CompletionTrendChart;
use App\Filament\Widgets\Reports\KPICards;
use App\Filament\Widgets\Reports\StatusDistributionChart;
use App\Filament\Widgets\Reports\TasksByDepartmentChart;
use App\Filament\Widgets\Reports\TopEmployeesBarChart;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Group;
use Filament\Notifications\Notification;

class PerformanceDashboard extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationGroup = 'التقارير';
    protected static ?string $navigationLabel = 'لوحة التقارير';
    protected static ?string $title = 'لوحة التقارير';

    protected static string $view = 'filament.pages.reports.performance-dashboard';

    public static function canAccess(): bool
    {
        return auth()->check()
            && auth()->user()->hasAnyRole(['admin','super-admin']);
    }

    public array $filters = [];

    public ?string $date_from = null;
    public ?string $date_to   = null;
    public ?int $branch_id    = null;
    public ?int $dept_id      = null;
    public ?int $employee_id  = null;
    public ?string $status    = null;

    protected $queryString = [
        'date_from'   => ['except' => null],
        'date_to'     => ['except' => null],
        'branch_id'   => ['except' => null],
        'dept_id'     => ['except' => null],
        'employee_id' => ['except' => null],
        'status'      => ['except' => null],
    ];

    public function mount(): void
    {
        $this->form->fill([
            'date_from'   => $this->date_from,
            'date_to'     => $this->date_to,
            'branch_id'   => $this->branch_id,
            'dept_id'     => $this->dept_id,
            'employee_id' => $this->employee_id,
            'status'      => $this->status,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Group::make()->schema([
                Grid::make(12)->schema([
                    DatePicker::make('date_from')->label('التاريخ من')->displayFormat('Y-m-d')->columnSpan(2),
                    DatePicker::make('date_to')->label('التاريخ إلى')->displayFormat('Y-m-d')->columnSpan(2),
                    Select::make('branch_id')->label('الفرع')
                        ->searchable()
                        ->options(fn() => \App\Models\Showroom::orderBy('name')->pluck('name','id'))
                        ->columnSpan(2),
                    Select::make('dept_id')->label('القسم')
                        ->searchable()
                        ->options(fn() => \App\Models\Department::orderBy('dept_name')->pluck('dept_name','dept_id'))
                        ->columnSpan(2),
                    Select::make('employee_id')->label('الموظف')
                        ->searchable()
                        ->options(fn() => \App\Models\Employee::orderBy('employee_name')->pluck('employee_name','employee_id'))
                        ->columnSpan(2),
                    Select::make('status')->label('الحالة')->options([
                        'pending'            => 'قيد الانتظار',
                        'assigned'           => 'مُسندة',
                        'received'           => 'مستلمة',
                        'under_review'       => 'تحت المراجعة',
                        'approved'           => 'معتمدة',
                        'rejected'           => 'مرفوضة',
                        'in_progress'        => 'قيد التنفيذ',
                        'materials_wait'     => 'انتظار خامات',
                        'materials_prep'     => 'تحضير خامات',
                        'materials_done'     => 'خامات مكتملة',
                        'on_hold'            => 'متوقفة',
                        'completed'          => 'مكتملة',
                        'cancelled'          => 'ملغاة',
                        'waiting_production' => 'انتظار تصنيع',
                    ])->columnSpan(2),
                ])->columns(12),

                Forms\Components\Actions::make([
                    Action::make('apply')->label('تطبيق الفلاتر')->icon('heroicon-o-funnel')
                        ->action(function () {
                            // اقرأ حالة الفورم
                            $state = $this->form->getState();

                            // حدّث خصائص الصفحة
                            foreach ($state as $k => $v) $this->{$k} = $v ?: null;

                            // أعد التوجيه لنفس الصفحة مع Query String
                            $params = array_filter([
                                'date_from'   => $this->date_from,
                                'date_to'     => $this->date_to,
                                'branch_id'   => $this->branch_id,
                                'dept_id'     => $this->dept_id,
                                'employee_id' => $this->employee_id,
                                'status'      => $this->status,
                            ], fn($v) => !is_null($v) && $v !== '');

                            $this->redirect(static::getUrl($params));
                        }),
                    Action::make('reset')->label('إعادة التعيين')->icon('heroicon-o-arrow-path')->color('gray')
                        ->action(function () {
                            $this->reset(['date_from','date_to','branch_id','dept_id','employee_id','status']);
                            $this->form->fill([]);
                            $this->redirect(static::getUrl());
                        }),
                ])->alignCenter(),
            ])->columnSpanFull(),
        ])->statePath('filters');
    }

    protected function getWidgets(): array
    {
        return [
            KPICards::class,
            TasksByDepartmentChart::class,
            CompletionTrendChart::class,
            StatusDistributionChart::class,
            TopEmployeesBarChart::class,
        ];
    }
}
