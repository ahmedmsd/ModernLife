<?php

namespace App\Notifications\Concerns;

use Filament\Notifications\Notification as FilamentNotification;

trait AsFilamentDatabaseNotification
{
    protected function filamentDbMessage(
        string $title,
        ?string $body = null,
        array $extra = []
    ): array {
        return FilamentNotification::make()
                ->title($title)
                ->body($body)
                ->getDatabaseMessage()
            + $extra;
    }
}
