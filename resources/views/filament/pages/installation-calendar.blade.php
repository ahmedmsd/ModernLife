<x-filament::page>
    <x-filament::section>
        <x-slot name="heading">التقويم الشهري للتركيب (متوقّع)</x-slot>

        {{-- نلف التقويم داخل Alpine ليتوفر this.$wire --}}
        <div
            x-data="installCalendar()"
            x-init="init()"
            class="rounded-lg border bg-white dark:bg-gray-900 p-2"
        >
            {{-- مهم: wire:ignore حتى لا يعيد Livewire لمس DOM التقويم --}}
            <div x-ref="cal" id="install-cal" wire:ignore></div>
        </div>

        {{-- FullCalendar (CDN) --}}
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css">
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

        <script>
            // مكوّن Alpine: ينشئ التقويم ويتواصل مع Livewire عبر this.$wire
            function installCalendar() {
                return {
                    calendar: null,

                    init() {
                        const el = this.$refs.cal;

                        this.calendar = new FullCalendar.Calendar(el, {
                                initialView: 'dayGridMonth',
                                locale: 'ar',
                                firstDay: 6,
                                height: 'auto',
                                headerToolbar: { left: 'prev,next today', center: 'title', right: '' },
                                buttonText: { today: 'اليوم' },
                                dayMaxEventRows: true,

                                events: (info, success, failure) => {
                                this.$wire.fetchEvents(info.startStr, info.endStr)
                                    .then(events => success(events))
                                    .catch(err => failure(err));
                            },

                            eventClick: (info) => {
                            const id = parseInt(info.event.id, 10);
                            if (!isNaN(id)) {
                                this.$wire.loadTaskDetails(id);
                            }
                        },
                    });

                this.calendar.render();

                // عند تغيير الفلاتر من Livewire نعيد تحميل الأحداث
                window.addEventListener('refresh-install-calendar', () => this.calendar.refetchEvents());
            },
            };
            }
        </script>
    </x-filament::section>


    {{-- نافذة التفاصيل (Slide-over) --}}
    <x-filament::modal id="task-detail" width="4xl" slide-over>
        <x-slot name="header">
            <div class="flex items-center justify-between w-full">
                <div>
                    <div class="text-base font-bold">تفاصيل الموعد / المهمة</div>
                    <div class="text-xs text-gray-500">معلومات مرتبطة بموعد التركيب المتوقّع</div>
                </div>
            </div>
        </x-slot>

        <div class="space-y-4">
            {{-- سطر علوي مختصر --}}
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                <div class="text-sm">
                    <div class="font-semibold">
                        {{ $detail['task_title'] ?? '—' }}
                        <span class="text-gray-500">(#{{ $detail['task_id'] ?? '—' }})</span>
                    </div>
                    <div class="text-gray-500">
                        الحالة:
                        <span class="font-semibold">{{ $detail['status'] ?? '—' }}</span>
                    </div>
                </div>

                <div class="text-sm">
                    <div>التركيب (متوقع): <span class="font-semibold">{{ $detail['planned_install_at'] ?? '—' }}</span></div>
                    <div class="text-gray-500">
                        التصنيع (متوقع):
                        <span class="font-semibold">{{ $detail['planned_start_at'] ?? '—' }}</span>
                        <span class="mx-1">→</span>
                        <span class="font-semibold">{{ $detail['planned_end_at'] ?? '—' }}</span>
                    </div>
                </div>
            </div>

            {{-- تفاصيل الطلب/المشروع --}}
            <x-filament::section>
                <x-slot name="heading">الطلب / المشروع</x-slot>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                    <div><span class="text-gray-500">رقم الطلب:</span> <span class="font-semibold">{{ $detail['production_request'] ?? '—' }}</span></div>
                    <div><span class="text-gray-500">رقم المشروع:</span> <span class="font-semibold">{{ $detail['project_id'] ?? '—' }}</span></div>
                    <div class="md:col-span-2">
                        <span class="text-gray-500">اسم المشروع:</span> <span class="font-semibold">{{ $detail['project_name'] ?? '—' }}</span>
                    </div>
                    <div><span class="text-gray-500">العميل:</span> <span class="font-semibold">{{ $detail['client_name'] ?? '—' }}</span></div>
                    <div><span class="text-gray-500">القسم:</span> <span class="font-semibold">{{ $detail['department'] ?? '—' }}</span></div>
                    <div><span class="text-gray-500">المالك الحالي:</span> <span class="font-semibold">{{ $detail['owner_role'] ?? '—' }}</span></div>
                </dl>

                {{-- روابط سريعة --}}
                <div class="mt-3 flex flex-wrap gap-2">
                    @if (!empty($detail['links']['task']))
                        <a href="{{ $detail['links']['task'] }}" target="_blank" class="text-primary-600 underline">عرض المهمة</a>
                    @endif
                    @if (!empty($detail['links']['project']))
                        <a href="{{ $detail['links']['project'] }}" target="_blank" class="text-primary-600 underline">عرض المشروع</a>
                    @endif
                    @if (!empty($detail['links']['request']))
                        <a href="{{ $detail['links']['request'] }}" target="_blank" class="text-primary-600 underline">عرض طلب الإنتاج</a>
                    @endif
                </div>
            </x-filament::section>

            {{-- نظرة سريعة على طلبات الخامات التابعة للمهمة (أحدثها أولًا) --}}
            <x-filament::section>
                <x-slot name="heading">طلبات الخامات (أحدث أولًا)</x-slot>
                @php $mrs = $detail['materials'] ?? []; @endphp
                @if (empty($mrs))
                    <div class="text-sm text-gray-500">لا توجد طلبات خامات مرتبطة.</div>
                @else
                    <div class="overflow-x-auto rounded-md border bg-white/80 dark:bg-gray-900/70">
                        <table class="w-full text-sm rtl:text-right">
                            <thead class="bg-gray-100 dark:bg-gray-900">
                            <tr>
                                <th class="px-3 py-2 font-semibold">#</th>
                                <th class="px-3 py-2 font-semibold">الحالة</th>
                                <th class="px-3 py-2 font-semibold">موعد التوريد (متوقع)</th>
                                <th class="px-3 py-2 font-semibold">تم التوريد (فعلي)</th>
                                <th class="px-3 py-2 font-semibold">المرجع</th>
                                <th class="px-3 py-2 font-semibold">التكلفة</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach ($mrs as $m)
                                <tr class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-900 dark:even:bg-gray-800">
                                    <td class="px-3 py-2">{{ $m['id'] }}</td>
                                    <td class="px-3 py-2">{{ $m['status'] }}</td>
                                    <td class="px-3 py-2">{{ $m['expected'] }}</td>
                                    <td class="px-3 py-2">{{ $m['provided'] }}</td>
                                    <td class="px-3 py-2">{{ $m['po_number'] }}</td>
                                    <td class="px-3 py-2">{{ $m['actual_cost'] }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-filament::section>
        </div>

        <x-slot name="footer">
            <x-filament::button color="gray" x-on:click="$dispatch('close-modal', { id: 'task-detail' })">
                إغلاق
            </x-filament::button>
        </x-slot>
    </x-filament::modal>
</x-filament::page>
