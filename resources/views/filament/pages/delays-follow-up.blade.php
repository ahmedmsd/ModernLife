{{-- resources/views/filament/pages/delays-follow-up.blade.php --}}
<x-filament::page>
    <x-filament::tabs>
        <x-filament::tabs.item
            :active="$tab === 'tasks'"
            wire:click="$set('tab','tasks')"
        >
            تأخيرات المهام
        </x-filament::tabs.item>

        <x-filament::tabs.item
            :active="$tab === 'requests'"
            wire:click="$set('tab','requests')"
        >
            تأخيرات الطلبات
        </x-filament::tabs.item>
    </x-filament::tabs>

    <div class="mt-4">
        @if ($tab === 'tasks')
            @livewire(\App\Filament\Widgets\DelayedTasksTable::class, [], key('delayed-tasks'))
        @else
            @livewire(\App\Filament\Widgets\DelayedRequestsTable::class, [], key('delayed-requests'))
        @endif
    </div>
</x-filament::page>
