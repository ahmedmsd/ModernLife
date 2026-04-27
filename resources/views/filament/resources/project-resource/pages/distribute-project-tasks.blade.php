<x-filament-panels::page>
    <x-filament-panels::form wire:submit="save">
        {{ $this->form }}

        <x-slot name="footer">
            <x-filament::button type="submit" color="success">
                حفظ المهام
            </x-filament::button>
        </x-slot>
    </x-filament-panels::form>

    <div class="mt-10">
        <x-filament::section>
            <x-slot name="heading">المهام الحالية</x-slot>

            <table class="w-full text-start divide-y divide-gray-200 dark:divide-white/5">
                <thead>
                    <tr class="bg-gray-50 dark:bg-white/5">
                        <th class="px-4 py-2 text-start text-sm font-semibold">القسم</th>
                        <th class="px-4 py-2 text-start text-sm font-semibold">الموظف</th>
                        <th class="px-4 py-2 text-start text-sm font-semibold">تاريخ التسليم</th>
                        <th class="px-4 py-2 text-start text-sm font-semibold">الحالة</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                    @forelse($record->tasks as $task)
                        @php $index = $loop->index; @endphp
                        <tr class="border-t">
                            <td class="px-4 py-2 text-sm">{{ $task->department->name ?? '-' }}</td>
                            <td class="px-4 py-2 text-sm">{{ $task->employee->name ?? '-' }}</td>
                            <td class="px-4 py-2 text-sm">{{ $task->due_date?->format('Y-m-d') }}</td>
                            <td class="px-4 py-2 text-sm">
                                <select
                                    wire:model="tasks.{{ $index }}.status"
                                    class="text-sm rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-white/5 dark:border-white/10">
                                    <option value="assigned">موزعة</option>
                                    <option value="in_progress">قيد التنفيذ</option>
                                    <option value="completed">مكتملة</option>
                                </select>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-gray-500">لا توجد مهام حالياً.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-filament::section>
    </div>
</x-filament-panels::page>

