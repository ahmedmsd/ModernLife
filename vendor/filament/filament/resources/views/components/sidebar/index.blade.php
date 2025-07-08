@php
    use Filament\Support\Facades\FilamentView;
    use Filament\View\PanelsRenderHook;
    use Filament\Navigation\NavigationGroup;

    $navigation = $navigation ?? [];
    $isRtl = in_array(app()->getLocale(), ['ar', 'he', 'fa', 'ur']);
    $openSidebarClasses = 'fi-sidebar-open w-[--sidebar-width] translate-x-0 shadow-xl ring-1 ring-gray-950/5 dark:ring-white/10 ' . ($isRtl ? '' : 'rtl:-translate-x-0');
@endphp

<aside x-data="{}" x-cloak @class([
    'fi-sidebar fixed inset-y-0 start-0 z-30 flex flex-col h-screen content-start bg-white transition-all dark:bg-gray-900 lg:z-0 lg:bg-transparent lg:shadow-none lg:ring-0 lg:transition-none dark:lg:bg-transparent',
    'lg:translate-x-0 rtl:lg:-translate-x-0' => !(filament()->isSidebarCollapsibleOnDesktop() || filament()->isSidebarFullyCollapsibleOnDesktop() || filament()->hasTopNavigation()),
    'lg:-translate-x-full rtl:lg:translate-x-full' => filament()->hasTopNavigation(),
]) x-bind:class="
        $store.sidebar.isOpen
            ? '{{ $openSidebarClasses }} lg:sticky'
            : '-translate-x-full rtl:translate-x-full lg:sticky lg:translate-x-0 rtl:lg:-translate-x-0'
    ">
    {{-- Header --}}
    <div class="overflow-x-clip">
        <header
            class="fi-sidebar-header flex h-16 items-center bg-white px-6 ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 lg:shadow-sm">
            <div x-show="$store.sidebar.isOpen" x-transition:enter="lg:transition lg:delay-100"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                @if ($homeUrl = filament()->getHomeUrl())
                    <a {{ \Filament\Support\generate_href_html($homeUrl) }}>
                        <x-filament-panels::logo />
                    </a>
                @else
                    <x-filament-panels::logo />
                @endif
            </div>

            {{-- Expand Sidebar --}}
            @if (filament()->isSidebarCollapsibleOnDesktop())
                <x-filament::icon-button color="gray" :icon="$isRtl ? 'heroicon-o-chevron-left' : 'heroicon-o-chevron-right'"
                    :icon-alias="$isRtl ? ['panels::sidebar.expand-button.rtl', 'panels::sidebar.expand-button'] : 'panels::sidebar.expand-button'"
                    icon-size="lg" :label="__('filament-panels::layout.actions.sidebar.expand.label')"
                    x-on:click="$store.sidebar.open()" x-show="! $store.sidebar.isOpen" x-cloak x-data="{}"
                    class="mx-auto" />
            @endif

            {{-- Collapse Sidebar --}}
            @if (filament()->isSidebarCollapsibleOnDesktop() || filament()->isSidebarFullyCollapsibleOnDesktop())
                <x-filament::icon-button color="gray" :icon="$isRtl ? 'heroicon-o-chevron-right' : 'heroicon-o-chevron-left'"
                    :icon-alias="$isRtl ? ['panels::sidebar.collapse-button.rtl', 'panels::sidebar.collapse-button'] : 'panels::sidebar.collapse-button'"
                    icon-size="lg" :label="__('filament-panels::layout.actions.sidebar.collapse.label')"
                    x-on:click="$store.sidebar.close()" x-show="$store.sidebar.isOpen" x-cloak x-data="{}"
                    class="ms-auto hidden lg:flex" />
            @endif
        </header>
    </div>

    {{-- Navigation --}}
    <nav class="fi-sidebar-nav flex-grow flex flex-col gap-y-7 overflow-y-auto overflow-x-hidden px-6 py-8"
        style="scrollbar-gutter: stable">
        {{ FilamentView::renderHook(PanelsRenderHook::SIDEBAR_NAV_START) }}

        {{-- Tenant Menu --}}
        @if (filament()->hasTenancy() && filament()->hasTenantMenu())
            <div @class([
                'fi-sidebar-nav-tenant-menu-ctn',
                '-mx-2' => !filament()->isSidebarCollapsibleOnDesktop(),
            ])
                x-bind:class="$store.sidebar.isOpen ? '-mx-2' : '-mx-4'">
                <x-filament-panels::tenant-menu />
            </div>
        @endif

        {{-- Navigation Groups --}}
        <ul class="fi-sidebar-nav-groups -mx-2 flex flex-col gap-y-7">
            @foreach ($navigation as $group)
                <x-filament-panels::sidebar.group :active="$group->isActive()" :collapsible="$group->isCollapsible()"
                    :icon="$group->getIcon()" :items="$group->getItems()" :label="$group->getLabel() ?? 'بدون اسم'"
                    :attributes="\Filament\Support\prepare_inherited_attributes($group->getExtraSidebarAttributeBag())" />
            @endforeach
        </ul>

        {{-- حفظ حالة القوائم المفتوحة --}}
        <script>
            document.addEventListener('alpine:init', () => {
                let collapsedGroups = JSON.parse(localStorage.getItem('collapsedGroups')) || [];

                document.querySelectorAll('.fi-sidebar-group').forEach((group) => {
                    const label = group.dataset.groupLabel;

                    if (!label || !collapsedGroups.includes(label)) return;

                    group.querySelector('.fi-sidebar-group-items')?.style.setProperty('display', 'none');
                    group.querySelector('.fi-sidebar-group-collapse-button')?.classList.add('rotate-180');
                });

                // حفظ التغيير عند الضغط
                document.querySelectorAll('.fi-sidebar-group-collapse-button').forEach((button) => {
                    button.addEventListener('click', (e) => {
                        const group = button.closest('.fi-sidebar-group');
                        const label = group?.dataset?.groupLabel;

                        if (!label) return;

                        const items = group.querySelector('.fi-sidebar-group-items');
                        const isCollapsed = items.style.display === 'none';

                        items.style.display = isCollapsed ? '' : 'none';
                        button.classList.toggle('rotate-180', !isCollapsed);

                        let updatedGroups = JSON.parse(localStorage.getItem('collapsedGroups')) || [];

                        if (!isCollapsed) {
                            updatedGroups.push(label);
                        } else {
                            updatedGroups = updatedGroups.filter((item) => item !== label);
                        }

                        localStorage.setItem('collapsedGroups', JSON.stringify(updatedGroups));
                    });
                });
            });
        </script>

        {{ FilamentView::renderHook(PanelsRenderHook::SIDEBAR_NAV_END) }}
    </nav>

    {{ FilamentView::renderHook(PanelsRenderHook::SIDEBAR_FOOTER) }}
</aside>