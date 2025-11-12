@php
    $raw = (isset($getState) && is_callable($getState))
        ? $getState()
        : ((isset($state) && is_callable($state)) ? $state() : ($state ?? []));

    $logs = $raw instanceof \Illuminate\Support\Collection ? $raw : collect($raw);

    $colorFor = function (?string $type): string {
        return match ($type) {
            'qa_rejected_manufacturing', 'qa_rejected_installation','dept_reject_to_factory' => '#dc2626', // أحمر
            'completed', 'qa_approved_manufacturing', 'qa_approved_installation' => '#16a34a', // أخضر
            'materials_requested', 'installation_sent_to_qa', 'manufacturing_sent_to_qa' => '#d97706', // برتقالي
            default => '#2563eb', // أزرق
        };
    };

    $roleLabel = function (?string $r): string {
        return [
            'showroom_manager'     => 'مدير المعرض',
            'factory_manager'      => 'مدير المصنع',
            'department_manager'   => 'مدير القسم',
            'purchasing_manager'   => 'قسم المشتريات',
            'quality_manager'      => 'قسم الجودة',
            'installation_manager' => 'مدير التركيب',
        ][$r] ?? (string) $r;
    };

    $statusLabel = function (?string $s): string {
        if ($s === null) return '—';
        $s = strtolower($s);
        return [
            'draft'              => 'مسودة',
            'submitted'          => 'مرسل',
            'received'           => 'تم الاستلام',
            'pending'            => 'بالانتظار ',
            'waiting_production' => 'بانتظار بدء التصنيع',
            'in_progress'        => 'قيد التنفيذ',
            'materials_wait'     => 'بانتظار الخامات',
            'materials_prep'     => 'تجهيز الخامات',
            'materials_done'     => 'الخامات جاهزة',
            'under_review'       => 'قيد المراجعة',
            'approved'           => 'معتمد',
            'rejected'           => 'مرفوض',
            'on_hold'            => 'موقوف مؤقتاً',
            'rework'             => 'مطلوب إعادة تنفيذ',
            'completed'          => 'مكتملة',
            'cancelled'          => 'ملغاة',
            'assigned_changed'      => 'تغيير الإسناد',
            'ownership_changed'     => 'تغيير المِلكية',
            'ownership_received'    => 'تأكيد استلام المِلكية',

            'department_changed'    => 'تغيير القسم',
            'due_changed'           => 'تغيير تاريخ التسليم',
            'plan_set'              => 'تحديد المواعيد المخططة',

            'returned_to_factory' => 'مُعاد لمدير المصنع',
            'manufacturing_sent_to_qa' => 'إرسال التصنيع للجودة',
            'installation_sent_to_qa'  => 'إرسال التركيب للجودة',

            'materials_request_opened'   => 'فتح طلب خامات',
            'materials_request_rejected' => 'رفض طلب الخامات',
            'materials_request_cancelled'=> 'إلغاء طلب الخامات',
            'purchasing_ack_hint'        => 'إشارة استلام المشتريات',
        ][$s] ?? $s;
    };

    $fmt = function ($v) {
        if (!$v) return '—';
        try { return \Illuminate\Support\Carbon::parse($v)->format('Y-m-d H:i'); } catch (\Throwable $e) { return (string) $v; }
    };
@endphp

@if ($logs->isEmpty())
    <div style="opacity:.75">لا يوجد سجل حركات حتى الآن.</div>
@else
    <div style="position:relative;padding-left:1.25rem;">
        <div style="position:absolute;left:6px;top:0;bottom:0;width:2px;background:#e5e7eb;"></div>

        @foreach ($logs as $log)
            @php
                $type = $log->type ?? null;
                $clr  = $colorFor($type);

                $when = $log->happened_at
                    ? \Illuminate\Support\Carbon::parse($log->happened_at)
                    : ($log->created_at ? \Illuminate\Support\Carbon::parse($log->created_at) : null);

                $by = optional($log->causer)->name
                    ?? optional($log->causer)->full_name
                    ?? optional($log->causer)->full_name_en
                    ?? optional($log->causer)->full_name_ar
                    ?? '—';

                $note = trim((string)($log->note ?? ''));
                $data = is_array($log->data) ? $log->data : (json_decode($log->data ?? '[]', true) ?: []);
            @endphp

            <div style="display:flex;gap:.75rem;margin-bottom:1rem;">
                <div style="width:14px;height:14px;border-radius:9999px;background:{{ $clr }};position:relative;top:.25rem;"></div>

                <div style="flex:1;background:#fff;border:1px solid #e5e7eb;border-radius:.75rem;padding:.75rem 1rem;box-shadow:0 1px 2px rgba(0,0,0,0.03)">
                    <div style="display:flex;flex-wrap:wrap;gap:.5rem;align-items:center;margin-bottom:.35rem">
                        <strong style="color:#111827">{{ $log->typeLabel }}</strong>
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

                    @php
                        $showRows = [];
                        // 1) تغيير الحالة
                        if ($type === 'status_changed') {
                            $from = $data['from'] ?? null;
                            $to   = $data['to']   ?? null;
                            $showRows[] = 'من الحالة: <b>'.$statusLabel($from).'</b> &nbsp; → &nbsp; إلى: <b>'.$statusLabel($to).'</b>';
                            if ($r = data_get($data, 'reason')) {
                                $showRows[] = 'السبب: <b>'.e($r).'</b>';
                            }
                        }

                        // 2) تغيير الملكية
                        if ($type === 'ownership_changed') {
                            $fromR = data_get($data, 'from.role');
                            $fromU = data_get($data, 'from.user');
                            $toR   = data_get($data, 'to.role');
                            $toU   = data_get($data, 'to.user');
                            $showRows[] = 'من: <b>'.$roleLabel($fromR).'</b> (User ID: '.($fromU ?? '—').') &nbsp; → &nbsp; إلى: <b>'.$roleLabel($toR).'</b> (User ID: '.($toU ?? '—').')';
                        }

                        // 3) إرسال إلى جهة (sent_to_*)
                        if (str_starts_with((string)$type, 'sent_to_')) {
                            $toR = data_get($data, 'to') ?? data_get($data, 'role') ?? null;
                            $toU = data_get($data, 'user');
                            $showRows[] = 'تم الإرسال إلى: <b>'.$roleLabel($toR).'</b>' . (!blank($toU) ? ' (User ID: '.$toU.')' : '');
                        }

                        // 4) تغيّر القسم
                        if ($type === 'department_changed') {
                            $from = $data['from'] ?? null;
                            $to   = $data['to']   ?? null;
                            // مبدئياً نعرض الـ ID، ولو لاحقًا خزّنا الاسم في data نعرضه
                            $showRows[] = 'القسم: من <b>'.($data['from_name'] ?? $from ?? '—').'</b> → إلى <b>'.($data['to_name'] ?? $to ?? '—').'</b>';
                        }

                        // 5) تغيّر تاريخ التسليم
                        if ($type === 'due_changed') {
                            $from = $data['from'] ?? null;
                            $to   = $data['to']   ?? null;
                            $showRows[] = 'تاريخ التسليم: من <b>'.$fmt($from).'</b> → إلى <b>'.$fmt($to).'</b>';
                        }

                        // 6) ضبط المواعيد المخططة
                        if ($type === 'plan_set' || $type === 'planning_hint_set' || $type === 'plan_updated') {
                            $showRows[] = 'بداية مخططة: <b>'.$fmt(data_get($data, 'planned_start_at')).'</b>';
                            $showRows[] = 'نهاية مخططة: <b>'.$fmt(data_get($data, 'planned_end_at')).'</b>';
                            if (data_get($data, 'planned_install_at')) {
                                $showRows[] = 'تركيب مخطط: <b>'.$fmt(data_get($data, 'planned_install_at')).'</b>';
                            }
                        }

                        // 7) الجودة (إقرار/رفض/استلام)
                        if (in_array($type, ['qa_approved_manufacturing','qa_rejected_manufacturing','qa_ack_manufacturing','qa_approved_installation','qa_rejected_installation','qa_ack_installation'], true)) {
                            if ($s = data_get($data, 'status')) {
                                $showRows[] = 'الحالة بعد القرار: <b>'.$statusLabel($s).'</b>';
                            }
                            if ($r = data_get($data, 'reason')) {
                                $showRows[] = 'السبب: <b>'.e($r).'</b>';
                            }
                        }

                        if (in_array($type, ['dept_reject_to_factory'], true)) {
                            if ($s = data_get($data, 'status')) {
                                $showRows[] = 'الحالة بعد القرار: <b>'.$statusLabel($s).'</b>';
                            }
                            if ($r = data_get($data, 'reason')) {
                                $showRows[] = 'السبب: <b>'.e($r).'</b>';
                            }
                        }


                        // 8) المشتريات / الخامات
                        if (in_array($type, ['materials_request_opened','materials_received_ok','materials_received_partial','materials_request_cancelled','purchasing_ack','purchasing_ack_hint','materials_provided_note'], true)) {
                            if ($mr = data_get($data, 'mr_id') ?? data_get($data, 'mr.id') ?? data_get($data, 'mr')) {
                                $showRows[] = 'طلب الخامات #: <b>'.$mr.'</b>';
                            }
                            if ($by = data_get($data, 'by')) {
                                $showRows[] = 'تمت بواسطة: <b>'.(is_array($by)? (data_get($by,'name') ?? data_get($by,'id')) : $by).'</b>';
                            }
                            if ($qty = data_get($data, 'qty')) {
                                $showRows[] = 'الكمية: <b>'.$qty.'</b>';
                            }
                            if ($allow = data_get($data, 'allow_continue')) {
                                $showRows[] = 'السماح بالاستمرار مع النقص: <b>'.($allow ? 'نعم' : 'لا').'</b>';
                            }
                        }
                    @endphp

                    @if (!empty($showRows))
                        <div class="rounded-md border p-3 bg-gray-50 dark:bg-gray-800 text-[.92rem] leading-7">
                            @foreach ($showRows as $row)
                                <div>{!! $row !!}</div>
                            @endforeach
                        </div>
                    @endif

                    @php
                        $rawNote = $log->note
                        ?? data_get($data, 'note')
                        ?? data_get($data, 'reason')
                        ?? data_get($data, 'reason_factory')
                        ?? data_get($data, 'reason_showroom');

                        $noteHtml = e($rawNote);

                        $noteHtml = preg_replace(
                            '~(https?://[^\s<]+)~i',
                            '<a href="$1" target="_blank" rel="noopener" class="underline">$1</a>',
                            $noteHtml
                        );

                        $noteHtml = nl2br($noteHtml);
                    @endphp

                    @if (filled($rawNote))
                        <div class="rounded-md border p-3 mt-2 bg-gray-50 dark:bg-gray-800">
                            <div class="text-xs opacity-70 mb-1">ملاحظة صاحب الحركة:</div>
                            <div class="font-medium leading-relaxed">{!! $noteHtml !!}</div>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif
