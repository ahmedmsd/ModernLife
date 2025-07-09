<?php

namespace App\Filament\Resources\ShowroomResource\Pages;

use App\Filament\Resources\ShowroomResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateShowroom extends CreateRecord
{
    protected static string $resource = ShowroomResource::class;

    protected function getRedirectUrl(): string
    {
        // Redirect to the index page instead of the edit page
        return ShowroomResource::getUrl('index');
    }
}
