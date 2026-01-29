<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <x-filament::section>
            <x-slot name="heading">
                حالة الاتصال (Connection Status)
            </x-slot>
            
            <div class="flex items-center space-x-4 rtl:space-x-reverse">
                <div @class([
                    'w-4 h-4 rounded-full',
                    'bg-success-500' => $connectionStatus === 'Connected',
                    'bg-danger-500' => str_contains($connectionStatus, 'Error') || $connectionStatus === 'Disconnected',
                    'bg-gray-400' => $connectionStatus === 'Checking...',
                ])></div>
                <span class="text-lg font-medium">{{ $connectionStatus }}</span>
            </div>
            
            <p class="mt-2 text-sm text-gray-500">
                يتم التحقق من الاتصال باستخدام مفاتيح API الموجودة في ملف .env
            </p>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">
                آخر مزامنة (Last Sync)
            </x-slot>
            
            <div class="flex items-center space-x-4 rtl:space-x-reverse">
                <x-filament::icon
                    icon="heroicon-m-clock"
                    class="w-6 h-6 text-gray-400"
                />
                <span class="text-lg font-medium">{{ $lastSync }}</span>
            </div>
            
            <p class="mt-2 text-sm text-gray-500">
                تاريخ آخر مرة تم فيها تشغيل أمر المزامنة اليدوية.
            </p>
        </x-filament::section>
    </div>

    <x-filament::section class="mt-6">
        <x-slot name="heading">
            تعليمات المزامنة
        </x-slot>
        
        <div class="prose dark:prose-invert max-w-none">
            <p>يمكنك استخدام الأزرار في الأعلى لبدء عملية المزامنة يدوياً. عملية المزامنة تقوم بما يلي:</p>
            <ul>
                <li><strong>العملاء:</strong> تحديث بيانات العملاء وربطهم بمعرف Zoho.</li>
                <li><strong>عروض الأسعار:</strong> جلب عروض الأسعار وتحديث بنودها وأسعارها.</li>
                <li><strong>أوامر البيع:</strong> جلب أوامر البيع (Sales Orders) التي تعتبر المصدر الأساسي لعمليات الإنتاج.</li>
            </ul>
            <p class="text-warning-600 font-bold">ملاحظة: المزامنة قد تستغرق وقتاً طويلاً اعتماداً على حجم البيانات في Zoho.</p>
        </div>
    </x-filament::section>
</x-filament-panels::page>
