<x-filament::page>
    <x-filament::form wire:submit="save">
        {{ $this->form }}

        <x-slot name="footer">
            <x-filament::button type="submit" color="success">
                حفظ المهام
            </x-filament::button>
        </x-slot>
    </x-filament::form>

    <div class="mt-10">
        <x-filament::section>
            <x-slot name="heading">المهام الحالية</x-slot>

            <x-filament::table>
                <x-slot name="head">
                    <tr>
                        <th class="px-4 py-2">القسم</th>
                        <th class="px-4 py-2">الموظف</th>
                        <th class="px-4 py-2">تاريخ التسليم</th>
                        <th class="px-4 py-2">الحالة</th>
                    </tr>
                </x-slot>

                <x-slot name="body">
                    @forelse($record->tasks as $task)
                        @php $index = $loop->index; @endphp
                        <tr class="border-t">
                            <td class="px-4 py-2">{{ $task->department->name ?? '-' }}</td>
                            <td class="px-4 py-2">{{ $task->employee->name ?? '-' }}</td>
                            <td class="px-4 py-2">{{ $task->due_date?->format('Y-m-d') }}</td>
                            <td class="px-4 py-2">
                                <select
                                    wire:model="tasks.{{ $index }}.status"
                                    class="text-sm rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
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
                </x-slot>
            </x-filament::table>
        </x-filament::section>
    </div>
</x-filament::page>
