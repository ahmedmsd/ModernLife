<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class DelaysFollowUp extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'متابعة التأخيرات';
    protected static ?string $title           = 'متابعة تأخيرات المهام والطلبات';
    protected static ?string $navigationGroup = 'لوحات المتابعة';
    protected static ?int $navigationSort     = 10;

    protected static string $view = 'filament.pages.delays-follow-up';

    public string $tab = 'tasks';
}
