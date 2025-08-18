<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ config('filament.direction') }}">
<head>
    @filamentHead
</head>

<body class="fi-body min-h-screen bg-gray-50 dark:bg-gray-950">
@filamentBody

@filamentScripts
</body>
</html>
