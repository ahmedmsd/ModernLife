@php
    use App\Enums\ProductionRequestStatus;
    use Illuminate\Support\Carbon;

    $statusEnum = ProductionRequestStatus::tryFrom($record->status);

    /* ================== تجميع البيانات من المهام وطلبات الخامات ================== */

    // نجلب مهام المشروع مع طلبات الخامات واللوجات (لو متاحة)
    $tasks = $record->project
        ? $record->project->tasks()
            ->with([
                'materialRequests:id,task_id,expected_delivery_at,approved_at,provided_at',
                'logs', // إن لم تكن العلاقة موجودة، احذفها أو أضفها لاحقًا في الموديل
            ])->get()
        : collect();

    // كل طلبات الخامات التابعة للمشروع عبر المهام
    $mrs = $tasks->flatMap(fn($t) => $t->materialRequests ?? collect());

    // نختار أحدث قيم لكل تاريخ مهم
    $expectedAt = optional(
        $mrs->filter(fn($m) => !is_null($m->expected_delivery_at))
            ->sortByDesc('expected_delivery_at')
            ->first()
    )->expected_delivery_at;

    $approvedAt = optional(
        $mrs->filter(fn($m) => !is_null($m->approved_at))
            ->sortByDesc('approved_at')
            ->first()
    )->approved_at;

    $providedAt = optional(
        $mrs->filter(fn($m) => !is_null($m->provided_at))
            ->sortByDesc('provided_at')
            ->first()
    )->provided_at;

    // نحولها لـ Carbon (المتغيرات التي يستخدمها الملخص لاحقًا)
    $expected = $expectedAt ? Carbon::parse($expectedAt) : null;
    $approved = $approvedAt ? Carbon::parse($approvedAt) : null;
    $provided = $providedAt ? Carbon::parse($providedAt) : null;

    // تواريخ الإنشاء والملخص العام من الطلب نفسه
    $created  = $record->created_at ? Carbon::parse($record->created_at) : null;

    /* ================== بدء/نهاية التصنيع الفعلية من اللوجات/أعمدة المهام ================== */

    // نجمع كل لوجات المهام
    $taskLogs = $tasks->flatMap(fn($t) => $t->logs ?? collect());

    // نبحث عن أحداث بدء/نهاية التصنيع (سواء مخزّنة في "type" أو "action")
    $startLog = $taskLogs->first(fn($l) => ($l->type ?? null) === 'manufacturing_started' || ($l->action ?? null) === 'manufacturing_started');
    $endLog   = $taskLogs->first(fn($l) => ($l->type ?? null) === 'manufacturing_finished' || ($l->action ?? null) === 'manufacturing_finished');
    $clientR  = $taskLogs->first(fn($l) => ($l->type ?? null) === 'client_receipt_uploaded' || ($l->action ?? null) === 'client_receipt_uploaded');

    // نقرأ التاريخ من أكثر من حقل محتمل
    $actualStartAt = $startLog?->action_at ?? $startLog?->happened_at ?? $startLog?->created_at;
    $actualEndAt   = $endLog?->action_at   ?? $endLog?->happened_at   ?? $endLog?->created_at;
    $clientAtRaw   = $clientR?->action_at  ?? $clientR?->happened_at  ?? $clientR?->created_at;

    // إن لم نجدها في اللوجات، نحاول من أعمدة المهام (إن وجدت)
    if (!$actualStartAt) {
        $actualStartAt = $tasks->min(fn($t) => $t->actual_start_at ?? $t->started_at ?? null);
    }
    if (!$actualEndAt) {
        $actualEndAt = $tasks->max(fn($t) => $t->actual_end_at ?? $t->finished_at ?? null);
    }

    // تحويل لـ Carbon لاستخدامها في الملخص
    $actualStartAt = $actualStartAt ? Carbon::parse($actualStartAt) : null;
    $actualEndAt   = $actualEndAt   ? Carbon::parse($actualEndAt)   : null;
    $clientAt      = $clientAtRaw   ? Carbon::parse($clientAtRaw)   : null;

    /* ================== دوال عرض مساعدة ================== */

    // تنسيق بسيط للتاريخ
    $fmt = function (?Carbon $c) { return $c?->format('Y-m-d H:i') ?? '—'; };

    // تحويل دقائق إلى (أيام/ساعات/دقائق)
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

    // فرق زمني إنساني بين تاريخين
    $humanDiff = function (?Carbon $a, ?Carbon $b) use ($minutesToHuman) {
        if (!$a || !$b) return '—';
        return $minutesToHuman($a->diffInMinutes($b));
    };

    // حساب فرق المتوقع/الفعلي للتوريد (نصي + لون شارة)
    $expectedVsActualText  = '—';
    $expectedVsActualColor = 'gray';
    if ($expected && $provided) {
        $mins = $expected->diffInMinutes($provided, false); // سالب = أبكر من الموعد
        if ($mins === 0) {
            $expectedVsActualText  = 'في الموعد تمامًا';
            $expectedVsActualColor = 'success';
        } elseif ($mins < 0) {
            $expectedVsActualText  = 'أبكر بـ ' . $minutesToHuman(abs($mins));
            $expectedVsActualColor = 'info';
        } else {
            $expectedVsActualText  = 'متأخر بـ ' . $minutesToHuman($mins);
            $expectedVsActualColor = 'danger';
        }
    }
@endphp

<x-filament::page>
    {{-- ============================ معلومات الطلب ============================ --}}
    <x-filament::section>
        <x-slot name="header">
            <h2 class="text-xl font-bold">معلومات الطلب</h2>
        </x-slot>

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
                {{-- نستخدم style مباشر هنا لأن اللون يأتي من enum كقيمة HEX --}}
                <span class="px-2 py-1 rounded-full text-white text-xs" style="background-color: {{ $bg }};">
                    {{ $label }}
                </span>
            </div>

            <div><strong>أنشئ بواسطة:</strong> {{ $record->creator->name ?? '-' }}</div>
            <div><strong>تاريخ الإنشاء:</strong> {{ optional($record->created_at)?->format('Y-m-d H:i') ?? '—' }}</div>

            <div class="col-span-2 md:col-span-3"><strong>الوصف:</strong> {{ $record->project_description ?? '-' }}</div>
        </dl>
    </x-filament::section>

    {{-- ============================ مهام الطلب / المشروع ============================ --}}
    <x-filament::section class="mt-6">
        <x-slot name="header">
            <h2 class="text-xl font-bold">مهام الطلب</h2>
        </x-slot>

        @php
            /** ملاحظات:
             * - نفترض أن الطلب مرتبط بمشروع واحد عبر $record->project، والمشروع يملك tasks().
             * - نحاول قراءة عنوان المهمة من أكثر من حقل محتمل (task_title / title / name)،
             *   واسم المسئول من owner/assignedTo/employee/department->manager... (سلسلة سقوط).
             * - تاريخ البداية: فعلي ثم مخطط ثم started_at ثم إنشاء المهمة (fallback).
             * - حالة المهمة: نعرض label + badge لون (جدول تحويل داخلي).
             */

            // تحميل آمن للمهام وعلاقات خفيفة (تقدر توسّعها لاحقًا لو احتجت)
            $record->loadMissing([
                'project.tasks.department',
                'project.tasks.employee',
                // أضِف هنا أي علاقات مساعدة لو موجودة عندك:
                // 'project.tasks.owner', 'project.tasks.assignedTo', 'project.tasks.ownerUser', ...
            ]);

            $tasks = $record->project
                ? $record->project->tasks()->orderBy('id')->get()
                : collect();

            // دالة آمنة للحصول على نص من عدة مفاتيح محتمَلة
            $pick = function ($obj, array $paths, $default = '—') {
                foreach ($paths as $p) {
                    $val = data_get($obj, $p);
                    if (!is_null($val) && $val !== '') return $val;
                }
                return $default;
            };

            // خرائط الحالة ← تسمية/لون (حدّثها حسب قاموس حالاتك)
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

            // هل لديك Resource لعرض المهمة؟ إن وجد، نضيف رابط "عرض"
            $taskResourceExists = class_exists(\App\Filament\Resources\ProductionTaskResource::class);
        @endphp

        @if ($tasks->isEmpty())
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
                            // عنوان المهمة: جرّب عدة حقول شائعة
                            $title = $pick($task, ['task_title','title','name'], 'مهمة #' . ($task->id ?? '—'));

                            // تاريخ البداية: actual → planned → started_at → created_at
                            $startRaw = $pick($task, [
                                'actual_start_at',
                                'started_at',
                                'planned_start_at',
                                'planned_start',
                                'created_at',
                            ], null);
                            $startAt = $startRaw ? \Illuminate\Support\Carbon::parse($startRaw)->format('Y-m-d H:i') : '—';

                            // اسم المسئول: سلسلة سقوط عبر علاقات محتملة
                            $ownerName = $pick($task, [
                                'current_owner_name',
                                'owner.name',
                                'ownerUser.name',
                                'assignedTo.name',
                                'assignee.name',
                                'employee.employee_name',
                                'department.manager_user.name',
                                'department.head_user.name',
                            ], '—');

                            // حالة المهمة (label/color)
                            $st = (string) ($task->status ?? '');
                            $stLabel = $statusLabel($st);
                            $stColor = $statusColor($st);

                            // رابط العرض (لو Resource موجود)
                            $viewUrl = $taskResourceExists
                                ? \App\Filament\Resources\ProductionTaskResource::getUrl('view', ['record' => $task])
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
                                {{-- توضيح القسم إن وجد --}}
                                @if (!empty($task->department?->dept_name))
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                        قسم: {{ $task->department->dept_name }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap">{{ $startAt }}</td>
                            <td class="px-3 py-2">{{ $ownerName }}</td>
                            <td class="px-3 py-2">
                                <x-filament::badge :color="$stColor">
                                    {{ $stLabel }}
                                </x-filament::badge>
                            </td>
                            @if ($viewUrl)
                                <td class="px-3 py-2">
                                    <a href="{{ $viewUrl }}" class="text-primary-600 underline">عرض</a>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>


    {{-- ============================ ملخص زمني (مرتب حسب تسلسل العمليات) ============================ --}}{{-- ============================ ملخص زمني (تفصيلي ومُرتب حسب تسلسل العمليات) ============================ --}}
    <x-filament::section class="mt-6">
        <x-slot name="header">
            <h2 class="text-xl font-bold">ملخص زمني</h2>
        </x-slot>

        @php
            // نحمل الحد الأدنى من العلاقات (بدون project.logs لتفادي العلاقة غير المعرّفة)
            $record->loadMissing([
                'logs',
                'project.tasks.department',
                'project.tasks.materialRequests.requestedBy',
                'project.tasks.materialRequests.providedBy',
            ]);

            /* -------------------- أدوات عرض مساعدة (للتوضيح لمن يأتي بعدك) -------------------- */

            // تأمين تحويل أي قيمة إلى مصفوفة
            $toArr = function ($val) {
                if (is_array($val)) return $val;
                if (is_object($val)) return (array) $val;
                if (is_string($val)) {
                    try { return json_decode($val, true, 512, JSON_THROW_ON_ERROR) ?? []; }
                    catch (\Throwable) { return []; }
                }
                return [];
            };

            // تنسيق تاريخ مختصر
            $fmt = fn (? \Illuminate\Support\Carbon $c) => $c?->format('Y-m-d H:i') ?? '—';

            // تحويل دقائق إلى (أيام/ساعات/دقائق) بالعربية
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

            // فرق زمني إنساني بين تاريخين (أو "—" لو ناقص)
            $humanDiff = function (? \Illuminate\Support\Carbon $a, ? \Illuminate\Support\Carbon $b) use ($minutesToHuman) {
                if (!$a || !$b) return '—';
                return $minutesToHuman($a->diffInMinutes($b));
            };

            // التقط أول حدث (حسب المفاتيح) من لوجات معيّنة
            $firstEventAt = function (array $keys, $logs) {
                $ev = collect($logs ?? [])->first(function ($l) use ($keys) {
                    $k = $l->action ?? $l->type ?? null;
                    return $k && in_array($k, $keys, true);
                });
                $at = $ev?->action_at ?? $ev?->happened_at ?? $ev?->created_at;
                return $at ? \Illuminate\Support\Carbon::parse($at) : null;
            };

            /* -------------------- مصادر البيانات -------------------- */

            $created  = $record->created_at ? \Illuminate\Support\Carbon::parse($record->created_at) : null;

            // مهام المشروع + طلبات الخامات
            $tasks = $record->project
                ? $record->project->tasks()->with([
                    'materialRequests:id,task_id,expected_delivery_at,approved_at,provided_at,requested_at,requested_by,provided_by',
                ])->get()
                : collect();

            $mrs = $tasks->flatMap(fn($t) => $t->materialRequests ?? collect());

            // تجميع لوجات المهام (إن وُجدت العلاقة) + لوجات الطلب
            $taskLogs = collect();
            foreach ($tasks as $t) {
                if (method_exists($t, 'logs')) {
                    $taskLogs = $taskLogs->merge($t->logs ?? collect());
                }
            }
            $reqLogs = $record->logs ?? collect();
            $allLogs = $reqLogs->concat($taskLogs);

            /* --- أوقات الإرسال بين الأدوار --- */
            $firstOwnerSendAt = function (string $role) use ($reqLogs, $toArr) {
                // أحداث إرسال صريحة إن وُجدت
                $explicit = [
                    'showroom_manager'   => ['sent_to_showroom'],
                    'factory_manager'    => ['sent_to_factory'],
                    'department_manager' => ['sent_to_department', 'waiting_production'], // جاهز لدى القسم
                ][$role] ?? [];

                $event = $reqLogs->first(function ($l) use ($role, $explicit, $toArr) {
                    $type = $l->action ?? $l->type ?? null;
                    if (in_array($type, $explicit, true)) return true;

                    // تغيّر ملكية/مالك → إلى الدور
                    if (in_array($type, ['ownership_changed','owner_changed','transition','status_changed'], true)) {
                        $data   = $toArr($l->data);
                        $toRole = data_get($data, 'to.owner_role')
                              ?? data_get($data, 'owner_role')
                              ?? data_get($data, 'to.owner');
                        return $toRole === $role;
                    }
                    return false;
                });

                // fallback: أول استلام من الدور
                if (!$event) {
                    $event = $reqLogs->first(function ($l) use ($role, $toArr) {
                        $type = $l->action ?? $l->type ?? null;
                        if (!in_array($type, ['ownership_received','owner_received','received'], true)) return false;
                        $data = $toArr($l->data);
                        $rRole = data_get($data, 'owner_role')
                              ?? data_get($data, 'to.owner_role')
                              ?? data_get($data, 'owner');
                        return $rRole === $role;
                    });
                }

                $at = $event?->action_at ?? $event?->happened_at ?? $event?->created_at;
                return $at ? \Illuminate\Support\Carbon::parse($at) : null;
            };

            $sentShowroomAt = $firstOwnerSendAt('showroom_manager');
            $sentFactoryAt  = $firstOwnerSendAt('factory_manager');
            $sentDeptAt     = $firstOwnerSendAt('department_manager');

            /* --- التوريد (متوقع/فعلي/اعتماد) من طلبات الخامات عبر المهام --- */
            $expectedAt = optional(
                $mrs->whereNotNull('expected_delivery_at')->sortBy('expected_delivery_at')->first()
            )->expected_delivery_at;

            $approvedAt = optional(
                $mrs->whereNotNull('approved_at')->sortBy('approved_at')->first()
            )->approved_at;

            $providedAt = optional(
                $mrs->whereNotNull('provided_at')->sortBy('provided_at')->first()
            )->provided_at;

            $expected = $expectedAt ? \Illuminate\Support\Carbon::parse($expectedAt) : null;
            $approved = $approvedAt ? \Illuminate\Support\Carbon::parse($approvedAt) : null;
            $provided = $providedAt ? \Illuminate\Support\Carbon::parse($providedAt) : null;

            /* --- خط التصنيع: فعلي ومخطط (تجميع من اللوجات أو أعمدة المهام) --- */
            // فعلي
            $actualStartAt = $firstEventAt(['manufacturing_started'], $allLogs)
                          ?? ($tasks->min(fn($t) => $t->actual_start_at ?? $t->started_at ?? null) ? \Illuminate\Support\Carbon::parse($tasks->min(fn($t) => $t->actual_start_at ?? $t->started_at ?? null)) : null);
            $actualEndAt   = $firstEventAt(['manufacturing_finished'], $allLogs)
                          ?? ($tasks->max(fn($t) => $t->actual_end_at ?? $t->finished_at ?? null) ? \Illuminate\Support\Carbon::parse($tasks->max(fn($t) => $t->actual_end_at ?? $t->finished_at ?? null)) : null);

            // مخطّط (من مهام الأقسام مجتمعة: أبكر بداية مخططة، وآخر نهاية مخططة)
            $plannedStartAgg = $tasks->min(fn($t) => $t->planned_start_at ?? $t->planned_start ?? null);
            $plannedEndAgg   = $tasks->max(fn($t) => $t->planned_end_at   ?? $t->planned_end   ?? null);

            $plannedStart = $plannedStartAgg ? \Illuminate\Support\Carbon::parse($plannedStartAgg) : null;
            $plannedEnd   = $plannedEndAgg   ? \Illuminate\Support\Carbon::parse($plannedEndAgg)   : null;

            // انحرافات المخطط/الفعلي للتصنيع
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

            /* --- ما بعد التصنيع / الختام --- */
            $qaMfgApproveAt   = $firstEventAt(['qa_approved_manufacturing'], $allLogs);
            $sentToInstallAt  = $firstEventAt(['sent_to_install'], $allLogs);
            $instStartedAt    = $firstEventAt(['installation_started'], $allLogs);
            $qaInstApproveAt  = $firstEventAt(['qa_approved_installation'], $allLogs);

            // استلام العميل / إغلاق
            $clientReceiptAt  = $firstEventAt(['client_receipt_uploaded'], $allLogs);
            $taskCompletedAt  = $firstEventAt(['task_completed'], $allLogs);
            $projectCompletedAt = $firstEventAt(['project_completed'], $reqLogs);
            $requestClosedAt    = $firstEventAt(['production_request_closed'], $reqLogs)
                               ?? (($record->status ?? null) === 'closed' ? ($record->updated_at ? \Illuminate\Support\Carbon::parse($record->updated_at) : null) : null);

            // إجمالي الوقت حتى استلام العميل
            $total_to_client = $humanDiff($created, $clientReceiptAt);

            /* --- فروق التوريد: فعلي مقابل متوقع --- */
            $expectedVsActualText  = '—'; $expectedVsActualColor = 'gray';
            if ($expected && $provided) {
                $mins = $expected->diffInMinutes($provided, false); // سالب = أبكر
                $expectedVsActualText  = $mins === 0 ? 'في الموعد تمامًا'
                    : ($mins < 0 ? 'أبكر بـ ' : 'متأخر بـ ') . $minutesToHuman(abs($mins));
                $expectedVsActualColor = $mins <= 0 ? ($mins === 0 ? 'success' : 'info') : 'danger';
            }

            /* --- فترات تفصيلية بين كل طرف والآخر --- */
            $created_to_showroom   = $humanDiff($created,          $sentShowroomAt);
            $showroom_to_factory   = $humanDiff($sentShowroomAt,   $sentFactoryAt);
            $factory_to_department = $humanDiff($sentFactoryAt,    $sentDeptAt);
            $dept_to_supply        = $humanDiff($sentDeptAt,       $provided);
            $dept_to_mfg_start     = $humanDiff($sentDeptAt,       $actualStartAt);
            $mfg_duration          = $humanDiff($actualStartAt,    $actualEndAt);
            $mfg_to_qa_approve     = $humanDiff($actualEndAt,      $qaMfgApproveAt);
            $qa_to_install_start   = $humanDiff($qaMfgApproveAt,   $instStartedAt);
            $install_to_qa_appr    = $humanDiff($instStartedAt,    $qaInstApproveAt);
            $qa_appr_to_client     = $humanDiff($qaInstApproveAt,  $clientReceiptAt);

            // فترات عامة إضافية
            $created_to_provided   = $humanDiff($created,  $provided);
            $approved_to_provided  = $humanDiff($approved, $provided);
        @endphp

        {{-- ➊ التسليم عبر الأدوار (حسب تسلسل العمليات) --}}
        <h3 class="mt-2 mb-3 text-sm font-semibold text-gray-700 dark:text-gray-200">التسليم عبر الأدوار</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
            <x-filament::card>
                <div class="flex items-center justify-between">
                    <div class="text-gray-600 dark:text-gray-300">الإنشاء ← إرسال إلى مدير المعرض</div>
                    <div class="font-semibold">{{ $created_to_showroom }}</div>
                </div>
            </x-filament::card>
            <x-filament::card>
                <div class="flex items-center justify-between">
                    <div class="text-gray-600 dark:text-gray-300">المعرض ← المصنع</div>
                    <div class="font-semibold">{{ $showroom_to_factory }}</div>
                </div>
            </x-filament::card>
            <x-filament::card>
                <div class="flex items-center justify-between">
                    <div class="text-gray-600 dark:text-gray-300">المصنع ← مدير القسم</div>
                    <div class="font-semibold">{{ $factory_to_department }}</div>
                </div>
            </x-filament::card>
        </div>

        {{-- ➋ توريد الخامات (متوقع/فعلي/فروق) --}}
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

        {{-- ➌ التصنيع (مخطط/فعلي/انحرافات) --}}
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

        {{-- ➍ التركيب والختام --}}
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

        {{-- (اختياري) عرض تواريخ مرجعية للشفافية --}}
        <div class="mt-4 p-3 rounded border bg-white/60 dark:bg-gray-900/40 text-xs">
            <div class="text-gray-600 dark:text-gray-300 mb-1">تواريخ مرجعية</div>
            <div class="space-x-4 space-x-reverse">
                <span>إلى المعرض: <span class="font-semibold">{{ $fmt($sentShowroomAt) }}</span></span>
                <span class="mx-2 text-gray-400">•</span>
                <span>إلى المصنع: <span class="font-semibold">{{ $fmt($sentFactoryAt) }}</span></span>
                <span class="mx-2 text-gray-400">•</span>
                <span>إلى مدير القسم: <span class="font-semibold">{{ $fmt($sentDeptAt) }}</span></span>
                <span class="mx-2 text-gray-400">•</span>
                <span>بدء التصنيع (فعلي): <span class="font-semibold">{{ $fmt($actualStartAt) }}</span></span>
                <span class="mx-2 text-gray-400">•</span>
                <span>نهاية التصنيع (فعلي): <span class="font-semibold">{{ $fmt($actualEndAt) }}</span></span>
                <span class="mx-2 text-gray-400">•</span>
                <span>استلام العميل: <span class="font-semibold">{{ $fmt($clientReceiptAt) }}</span></span>
            </div>
        </div>
    </x-filament::section>




    {{-- ============================ ملفات التصنيع ============================ --}}
    <x-filament::section class="mt-6">
        <x-slot name="header">
            <h2 class="text-xl font-bold">ملفات التصنيع</h2>
        </x-slot>

        <ul class="mt-4 space-y-3">
            @if ($record->agreement_file)
                <li class="flex justify-between items-center bg-gray-50 dark:bg-gray-800 p-3 rounded border">
                    <span><strong>ملف الاتفاقية:</strong></span>
                    <a href="{{ Storage::disk('public')->url($record->agreement_file) }}"
                       class="text-primary-600 underline" target="_blank">
                        تحميل الملف
                    </a>
                </li>
            @endif

            @forelse ($record->files as $file)
                <li class="flex justify-between items-center bg-gray-50 dark:bg-gray-800 p-3 rounded border">
                    <span><strong>{{ $file->department->dept_name }}</strong></span>
                    <a href="{{ Storage::disk('public')->url($file->file_path) }}"
                       class="text-primary-600 underline" target="_blank">
                        تحميل الملف
                    </a>
                </li>
            @empty
                <p class="text-sm text-gray-500">لا توجد ملفات مرتبطة.</p>
            @endforelse
        </ul>
    </x-filament::section>

    {{-- ============================ سجل الأحداث (شامل الطلب/المشروع/المهام + طلبات الخامات) ============================ --}}
    <x-filament::section class="mt-6">
        <x-slot name="header">
            <h2 class="text-xl font-bold">سجل الأحداث</h2>
        </x-slot>

        @php
            $record->loadMissing([
                'logs.causer',
                'project.tasks.logs.causer',
                'project.tasks.logs.causer',
                'project.tasks.department',
                'project.tasks.materialRequests.requestedBy',
                'project.tasks.materialRequests.providedBy',
            ]);

            // دالة مساعدة لصناعة عنصر لوج "اصطناعي" بواجهة مشابهة للّوج الأصلي
            $mkLog = function (string $action, $at, ?string $note, ?string $who, array $data = []) {
                $at = $at ? (\Illuminate\Support\Carbon::parse($at)) : null;
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

            // 1) اجمع لوجات الطلب + المشروع + المهام
            $allLogs = collect();

            // لوجات الطلب
            $allLogs = $allLogs->merge($record->logs ?? collect());

            // لوجات المشروع (لو موجودة)
            if ($record->project && method_exists($record->project, 'logs')) {
                $allLogs = $allLogs->merge($record->project->logs ?? collect());
            }

            // لوجات كل المهام
            $tasks = $record->project?->tasks ?? collect();
            foreach ($tasks as $task) {
                if (method_exists($task, 'logs')) {
                    $allLogs = $allLogs->merge($task->logs ?? collect());
                }
            }

            // 2) أحداث اصطناعية من طلبات الخامات لكل مهمة (لجعل التتبع واضحًا)
            foreach ($tasks as $task) {
                foreach ($task->materialRequests ?? [] as $mr) {
                    $dept = $task->department->dept_name ?? '—';
                    // طلب الخامات (مُنشأ/متوقع/معتمد/مورَّد)
                    if (!empty($mr->requested_at)) {
                        $allLogs->push($mkLog(
                            'materials_requested',
                            $mr->requested_at,
                            "طلب خامات للمهمة #{$task->id} — قسم {$dept}",
                            $mr->requestedBy->name ?? null,
                            ['task_id' => $task->id, 'department' => $dept]
                        ));
                    }
                    if (!empty($mr->expected_delivery_at)) {
                        $allLogs->push($mkLog(
                            'materials_expected',
                            $mr->expected_delivery_at,
                            "موعد توريد متوقّع للمهمة #{$task->id} — قسم {$dept}",
                            $mr->requestedBy->name ?? null,
                            ['task_id' => $task->id, 'department' => $dept]
                        ));
                    }
                    if (!empty($mr->approved_at)) {
                        $allLogs->push($mkLog(
                            'materials_approved',
                            $mr->approved_at,
                            "اعتماد المشتريات لطلب الخامات للمهمة #{$task->id} — قسم {$dept}",
                            null,
                            ['task_id' => $task->id, 'department' => $dept]
                        ));
                    }
                    if (!empty($mr->provided_at)) {
                        $allLogs->push($mkLog(
                            'materials_provided',
                            $mr->provided_at,
                            "توريد الخامات للمهمة #{$task->id} — قسم {$dept}",
                            $mr->providedBy->name ?? null,
                            ['task_id' => $task->id, 'department' => $dept]
                        ));
                    }
                }
            }

            // 3) أيقونات/ألوان وتعريب مفاتيح الأحداث
            $iconMap = [
                'created'                     => ['heroicon-o-document-plus', 'primary'],
                'transition'                  => ['heroicon-o-arrow-right', 'info'],
                'received'                    => ['heroicon-o-hand-thumb-up', 'success'],
                'rejected'                    => ['heroicon-o-x-circle', 'danger'],
                'status_changed'              => ['heroicon-o-adjustments-vertical', 'warning'],
                'project_bootstrap'           => ['heroicon-o-briefcase', 'success'],
                'sent_to_factory'             => ['heroicon-o-paper-airplane', 'info'],

                // المواد
                'materials_requested'         => ['heroicon-o-clipboard-document-list', 'zinc'],
                'materials_expected'          => ['heroicon-o-calendar', 'amber'],
                'materials_approved'          => ['heroicon-o-check-badge', 'green'],
                'materials_provided'          => ['heroicon-o-truck', 'orange'],
                'materials_received_ok'       => ['heroicon-o-hand-thumb-up', 'violet'],

                // التصنيع
                'waiting_production'          => ['heroicon-o-clock', 'amber'],
                'manufacturing_started'       => ['heroicon-o-play-circle', 'sky'],
                'manufacturing_finished'      => ['heroicon-o-check-circle', 'emerald'],
                'manufacturing_sent_to_qa'    => ['heroicon-o-shield-check', 'blue'],

                // ما بعد التصنيع
                'qa_approved_installation'    => ['heroicon-o-wrench', 'teal'],
                'client_receipt_uploaded'     => ['heroicon-o-arrow-up-on-square', 'indigo'],
                'project_completed'           => ['heroicon-o-flag', 'green'],
                'production_request_closed'   => ['heroicon-o-lock-closed', 'slate'],
            ];

            $labelMap = [
                'created'                     => 'تم الإنشاء',
                'received'                    => 'تأكيد استلام',
                'transition'                  => 'انتقال ',
                'created'                     => 'تم الإنشاء',
                'created'                     => 'تم الإنشاء',

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

            // 4) الترتيب تنازليًا حسب التاريخ (مع fallback)
            $allLogs = $allLogs->filter()->sortByDesc(function ($l) {
                $t = $l->action_at ?? $l->happened_at ?? $l->created_at ?? null;
                return $t ? \Illuminate\Support\Carbon::parse($t)->timestamp : 0;
            })->values();
        @endphp

        @forelse ($allLogs as $log)
            @php
                $logEnum  = \App\Enums\ProductionRequestStatus::tryFrom($log->action ?? $log->type ?? '');
                $rawAt    = $log->action_at ?? $log->happened_at ?? $log->created_at;
                $at       = $rawAt instanceof \Illuminate\Support\Carbon ? $rawAt : ($rawAt ? \Illuminate\Support\Carbon::parse($rawAt) : null);

                // استخراج اسم المنفّذ
                $who = $log->causer->name
                    ?? data_get($log->data, 'causer_name')
                    ?? data_get($log->data, 'by')
                    ?? (data_get($log->data, 'owner_role_label') ?: data_get($log->data, 'owner_role'))
                    ?? 'مجهول';

                $actionKey   = $log->action ?? $log->type ?? 'event';
                [$icon, $color] = $iconMap[$actionKey] ?? ['heroicon-o-information-circle', 'gray'];
                $actionLabel = $labelMap[$actionKey] ?? ($logEnum?->label() ?? $actionKey);

                // وصف مختصر للمصدر (طلب/مشروع/مهمة)
                $srcTxt = 'الطلب';
                if (!empty(data_get($log->data, 'task_id'))) {
                    $srcTxt = 'مهمة #' . data_get($log->data, 'task_id');
                    if ($dept = data_get($log->data, 'department')) {
                        $srcTxt .= ' — ' . $dept;
                    }
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
                        @if (($log->action ?? '') === \App\Enums\ProductionRequestStatus::REJECTED->value)
                            <div class="font-semibold text-red-700">سبب الرفض:</div>
                        @endif
                        <div>{{ $log->note }}</div>
                    </div>
                @endif

                <div class="mt-3">
                    <x-filament::badge :color="$color">
                        {{ $actionLabel }}
                    </x-filament::badge>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-500">لا يوجد سجل للأحداث حالياً.</p>
        @endforelse
    </x-filament::section>
</x-filament::page>
