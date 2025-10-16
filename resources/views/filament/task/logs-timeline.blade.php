@php
    $raw = (isset($getState) && is_callable($getState))
        ? $getState()
        : ((isset($state) && is_callable($state)) ? $state() : ($state ?? []));

    $logs = $raw instanceof \Illuminate\Support\Collection ? $raw : collect($raw);

    // لون لكل نوع حدث
    $colorFor = function (?string $type): string {
        return match ($type) {
            'qa_rejected_manufacturing', 'qa_rejected_installation' => '#dc2626', // أحمر
            'completed', 'qa_approved_manufacturing', 'qa_approved_installation' => '#16a34a', // أخضر
            'materials_requested', 'installation_sent_to_qa', 'manufacturing_sent_to_qa' => '#d97706', // برتقالي
            default => '#2563eb', // أزرق
        };
    };
@endphp

@if ($logs->isEmpty())
    <div style="opacity:.75">لا يوجد سجل حركات حتى الآن.</div>
@else
    <div style="position:relative;padding-left:1.25rem;">
        <div style="position:absolute;left:6px;top:0;bottom:0;width:2px;background:#e5e7eb;"></div>

        @foreach ($logs as $log)
            @php
                /** @var \App\Models\TaskLog $log */
                $type = $log->type ?? null;
                $clr  = $colorFor($type);

                $when = $log->happened_at
                    ? \Illuminate\Support\Carbon::parse($log->happened_at)
                    : ($log->created_at ? \Illuminate\Support\Carbon::parse($log->created_at) : null);

                // اسم المنفّذ من علاقة causer (إن وُجدت)
                $by = optional($log->causer)->name
                    ?? optional($log->causer)->full_name
                    ?? optional($log->causer)->full_name_en
                    ?? optional($log->causer)->full_name_ar
                    ?? '—';

                $note = trim((string)($log->note ?? ''));
            @endphp

            <div style="display:flex;gap:.75rem;margin-bottom:1rem;">
                <div style="width:14px;height:14px;border-radius:9999px;background:{{ $clr }};position:relative;top:.25rem;"></div>

                <div style="flex:1;background:#fff;border:1px solid #e5e7eb;border-radius:.75rem;padding:.75rem 1rem;box-shadow:0 1px 2px rgba(0,0,0,0.03)">
                    <div style="display:flex;flex-wrap:wrap;gap:.5rem;align-items:center;margin-bottom:.35rem">
                        {{-- تسمية نوع الحركة من Accessor --}}
                        <strong style="color:#111827">    {{ $log->typeLabel }}</strong>

                        <span style="font-size:.85rem;color:#6b7280">
                            @if($when)
                                {{ $when->format('Y-m-d H:i') }}
                                <span style="opacity:.8"> ({{ $when->diffForHumans() }})</span>
                            @else
                                —
                            @endif
                        </span>
                    </div>

                    <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:.35rem">
                        <span style="font-size:.9rem;color:#374151">
                            نفّذها: <span style="font-weight:600">{{ $by }}</span>
                        </span>

                        @if($log->status_label)
                            <span style="font-size:.85rem;color:#6b7280">
                                الحالة: <strong>{{ $log->status_label }}</strong>
                            </span>
                        @endif
                    </div>

                    @if(!blank($log->note) || filled(data_get($log->data, 'note')))
                        <div class="rounded-md border p-3 mt-2 bg-gray-50 dark:bg-gray-800">
                            <div class="text-xs opacity-70 mb-1">ملاحظة صاحب الحركة:</div>
                            <div class="font-medium leading-relaxed">
                                {{ $log->note ?? data_get($log->data, 'note') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif
