<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\View\PanelsRenderHook;
use Filament\Events\ServingFilament;
use Illuminate\Support\Facades\Event;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // التسجيل عبر نظام الأحداث
        Event::listen(ServingFilament::class, function () {
            $this->addNavigationScript();
        });
    }

    protected function addNavigationScript(): void
    {
        PanelsRenderHook::register(
            PanelsRenderHook::BODY_END,
            fn (): string => <<<'HTML'
                <script>
                    document.addEventListener('DOMContentLoaded', () => {
                        const closeInactiveGroups = () => {
                            document.querySelectorAll('.fi-sidebar-group').forEach(group => {
                                const isActive = group.querySelector('.fi-active');
                                const toggleButton = group.querySelector('button[aria-expanded="true"]');
                                
                                if (!isActive && toggleButton) {
                                    toggleButton.click();
                                }
                            });
                        };
                        
                        // التنفيذ الأولي
                        closeInactiveGroups();
                        
                        // متابعة التغيرات الديناميكية (اختياري)
                        new MutationObserver(closeInactiveGroups)
                            .observe(document.body, { subtree: true, childList: true });
                    });
                </script>
            HTML
        );
    }
}