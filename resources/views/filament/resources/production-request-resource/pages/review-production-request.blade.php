@php
    use App\Enums\ProductionRequestStatus;
    $statusEnum = ProductionRequestStatus::tryFrom($record->status);
@endphp

<x-filament::page>
    <x-filament::section>
        <x-slot name="header">
            <h2 class="text-xl font-bold">تفاصيل الطلب</h2>
        </x-slot>

        <dl class="grid grid-cols-2 gap-4 text-sm">
            <div><strong>اسم المشروع:</strong> {{ $record->project_name }}</div>
            <div><strong>العميل:</strong> {{ $record->client->client_name ?? '-' }}</div>
            <div><strong>المعرض:</strong> {{ $record->showroom->name ?? '-' }}</div>
            <div>
                <strong>الحالة الحالية:</strong>
                <span class="px-2 py-1 rounded-full text-white text-xs" style="background-color: {{ $statusEnum->color() }};">
                    {{ $statusEnum->label() }}
                </span>
            </div>            <div class="col-span-2"><strong>الوصف:</strong> {{ $record->project_description }}</div>
        </dl>
    </x-filament::section>

    <x-filament::section>
        <x-slot name="header">
            <h2 class="text-xl font-bold">ملفات الأقسام</h2>
        </x-slot>
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
            <div class="flex justify-between items-center p-2 bg-gray-100 dark:bg-gray-800 rounded mb-2">
                <span><strong>{{ $file->department?->dept_name ?? 'قسم غير معروف' }}</strong></span>
                <a href="{{ Storage::disk('public')->url($file->file_path) }}" class="text-primary-600 underline" target="_blank">تحميل الملف</a>
            </div>
        @empty
            <p class="text-sm text-gray-500">لا توجد ملفات.</p>
        @endforelse
    </x-filament::section>
</x-filament::page>
