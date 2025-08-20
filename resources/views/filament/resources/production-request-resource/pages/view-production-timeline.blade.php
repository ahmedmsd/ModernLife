@php
    use App\Enums\ProductionRequestStatus;
    use Illuminate\Support\Carbon;

    $statusEnum = ProductionRequestStatus::tryFrom($record->status);
@endphp

<x-filament::page>
    {{-- قسم معلومات الطلب --}}
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

                    $bg = $statusEnum?->color() ?? '#64748b';
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

    {{-- قسم ملفات التصنيع --}}
    <x-filament::section>
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

    {{-- سجل الأحداث --}}
    <x-filament::section>
        <x-slot name="header">
            <h2 class="text-xl font-bold">سجل الأحداث</h2>
        </x-slot>

        @forelse ($record->logs->sortByDesc('action_at') as $log)
            @php
                $logEnum = ProductionRequestStatus::tryFrom($log->action);
                $rawAt = $log->action_at ?? $log->happened_at ?? $log->created_at;
                $at = $rawAt instanceof Carbon ? $rawAt : ($rawAt ? Carbon::parse($rawAt) : null);
            @endphp

            <div class="border rounded-md p-4 mb-4 bg-white dark:bg-gray-900 shadow-sm">
                <div class="flex justify-between text-sm">
                    <div>
                        <strong>{{ $log->user->name ?? 'مجهول' }}</strong>
                        <span>قام بـ:</span>
                        <span class="font-semibold text-primary-700">
                            {{ $logEnum?->label() ?? $log->action }}
                        </span>
                    </div>
                    <div class="text-gray-600">{{ $at?->diffForHumans() ?? '—' }}</div>
                </div>

                @if ($log->note)
                    <div class="mt-3 text-sm">
                        @if ($log->action === \App\Enums\ProductionRequestStatus::REJECTED->value)
                            <div class="font-semibold text-red-700">سبب الرفض:</div>
                        @endif
                        <div>{{ $log->note }}</div>
                    </div>
                @endif
            </div>
        @empty
            <p class="text-sm text-gray-500">لا يوجد سجل للأحداث حالياً.</p>
        @endforelse
    </x-filament::section>
</x-filament::page>
