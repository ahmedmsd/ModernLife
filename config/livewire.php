<?php

return [
    'class_namespace' => 'App',
    'view_path' => resource_path('views/livewire'),

    'temporary_file_upload' => [
        'disk' => env('LIVEWIRE_TEMP_DISK', 'local'),
        'directory' => env('LIVEWIRE_TEMP_DIR', 'livewire-tmp'),
        'rules' => 'file|max:51200',
        'cleanup' => true,
        'cleanup_probability' => 0,
    ],

    'middleware_group' => 'web',
];
