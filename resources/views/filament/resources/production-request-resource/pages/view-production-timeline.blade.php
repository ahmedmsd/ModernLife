@php
use App\Enums\ProductionRequestStatus;

$statusEnum = $record->status instanceof \BackedEnum
? $record->status
: ProductionRequestStatus::tryFrom($record->status);
@endphp
 {{-- تعريف ألوان الحالات --}}
    @php
        function getStatusColor($status): string {
            return match ($status?->value ?? $status) {
                'draft' => '#6b7280', // رمادي
                'submitted' => '#3b82f6', // أزرق
                'under_review' => '#f59e0b', // أصفر
                'approved' => '#10b981', // أخضر
                'rejected' => '#ef4444', // أحمر
                default => '#9ca3af',     // رمادي افتراضي
            };
        }
    @endphp
    

<x-filament::page>
    {{-- معلومات الطلب --}}
    <x-filament::section>
        <x-slot name="header">
            <h2 class="text-xl font-bold">معلومات الطلب</h2>
        </x-slot>

        <dl class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
            <div><strong>اسم المشروع:</strong> {{ $record->project_name }}</div>
            <div><strong>العميل:</strong> {{ $record->client->client_name ?? '-' }}</div>
            <div><strong>المعرض:</strong> {{ $record->showroom->name ?? '-' }}</div>
            <div><strong>الحالة الحالية:</strong>
                <span class="px-2 py-1 rounded-full text-white text-xs bg-primary-600">
                    {{ $statusEnum ? $statusEnum->label() : $record->status }}
                </span>
            </div>
            <div><strong>أنشئ بواسطة:</strong> {{ $record->creator->name ?? '-' }}</div>
            <div><strong>تاريخ الإنشاء:</strong> {{ $record->created_at->format('Y-m-d H:i') }}</div>
            <div class="col-span-2 md:col-span-3"><strong>الوصف:</strong> {{ $record->project_description ?? '-' }}</div>
        </dl>
    </x-filament::section>

    {{-- ملفات التصنيع --}}
    <x-filament::section>
        <x-slot name="header">
            <h2 class="text-xl font-bold">ملفات التصنيع</h2>
        </x-slot>

        <ul class="mt-4 space-y-3">
            @if ($record->agreement_file)
            <li class="flex justify-between items-center bg-gray-50 dark:bg-gray-800 p-3 rounded border">
                <span><strong>ملف الاتفاقية:</strong></span>
                <a href="{{ Storage::url($record->agreement_file) }}" class="text-primary-600 underline" target="_blank">
                    تحميل الملف
                </a>
            </li>
            @endif

            @forelse ($record->files as $file)
            <li class="flex justify-between items-center bg-gray-50 dark:bg-gray-800 p-3 rounded border">
                <span><strong>{{ $file->department->dept_name ?? 'قسم غير معروف' }}</strong></span>
                <a href="{{ Storage::disk('public')->url($file->file_path) }}" class="text-primary-600 underline" target="_blank">
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
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">سجل الأحداث</h2>
    </x-slot>

    @forelse ($record->logs->sortByDesc('action_at') as $log)
        <div class="border rounded-md p-4 bg-white dark:bg-gray-900 mb-4 shadow-sm">
            <div class="flex justify-between text-sm">
                <div>
                    <strong class="text-gray-900 dark:text-white">
                        {{ $log->user->name ?? 'مستخدم غير معروف' }}
                    </strong>
                    <span class="ml-2 text-gray-700 dark:text-gray-300">قام بـ:</span>
                    <span class="font-semibold text-primary-700 dark:text-primary-300">
                        {{ \App\Enums\ProductionRequestStatus::tryFrom($log->action)?->label() ?? $log->action }}
                    </span>
                </div>
                <div class="text-gray-600 dark:text-gray-400">
                    {{ \Carbon\Carbon::parse($log->action_at)->diffForHumans() }}
                </div>
            </div>

            @if($log->note)
                <div class="mt-3 text-sm leading-relaxed">
                    @if($log->action === \App\Enums\ProductionRequestStatus::Rejected->value)
                        <div class="font-semibold text-red-700 dark:text-red-300">سبب الرفض:</div>
                        <div class="text-gray-900 dark:text-red-100">{{ $log->note }}</div>
                    @else
                        <div class="text-gray-900 dark:text-gray-100">{{ $log->note }}</div>
                    @endif
                </div>
            @endif
        </div>
    @empty
        <p class="text-sm text-gray-500 dark:text-gray-300">لا يوجد سجل للأحداث حتى الآن.</p>
    @endforelse
</x-filament::section>


</x-filament::page>