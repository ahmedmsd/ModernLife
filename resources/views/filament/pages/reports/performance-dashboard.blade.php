<x-filament-panels::page>
    <x-filament::section :heading="__('معايير التقرير')" class="mb-6">
        {{ $this->form }}
    </x-filament::section>

    <x-filament-widgets::widgets
        :widgets="$this->getWidgets()"
        :columns="[
            'sm' => 1,
            'md' => 2,
            'xl' => 3,
        ]"
    />
</x-filament-panels::page>
