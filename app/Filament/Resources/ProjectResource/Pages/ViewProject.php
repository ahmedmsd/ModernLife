<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Filament\Resources\TaskResource;
use App\Models\ProductionTask;
use App\Models\MaterialRequest;
use App\Models\Project;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\{Section, TextEntry, Tabs};
use Filament\Infolists\Components\Tabs\Tab;
use Filament\Support\Enums\MaxWidth;
use Filament\Actions\Action;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class ViewProject extends ViewRecord
{
    protected static string $resource = ProjectResource::class;
    protected static ?string $title   = 'عرض المشروع';

    public ?string $filterStatus = null;
    public ?int    $filterDeptId = null;
    public ?int    $filterEmpId  = null;

    protected $queryString = [
        'filterStatus' => ['except' => ''],
        'filterDeptId' => ['except' => ''],
        'filterEmpId'  => ['except' => ''],
    ];

    public function getMaxContentWidth(): MaxWidth|string|null
    {
        return MaxWidth::Full;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('filters')
                ->label('تصفية المهام')
                ->icon('heroicon-o-funnel')
                ->form([
                    \Filament\Forms\Components\Select::make('status')->label('الحالة')->options([
                        'pending'=>'قيد الإنشاء','assigned'=>'مُسندة','acknowledged'=>'تأكيد الاستلام',
                        'in_progress'=>'قيد التنفيذ','blocked'=>'متوقفة','under_review'=>'قيد المراجعة',
                        'rework'=>'إعادة عمل','completed'=>'مكتملة','closed'=>'مغلقة','cancelled'=>'ملغاة','draft'=>'مسودة',
                    ])->searchable()->placeholder('الكل'),
                    \Filament\Forms\Components\Select::make('dept_id')->label('القسم')
                        ->options(fn()=> \App\Models\Department::orderBy('dept_name')->pluck('dept_name','dept_id')->all())
                        ->searchable()->placeholder('الكل'),
                    \Filament\Forms\Components\Select::make('emp_id')->label('المسؤول')
                        ->options(fn()=> \App\Models\Employee::orderBy('employee_name')->pluck('employee_name','employee_id')->all())
                        ->searchable()->placeholder('الكل'),
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

            Action::make('downloadAllFiles')
                ->label('تنزيل كل ملفات المشروع (Zip)')
                ->icon('heroicon-o-archive-box-arrow-down')
                ->action(function () {
                    $files = $this->collectProjectFiles($this->getRecord());
                    if (empty($files)) {
                        \Filament\Notifications\Notification::make()->warning()->title('لا توجد ملفات للتنزيل')->send();
                        return;
                    }
                    $zipName = "project-{$this->record->id}-files.zip";
                    $zipPath = storage_path("app/tmp/{$zipName}");
                    @mkdir(dirname($zipPath), 0777, true);

                    $zip = new ZipArchive();
                    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                        \Filament\Notifications\Notification::make()->danger()->title('فشل إنشاء ملف مضغوط')->send();
                        return;
                    }

                    foreach ($files as $f) {
                        $path = $f['path'];
                        $display = $f['display'] ?? basename((string)$path);
                        $storagePath = $this->resolveStorageAbsolutePath($path);
                        if ($storagePath && is_file($storagePath)) {
                            $zip->addFile($storagePath, $display);
                        }
                    }
                    $zip->close();

                    return response()->download($zipPath)->deleteFileAfterSend(true);
                }),
        ];
    }

    /* ============================ Helpers ============================ */

    private function normalizeStatus(string|array|null $v): ?string
    {
        if (is_array($v)) {
            $v = $v['status'] ?? $v['to'] ?? $v['value'] ?? array_values($v)[0] ?? null;
            if (is_array($v)) $v = array_values($v)[0] ?? null;
        }
        return $v === null ? null : (string) $v;
    }

    private function statusAr(string|array|null $v): ?string
    {
        $v = $this->normalizeStatus($v);
        $map = [
            'pending'=>'قيد الإنشاء','assigned'=>'مُسندة','acknowledged'=>'تأكيد الاستلام',
            'in_progress'=>'قيد التنفيذ','blocked'=>'متوقفة','under_review'=>'قيد المراجعة',
            'rework'=>'إعادة عمل','completed'=>'مكتملة','closed'=>'مغلقة','cancelled'=>'ملغاة','draft'=>'مسودة',
            'unknown'=>'غير معروفة',
        ];
        return $map[$v] ?? $v;
    }

    private function statusHex(string|array|null $status): string
    {
        $status = $this->normalizeStatus($status);
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

    private function parseDate($v): ?Carbon
    {
        if ($v instanceof Carbon) return $v;
        if (empty($v)) return null;
        return Carbon::parse($v);
    }

    /* ============================ Data ============================ */

    private function filteredTasksQuery(): Builder
    {
        return ProductionTask::query()
            ->where('project_id', $this->record->id)
            ->when($this->filterStatus, fn ($q, $v) => $q->where('status', $v))
            ->when($this->filterDeptId, fn ($q, $v) => $q->where('department_id', $v))
            ->when($this->filterEmpId,  fn ($q, $v) => $q->where('assigned_to_employee_id', $v))
            ->with([
                'department:dept_id,dept_name',
                'employee:employee_id,employee_name',
                'logs' => fn ($q) => $q->orderBy('happened_at')
                    ->select('id','task_id','type','data','happened_at','created_at'),
                'materialRequests' => fn ($q) => $q->select([
                    'id','task_id','po_file','invoice_file','estimated_cost','actual_cost',
                    'status','provided_at','expected_delivery_at'
                ]),
            ]);
    }

    private function taskCycleSeconds(ProductionTask $t): int
    {
        $start = $this->parseDate($t->created_at) ?: now();
        $end   = $this->parseDate($t->closed_at)  ?: now();
        return max(0, $start->diffInSeconds($end));
    }

    private function taskStageSeconds(ProductionTask $t, string $stage): int
    {
        $logs = $t->logs;
        if (!$logs || $logs->isEmpty()) return 0;

        $start = $this->parseDate($t->created_at)
            ?: ($logs->first()?->happened_at ? Carbon::parse($logs->first()->happened_at) : now());
        $end   = $this->parseDate($t->closed_at) ?: $this->parseDate($logs->last()?->happened_at) ?: now();

        $firstChange = $logs->firstWhere('type','status_changed');
        $current = is_array($firstChange?->data ?? null) ? ($firstChange->data['from'] ?? 'pending') : ($t->status ?? 'pending');

        $cursor  = $start->clone();
        $seconds = 0;

        foreach ($logs as $log) {
            $tAt = $this->parseDate($log->happened_at ?? $log->created_at);
            if (!$tAt) continue;

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

    private function computeProjectStats(): array
    {
        /** @var Project $project */
        $project = $this->getRecord();
        $tasks   = $this->filteredTasksQuery()->get();

        $now = now();
        $completedLike = ['completed','closed'];
        $activeLike    = ['pending','assigned','acknowledged','in_progress','under_review','rework','blocked'];

        $total     = $tasks->count();
        $completed = $tasks->whereIn('status',$completedLike)->count();
        $active    = $tasks->whereIn('status',$activeLike)->count();
        $blocked   = $tasks->where('status','blocked')->count();

        $overdue   = $tasks->filter(function ($t) {
            $due = $this->parseDate($t->due_date);
            return $due && now()->gt($due) && !in_array($t->status, ['completed','closed'], true);
        })->count();

        $onTime = $tasks->filter(function ($t) {
            $due = $this->parseDate($t->due_date);
            $closed = $this->parseDate($t->closed_at);
            return $due && $closed && $closed->lte($due->copy()->endOfDay());
        })->count();
        $slaRate = $total ? round($onTime * 100 / $total, 1) : 0.0;

        $openMr = MaterialRequest::whereIn('task_id', $tasks->pluck('id'))
            ->whereNull('provided_at')->count();

        $cycleSecs = $tasks->whereIn('status',$completedLike)
            ->map(fn($t)=>$this->taskCycleSeconds($t))->values();

        $avgCycle = $cycleSecs->count() ? intval($cycleSecs->avg()) : 0;
        $medCycle = $cycleSecs->count() ? $cycleSecs->sort()->values()->get(intval(($cycleSecs->count()-1)/2)) : 0;
        $avgBlocked = $tasks->count()
            ? intval($tasks->map(fn($t)=>$this->taskStageSeconds($t,'blocked'))->avg() ?? 0)
            : 0;

        $countsByStatus = $tasks->groupBy('status')->map->count()->all();

        $rows = $tasks->map(function($t){
            $sec = $this->taskCycleSeconds($t);
            $viewUrl = class_exists(ProductionTaskResource::class)
                ? ProductionTaskResource::getUrl('view', ['record' => $t])
                : '#';
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
                'url'       => $viewUrl,
            ];
        })->sortByDesc('sec')->values()->all();

        $files = $this->collectProjectFiles($project, $tasks->all());

        $activity = $tasks->flatMap(fn($t)=>$t->logs)
            ->sortByDesc('happened_at')->take(50)->values()
            ->map(function($log){
                $at = $this->parseDate($log->happened_at ?? $log->created_at);
                $raw = $log->data ?? null;
                $act = is_array($raw) ? ($raw['to'] ?? $raw['status'] ?? $raw['action'] ?? $log->type) : ($log->type ?? '—');
                $actNorm = $this->normalizeStatus($act);
                return [
                    'at'   => $at?->format('Y-m-d H:i') ?? '—',
                    'when' => $at?->diffForHumans() ?? '—',
                    'type' => $log->type ?? '—',
                    'what' => $this->statusAr($actNorm) ?? $actNorm ?? '—',
                    'task' => $log->task_id ?? null,
                ];
            })->all();

        $days = 14; $series = [];
        for ($i=$days-1; $i>=0; $i--) $series[now()->copy()->subDays($i)->format('Y-m-d')] = 0;
        foreach ($tasks as $t) {
            $d = optional($t->closed_at)?->format('Y-m-d');
            if ($d && array_key_exists($d,$series)) $series[$d] += 1;
        }

        // تأخيرات بارزة
        $delays = $tasks->filter(fn($t)=> $t->due_date && !in_array($t->status,['completed','closed'],true))
            ->map(function($t){
                $due = $this->parseDate($t->due_date);
                $daysLate = $due ? max(0, $due->diffInDays(now(), false)) : 0;
                return [
                    'id' => $t->id,
                    'dept' => $t->department->dept_name ?? '—',
                    'emp' => $t->employee->employee_name ?? '—',
                    'days_late' => $daysLate,
                ];
            })
            ->sortByDesc('days_late')->take(10)->values()->all();

        return compact(
            'total','completed','active','blocked','overdue','onTime','slaRate',
            'countsByStatus','avgCycle','medCycle','avgBlocked','rows','files','activity','series','openMr','delays'
        );
    }

    /**
     * يجمع الملفات من: ملفات المشروع (إن وُجدت علاقة files)، ملفات المهام (حقول شائعة)،
     * وطلبات الخامات (po_file, invoice_file).
     */
    private function collectProjectFiles(Project $project, array $tasks = []): array
    {
        $out = [];

        if (method_exists($project, 'files')) {
            foreach ($project->files as $pf) {
                $path = $pf->file_path ?? null;
                if ($path) $out[] = [
                    'source'  => 'project',
                    'ref'     => $project->id,
                    'label'   => $pf->file_name ?? basename($path),
                    'path'    => $path,
                    'display' => "project/{$project->id}/".($pf->file_name ?? basename($path)),
                ];
            }
        }

        $knownTaskFileCols = [
            'attachment','file_path','drawing_file','design_file','manufacturing_file','install_file','client_receipt'
        ];

        /** @var ProductionTask $t */
        foreach ($tasks as $t) {
            foreach ($knownTaskFileCols as $col) {
                $path = $t->getAttribute($col);
                if ($path) {
                    $out[] = [
                        'source'  => 'task',
                        'ref'     => $t->id,
                        'label'   => "{$col} (#{$t->id})",
                        'path'    => $path,
                        'display' => "tasks/{$t->id}/{$col}-".basename((string)$path),
                    ];
                }
            }

            foreach ($t->materialRequests ?? [] as $mr) {
                foreach (['po_file','invoice_file'] as $mcol) {
                    $mpath = $mr->{$mcol} ?? null;
                    if ($mpath) {
                        $out[] = [
                            'source'  => 'material_request',
                            'ref'     => $mr->id,
                            'label'   => "{$mcol} (MR #{$mr->id})",
                            'path'    => $mpath,
                            'display' => "mr/{$mr->id}/{$mcol}-".basename((string)$mpath),
                        ];
                    }
                }
            }
        }

        // حذف المكررات
        $seen = [];
        $final = [];
        foreach ($out as $row) {
            $key = md5(($row['path'] ?? '') . '|' . ($row['display'] ?? ''));
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $final[] = $row;
            }
        }
        return $final;
    }

    private function encodeUrlPath(string $url): string
    {
        $parts = parse_url($url);
        $scheme = $parts['scheme'] ?? null;
        $host   = $parts['host']   ?? null;
        $port   = isset($parts['port']) ? ':' . $parts['port'] : '';
        $path   = $parts['path']   ?? '';
        $query  = isset($parts['query']) ? '?' . $parts['query'] : '';
        $frag   = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

        $segments = array_map(fn ($s) => rawurlencode(urldecode($s)), explode('/', ltrim($path, '/')));
        $encodedPath = '/' . implode('/', $segments);

        if ($scheme && $host) {
            return "{$scheme}://{$host}{$port}{$encodedPath}{$query}{$frag}";
        }

        // Relative URL
        return "{$encodedPath}{$query}{$frag}";
    }

    private function getFileUrl(string $path): ?string
    {
        if (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://'])) {
            return $this->encodeUrlPath($path);
        }

        if (\Illuminate\Support\Str::startsWith($path, ['/storage/'])) {
            return $this->encodeUrlPath(url($path));
        }

        try {
            if (\Storage::exists($path)) {
                return $this->encodeUrlPath(\Storage::url($path));
            }
        } catch (\Throwable $e) {}

        try {
            if (\Storage::disk('public')->exists($path)) {
                return $this->encodeUrlPath(\Storage::disk('public')->url($path));
            }
        } catch (\Throwable $e) {}

        $abs = public_path(ltrim($path, '/'));
        if (is_file($abs)) {
            return $this->encodeUrlPath(url('/' . ltrim($path, '/')));
        }

        return null;
    }



    private function resolveStorageAbsolutePath(string $path): ?string
    {
        if (Str::startsWith($path, ['http://', 'https://'])) return null;
        if (Str::startsWith($path, ['/storage/'])) {
            $rel = Str::after($path, '/storage/');
            return storage_path('app/public/' . $rel);
        }
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->path($path);
        }
        $abs = public_path(ltrim($path, '/'));
        return is_file($abs) ? $abs : null;
    }

    /* ============================ Renderers ============================ */

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

        $html .= '<div class="mt-4 grid grid-cols-2 md:grid-cols-3 gap-3 w-full">';
        $html .= $card('متوسط دورة الإكمال', $this->humanFromSeconds($s['avgCycle']), '#2563eb');
        $html .= $card('الوسيط لدورة الإكمال', $this->humanFromSeconds($s['medCycle']), '#7c3aed');
        $html .= $card('متوسط زمن الحجب/مهمة', $this->humanFromSeconds($s['avgBlocked']), '#a855f7');
        $html .= '</div>';

        // قائمة مختصرة لأكثر المهام تأخرًا
        if (!empty($s['delays'])) {
            $html .= '<div class="mt-4 rounded-xl border bg-white/80 dark:bg-gray-900/70 p-4 shadow-sm">';
            $html .= '<div class="text-sm text-gray-500 dark:text-gray-400 mb-2">أكثر المهام تأخرًا</div>';
            $html .= '<ul class="text-sm space-y-1">';
            foreach ($s['delays'] as $d) {
                $html .= '<li>مهمة #'.$d['id'].' — '.$d['dept'].' — '.$d['emp'].' • متأخرة '.$d['days_late'].' يوم</li>';
            }
            $html .= '</ul></div>';
        }

        return $html;
    }

    private function renderStatusDistributionChart(array $counts): string
    {
        if (empty($counts)) return '<div class="text-sm text-gray-500">لا توجد مهام.</div>';

        $order = ['pending','assigned','acknowledged','in_progress','blocked','under_review','rework','completed','closed','cancelled','draft'];
        $counts = collect($counts)->sortBy(function($v,$k) use ($order){ $idx = array_search($k,$order,true); return $idx===false?999:$idx; })->all();

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
                            <th class="px-3 py-2 font-semibold">رابط</th>
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
                                <td class="px-3 py-2">
                                    <?php if (!empty($r['url']) && $r['url'] !== '#'): ?>
                                        <a class="text-primary-600 underline" href="<?= e($r['url']) ?>" target="_blank">عرض</a>
                                    <?php else: ?>—<?php endif; ?>
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
                <div class="text-sm font-semibold">ملفات المشروع عبر دورة الحياة</div>
            </div>
            <div class="p-4">
                <div class="overflow-x-auto">
                    <table class="w-full table-auto text-sm rtl:text-right">
                        <thead class="bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                        <tr>
                            <th class="px-3 py-2 font-semibold">المصدر</th>
                            <th class="px-3 py-2 font-semibold">المرجع</th>
                            <th class="px-3 py-2 font-semibold">الاسم</th>
                            <th class="px-3 py-2 font-semibold">تحميل</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-800 dark:text-gray-200">
                        <?php foreach ($files as $f):
                            $path = $f['path'] ?? null;
                            $url = $path ? $this->getFileUrl($path) : null;
                            ?>
                            <tr class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-900 dark:even:bg-gray-800">
                                <td class="px-3 py-2"><?= e($f['source']) ?></td>
                                <td class="px-3 py-2"><?= e((string)($f['ref'] ?? '—')) ?></td>
                                <td class="px-3 py-2"><?= e($f['label'] ?? basename((string)$path)) ?></td>
                                <td class="px-3 py-2">
                                    <?php if ($url): ?>
                                        <a class="text-primary-600 underline" target="_blank" href="<?= e($url) ?>">تحميل</a>
                                    <?php else: ?>—<?php endif; ?>
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

    /* ============================ Infolist ============================ */

    public function infolist(Infolist $infolist): Infolist
    {
        $stats = $this->computeProjectStats();

        return $infolist
            ->columns(12)
            ->schema([
                Tabs::make('projectTabs')->columnSpan(12)->tabs([
                    Tab::make('معلومات المشروع')->schema([
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

                    Tab::make('ملخص')->schema([
                        Section::make()->columns(1)->schema([
                            TextEntry::make('cards')->label('المؤشرات')->html()
                                ->state(fn()=> $this->renderCardsHtml($stats))->columnSpanFull(),
                            Section::make('مخططات')->columns(2)->schema([
                                TextEntry::make('status_chart')->label('توزيع الحالات')->html()
                                    ->state(fn()=> $this->renderStatusDistributionChart($stats['countsByStatus'])),
                                TextEntry::make('daily_chart')->label('الإنجاز اليومي')->html()
                                    ->state(fn()=> $this->renderDailyCompletionChart($stats['series'])),
                            ])->columnSpanFull(),
                        ]),
                    ]),

                    Tab::make('مهام')->schema([
                        Section::make()->columns(1)->schema([
                            TextEntry::make('tasks_table')->label('الوقت المستغرق لكل مهمة')->html()
                                ->state(fn()=> $this->renderTasksTableHtml($stats['rows']))->columnSpanFull(),
                        ]),
                    ]),

                    Tab::make('ملفات')->schema([
                        Section::make()->columns(1)->schema([
                            TextEntry::make('files_table')->label('ملفات المشروع')->html()
                                ->state(fn()=> $this->renderFilesTableHtml($stats['files']))->columnSpanFull(),
                        ]),
                    ]),

                    Tab::make('نشاط')->schema([
                        Section::make()->columns(1)->schema([
                            TextEntry::make('activity')->label('آخر النشاط')->html()
                                ->state(fn()=> $this->renderActivityHtml($stats['activity']))->columnSpanFull(),
                        ]),
                    ]),
                ]),
            ]);
    }

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
