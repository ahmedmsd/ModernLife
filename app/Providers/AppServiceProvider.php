<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Panel;
use Filament\Facades\Filament;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
       Filament::registerRenderHook(
    'panels::body.end',
    fn (): string => <<<'HTML'
        <script>
            function updateSidebarGroups() {
                setTimeout(() => {
                    document.querySelectorAll('.fi-sidebar-group').forEach(group => {
                        const toggleButton = group.querySelector('button[aria-expanded]');
                        const itemsContainer = group.querySelector('[id^="headlessui-disclosure-panel-"]');

                        const isActive = group.querySelector('.fi-active');

                        // إذا الزر موجود
                        if (toggleButton && itemsContainer) {
                            if (isActive) {
                                // افتح القائمة
                                toggleButton.setAttribute('aria-expanded', 'true');
                                itemsContainer.removeAttribute('hidden');
                            } else {
                                // اغلق القائمة
                                toggleButton.setAttribute('aria-expanded', 'false');
                                itemsContainer.setAttribute('hidden', 'true');
                            }
                        }
                    });
                }, 100); // تأخير بسيط بعد تحديث Livewire
            }

            // عند تحميل Livewire
            document.addEventListener('livewire:load', updateSidebarGroups);

            // بعد كل عملية DOM update من Livewire
            window.Livewire && window.Livewire.hook && window.Livewire.hook('message.processed', updateSidebarGroups);
        </script>
    HTML
);


    }
}
