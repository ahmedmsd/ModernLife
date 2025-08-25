{{-- resources/views/filament/resources/production-request-resource/pages/review-request.blade.php --}}
@php
    use App\Enums\ProductionRequestPhase as Phase;
    use App\Enums\PhaseStatus as S;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $phaseEnum  = Phase::tryFrom($record->current_phase);
    $statusEnum = S::tryFrom($record->phase_status);

    $phaseLabel  = $phaseEnum?->label() ?? ($record->current_phase ?: '—');
    $statusLabel = $statusEnum?->label() ?? ($record->phase_status ?: '—');

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
        'materials_prep' => 'purple',
        'materials_done' => 'emerald',
        'on_hold'        => 'yellow',
        'completed'      => 'green',
        'cancelled'      => 'gray',
        default          => 'gray',
    };

    $clientName   = $record->client->client_name ?? '—';
    $showroomName = $record->showroom->name ?? 'غير مرتبط';
    $ownerRole    = $record->current_owner_role ?? '—';
    $reqType      = $record->request_type === 'indirect' ? 'غير مباشر' : 'مباشر';

    $sentAt    = $record->sent_to_owner_at?->format('Y-m-d H:i') ?? '—';
    $received  = $record->received_by_owner_at?->format('Y-m-d H:i') ?? '—';
    $pendingFor= $record->sent_to_owner_at && $record->phase_status === 'pending'
                    ? $record->sent_to_owner_at->diffForHumans(now(), true)
                    : '—';

    // مشروع مرتبط (إن وُجد)
    $project = $record->project ?? null;
    $tasksTotal = $project?->tasks()->count() ?? 0;
    $tasksDone  = $project?->tasks()->where('status','completed')->count() ?? 0;
    $projectStatus = $project?->status ?? '—';



    // جهّز صفوف الجدول: أولاً الاتفاقية (إن وجدت)، ثم ملفات الأقسام
    $rows = collect();

    if ($record->agreement_file) {
        $rows->push((object)[
            'is_agreement'   => true,
            'name'           => 'ملف الاتفاقية',
            'description'    => 'اتفاقية المشروع (PDF)',
            'department'     => null,
            'file_path'      => $record->agreement_file,
            'estimated_cost' => null,
        ]);
    }

    $deptFiles = $record->files ?? collect();
    foreach ($deptFiles as $f) {
        $rows->push((object)[
            'is_agreement'   => false,
            'name'           => $f->file_name ?? basename($f->file_path ?? ''),
            'description'    => $f->description ?? '—',
            'department'     => $f->department->dept_name ?? '—',
            'file_path'      => $f->file_path,
            'estimated_cost' => $f->estimated_cost,
        ]);
    }
@endphp


<x-filament::page>
    {{-- شارات --}}
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
                @if ($project)
                    <a href="{{ \App\Filament\Resources\ProjectResource::getUrl('view', ['record' => $project]) }}"
                       class="text-primary-600 underline">#{{ $project->id }} — {{ $project->project_name }}</a>
                @else
                    <span class="font-semibold">—</span>
                @endif
            </div>

            <div><span class="text-gray-500 dark:text-gray-400">أُرسل للمالك:</span> <span class="font-semibold">{{ $sentAt }}</span></div>
            <div><span class="text-gray-500 dark:text-gray-400">تم الاستلام:</span> <span class="font-semibold">{{ $received }}</span></div>

            @if ($project)
                <div class="col-span-1 md:col-span-2 lg:col-span-3">
                    <span class="text-gray-500 dark:text-gray-400">حالة المشروع:</span>
                    <span class="font-semibold">{{ $projectStatus }}</span>
                    <span class="mx-2 text-gray-400">•</span>
                    <span class="text-gray-500 dark:text-gray-400">تقدّم المهام:</span>
                    <span class="font-semibold">{{ $tasksDone }} / {{ $tasksTotal }}</span>
                </div>
            @endif

            <div class="col-span-1 md:col-span-2 lg:col-span-3">
                <span class="text-gray-500 dark:text-gray-400">مدة الانتظار الحالية:</span>
                <span class="font-semibold">{{ $pendingFor }}</span>
            </div>
        </div>
    </x-filament::section>

    {{-- ملفات الطلب + ملف الاتفاقية في جدول واحد --}}
    <x-filament::section class="mt-6">
        <x-slot name="heading">الملفات</x-slot>

        @php
            $rows = [];

            // سطر الاتفاقية أولاً (إن وجد)
            if (!empty($record->agreement_file)) {
                $rows[] = [
                    'idx'  => '—',
                    'name' => 'ملف الاتفاقية',
                    'desc' => 'اتفاقية المشروع (PDF)',
                    'dept' => '—',
                    'url'  => Storage::disk('public')->exists($record->agreement_file)
                                ? Storage::disk('public')->url($record->agreement_file)
                                : null,
                ];
            }

            // ثم ملفات الأقسام
            $files = $record->productionRequestFiles ?? $record->files ?? collect();
            foreach ($files as $i => $f) {
                $rows[] = [
                    'idx'  => $i + 1,
                    'name' => $f->file_name ?? basename($f->file_path ?? ''),
                    'desc' => $f->description ?? '—',
                    'dept' => $f->department->dept_name ?? '—',
                    'url'  => $f->file_path ? Storage::disk('public')->url($f->file_path) : null,
                ];
            }
        @endphp

        @if (count($rows))
            <div class="overflow-x-auto rounded-xl border bg-white/80 dark:bg-gray-900/70">
                <table class="w-full text-sm rtl:text-right">
                    <thead class="bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                    <tr>
                        <th class="px-3 py-2 font-semibold">#</th>
                        <th class="px-3 py-2 font-semibold">الاسم</th>
                        <th class="px-3 py-2 font-semibold">الوصف</th>
                        <th class="px-3 py-2 font-semibold">القسم</th>
                        <th class="px-3 py-2 font-semibold">التكلفة</th>
                        <th class="px-3 py-2 font-semibold">تحميل</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-800 dark:text-gray-200">
                    @foreach ($rows as $i => $row)
                        @php
                            $url = $row->file_path ? Storage::disk('public')->url($row->file_path) : null;
                        @endphp
                        <tr class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-900 dark:even:bg-gray-800">
                            <td class="px-3 py-2">{{ $row->is_agreement ? '—' : $i }}</td>
                            <td class="px-3 py-2">{{ $row->name }}</td>
                            <td class="px-3 py-2">{{ $row->description }}</td>
                            <td class="px-3 py-2">{{ $row->is_agreement ? '—' : $row->department }}</td>

                            {{-- التكلفة: لا تُعرض للاتفاقية --}}
                            <td class="px-3 py-2">
                                @if(!$row->is_agreement && !is_null($row->estimated_cost))
                                    SAR {{ number_format((float)$row->estimated_cost, 2) }}
                                @else
                                    —
                                @endif
                            </td>

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

    {{-- سجل النشاط المختصر (يعتمد صفحة الـ Timeline المفصلة لديك) --}}
    <x-filament::section class="mt-6">
        <x-slot name="heading">ملاحظات</x-slot>
        <div class="text-sm text-gray-600 dark:text-gray-300">
            الطلب سيظل مفتوحًا حتى اكتمال المشروع وجميع المهام المرتبطة به. عند اكتمالها، يُغلق الطلب تلقائيًا.
        </div>
    </x-filament::section>
</x-filament::page>
