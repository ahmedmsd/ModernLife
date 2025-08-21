<?php

return [
    'class_namespace' => 'App\\Livewire',
    'view_path' => resource_path('views/livewire'),

    // مهم: الرفع المؤقت
    'temporary_file_upload' => [
        // اتركه على local (storage/app)
        'disk' => env('LIVEWIRE_TEMP_DISK', 'local'),

        // المجلد داخل disk أعلاه
        'directory' => env('LIVEWIRE_TEMP_DIR', 'livewire-tmp'),

        // حدود الرفع (50MB مثالاً)
        'rules' => 'file|max:51200',

        // منع مشاكل تنظيف الملفات بسرعة
        'cleanup' => true,
        'cleanup_probability' => 0, // نعطل التنظيف الآلي مؤقتاً
    ],

    'middleware_group' => 'web',
];
