<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskLog extends Model
{
    protected $table = 'production_tasks_log';

    protected $fillable = ['task_id', 'type', 'data', 'causer_id', 'happened_at', 'note'];

    protected $casts = [
        'data'        => 'array',
        'happened_at' => 'datetime',
    ];

    /* ============ سكوبات ============ */

    public function scopeCore(Builder $q): Builder
    {
        return $q->whereIn('type', [
            'created',
            'status_changed',

            'sent_to_showroom',
            'sent_to_factory',
            'sent_to_department',
            'sent_to_purchasing',
            'sent_to_quality',
            'sent_to_install',

            'purchasing_ack',
            'manufacturing_started',
            'manufacturing_finished',
            'installation_started',
            'installation_finished',
            'qa_approved_manufacturing',
            'qa_rejected_manufacturing',
            'qa_approved_installation',
            'qa_rejected_installation',

            'materials_provided_note',
            'materials_received_ok',
            'materials_received_partial',
            'materials_received_issue',
            'planning_hint_set',

            'qa_ack_manufacturing',
            'qa_ack_installation',
            'sent_back_to_install',
            'owner_receive_note',

            'client_receipt_uploaded',
            'task_completed',
            'project_completed',
            'production_request_closed',
        ]);
    }

    public function scopeOrderForTimeline(Builder $q): Builder
    {
        return $q->orderByRaw('COALESCE(happened_at, created_at) ASC')->orderBy('id');
    }

    /* ============ Accessors ============ */

    protected function typeLabel(): Attribute
    {
        return Attribute::get(function () {
            // 1) جرّب الترجمة من lang
            $key = "tasks.logs.types.{$this->type}";
            $translated = __($key);
            if ($translated !== $key) {
                return $translated;
            }

            // 2) fallback عربي في حال مافي مفتاح ترجمة
            $fallback = [
                // تأسيسية
                'created'                     => 'تم إنشاء المهمة',

                // تغييرات الحالة
                'status_changed'              => 'تغيير حالة المهمة',

                // إرسال الملكية
                'sent_to_showroom'            => 'تم الإرسال إلى المعرض',
                'sent_to_factory'             => 'تم الإرسال إلى التصنيع',
                'sent_to_department'          => 'تم الإرسال إلى مدير القسم',
                'sent_to_purchasing'          => 'تم الإرسال إلى المشتريات',
                'sent_to_quality'             => 'تم الإرسال إلى الجودة',
                'sent_to_install'             => 'تم الإرسال إلى التركيب',

                // أحداث تشغيلية
                'purchasing_ack'              => 'تأكيد استلام المشتريات',
                'manufacturing_started'       => 'بدء التصنيع',
                'manufacturing_finished'      => 'إنهاء التصنيع',
                'installation_started'        => 'بدء التركيب',
                'installation_finished'       => 'إنهاء التركيب',

                // جودة — بعد التصنيع
                'qa_approved_manufacturing'   => 'اعتماد الجودة (بعد التصنيع)',
                'qa_rejected_manufacturing'   => 'رفض الجودة (بعد التصنيع)',

                // جودة — بعد التركيب
                'qa_approved_installation'    => 'اعتماد الجودة (بعد التركيب)',
                'qa_rejected_installation'    => 'رفض الجودة (بعد التركيب)',

                // ملاحظات
                'owner_receive_note'          => 'ملاحظة المالك/المستخدم',

                // إكمال
                'client_receipt_uploaded'     => 'تم رفع سند استلام العميل',
                'task_completed'              => 'اكتملت المهمة',
                'project_completed'           => 'اكتمل المشروع',
                'production_request_closed'   => 'تم إغلاق طلب الإنتاج',
            ];

            return $fallback[$this->type] ?? $this->type; // آخر fallback
        });
    }

    public function statusLabel()
    {
        $status = data_get($this->data, 'status');
        if (!$status) return null;
        $key = "tasks.statuses.$status";
        $translated = __($key);
        return $translated !== $key ? $translated : $status;
    }

    /* ============ علاقات ============ */

    public function task() : BelongsTo
    {
        return $this->belongsTo(ProductionTask::class, 'task_id', 'id');
    }

    public function causer() : BelongsTo
    {
        return $this->belongsTo(User::class, 'causer_id');
    }
}
