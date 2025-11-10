<x-filament::page class="space-y-6">

    {{-- فلاتر النطاق الزمني --}}
    <x-filament::section>
        <x-slot name="heading">نطاق العرض</x-slot>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div>
                <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">من تاريخ</label>
                <input
                    type="date"
                    wire:model.defer="from"
                    class="fi-input block w-full rounded-lg border border-gray-300 dark:border-gray-700
                           bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 shadow-sm
                           focus:ring-2 focus:ring-primary-600 focus:border-primary-600 p-2.5"
                />
            </div>

            <div>
                <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">إلى تاريخ</label>
                <input
                    type="date"
                    wire:model.defer="to"
                    class="fi-input block w-full rounded-lg border border-gray-300 dark:border-gray-700
                           bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 shadow-sm
                           focus:ring-2 focus:ring-primary-600 focus:border-primary-600 p-2.5"
                />
            </div>

            <div class="flex items-end">
                <x-filament::button wire:click="$refresh" icon="heroicon-o-magnifying-glass">
                    تطبيق
                </x-filament::button>
            </div>
        </div>
    </x-filament::section>

    {{-- جدول المواعيد مجمّع حسب اليوم --}}
    @php $groups = $this->groupedByDate(); @endphp

    @forelse ($groups as $date => $requests)
        <x-filament::section collapsible>
            <x-slot name="heading">
                مواعيد الصيانة {{ \Illuminate\Support\Carbon::parse($date)->translatedFormat('Y-m-d (l)') }}
            </x-slot>

            <div class="overflow-x-auto rounded-xl border bg-white/80 dark:bg-gray-900/70">
                <table class="w-full text-sm rtl:text-right">
                    <thead class="bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                    <tr>
                        <th class="px-3 py-2 font-semibold">#</th>
                        <th class="px-3 py-2 font-semibold">المشروع</th>
                        <th class="px-3 py-2 font-semibold">العميل</th>
                        <th class="px-3 py-2 font-semibold">المعرض</th>
                        <th class="px-3 py-2 font-semibold">الحالة</th>
                        <th class="px-3 py-2 font-semibold">تاريخ الصيانة</th>
                        <th class="px-3 py-2 font-semibold">إجراءات</th>
                    </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-800 dark:text-gray-200">
                    @foreach ($requests as $i => $r)
                        @php
                            $canEdit = $this->canReschedule($r);

                            $statusLabel = match ((string) $r->status) {
                                'new'         => 'طلب جديد',
                                'in_progress' => 'قيد التنفيذ',
                                'completed'   => 'مكتمل',
                                'cancelled'   => 'ملغي',
                                default       => (string) $r->status,
                            };

                            $showroomName = $r->showroom?->name
                                ?? '—';

                            $clientName = $r->client?->client_name ?? '—';
                            $projName   = $r->project?->project_name ?? "طلب صيانة #{$r->id}";

                            $maintenanceDate = $r->expected_start_at
                                ?? $r->actual_start_at
                                ?? '-';
                        @endphp

                        <tr class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-900 dark:even:bg-gray-800">
                            <td class="px-3 py-2">{{ $i + 1 }}</td>

                            <td class="px-3 py-2">
                                {{ $projName }}
                            </td>

                            <td class="px-3 py-2">{{ $clientName }}</td>
                            <td class="px-3 py-2">{{ $showroomName }}</td>
                            <td class="px-3 py-2">{{ $statusLabel }}</td>

                            <td class="px-3 py-2">
                                {{ optional($maintenanceDate)->format('Y-m-d') ?? '—' }}
                            </td>

                            <td class="px-3 py-2">
                                <div class="flex items-center gap-2">
                                    @if ($canEdit)
                                        <x-filament::button
                                            color="warning"
                                            icon="heroicon-o-calendar"
                                            wire:click="openRescheduleModal({{ $r->id }})"
                                        >
                                            تعديل الموعد
                                        </x-filament::button>
                                    @else
                                        <x-filament::badge color="gray" size="sm">عرض فقط</x-filament::badge>
                                    @endif

                                    <a class="text-primary-600 underline"
                                       href="{{ class_exists(\App\Filament\Resources\MaintenanceRequestResource::class)
                                                    ? \App\Filament\Resources\MaintenanceRequestResource::getUrl('view', ['record' => $r])
                                                    : '#' }}"
                                       target="_blank"
                                    >
                                        تفاصيل الطلب
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    @empty
        <x-filament::section>
            <x-slot name="heading">لا توجد مواعيد صيانة في الفترة المحددة</x-slot>
            <p class="text-gray-500 text-sm">
                يمكنك تغيير نطاق التاريخ من الأعلى لعرض مواعيد أخرى.
            </p>
        </x-filament::section>
    @endforelse

    {{-- مودال تعديل موعد الصيانة --}}
    <x-filament::modal id="reschedule-modal" width="md">
        <x-slot name="heading">تعديل موعد الصيانة</x-slot>

        <div class="space-y-3">
            <div>
                <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                    تاريخ الصيانة الجديد
                </label>
                <input
                    type="date"
                    wire:model.defer="newMaintenanceDate"
                    class="fi-input block w-full rounded-lg border border-gray-300 dark:border-gray-700
                           bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 shadow-sm
                           focus:ring-2 focus:ring-primary-600 focus:border-primary-600 p-2.5"
                />
                @error('newMaintenanceDate')
                <p class="text-danger-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                    ملاحظة (اختياري)
                </label>
                <textarea
                    wire:model.defer="rescheduleNote"
                    rows="3"
                    class="fi-input block w-full rounded-lg border border-gray-300 dark:border-gray-700
                           bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 shadow-sm
                           focus:ring-2 focus:ring-primary-600 focus:border-primary-600 p-2.5"
                ></textarea>
            </div>
        </div>

        <x-slot name="footerActions">
            <x-filament::button
                color="gray"
                wire:click="$dispatch('close-modal', { id: 'reschedule-modal' })"
            >
                إلغاء
            </x-filament::button>

            <x-filament::button
                color="primary"
                wire:click="saveReschedule"
                icon="heroicon-o-check-circle"
            >
                حفظ
            </x-filament::button>
        </x-slot>
    </x-filament::modal>
</x-filament::page>
