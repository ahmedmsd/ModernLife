<?php

namespace App\Filament\Resources\LegacyClientProjectResource\Pages;

use App\Filament\Resources\LegacyClientProjectResource;
use Filament\Resources\Pages\ListRecords;

class ListLegacyClientProjects extends ListRecords
{
    protected static string $resource = LegacyClientProjectResource::class;
    protected static ?string $title = 'مشروعات العملاء القديمة';
}
