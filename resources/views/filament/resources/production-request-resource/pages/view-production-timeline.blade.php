<x-filament::page>
    <x-filament::section>
        <x-slot name="header">
            <h2 class="text-xl font-bold">معلومات الطلب</h2>
        </x-slot>

        <dl class="grid grid-cols-2 gap-4 text-sm">
            <div><strong>اسم المشروع:</strong> {{ $record->project_name }}</div>
            <div><strong>العميل:</strong> {{ $record->client->client_name ?? '-' }}</div>
            <div><strong>المعرض:</strong> {{ $record->showroom->name ?? '-' }}</div>
            <div><strong>الحالة الحالية:</strong> <span class="font-semibold text-primary-600">{{ $record->status }}</span></div>
            <div><strong>أنشئ بواسطة:</strong> {{ $record->creator->name ?? '-' }}</div>
            <div><strong>تاريخ الإنشاء:</strong> {{ $record->created_at->format('Y-m-d H:i') }}</div>
            <div class="col-span-2"><strong>الوصف:</strong> {{ $record->project_description ?? '-' }}</div>
        </dl>
    </x-filament::section>

    <x-filament::section>
        <x-slot name="header">
            <h2 class="text-xl font-bold">ملفات التصنيع</h2>
        </x-slot>

         @if ($record->agreement_file)
         <ul class="mt-4 space-y-3">
          
            <li class="flex items-center justify-between bg-gray-50 dark:bg-gray-800 p-3 rounded-md border">
                <span>
                    <strong>ملف الاتفاقية:</strong>
                </span>
                <a href="{{ Storage::url($record->agreement_file) }}" class="text-primary-600 underline" target="_blank">
                    تحميل الملف
                </a>
            </li>
        </ul>         
        @endif

        @if ($record->files && $record->files->count())
        <ul class="mt-4 space-y-3">
            @foreach ($record->files as $file)
            <li class="flex items-center justify-between bg-gray-50 dark:bg-gray-800 p-3 m-3 rounded-md border">
                <span>
                    <strong>{{ $file->department?->dept_name ?? 'قسم غير معروف' }}</strong>
                </span>
                <a href="{{ Storage::disk('public')->url($file->file_path) }}"
                    class="text-primary-600 hover:underline" target="_blank">
                    تحميل الملف
                </a>
            </li>
            @endforeach
        </ul>
        @else
        <p class="text-sm text-gray-500">لا توجد ملفات مرتبطة.</p>
        @endif
    </x-filament::section>
    <x-filament::section>
        <x-slot name="header">
            <h2 class="text-xl font-bold">سجل الأحداث</h2>
        </x-slot>

        @if ($record->logs && $record->logs->count())
        <div class="space-y-4 mt-4">
            @foreach ($record->logs->sortByDesc('action_at') as $log)
            <div class="border rounded-md p-4 bg-gray-50 dark:bg-gray-800">
                <div class="flex justify-between text-sm text-gray-600 dark:text-gray-300">
                    <div>
                        <strong>{{ $log->user->name ?? 'مستخدم غير معروف' }}</strong>
                        <span class="ml-2">قام بـ:</span>
                        <span class="font-semibold text-primary-600">{{ $log->action }}</span>
                    </div>
                    <div>
                        <span>{{ \Carbon\Carbon::parse($log->action_at)->format('Y-m-d H:i') }}</span>
                    </div>
                </div>
                @if($log->note)
                <div class="mt-2 text-sm text-gray-700 dark:text-gray-100">
                    {{ $log->note }}
                </div>
                @endif
            </div>
            @endforeach
        </div>
        @else
        <p class="text-sm text-gray-500 dark:text-gray-300">لا يوجد سجل للأحداث حتى الآن.</p>
        @endif
    </x-filament::section>

</x-filament::page>