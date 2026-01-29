<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Services\Zoho\ZohoCrmService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class ZohoSync extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    protected static ?string $navigationGroup = 'Zoho CRM';
    protected static ?int $navigationSort = 0;
    protected static ?string $navigationLabel = 'مزامنة Zoho (Sync)';
    protected static ?string $title = 'مزامنة بيانات Zoho CRM';

    protected static string $view = 'filament.pages.zoho-sync';

    public $connectionStatus = 'Checking...';
    public $lastSync = 'Never';

    public function mount(ZohoCrmService $zohoService)
    {
        try {
            $token = $zohoService->getRecords('Accounts', 1, 1);
            $this->connectionStatus = !empty($token) || is_array($token) ? 'Connected' : 'Disconnected';
        } catch (\Exception $e) {
            $this->connectionStatus = 'Error: ' . $e->getMessage();
        }
        
        // You would typically get this from a settings table or cache
        $this->lastSync = cache('zoho_last_sync_time', 'Never');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sync_all')
                ->label('مزامنة الكل (Sync All)')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->action(fn () => $this->runSync('all')),

            Action::make('sync_accounts')
                ->label('العملاء فقط')
                ->icon('heroicon-o-user-group')
                ->color('gray')
                ->action(fn () => $this->runSync('accounts')),

            Action::make('sync_quotes')
                ->label('عروض الأسعار')
                ->icon('heroicon-o-document-text')
                ->color('gray')
                ->action(fn () => $this->runSync('quotes')),

            Action::make('sync_orders')
                ->label('أوامر البيع')
                ->icon('heroicon-o-shopping-bag')
                ->color('gray')
                ->action(fn () => $this->runSync('sales_orders')),
        ];
    }

    public function runSync($module)
    {
        try {
            if ($module === 'all') {
                Artisan::call('zoho:sync');
            } else {
                Artisan::call('zoho:sync', ['module' => $module]);
            }

            cache(['zoho_last_sync_time' => now()->format('Y-m-d H:i:s')], now()->addDays(30));
            $this->lastSync = now()->format('Y-m-d H:i:s');

            Notification::make()
                ->title('تمت المزامنة بنجاح')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Log::error("Zoho Sync Page Error: " . $e->getMessage());
            Notification::make()
                ->title('خطأ في المزامنة')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
