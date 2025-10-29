@php
    use App\Enums\ProductionRequestStatus;
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Arr;
    use Illuminate\Support\Str;

    /* ======================= أدوات عامة موحّدة ======================= */

     $normalizeRole = function (?string $role) {
        $role = (string) $role;
        $role = Str::of($role)->lower()->replace([' ', '-'], '_')->value();
        return match ($role) {
            'showroom', 'showroomowner', 'showroom_manager'      => 'showroom_manager',
            'factory', 'factoryowner', 'factory_manager'          => 'factory_manager',
            'dept', 'department', 'department_manager'            => 'department_manager',
            default                                                => $role,
        };
    };

$detectRole = function (array $data) use ($normalizeRole) : ?string {
    $role = data_get($data, 'owner_role')
         ?? data_get($data, 'to.owner_role')
         ?? data_get($data, 'owner')
         ?? data_get($data, 'to.role');

    if (!empty($role)) {
        return $normalizeRole((string) $role);
    }

    $phase = Str::of((string) (data_get($data, 'phase') ?? data_get($data, 'to.phase') ?? ''))
        ->lower()->value();

    if ($phase === '') return null;

    if (Str::contains($phase, 'showroom'))   return 'showroom_manager';
    if (Str::contains($phase, 'factory'))    return 'factory_manager';
    if (Str::contains($phase, 'department')) return 'department_manager';

    return null;
};
    $toArr = function ($v) {
        if (is_array($v)) return $v;
        if ($v instanceof \JsonSerializable) $v = $v->jsonSerialize();
        if (is_object($v)) return (array) $v;
        if (is_string($v)) {
            $j = json_decode($v, true);
            if (json_last_error() === JSON_ERROR_NONE) return $j ?? [];
        }
        return (array) $v;
    };



    // تنسيق وقت مختصر
    $fmt = function (?Carbon $c) { return $c?->format('Y-m-d H:i') ?? '—'; };

    // تحويل دقائق إلى نص عربي
    $minutesToHuman = function (int $min): string {
        $d = intdiv($min, 1440);
        $h = intdiv($min % 1440, 60);
        $m = $min % 60;
        $parts = [];
        if ($d) $parts[] = "{$d} يوم";
        if ($h) $parts[] = "{$h} ساعة";
        if ($m || (!$d && !$h)) $parts[] = "{$m} دقيقة";
        return implode(' و ', $parts);
    };

    // فرق زمني آمن
    $humanDiff = function ($from, $to) use ($minutesToHuman) {
        if (blank($from) || blank($to)) return '—';
        $from = $from instanceof Carbon ? $from : Carbon::parse($from);
        $to   = $to   instanceof Carbon ? $to   : Carbon::parse($to);
        if ($to->lessThan($from)) { [$from, $to] = [$to, $from]; }
        return $minutesToHuman($from->diffInMinutes($to));
    };

    /* ======================= رؤوس بيانات أساسية ======================= */

    $statusEnum  = ProductionRequestStatus::tryFrom($record->status);
    $clientNote  = trim($record->client->notes ?? '');
    $clientName  = $record->client->client_name ?? '—';
    $created     = $record->created_at ? Carbon::parse($record->created_at) : null;

    // تحميل علاقات لازمة
    $record->loadMissing([
        'logs',
        'creator',
        'client',
        'showroom',
        'project.tasks.department',
        'project.tasks.employee',
        'project.tasks.materialRequests.requestedBy',
        'project.tasks.materialRequests.providedBy',
        'project.tasks.logs',
    ]);

    /* ======================= تجميع اللوج ======================= */

    $reqLogs  = collect($record->logs ?? []);
    $projLogs = $record->project
    ? collect($record->project->taskLogs ?? [])
    : collect();
    $taskLogs  = collect();

    $tasks = $record->project
        ? $record->project->tasks()->with([
            'department',
            'employee',
            'materialRequests:id,task_id,expected_delivery_at,approved_at,provided_at,requested_at,requested_by,provided_by',
            'logs',
        ])->orderBy('id')->get()
        : collect();

    foreach ($tasks as $t) {
        $taskLogs = $taskLogs->merge($t->logs ?? collect());
    }

    $allLogs = $reqLogs
        ->concat($projLogs)
        ->concat($taskLogs)
        ->filter();

    // ترتيب زمني واحد للكل
    $allLogs = $allLogs->sortBy(function ($l) {
        $t = $l->action_at ?? $l->happened_at ?? $l->created_at ?? null;
        return $t ? Carbon::parse($t)->timestamp : 0;
    })->values();

    // ترتيب فرعي للوج الطلب فقط (لاستخراج الإرسال/الاستلام للأدوار)
    $reqLogsSorted = $reqLogs->sortBy(function ($l) {
        $t = $l->action_at ?? $l->happened_at ?? $l->created_at ?? null;
        return $t ? Carbon::parse($t)->timestamp : 0;
    })->values();

    /* ======================= مساعدات استخراج الأحداث ======================= */

    $firstEventAt = function (array $keys, $logs) {
        $ev = collect($logs ?? [])->first(function ($l) use ($keys) {
            $k = $l->action ?? $l->type ?? null;
            return $k && in_array($k, $keys, true);
        });
        $at = $ev?->action_at ?? $ev?->happened_at ?? $ev?->created_at;
        return $at ? Carbon::parse($at) : null;
    };

   $firstOwnerSendAt = function (string $role) use ($reqLogsSorted, $toArr, $normalizeRole, $detectRole) {
    $role = $normalizeRole($role);

    $explicit = [
        'showroom_manager'   => ['sent_to_showroom'],
        'factory_manager'    => ['sent_to_factory'],
        'department_manager' => ['sent_to_department', 'waiting_production'],
    ][$role] ?? [];

    $event = $reqLogsSorted->first(function ($l) use ($explicit, $role, $toArr, $detectRole) {
        $type = $l->action ?? $l->type ?? null;

        // أحداث إرسال صريحة
        if (in_array($type, $explicit, true)) return true;

        // انتقالات تحمل phase فقط
        if (in_array($type, ['ownership_changed','owner_changed','transition','status_changed'], true)) {
            $data   = $toArr($l->data);
            $toRole = $detectRole($data);
            return $toRole === $role;
        }
        return false;
    });

    $at = $event?->action_at ?? $event?->happened_at ?? $event?->created_at;
    return $at ? \Illuminate\Support\Carbon::parse($at) : null;
};

$firstOwnerReceiveAt = function (string $role) use ($reqLogsSorted, $toArr, $normalizeRole, $detectRole) {
    $role = $normalizeRole($role);

    $event = $reqLogsSorted->first(function ($l) use ($role, $toArr, $detectRole) {
        $type = $l->action ?? $l->type ?? null;
        if (!in_array($type, ['ownership_received','owner_received','received'], true)) return false;

        $data  = $toArr($l->data);
        $rRole = $detectRole($data);

        return $rRole === $role;
    });

    $at = $event?->action_at ?? $event?->happened_at ?? $event?->created_at;
    return $at ? \Illuminate\Support\Carbon::parse($at) : null;
};
    /* ======================= مواد/توريد ======================= */

    $mrs = $tasks->flatMap(fn($t) => $t->materialRequests ?? collect());

    $expectedAt = optional(
        $mrs->whereNotNull('expected_delivery_at')->sortBy('expected_delivery_at')->first()
    )->expected_delivery_at;

    $approvedAt = optional(
        $mrs->whereNotNull('approved_at')->sortBy('approved_at')->first()
    )->approved_at;

    $providedAt = optional(
        $mrs->whereNotNull('provided_at')->sortBy('provided_at')->first()
    )->provided_at;

    $expected = $expectedAt ? Carbon::parse($expectedAt) : null;
    $approved = $approvedAt ? Carbon::parse($approvedAt) : null;
    $provided = $providedAt ? Carbon::parse($providedAt) : null;

    /* ======================= تصنيع فعلي/مخطط ======================= */

    $actualStartAt = $firstEventAt(['manufacturing_started'], $allLogs)
        ?? ($tasks->min(fn($t) => $t->actual_start_at ?? $t->started_at ?? null)
            ? Carbon::parse($tasks->min(fn($t) => $t->actual_start_at ?? $t->started_at ?? null)) : null);

    $actualEndAt = $firstEventAt(['manufacturing_finished'], $allLogs)
        ?? ($tasks->max(fn($t) => $t->actual_end_at ?? $t->finished_at ?? null)
            ? Carbon::parse($tasks->max(fn($t) => $t->actual_end_at ?? $t->finished_at ?? null)) : null);

    $plannedStartAgg = $tasks->min(fn($t) => $t->planned_start_at ?? $t->planned_start ?? null);
    $plannedEndAgg   = $tasks->max(fn($t) => $t->planned_end_at   ?? $t->planned_end   ?? null);
    $plannedStart    = $plannedStartAgg ? Carbon::parse($plannedStartAgg) : null;
    $plannedEnd      = $plannedEndAgg   ? Carbon::parse($plannedEndAgg)   : null;

    /* ======================= أحداث ما بعد التصنيع ======================= */

    $qaMfgApproveAt   = $firstEventAt(['qa_approved_manufacturing'], $allLogs);
    $sentToInstallAt  = $firstEventAt(['sent_to_install'], $allLogs);
    $instStartedAt    = $firstEventAt(['installation_started'], $allLogs);
    $qaInstApproveAt  = $firstEventAt(['qa_approved_installation'], $allLogs);

    $clientReceiptAt  = $firstEventAt(['client_receipt_uploaded'], $allLogs);
    $taskCompletedAt  = $firstEventAt(['task_completed'], $allLogs);
    $projectCompletedAt = $firstEventAt(['project_completed'], $reqLogs);
    $requestClosedAt    = $firstEventAt(['production_request_closed'], $reqLogs)
                       ?? (($record->status ?? null) === 'closed'
                           ? ($record->updated_at ? Carbon::parse($record->updated_at) : null)
                           : null);

    /* ======================= أوقات إرسال/استلام الأدوار ======================= */

    $sentShowroomAt = $firstOwnerSendAt('showroom_manager');
    $sentFactoryAt  = $firstOwnerSendAt('factory_manager');
    $sentDeptAt     = $firstOwnerSendAt('department_manager');

    $recvShowroomAt = $firstOwnerReceiveAt('showroom_manager')   ?? $sentShowroomAt;
    $recvFactoryAt  = $firstOwnerReceiveAt('factory_manager')    ?? $sentFactoryAt;
    $recvDeptAt     = $firstOwnerReceiveAt('department_manager') ?? $sentDeptAt;

    /* ======================= فروق زمنية/مؤشرات ======================= */

    $expectedVsActualText  = '—';
    $expectedVsActualColor = 'gray';
    if ($expected && $provided) {
        $mins = $expected->diffInMinutes($provided, false); // سالب = أبكر
        $expectedVsActualText  = $mins === 0 ? 'في الموعد تمامًا'
            : ($mins < 0 ? 'أبكر بـ ' : 'متأخر بـ ') . $minutesToHuman(abs($mins));
        $expectedVsActualColor = $mins <= 0 ? ($mins === 0 ? 'success' : 'info') : 'danger';
    }

    $startDriftText = '—'; $startDriftColor = 'gray';
    if ($plannedStart && $actualStartAt) {
        $mins = $plannedStart->diffInMinutes($actualStartAt, false);
        $startDriftText  = $mins === 0 ? 'في الموعد'
            : ($mins < 0 ? 'أبكر بـ ' : 'متأخر بـ ') . $minutesToHuman(abs($mins));
        $startDriftColor = $mins <= 0 ? ($mins === 0 ? 'success' : 'info') : 'danger';
    }

    $endDriftText = '—'; $endDriftColor = 'gray';
    if ($plannedEnd && $actualEndAt) {
        $mins = $plannedEnd->diffInMinutes($actualEndAt, false);
        $endDriftText  = $mins === 0 ? 'في الموعد'
            : ($mins < 0 ? 'أبكر بـ ' : 'متأخر بـ ') . $minutesToHuman(abs($mins));
        $endDriftColor = $mins <= 0 ? ($mins === 0 ? 'success' : 'info') : 'danger';
    }

    // فترات رئيسية
    $created_to_showroom   = $humanDiff($created,        $recvShowroomAt);   // الإنشاء → استلام المعرض
    $showroom_to_factory   = $humanDiff($recvShowroomAt, $recvFactoryAt);    // استلام المعرض → استلام المصنع
    $factory_to_department = $humanDiff($recvFactoryAt,  $recvDeptAt);       // استلام المصنع → استلام القسم

    $dept_to_supply        = $humanDiff($sentDeptAt,     $provided);
    $dept_to_mfg_start     = $humanDiff($sentDeptAt,     $actualStartAt);
    $mfg_duration          = $humanDiff($actualStartAt,  $actualEndAt);
    $mfg_to_qa_approve     = $humanDiff($actualEndAt,    $qaMfgApproveAt);
    $qa_to_install_start   = $humanDiff($qaMfgApproveAt, $instStartedAt);
    $install_to_qa_appr    = $humanDiff($instStartedAt,  $qaInstApproveAt);
    $qa_appr_to_client     = $humanDiff($qaInstApproveAt,$clientReceiptAt);

    $total_to_client       = $humanDiff($created,        $clientReceiptAt);
    $created_to_provided   = $humanDiff($created,        $provided);
    $approved_to_provided  = $humanDiff($approved,       $provided);
@endphp

<x-filament::page>
    <!-- ============================ معلومات الطلب ============================ -->
    <x-filament::section>
        @if ($clientNote !== '')
            <div class="mb-4 rounded-xl border p-4 md:p-5" role="note" aria-label="ملاحظات العميل الخاصة بالدفع"
                 style="background:#fff3cd;border-color:#ffeeba;border-left:8px solid #f59e0b;color:#856404;" dir="rtl">
                <div style="display:flex; gap:12px; align-items:flex-start;">
                    <x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-6 w-6" style="color:#d97706;" />
                    <div style="flex:1;">
                        <div style="margin-bottom:4px; font-weight:700;">
                            ملاحظات العميل الخاصة بالدفع — {{ $clientName }}
                        </div>
                        <div style="line-height:1.7; font-size:0.95rem;">{!! nl2br(e($clientNote)) !!}</div>
                    </div>
                </div>
            </div>
        @endif

        <x-slot name="header"><h2 class="text-xl font-bold">معلومات الطلب</h2></x-slot>

        <dl class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
            <div><strong>اسم المشروع:</strong> {{ $record->project_name }}</div>
            <div><strong>العميل:</strong> {{ $record->client->client_name ?? '-' }}</div>
            <div><strong>المعرض:</strong> {{ $record->showroom->name ?? '-' }}</div>

            <div>
                <strong>الحالة الحالية:</strong>
                @php
                    $label = $statusEnum?->label() ?? (string) $record->status;
                    $bg    = $statusEnum?->color() ?? '#64748b';
                @endphp
                <span class="px-2 py-1 rounded-full text-white text-xs" style="background-color: {{ $bg }};">
                    {{ $label }}
                </span>
            </div>

            <div><strong>أنشئ بواسطة:</strong> {{ $record->creator->name ?? '-' }}</div>
            <div><strong>تاريخ الإنشاء:</strong> {{ optional($record->created_at)?->format('Y-m-d H:i') ?? '—' }}</div>

            <div class="col-span-2 md:col-span-3"><strong>الوصف:</strong> {{ $record->project_description ?? '-' }}</div>
        </dl>
    </x-filament::section>

    <!-- ============================ مهام الطلب / المشروع ============================ -->
    <x-filament::section class="mt-6">
        <x-slot name="header"><h2 class="text-xl font-bold">مهام الطلب</h2></x-slot>

        @php
            $pick = function ($obj, array $paths, $default = '—') {
                foreach ($paths as $p) {
                    $val = data_get($obj, $p);
                    if (!is_null($val) && $val !== '') return $val;
                }
                return $default;
            };

            $statusLabel = function (?string $s): string {
                return match ($s) {
                    'pending'            => 'قيد الانتظار',
                    'received'           => 'تم الاستلام',
                    'under_review'       => 'قيد المراجعة',
                    'approved'           => 'معتمد',
                    'rejected'           => 'مرفوض',
                    'materials_prep'     => 'تحضير الخامات',
                    'materials_done'     => 'تم توفير الخامات',
                    'waiting_production' => 'جاهز لبدء التصنيع',
                    'in_progress'        => 'قيد التنفيذ',
                    'on_hold'            => 'معلّق',
                    'completed'          => 'مكتمل',
                    'cancelled'          => 'ملغي',
                    default              => $s ?: '—',
                };
            };
            $statusColor = function (?string $s): string {
                return match ($s) {
                    'pending'            => 'zinc',
                    'under_review'       => 'amber',
                    'approved'           => 'green',
                    'rejected'           => 'red',
                    'materials_prep'     => 'purple',
                    'materials_done'     => 'emerald',
                    'waiting_production' => 'amber',
                    'in_progress'        => 'sky',
                    'on_hold'            => 'yellow',
                    'completed'          => 'green',
                    'cancelled'          => 'gray',
                    default              => 'gray',
                };
            };
            $taskResourceExists = class_exists(\App\Filament\Resources\TaskResource::class);
        @endphp

        @if (($tasks ?? collect())->isEmpty())
            <p class="text-sm text-gray-500">لا توجد مهام مرتبطة بهذا الطلب.</p>
        @else
            <div class="overflow-x-auto rounded-xl border bg-white/80 dark:bg-gray-900/70">
                <table class="w-full text-sm rtl:text-right">
                    <thead class="bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                    <tr>
                        <th class="px-3 py-2 font-semibold w-14">#</th>
                        <th class="px-3 py-2 font-semibold">المهمة</th>
                        <th class="px-3 py-2 font-semibold whitespace-nowrap">تاريخ البداية</th>
                        <th class="px-3 py-2 font-semibold">المسئول</th>
                        <th class="px-3 py-2 font-semibold">الحالة</th>
                        @if ($taskResourceExists)
                            <th class="px-3 py-2 font-semibold w-24">الإجراء</th>
                        @endif
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-800 dark:text-gray-200">
                    @foreach ($tasks as $i => $task)
                        @php
                            $title = $pick($task, ['task_title','title','name'], 'مهمة #' . ($task->id ?? '—'));
                            $startRaw = $pick($task, [
                                'actual_start_at','started_at','planned_start_at','planned_start','created_at',
                            ], null);
                            $startAt = $startRaw ? Carbon::parse($startRaw)->format('Y-m-d H:i') : '—';
                            $ownerName = $pick($task, [
                                'current_owner_name','owner.name','ownerUser.name','assignedTo.name','assignee.name',
                                'employee.employee_name','department.manager_user.name','department.head_user.name',
                            ], '—');
                            $st = (string) ($task->status ?? '');
                            $stLabel = $statusLabel($st);
                            $stColor = $statusColor($st);
                            $viewUrl = $taskResourceExists
                                ? \App\Filament\Resources\TaskResource::getUrl('view', ['record' => $task])
                                : null;
                        @endphp

                        <tr class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-900 dark:even:bg-gray-800">
                            <td class="px-3 py-2">{{ $task->id ?? $loop->iteration }}</td>
                            <td class="px-3 py-2 font-semibold">
                                @if ($viewUrl)
                                    <a href="{{ $viewUrl }}" class="text-primary-600 underline">{{ $title }}</a>
                                @else
                                    {{ $title }}
                                @endif
                                @if (!empty($task->department?->dept_name))
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                        قسم: {{ $task->department->dept_name }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap">{{ $startAt }}</td>
                            <td class="px-3 py-2">{{ $ownerName }}</td>
                            <td class="px-3 py-2">
                                <x-filament::badge :color="$stColor">{{ $stLabel }}</x-filament::badge>
                            </td>
                            @if ($viewUrl)
                                <td class="px-3 py-2"><a href="{{ $viewUrl }}" class="text-primary-600 underline">عرض</a></td>
                            @endif
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>

    <!-- ============================ ملخص زمني ============================ -->
    <x-filament::section class="mt-6">
        <x-slot name="header"><h2 class="text-xl font-bold">ملخص زمني</h2></x-slot>

        <h3 class="mt-2 mb-3 text-sm font-semibold text-gray-700 dark:text-gray-200">التسليم عبر الأدوار</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
            <x-filament::card>
                <div class="flex items-center justify-between">
                    <div class="text-gray-600 dark:text-gray-300">الإنشاء ← استلام مدير المعرض</div>
                    <div class="font-semibold">{{ $created_to_showroom }}</div>
                </div>
            </x-filament::card>
            <x-filament::card>
                <div class="flex items-center justify-between">
                    <div class="text-gray-600 dark:text-gray-300">المعرض ← المصنع (استلام)</div>
                    <div class="font-semibold">{{ $showroom_to_factory }}</div>
                </div>
            </x-filament::card>
            <x-filament::card>
                <div class="flex items-center justify-between">
                    <div class="text-gray-600 dark:text-gray-300">المصنع ← مدير القسم (استلام)</div>
                    <div class="font-semibold">{{ $factory_to_department }}</div>
                </div>
            </x-filament::card>
        </div>

        <h3 class="mt-6 mb-3 text-sm font-semibold text-gray-700 dark:text-gray-200">توريد الخامات</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
            <x-filament::card><div class="flex items-center justify-between"><div class="text-gray-600 dark:text-gray-300">توريد (متوقع)</div><div class="font-semibold">{{ $fmt($expected) }}</div></div></x-filament::card>
            <x-filament::card><div class="flex items-center justify-between"><div class="text-gray-600 dark:text-gray-300">توريد (فعلي)</div><div class="font-semibold">{{ $fmt($provided) }}</div></div></x-filament::card>
            <x-filament::card>
                <div class="flex items-center justify-between">
                    <div class="text-gray-600 dark:text-gray-300">الفرق (فعلي − متوقع)</div>
                    <x-filament::badge :color="$expectedVsActualColor">{{ $expectedVsActualText }}</x-filament::badge>
                </div>
            </x-filament::card>

            <x-filament::card><div class="flex items-center justify-between"><div class="text-gray-600 dark:text-gray-300">من الإنشاء حتى التوريد</div><div class="font-semibold">{{ $created_to_provided }}</div></div></x-filament::card>
            <x-filament::card><div class="flex items-center justify-between"><div class="text-gray-600 dark:text-gray-300">من الاعتماد حتى التوريد</div><div class="font-semibold">{{ $approved_to_provided }}</div></div></x-filament::card>
            <x-filament::card><div class="flex items-center justify-between"><div class="text-gray-600 dark:text-gray-300">القسم ← التوريد (فعلي)</div><div class="font-semibold">{{ $dept_to_supply }}</div></div></x-filament::card>
        </div>

        <h3 class="mt-6 mb-3 text-sm font-semibold text-gray-700 dark:text-gray-200">التصنيع</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
            <x-filament::card><div class="flex items-center justify-between"><div class="text-gray-600 dark:text-gray-300">بداية مخططـة</div><div class="font-semibold">{{ $fmt($plannedStart) }}</div></div></x-filament::card>
            <x-filament::card><div class="flex items-center justify-between"><div class="text-gray-600 dark:text-gray-300">نهاية مخططـة</div><div class="font-semibold">{{ $fmt($plannedEnd) }}</div></div></x-filament::card>
            <x-filament::card><div class="flex items-center justify-between"><div class="text-gray-600 dark:text-gray-300">القسم ← بدء التصنيع (فعلي)</div><div class="font-semibold">{{ $dept_to_mfg_start }}</div></div></x-filament::card>

            <x-filament::card><div class="flex items-center justify-between"><div class="text-gray-600 dark:text-gray-300">بداية فعليـة</div><div class="font-semibold">{{ $fmt($actualStartAt) }}</div></div></x-filament::card>
            <x-filament::card><div class="flex items-center justify-between"><div class="text-gray-600 dark:text-gray-300">نهاية فعليـة</div><div class="font-semibold">{{ $fmt($actualEndAt) }}</div></div></x-filament::card>
            <x-filament::card><div class="flex items-center justify-between"><div class="text-gray-600 dark:text-gray-300">مدة التصنيع (فعلي)</div><div class="font-semibold">{{ $mfg_duration }}</div></div></x-filament::card>

            <x-filament::card>
                <div class="flex items-center justify-between">
                    <div class="text-gray-600 dark:text-gray-300">انحراف بدء التصنيع</div>
                    <x-filament::badge :color="$startDriftColor">{{ $startDriftText }}</x-filament::badge>
                </div>
            </x-filament::card>
            <x-filament::card>
                <div class="flex items-center justify-between">
                    <div class="text-gray-600 dark:text-gray-300">انحراف نهاية التصنيع</div>
                    <x-filament::badge :color="$endDriftColor">{{ $endDriftText }}</x-filament::badge>
                </div>
            </x-filament::card>
            <x-filament::card><div class="flex items-center justify-between"><div class="text-gray-600 dark:text-gray-300">من نهاية التصنيع → اعتماد جودة التصنيع</div><div class="font-semibold">{{ $mfg_to_qa_approve }}</div></div></x-filament::card>
        </div>

        <h3 class="mt-6 mb-3 text-sm font-semibold text-gray-700 dark:text-gray-200">التركيب والختام</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
            <x-filament::card><div class="flex items-center justify-between"><div class="text-gray-600 dark:text-gray-300">بدء التركيب</div><div class="font-semibold">{{ $fmt($instStartedAt) }}</div></div></x-filament::card>
            <x-filament::card><div class="flex items-center justify-between"><div class="text-gray-600 dark:text-gray-300">اعتماد الجودة للتركيب</div><div class="font-semibold">{{ $fmt($qaInstApproveAt) }}</div></div></x-filament::card>
            <x-filament::card><div class="flex items-center justify-between"><div class="text-gray-600 dark:text-gray-300">التركيب → اعتماد الجودة</div><div class="font-semibold">{{ $install_to_qa_appr }}</div></div></x-filament::card>

            <x-filament::card class="md:col-span-2 lg:col-span-3">
                <div class="flex items-center justify-between">
                    <div class="text-gray-600 dark:text-gray-300">اعتماد تركيب → استلام العميل</div>
                    <div class="font-semibold">{{ $qa_appr_to_client }}</div>
                </div>
            </x-filament::card>

            <x-filament::card class="md:col-span-2 lg:col-span-3">
                <div class="flex items-center justify-between">
                    <div class="text-gray-600 dark:text-gray-300">إجمالي الوقت حتى استلام العميل</div>
                    <div class="font-semibold">{{ $total_to_client }}</div>
                </div>
            </x-filament::card>
        </div>

        <div class="mt-4 p-3 rounded border bg-white/60 dark:bg-gray-900/40 text-xs">
            <div class="text-gray-600 dark:text-gray-300 mb-1">تواريخ مرجعية</div>
            <div class="space-x-4 space-x-reverse">
                <span>استلام المعرض: <span class="font-semibold">{{ $fmt($recvShowroomAt) }}</span></span>
                <span class="mx-2 text-gray-400">•</span>
                <span>استلام المصنع: <span class="font-semibold">{{ $fmt($recvFactoryAt) }}</span></span>
                <span class="mx-2 text-gray-400">•</span>
                <span>استلام مدير القسم: <span class="font-semibold">{{ $fmt($recvDeptAt) }}</span></span>
                <span class="mx-2 text-gray-400">•</span>
                <span>بدء التصنيع (فعلي): <span class="font-semibold">{{ $fmt($actualStartAt) }}</span></span>
                <span class="mx-2 text-gray-400">•</span>
                <span>نهاية التصنيع (فعلي): <span class="font-semibold">{{ $fmt($actualEndAt) }}</span></span>
                <span class="mx-2 text-gray-400">•</span>
                <span>استلام العميل: <span class="font-semibold">{{ $fmt($clientReceiptAt) }}</span></span>
            </div>
        </div>
    </x-filament::section>

    <!-- ============================ ملفات التصنيع ============================ -->
    <x-filament::section class="mt-6">
        <x-slot name="header"><h2 class="text-xl font-bold">ملفات التصنيع</h2></x-slot>

        <ul class="mt-4 space-y-3">
            @if ($record->agreement_file)
                <li class="flex justify-between items-center bg-gray-50 dark:bg-gray-800 p-3 rounded border">
                    <span><strong>ملف الاتفاقية:</strong></span>
                    <a href="{{ Storage::disk('public')->url($record->agreement_file) }}" class="text-primary-600 underline" target="_blank">تحميل الملف</a>
                </li>
            @endif

            @if ($record->additional_work_file)
                <li class="flex justify-between items-center bg-gray-50 dark:bg-gray-800 p-3 rounded border">
                    <span><strong>ملف الأعمال الإضافية:</strong></span>
                    <a href="{{ Storage::disk('public')->url($record->additional_work_file) }}" class="text-primary-600 underline" target="_blank">تحميل الملف</a>
                </li>
            @endif

            @forelse ($record->files as $file)
                <li class="flex justify-between items-center bg-gray-50 dark:bg-gray-800 p-3 rounded border">
                    <span><strong>{{ $file->department->dept_name }}</strong></span>
                    <a href="{{ Storage::disk('public')->url($file->file_path) }}" class="text-primary-600 underline" target="_blank">تحميل الملف</a>
                </li>
            @empty
                <p class="text-sm text-gray-500">لا توجد ملفات مرتبطة.</p>
            @endforelse
        </ul>
    </x-filament::section>

    <!-- ============================ سجل الأحداث ============================ -->
    <x-filament::section class="mt-6">
        <x-slot name="header"><h2 class="text-xl font-bold">سجل الأحداث</h2></x-slot>

        @php
            // صانع لوج بسيط لطلبات الخامات
            $mkLog = function (string $action, $at, ?string $note, ?string $who, array $data = []) {
                $at = $at ? Carbon::parse($at) : null;
                return (object) [
                    'action'      => $action,
                    'type'        => $action,
                    'action_at'   => $at,
                    'happened_at' => null,
                    'created_at'  => $at,
                    'note'        => $note,
                    'data'        => $data,
                    'causer'      => (object) ['name' => $who],
                ];
            };

            $displayLogs = collect($record->logs ?? []);
            if ($record->project && method_exists($record->project, 'logs')) {
                $displayLogs = $displayLogs->merge($record->project->logs ?? collect());
            }
            foreach ($tasks as $task) {
                if (method_exists($task, 'logs')) {
                    $displayLogs = $displayLogs->merge($task->logs ?? collect());
                }
                foreach ($task->materialRequests ?? [] as $mr) {
                    $dept = $task->department->dept_name ?? '—';
                    if (!empty($mr->requested_at)) {
                        $displayLogs->push($mkLog('materials_requested', $mr->requested_at, "طلب خامات للمهمة #{$task->id} — قسم {$dept}", $mr->requestedBy->name ?? null, ['task_id' => $task->id, 'department' => $dept]));
                    }
                    if (!empty($mr->expected_delivery_at)) {
                        $displayLogs->push($mkLog('materials_expected', $mr->expected_delivery_at, "موعد توريد متوقّع للمهمة #{$task->id} — قسم {$dept}", $mr->requestedBy->name ?? null, ['task_id' => $task->id, 'department' => $dept]));
                    }
                    if (!empty($mr->approved_at)) {
                        $displayLogs->push($mkLog('materials_approved', $mr->approved_at, "اعتماد المشتريات لطلب الخامات للمهمة #{$task->id} — قسم {$dept}", null, ['task_id' => $task->id, 'department' => $dept]));
                    }
                    if (!empty($mr->provided_at)) {
                        $displayLogs->push($mkLog('materials_provided', $mr->provided_at, "توريد الخامات للمهمة #{$task->id} — قسم {$dept}", $mr->providedBy->name ?? null, ['task_id' => $task->id, 'department' => $dept]));
                    }
                }
            }

            $iconMap = [
                'created'                   => ['heroicon-o-document-plus', 'primary'],
                'transition'                => ['heroicon-o-arrow-right', 'info'],
                'received'                  => ['heroicon-o-hand-thumb-up', 'success'],
                'rejected'                  => ['heroicon-o-x-circle', 'danger'],
                'status_changed'            => ['heroicon-o-adjustments-vertical', 'warning'],
                'project_bootstrap'         => ['heroicon-o-briefcase', 'success'],
                'sent_to_factory'           => ['heroicon-o-paper-airplane', 'info'],

                // المواد
                'materials_requested'       => ['heroicon-o-clipboard-document-list', 'zinc'],
                'materials_expected'        => ['heroicon-o-calendar', 'amber'],
                'materials_approved'        => ['heroicon-o-check-badge', 'green'],
                'materials_provided'        => ['heroicon-o-truck', 'orange'],
                'materials_received_ok'     => ['heroicon-o-hand-thumb-up', 'violet'],

                // التصنيع
                'waiting_production'        => ['heroicon-o-clock', 'amber'],
                'manufacturing_started'     => ['heroicon-o-play-circle', 'sky'],
                'manufacturing_finished'    => ['heroicon-o-check-circle', 'emerald'],
                'manufacturing_sent_to_qa'  => ['heroicon-o-shield-check', 'blue'],

                // ما بعد التصنيع
                'qa_approved_installation'  => ['heroicon-o-wrench', 'teal'],
                'client_receipt_uploaded'   => ['heroicon-o-arrow-up-on-square', 'indigo'],
                'project_completed'         => ['heroicon-o-flag', 'green'],
                'production_request_closed' => ['heroicon-o-lock-closed', 'slate'],
            ];

            $labelMap = [
                'created'                     => 'تم الإنشاء',
                'received'                    => 'تأكيد استلام',
                'transition'                  => 'انتقال',
                'assigned_changed'            => 'تغيير الإسناد',
                'ownership_changed'           => 'تغيير الملكية (الدور)',
                'owner_changed'               => 'تغيير المالك (المستخدم)',
                'status_changed'              => 'تغيير الحالة العامة',
                'ownership_received'          => 'تأكيد استلام الملكية',
                'owner_received'              => 'تأكيد استلام المالك',
                'plan_set'                    => 'تحديد الخطة',
                'planning_set'                => 'تحديد المواعيد',
                'planning_hint_set'           => 'تحديد مواعيد (مبدئية)',

                'manufacturing_started'       => 'بدء التصنيع (فعلي)',
                'manufacturing_finished'      => 'نهاية التصنيع (فعلي)',
                'manufacturing_sent_to_qa'    => 'إرسال للجودة',
                'qa_ack_manufacturing'        => 'تأكيد استلام الجودة للتصنيع',
                'qa_approved_manufacturing'   => 'اعتماد الجودة للتصنيع',

                'sent_to_install'             => 'إرسال للتركيب',
                'install_acknowledged'        => 'تأكيد استلام التركيب',
                'installation_started'        => 'بدء التركيب',
                'installation_sent_to_qa'     => 'إرسال للجودة بعد التركيب',
                'qa_ack_installation'         => 'تأكيد استلام الجودة للتركيب',
                'qa_approved_installation'    => 'اعتماد التركيب',

                'materials_requested'         => 'تم طلب الخامات',
                'materials_expected'          => 'تحديد تاريخ التوريد المتوقع',
                'materials_approved'          => 'تم اعتماد طلب الخامات',
                'materials_provided'          => 'تم توريد الخامات',
                'materials_received_ok'       => 'تم استلام الخامات',

                'client_receipt_uploaded'     => 'رفع سند استلام العميل',
                'task_completed'              => 'اكتمال المهمة',
                'project_completed'           => 'اكتمال المشروع',
                'production_request_closed'   => 'إقفال طلب التصنيع',
                'request_finalized'           => 'إقفال طلب التصنيع',
            ];

            $displayLogs = $displayLogs->filter()->sortByDesc(function ($l) {
                $t = $l->action_at ?? $l->happened_at ?? $l->created_at ?? null;
                return $t ? Carbon::parse($t)->timestamp : 0;
            })->values();
        @endphp

        @forelse ($displayLogs as $log)
            @php
                $rawAt  = $log->action_at ?? $log->happened_at ?? $log->created_at;
                $at     = $rawAt instanceof Carbon ? $rawAt : ($rawAt ? Carbon::parse($rawAt) : null);
                $who    = $log->causer->name
                        ?? data_get($log->data, 'causer_name')
                        ?? data_get($log->data, 'by')
                        ?? (data_get($log->data, 'owner_role_label') ?: data_get($log->data, 'owner_role'))
                        ?? 'مجهول';

                $actionKey   = $log->action ?? $log->type ?? 'event';
                [$icon, $color] = $iconMap[$actionKey] ?? ['heroicon-o-information-circle', 'gray'];
                $actionLabel = $labelMap[$actionKey] ?? ($actionKey);

                $srcTxt = 'الطلب';
                if (!empty(data_get($log->data, 'task_id'))) {
                    $srcTxt = 'مهمة #' . data_get($log->data, 'task_id');
                    if ($dept = data_get($log->data, 'department')) $srcTxt .= ' — ' . $dept;
                }
            @endphp

            <div class="border rounded-md p-4 mb-4 bg-white dark:bg-gray-900 shadow-sm">
                <div class="flex items-start justify-between gap-4 text-sm">
                    <div class="flex items-center gap-2">
                        <x-filament::icon :icon="$icon" class="h-5 w-5 text-gray-500 dark:text-gray-400" />
                        <div>
                            <div class="font-semibold">
                                {{ $actionLabel }}
                                <span class="text-gray-500 dark:text-gray-400">— {{ $srcTxt }}</span>
                            </div>
                            <div class="text-gray-600 dark:text-gray-400">
                                <span class="font-medium">{{ $who }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="text-xs text-gray-600 dark:text-gray-400 text-right">
                        <div>{{ $at?->format('Y-m-d H:i') ?? '—' }}</div>
                        <div>{{ $at?->diffForHumans() ?? '—' }}</div>
                    </div>
                </div>

                @if (!empty($log->note))
                    <div class="mt-3 text-sm">
                        <div>{{ $log->note }}</div>
                    </div>
                @endif

                <div class="mt-3">
                    <x-filament::badge :color="$color">{{ $actionLabel }}</x-filament::badge>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-500">لا يوجد سجل للأحداث حالياً.</p>
        @endforelse
    </x-filament::section>
</x-filament::page>
