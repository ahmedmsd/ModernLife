{{-- resources/views/filament/resources/production-request-resource/pages/review-request.blade.php --}}
@php
    use App\Enums\ProductionRequestPhase as Phase;
    use App\Enums\PhaseStatus as S;
    use Illuminate\Support\Facades\Storage;
    use Carbon\Carbon;

    /* ---------------- ترجمات بسيطة داخل البلَيد (يمكن نقلها لملفات lang لاحقًا) ---------------- */
    $phaseLabelFn = function (?string $v): string {
        return match ($v) {
            'showroom_review'            => 'مراجعة المعرض',
            'factory_intake'             => 'استلام المصنع',
            'department_assignment'      => 'إسناد الأقسام',
            'purchasing'                 => 'المشتريات',
            'manufacturing'              => 'التصنيع',
            'quality_after_manufacture'  => 'جودة ما بعد التصنيع',
            'installation'               => 'التركيب',
            'quality_after_installation' => 'جودة ما بعد التركيب',
            'closed'                     => 'مغلق',
            default                      => $v ?? '—',
        };
    };

    $statusLabelFn = function (?string $v): string {
        return match ($v) {
            'pending'        => 'قيد الانتظار',
            'received'       => 'تم الاستلام',
            'under_review'   => 'قيد المراجعة',
            'approved'       => 'معتمد',
            'rejected'       => 'مرفوض',
            'in_progress'    => 'قيد التنفيذ',
            'materials_wait' => 'بانتظار الخامات',
            'materials_prep' => 'تحضير الخامات',
            'materials_done' => 'تم توفير الخامات',
            'on_hold'        => 'معلق',
            'completed'      => 'مكتمل',
            'cancelled'      => 'ملغي',
            default          => $v ?? '—',
        };
    };

    $roleLabelFn = function (?string $role): string {
        return match ($role) {
            'showroom_manager'    => 'مدير المعرض',
            'factory_manager'     => 'مدير المصنع',
            'purchasing_manager'  => 'مدير المشتريات',
            'department_manager'  => 'مدير القسم',
            'quality_manager'     => 'مسؤول الجودة',
            'installation_manager'=> 'مدير التركيب',
            default               => $role ?? '—',
        };
    };

    /* ---------------- عناوين و ألوان ---------------- */
    $phaseEnum   = Phase::tryFrom($record->current_phase);
    $statusEnum  = S::tryFrom($record->phase_status);
    $phaseLabel  = $phaseEnum?->label()  ?? $phaseLabelFn($record->current_phase);
    $statusLabel = $statusEnum?->label() ?? $statusLabelFn($record->phase_status);

    $phaseColor = match ($record->current_phase) {
        'sales_intake'              => 'indigo',
        'showroom_review'           => 'sky',
        'factory_intake'            => 'amber',
        'department_assignment'     => 'violet',
        'purchasing'                => 'orange',
        'manufacturing'             => 'blue',
        'quality_after_manufacture' => 'emerald',
        'installation'              => 'fuchsia',
        'quality_after_installation'=> 'teal',
        'closed'                    => 'slate',
        default                     => 'gray',
    };

    $statusColor = match ($record->phase_status) {
        'pending'        => 'zinc',
        'received'       => 'blue',
        'under_review'   => 'amber',
        'approved'       => 'green',
        'rejected'       => 'red',
        'in_progress'    => 'sky',
        'materials_wait' => 'violet',
        'materials_prep' => 'purple',
        'materials_done' => 'emerald',
        'on_hold'        => 'yellow',
        'completed'      => 'green',
        'cancelled'      => 'gray',
        default          => 'gray',
    };

    /* ---------------- بيانات عامة ---------------- */
    $clientName   = $record->client->client_name ?? '—';
    $showroomName = $record->showroom->name ?? 'غير مرتبط بمعرض';
    $projectName  = $record->project?->project_name ?? '—';
    $ownerRole    = $roleLabelFn($record->current_owner_role ?? null);
    $reqType      = $record->request_type === 'indirect' ? 'غير مباشر' : 'مباشر';

    $sentAt    = $record->sent_to_owner_at ? $record->sent_to_owner_at->format('Y-m-d H:i') : '—';
    $received  = $record->received_by_owner_at ? $record->received_by_owner_at->format('Y-m-d H:i') : '—';
    $pendingFor= ($record->sent_to_owner_at && $record->phase_status === 'pending')
                    ? $record->sent_to_owner_at->diffForHumans(now(), true)
                    : '—';

    /* ---------------- فورماتر نص اللوج لو note فاضي ---------------- */
    $formatLogNote = function ($log) use ($phaseLabelFn, $statusLabelFn, $roleLabelFn): string {
        if (filled($log->note)) return $log->note;

        $type = $log->type ?? '—';
        $data = is_array($log->data) ? $log->data : [];

        return match ($type) {
            'transition' => 'انتقال من مرحلة '
                . $phaseLabelFn($data['from']['phase']   ?? null)
                . ' (' . $statusLabelFn($data['from']['status'] ?? null) . ')'
                . ' إلى مرحلة '
                . $phaseLabelFn($data['to']['phase']     ?? null)
                . ' (' . $statusLabelFn($data['to']['status'] ?? null) . ')'
                . (isset($data['owner_role']) ? ' | المالك: ' . $roleLabelFn($data['owner_role']) : ''),
            'received'   => 'تم تأكيد الاستلام — المرحلة: ' . $phaseLabelFn($data['phase'] ?? null)
                . ' | من ' . $statusLabelFn($data['from_status'] ?? null)
                . ' إلى '  . $statusLabelFn($data['to_status']   ?? null),
            'rejected'   => 'تم الرفض — المرحلة: ' . $phaseLabelFn($data['phase'] ?? null),
            'project_bootstrap' => 'تجهيز مشروع من الطلب'
                . (isset($data['project_id']) ? ' #'.$data['project_id'] : '')
                . ' — ملفات: ' . ($data['files_created'] ?? 0)
                . ' | مهام: ' . ($data['tasks_created'] ?? 0),
            default => '—',
        };
    };

    $formatWhen = function ($log): array {
        $at = $log->happened_at ?? $log->created_at ?? null;
        $carbon = $at instanceof Carbon ? $at : ($at ? Carbon::parse($at) : null);
        return [
            'at'   => $carbon?->format('Y-m-d H:i') ?? '—',
            'human'=> $carbon?->diffForHumans() ?? null,
        ];
    };

    /* ---------------- اللوجات: رتب حسب happened_at ثم created_at ---------------- */
    $logs = collect($record->logs ?? [])
        ->sortByDesc(function ($l) {
            return ($l->happened_at ?? $l->created_at ?? now())->value ?? ($l->happened_at ?? $l->created_at ?? now());
        })
        ->values();
@endphp

<x-filament::page>
    {{-- شارات المرحلة/الحالة --}}
    <div class="mb-4 flex flex-wrap items-center gap-2">
        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium text-white bg-{{ $phaseColor }}-600">
            المرحلة: {{ $phaseLabel }}
        </span>
        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium text-white bg-{{ $statusColor }}-600">
            الحالة: {{ $statusLabel }}
        </span>
        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium text-white bg-slate-600">
            نوع الطلب: {{ $reqType }}
        </span>
        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium text-white bg-gray-600">
            المالك الحالي: {{ $ownerRole }}
        </span>
    </div>

    {{-- معلومات أساسية --}}
    <x-filament::section>
        <x-slot name="heading">معلومات الطلب</x-slot>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
            <div><span class="text-gray-500 dark:text-gray-400">رقم الطلب:</span> <span class="font-semibold">{{ $record->id }}</span></div>
            <div><span class="text-gray-500 dark:text-gray-400">العميل:</span> <span class="font-semibold">{{ $clientName }}</span></div>
            <div><span class="text-gray-500 dark:text-gray-400">المعرض:</span> <span class="font-semibold">{{ $showroomName }}</span></div>

            <div>
                <span class="text-gray-500 dark:text-gray-400">المشروع:</span>
                @if ($record->project)
                    <a href="{{ \App\Filament\Resources\ProjectResource::getUrl('view', ['record' => $record->project]) }}"
                       class="text-primary-600 underline">{{ $projectName }}</a>
                @else
                    <span class="font-semibold">—</span>
                @endif
            </div>

            <div><span class="text-gray-500 dark:text-gray-400">أُرسل للمالك:</span> <span class="font-semibold">{{ $sentAt }}</span></div>
            <div><span class="text-gray-500 dark:text-gray-400">تم الاستلام:</span> <span class="font-semibold">{{ $received }}</span></div>

            <div class="col-span-1 md:col-span-2 lg:col-span-3">
                <span class="text-gray-500 dark:text-gray-400">مدة الانتظار الحالية:</span>
                <span class="font-semibold">{{ $pendingFor }}</span>
            </div>
        </div>
    </x-filament::section>

    {{-- ملفات الطلب --}}
    <x-filament::section class="mt-6">
        <x-slot name="heading">ملفات الطلب</x-slot>
        @php $files = $record->productionRequestFiles ?? $record->files ?? collect(); @endphp

        @if ($files->count())
            <div class="overflow-x-auto rounded-xl border bg-white/80 dark:bg-gray-900/70">
                <table class="w-full text-sm rtl:text-right">
                    <thead class="bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                    <tr>
                        <th class="px-3 py-2 font-semibold">#</th>
                        <th class="px-3 py-2 font-semibold">الاسم</th>
                        <th class="px-3 py-2 font-semibold">الوصف</th>
                        <th class="px-3 py-2 font-semibold">القسم</th>
                        <th class="px-3 py-2 font-semibold">تحميل</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-800 dark:text-gray-200">
                    @foreach ($files as $i => $f)
                        @php
                            $name = $f->file_name ?? basename($f->file_path ?? '');
                            $desc = $f->description ?? '—';
                            $dept = $f->department->dept_name ?? '—';
                            $url  = $f->file_path ? Storage::disk('public')->url($f->file_path) : null;
                        @endphp
                        <tr class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-900 dark:even:bg-gray-800">
                            <td class="px-3 py-2">{{ $i+1 }}</td>
                            <td class="px-3 py-2">{{ $name ?: '—' }}</td>
                            <td class="px-3 py-2">{{ $desc }}</td>
                            <td class="px-3 py-2">{{ $dept }}</td>
                            <td class="px-3 py-2">
                                @if ($url)
                                    <a class="text-primary-600 underline" target="_blank" href="{{ $url }}">تحميل</a>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-sm text-gray-500">لا توجد ملفات مرفقة.</div>
        @endif
    </x-filament::section>

    {{-- سجل النشاط --}}
    <x-filament::section class="mt-6">
        <x-slot name="heading">سجل النشاط</x-slot>

        @if ($logs->count())
            <div class="space-y-3">
                @foreach ($logs as $log)
                    @php
                        $when = $formatWhen($log);
                        $who  = $log->causer?->name ?? 'النظام';
                        $what = $formatLogNote($log);
                    @endphp

                    <div class="border rounded-md p-4 bg-white/80 dark:bg-gray-900/70">
                        <div class="flex justify-between text-sm">
                            <div>
                                <strong>{{ $who }}</strong>
                                <span class="text-gray-600 dark:text-gray-400">قام بـ</span>
                                <span class="font-semibold">{{ $log->type ?? '—' }}</span>
                            </div>
                            <div class="text-gray-600 dark:text-gray-400">
                                {{ $when['at'] }}
                                @if ($when['human'])
                                    <span class="mx-1">•</span>
                                    <span>{{ $when['human'] }}</span>
                                @endif
                            </div>
                        </div>

                        @if ($what && $what !== '—')
                            <div class="mt-2 text-sm text-gray-800 dark:text-gray-200">
                                {{ $what }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-sm text-gray-500">لا يوجد نشاط حتى الآن.</div>
        @endif
    </x-filament::section>
</x-filament::page>
