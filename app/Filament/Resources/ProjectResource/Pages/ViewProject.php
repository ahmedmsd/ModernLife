<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Models\ProductionTask;
use App\Models\MaterialRequest; // جدول production_tasks_material_requests
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\{Section, TextEntry, Tabs};
use Filament\Infolists\Components\Tabs\Tab;
use Filament\Support\Enums\MaxWidth;
use Filament\Actions\Action;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;

class ViewProject extends ViewRecord
{
    protected static string $resource = ProjectResource::class;
    protected static ?string $title   = 'عرض المشروع';

    /** اجعل الصفحة بعرض كامل */
    public function getMaxContentWidth(): MaxWidth|string|null
    {
        return MaxWidth::Full;
    }

    /* -------------------- Filters (query string) -------------------- */

    public ?string $filterStatus = null;
    public ?int    $filterDeptId = null;
    public ?int    $filterEmpId  = null;

    protected $queryString = [
        'filterStatus' => ['except' => ''],
        'filterDeptId' => ['except' => ''],
        'filterEmpId'  => ['except' => ''],
    ];

    /** إجراء لتغيير الفلاتر من رأس الصفحة */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('filters')
                ->label('تصفية المهام')
                ->icon('heroicon-o-funnel')
                ->form([
                    \Filament\Forms\Components\Select::make('status')
                        ->label('الحالة')
                        ->options([
                            'pending' => 'قيد الإنشاء','assigned'=>'مُسندة','acknowledged'=>'تأكيد الاستلام',
                            'in_progress'=>'قيد التنفيذ','blocked'=>'متوقفة','under_review'=>'قيد المراجعة',
                            'rework'=>'إعادة عمل','completed'=>'مكتملة','closed'=>'مغلقة','cancelled'=>'ملغاة','draft'=>'مسودة',
                        ])
                        ->searchable()
                        ->placeholder('الكل'),
                    \Filament\Forms\Components\Select::make('dept_id')
                        ->label('القسم')
                        ->options(fn () => \App\Models\Department::orderBy('dept_name')->pluck('dept_name','dept_id'))
                        ->searchable()
                        ->placeholder('الكل'),
                    \Filament\Forms\Components\Select::make('emp_id')
                        ->label('المسؤول')
                        ->options(fn () => \App\Models\Employee::orderBy('employee_name')->pluck('employee_name','employee_id'))
                        ->searchable()
                        ->placeholder('الكل'),
                ])
                ->action(function (array $data) {
                    $this->filterStatus = $data['status'] ?? null;
                    $this->filterDeptId = $data['dept_id'] ?? null;
                    $this->filterEmpId  = $data['emp_id'] ?? null;
                }),

            Action::make('resetFilters')
                ->label('إلغاء التصفية')
                ->icon('heroicon-o-x-mark')
                ->color('gray')
                ->visible(fn()=> $this->filterStatus || $this->filterDeptId || $this->filterEmpId)
                ->action(function () {
                    $this->filterStatus = $this->filterDeptId = $this->filterEmpId = null;
                }),

            Action::make('exportCsv')
                ->label('تصدير CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    $stats = $this->computeProjectStats();
                    $rows  = $stats['rows'];
                    return response()->streamDownload(function () use ($rows) {
                        $out = fopen('php://output', 'w');
                        fputcsv($out, ['Task#','Department','Employee','Status','Duration','From','To']);
                        foreach ($rows as $r) {
                            fputcsv($out, [
                                $r['id'], $r['dept'], $r['emp'], $r['status_ar'],
                                $r['human'], $r['created'], $r['closed'],
                            ]);
                        }
                        fclose($out);
                    }, "project-{$this->record->id}-tasks.csv");
                }),
        ];
    }

    /* -------------------- Helpers (labels + colors) -------------------- */

    private function statusAr(?string $v): ?string
    {
        $map = [
            'pending'=>'قيد الإنشاء','assigned'=>'مُسندة','acknowledged'=>'تأكيد الاستلام',
            'in_progress'=>'قيد التنفيذ','blocked'=>'متوقفة','under_review'=>'قيد المراجعة',
            'rework'=>'إعادة عمل','completed'=>'مكتملة','closed'=>'مغلقة','cancelled'=>'ملغاة','draft'=>'مسودة',
            'unknown'=>'غير معروفة',
        ];
        return $map[$v] ?? $v;
    }

    private function statusHex(?string $status): string
    {
        return match ($status) {
            'pending','draft'            => '#64748b',
            'assigned','acknowledged'    => '#f59e0b',
            'in_progress'                => '#0ea5e9',
            'blocked','rework'           => '#a855f7',
            'under_review'               => '#06b6d4',
            'completed','closed'         => '#10b981',
            'cancelled'                  => '#ef4444',
            default                      => '#6b7280',
        };
    }

    private function humanFromSeconds(int $sec): string
    {
        return Carbon::now()->subSeconds(max(0,$sec))->diffForHumans(null,true);
    }

    /* -------------------- Data builders -------------------- */

    private function filteredTasksQuery(): Builder
    {
        return ProductionTask::query()
            ->where('project_id', $this->record->id)   // اربط بالمشروع الحالي
            ->when($this->filterStatus, fn ($q, $v) => $q->where('status', $v))
            ->when($this->filterDeptId, fn ($q, $v) => $q->where('department_id', $v))
            ->when($this->filterEmpId,  fn ($q, $v) => $q->where('assigned_to_employee_id', $v))
            ->with([
                'department:dept_id,dept_name',
                'employee:employee_id,employee_name',
                'logs' => fn ($q) => $q->orderBy('happened_at')
                    ->select('id', 'task_id', 'type', 'data', 'happened_at', 'created_at'),
            ]);
    }

    private function taskCycleSeconds(ProductionTask $t): int
    {
        $start = $t->created_at ? Carbon::parse($t->created_at) : now();
        $end   = $t->closed_at  ? Carbon::parse($t->closed_at)  : now();
        return max(0, $start->diffInSeconds($end));
    }

    /** احسب الفترة التي قضتها مهمة في حالة معيّنة (مثل blocked) */
    private function taskStageSeconds(ProductionTask $t, string $stage): int
    {
        $logs = $t->logs;
        if (!$logs || $logs->isEmpty()) return 0;

        $start = $t->created_at ? Carbon::parse($t->created_at)
            : ($logs->first()?->happened_at ? Carbon::parse($logs->first()->happened_at) : now());
        $end   = $t->closed_at ?? $logs->last()?->happened_at ?? now();
        $end   = $end instanceof Carbon ? $end : Carbon::parse($end);

        $firstChange = $logs->firstWhere('type','status_changed');
        $current = is_array($firstChange?->data ?? null) ? ($firstChange->data['from'] ?? 'pending') : ($t->status ?? 'pending');

        $cursor  = $start->clone();
        $seconds = 0;

        foreach ($logs as $log) {
            $tAt = $log->happened_at ?? $log->created_at;
            if (!$tAt) continue;
            $tAt = $tAt instanceof Carbon ? $tAt : Carbon::parse($tAt);

            if ($current === $stage) $seconds += max(0, $cursor->diffInSeconds($tAt));
            $cursor = $tAt->clone();

            if ($log->type === 'status_changed' && is_array($log->data ?? null)) {
                $current = $log->data['to'] ?? $current;
            }
        }
        if ($current === $stage && $end->greaterThan($cursor)) {
            $seconds += max(0, $cursor->diffInSeconds($end));
        }
        return $seconds;
    }

    /** الإحصائيات الرئيسية + الجداول + السلا */
    private function computeProjectStats(): array
    {
        $tasks = $this->filteredTasksQuery()->get();

        $now = now();
        $completedLike = ['completed','closed'];
        $activeLike    = ['pending','assigned','acknowledged','in_progress','under_review','rework','blocked'];

        $total     = $tasks->count();
        $completed = $tasks->whereIn('status',$completedLike)->count();
        $active    = $tasks->whereIn('status',$activeLike)->count();
        $blocked   = $tasks->where('status','blocked')->count();
        $overdue   = $tasks->filter(fn($t)=>$t->due_date && $now->gt($t->due_date) && !in_array($t->status,$completedLike))->count();

        // SLA: نسبة الانتهاء قبل الموعد
        $onTime = $tasks->filter(function ($t) {
            return $t->due_date && in_array($t->status,['completed','closed']) && $t->closed_at && $t->closed_at <= $t->due_date->endOfDay();
        })->count();
        $slaRate = $total ? round($onTime * 100 / $total, 1) : 0.0;

        // طلبات الخامات المفتوحة للمشروع
        $openMr = MaterialRequest::whereIn('task_id', $tasks->pluck('id'))
            ->whereNull('provided_at')->count();

        // دورات إكمال (للمنجزة/المغلقة)
        $cycleSecs = $tasks->filter(fn($t)=>in_array($t->status,$completedLike))
            ->map(fn($t)=>$this->taskCycleSeconds($t))
            ->values();

        $avgCycle = $cycleSecs->count() ? intval($cycleSecs->avg()) : 0;
        $medCycle = $cycleSecs->count() ? $cycleSecs->sort()->values()->get(intval(($cycleSecs->count()-1)/2)) : 0;

        // متوسط زمن الحجب لكل مهمة
        $avgBlocked = $tasks->count()
            ? intval($tasks->map(fn($t)=>$this->taskStageSeconds($t,'blocked'))->avg() ?? 0)
            : 0;

        $countsByStatus = $tasks->groupBy('status')->map->count()->all();

        // صفوف جدول المهام
        $rows = $tasks->map(function($t){
            $sec = $this->taskCycleSeconds($t);
            return [
                'id'        => $t->id,
                'dept'      => $t->department->dept_name ?? '—',
                'emp'       => $t->employee->employee_name ?? '—',
                'status'    => $t->status ?? 'unknown',
                'status_ar' => $this->statusAr($t->status ?? 'unknown'),
                'sec'       => $sec,
                'human'     => $this->humanFromSeconds($sec),
                'created'   => optional($t->created_at)?->format('Y-m-d H:i') ?? '—',
                'closed'    => optional($t->closed_at)?->format('Y-m-d H:i') ?? '—',
            ];
        })->sortByDesc('sec')->values()->all();

        // ملفات (من المهام – عدّل لو لديك علاقة مباشرة بالمشروع)
        $files = $tasks->map(fn($t)=>[
            'task_id'=>$t->id, 'dept'=>$t->department->dept_name ?? '—', 'file'=>$t->file_path ?? null,
        ])->filter(fn($f)=>!empty($f['file']))->values()->all();

        // آخر نشاط
        $activity = $tasks->flatMap(fn($t)=>$t->logs)->sortByDesc('happened_at')->take(30)->values()->map(function($log){
            $at = $log->happened_at ?? $log->created_at;
            $at = $at ? Carbon::parse($at) : null;
            $act = is_array($log->data ?? null) ? ($log->data['to'] ?? ($log->data['action'] ?? $log->type)) : ($log->type ?? '—');
            return ['at'=>$at?->format('Y-m-d H:i') ?? '—','when'=>$at?->diffForHumans() ?? '—','type'=>$log->type ?? '—','what'=>$this->statusAr($act) ?? $act,'task'=>$log->task_id ?? null];
        })->all();

        // الإنجاز اليومي (آخر 14 يوم)
        $days = 14;
        $series = [];
        for ($i=$days-1; $i>=0; $i--) $series[now()->copy()->subDays($i)->format('Y-m-d')] = 0;
        foreach ($tasks as $t) {
            $d = optional($t->closed_at)?->format('Y-m-d');
            if ($d && array_key_exists($d,$series)) $series[$d] += 1;
        }

        return compact(
            'total','completed','active','blocked','overdue','onTime','slaRate',
            'countsByStatus','avgCycle','medCycle','avgBlocked','rows','files','activity','series','openMr'
        );
    }

    /* -------------------- Renderers (HTML/SVG) -------------------- */

    private function renderCardsHtml(array $s): string
    {
        $card = fn($label,$value,$color) =>
            '<div class="rounded-xl border bg-white/80 dark:bg-gray-900/70 p-4 shadow-sm">
               <div class="text-sm text-gray-500 dark:text-gray-400">'.$label.'</div>
               <div class="mt-1 text-2xl font-semibold" style="color:'.$color.'">'.$value.'</div>
             </div>';

        $html  = '<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 w-full">';
        $html .= $card('إجمالي المهام', $s['total'],     '#334155');
        $html .= $card('نشطة الآن',     $s['active'],    '#0ea5e9');
        $html .= $card('محجوبة',        $s['blocked'],   '#a855f7');
        $html .= $card('متأخرة',        $s['overdue'],   '#ef4444');
        $html .= $card('طلبات خامات مفتوحة', $s['openMr'], '#f59e0b');
        $html .= $card('SLA قبل الموعد', $s['slaRate'].'%', '#10b981');
        $html .= '</div>';

        // دورات
        $html .= '<div class="mt-4 grid grid-cols-2 md:grid-cols-3 gap-3 w-full">';
        $html .= $card('متوسط دورة الإكمال', $this->humanFromSeconds($s['avgCycle']), '#2563eb');
        $html .= $card('الوسيط لدورة الإكمال', $this->humanFromSeconds($s['medCycle']), '#7c3aed');
        $html .= $card('متوسط زمن الحجب/مهمة', $this->humanFromSeconds($s['avgBlocked']), '#a855f7');
        $html .= '</div>';

        return $html;
    }

    private function renderStatusDistributionChart(array $counts): string
    {
        if (empty($counts)) return '<div class="text-sm text-gray-500">لا توجد مهام.</div>';

        $max = max($counts);
        $bar = function ($label, $value, $hex) use ($max) {
            $pct = $max > 0 ? ($value / $max) : 0;
            $w   = max(2, intval($pct * 280));
            return '<div class="flex items-center gap-2">
                        <span class="w-32 text-sm">'.$label.'</span>
                        <div class="flex-1">
                            <div class="h-2 rounded bg-gray-200 dark:bg-gray-700">
                                <div class="h-2 rounded" style="width:'.$w.'px;background:'.$hex.'"></div>
                            </div>
                        </div>
                        <span class="w-10 text-end text-sm">'.$value.'</span>
                    </div>';
        };
        $html = '<div class="space-y-2">';
        foreach ($counts as $status => $cnt) {
            $html .= $bar($this->statusAr($status) ?? $status, $cnt, $this->statusHex($status));
        }
        $html .= '</div>';
        return $html;
    }

    private function renderDailyCompletionChart(array $series): string
    {
        $w = 560; $h = 80; $pad = 6;
        $n = max(1, count($series)); $max = max(1, max($series)); $xs = array_values($series);
        $points = [];
        for ($i=0;$i<$n;$i++) {
            $x = $pad + ($w - 2*$pad) * ($i/($n-1 ?: 1));
            $y = $h - $pad - ($h - 2*$pad) * ($xs[$i] / $max);
            $points[] = $x.','.$y;
        }
        ob_start(); ?>
        <div class="rounded-xl border bg-white/80 dark:bg-gray-900/70 p-4 shadow-sm">
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">الإنجاز اليومي (آخر ١٤ يوم)</div>
            <svg viewBox="0 0 <?= $w ?> <?= $h ?>" class="w-full h-24">
                <polyline fill="none" stroke="#0ea5e9" stroke-width="2" points="<?= implode(' ', $points) ?>" />
                <?php for ($i=0;$i<$n;$i++):
                    $x = $pad + ($w - 2*$pad) * ($i/($n-1 ?: 1));
                    $y = $h - $pad - ($h - 2*$pad) * ($xs[$i] / $max); ?>
                    <circle cx="<?= $x ?>" cy="<?= $y ?>" r="2.5" fill="#0ea5e9" />
                <?php endfor; ?>
            </svg>
        </div>
        <?php return (string) ob_get_clean();
    }

    private function renderTasksTableHtml(array $rows): string
    {
        ob_start(); ?>
        <div class="rounded-xl border bg-white/80 dark:bg-gray-900/70 shadow-sm overflow-hidden w-full">
            <div class="px-4 py-3 border-b bg-gray-100 !text-gray-900 dark:bg-gray-900 dark:!text-gray-100">
                <div class="text-sm font-semibold">الوقت المستغرق لكل مهمة</div>
            </div>
            <div class="p-4">
                <div class="overflow-x-auto">
                    <table class="w-full table-auto text-sm rtl:text-right">
                        <thead class="bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-gray-100 sticky top-0 z-10">
                        <tr>
                            <th class="px-3 py-2 font-semibold">#</th>
                            <th class="px-3 py-2 font-semibold">القسم</th>
                            <th class="px-3 py-2 font-semibold">المسؤول</th>
                            <th class="px-3 py-2 font-semibold">الحالة</th>
                            <th class="px-3 py-2 font-semibold">المدة</th>
                            <th class="px-3 py-2 font-semibold">من</th>
                            <th class="px-3 py-2 font-semibold">إلى</th>
                            <th class="px-3 py-2 font-semibold">مؤشر</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-800 dark:text-gray-200">
                        <?php foreach ($rows as $r):
                            $hex = $this->statusHex($r['status'] ?? null);
                            $badge = '<span class="px-2 py-0.5 rounded text-white text-xs" style="background:'.$hex.'">'.$r['status_ar'].'</span>';
                            ?>
                            <tr class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-900 dark:even:bg-gray-800">
                                <td class="px-3 py-2"><?= e($r['id']) ?></td>
                                <td class="px-3 py-2"><?= e($r['dept']) ?></td>
                                <td class="px-3 py-2"><?= e($r['emp']) ?></td>
                                <td class="px-3 py-2"><?= $badge ?></td>
                                <td class="px-3 py-2"><?= e($r['human']) ?></td>
                                <td class="px-3 py-2"><?= e($r['created']) ?></td>
                                <td class="px-3 py-2"><?= e($r['closed']) ?></td>
                                <td class="px-3 py-2 w-48">
                                    <div class="w-full h-2 rounded bg-gray-200 dark:bg-gray-700 overflow-hidden">
                                        <div class="h-2" style="width: 100%; background: <?= e($hex) ?>;"></div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($this->filterStatus || $this->filterDeptId || $this->filterEmpId): ?>
                    <div class="mt-3 text-xs text-gray-500">
                        تظهر النتائج مُصفاة — استخدم زر “إلغاء التصفية” لإرجاع كل المهام.
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php return (string) ob_get_clean();
    }

    private function renderFilesTableHtml(array $files): string
    {
        if (empty($files)) return '<div class="text-sm text-gray-500">لا توجد ملفات مرتبطة بالمشروع.</div>';

        ob_start(); ?>
        <div class="rounded-xl border bg-white/80 dark:bg-gray-900/70 shadow-sm overflow-hidden w-full">
            <div class="px-4 py-3 border-b bg-gray-100 !text-gray-900 dark:bg-gray-900 dark:!text-gray-100">
                <div class="text-sm font-semibold">ملفات المشروع</div>
            </div>
            <div class="p-4">
                <div class="overflow-x-auto">
                    <table class="w-full table-auto text-sm rtl:text-right">
                        <thead class="bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                        <tr>
                            <th class="px-3 py-2 font-semibold"># المهمة</th>
                            <th class="px-3 py-2 font-semibold">القسم</th>
                            <th class="px-3 py-2 font-semibold">الملف</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-800 dark:text-gray-200">
                        <?php foreach ($files as $f): ?>
                            <tr class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-900 dark:even:bg-gray-800">
                                <td class="px-3 py-2"><?= e($f['task_id']) ?></td>
                                <td class="px-3 py-2"><?= e($f['dept']) ?></td>
                                <td class="px-3 py-2">
                                    <a class="text-primary-600 underline" target="_blank"
                                       href="<?= e(\Storage::disk('public')->url($f['file'])) ?>">تحميل</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php return (string) ob_get_clean();
    }

    private function renderActivityHtml(array $rows): string
    {
        if (empty($rows)) return '<div class="text-sm text-gray-500">لا يوجد نشاط بعد.</div>';

        ob_start(); ?>
        <div class="rounded-xl border bg-white/80 dark:bg-gray-900/70 shadow-sm overflow-hidden w-full">
            <div class="px-4 py-3 border-b bg-gray-100 !text-gray-900 dark:bg-gray-900 dark:!text-gray-100">
                <div class="text-sm font-semibold">آخر النشاط</div>
            </div>
            <div class="p-4">
                <div class="overflow-x-auto">
                    <table class="w-full table-auto text-sm rtl:text-right">
                        <thead class="bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                        <tr>
                            <th class="px-3 py-2 font-semibold">التاريخ</th>
                            <th class="px-3 py-2 font-semibold">منذ</th>
                            <th class="px-3 py-2 font-semibold">النوع</th>
                            <th class="px-3 py-2 font-semibold">الوصف</th>
                            <th class="px-3 py-2 font-semibold"># المهمة</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-800 dark:text-gray-200">
                        <?php foreach ($rows as $r): ?>
                            <tr class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-900 dark:even:bg-gray-800">
                                <td class="px-3 py-2"><?= e($r['at']) ?></td>
                                <td class="px-3 py-2"><?= e($r['when']) ?></td>
                                <td class="px-3 py-2"><?= e($r['type']) ?></td>
                                <td class="px-3 py-2"><?= e($r['what']) ?></td>
                                <td class="px-3 py-2"><?= e($r['task'] ?? '—') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php return (string) ob_get_clean();
    }

    /* -------------------- Infolist with Tabs -------------------- */

    public function infolist(Infolist $infolist): Infolist
    {
        $stats = $this->computeProjectStats();

        return $infolist
            ->columns(12)
            ->schema([
                Tabs::make('projectTabs')
                    ->columnSpan(12)
                    ->tabs([
                        Tab::make('معلومات المشروع')
                            ->schema([
                                Section::make()->columns(3)->schema([
                                    TextEntry::make('project_name')->label('اسم المشروع')
                                        ->state(fn()=> $this->record->project_name ?? $this->record->name ?? '—'),
                                    TextEntry::make('client')->label('العميل')
                                        ->state(fn()=> $this->record->client->client_name ?? '—'),
                                    TextEntry::make('showroom')->label('المعرض')
                                        ->state(fn()=> $this->record->showroom->name ?? '—'),
                                    TextEntry::make('start_date')->label('تاريخ البدء')
                                        ->state(fn()=> optional($this->record->start_date)?->format('Y-m-d') ?? '—'),
                                    TextEntry::make('due_date')->label('تاريخ التسليم')
                                        ->state(fn()=> optional($this->record->end_date)?->format('Y-m-d') ?? '—'),
                                    TextEntry::make('description')->label('الوصف')->columnSpanFull()
                                        ->state(fn()=> $this->record->description ?? $this->record->project_description ?? '—'),
                                ]),
                            ]),

                        Tab::make('ملخص')
                            ->schema([
                                Section::make()->columns(1)->schema([
                                    TextEntry::make('cards')->label('المؤشرات')->html()
                                        ->state(fn()=> $this->renderCardsHtml($stats))
                                        ->columnSpanFull(),

                                    Section::make('مخططات')->columns(2)->schema([
                                        TextEntry::make('status_chart')->label('توزيع الحالات')->html()
                                            ->state(fn()=> $this->renderStatusDistributionChart($stats['countsByStatus'])),
                                        TextEntry::make('daily_chart')->label('الإنجاز اليومي')->html()
                                            ->state(fn()=> $this->renderDailyCompletionChart($stats['series'])),
                                    ])->columnSpanFull(),
                                ]),
                            ]),

                        Tab::make('مهام')
                            ->schema([
                                Section::make()->columns(1)->schema([
                                    TextEntry::make('tasks_table')->label('الوقت المستغرق لكل مهمة')->html()
                                        ->state(fn()=> $this->renderTasksTableHtml($stats['rows']))
                                        ->columnSpanFull(),
                                ]),
                            ]),

                        Tab::make('ملفات')
                            ->schema([
                                Section::make()->columns(1)->schema([
                                    TextEntry::make('files_table')->label('ملفات المشروع')->html()
                                        ->state(fn()=> $this->renderFilesTableHtml($stats['files']))
                                        ->columnSpanFull(),
                                ]),
                            ]),

                        Tab::make('نشاط')
                            ->schema([
                                Section::make()->columns(1)->schema([
                                    TextEntry::make('activity')->label('آخر النشاط')->html()
                                        ->state(fn()=> $this->renderActivityHtml($stats['activity']))
                                        ->columnSpanFull(),
                                ]),
                            ]),
                    ]),
            ]);
    }

    /* -------------------- Breadcrumbs / Redirect -------------------- */

    protected function getRedirectUrl(): string
    {
        return url("/admin/projects/{$this->record->id}/manage-tasks");
    }

    public function getBreadcrumbs(): array
    {
        return [
            url("/admin/projects/{$this->record->id}/manage-tasks") => 'مهام المشروع',
            'عرض المشروع',
        ];
    }
}
