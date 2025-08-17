<?php

namespace App\Filament\Resources\NothingResource\Pages;

use App\Filament\Resources\NothingResource;
use Filament\Resources\Pages\Page;

class SystemSettings extends Page
{
    protected static string $resource = NothingResource::class;

    protected static string $view = 'filament.resources.nothing-resource.pages.system-settings';
}
