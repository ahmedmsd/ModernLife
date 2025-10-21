{{-- resources/views/filament/pages/assigned-tasks.blade.php --}}
<x-filament-panels::page>
    <x-slot name="subheading">
        <div class="text-sm text-gray-500">
            مرحباً {{ auth()->user()->name ?? '' }} — هنا تجد كل المهام المسندة إليك مع إجراءات حسب حالة كل مهمة.
        </div>
    </x-slot>

    {{ $this->table }}
</x-filament-panels::page>
