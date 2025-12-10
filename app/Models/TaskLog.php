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
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];


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
            $key = "tasks.logs.types.{$this->type}";
            $translated = __($key);
            if ($translated !== $key) {
                return $translated;
            }

            $fallback = [
                'created'                     => 'تم إنشاء المهمة',
                'status_changed'              => 'تغيير حالة المهمة',
                'assigned_to_dept'            => 'تم الإسناد للقسم',
                'assigned_to_dept_manager'    => 'تم الإسناد لمدير القسم',
                'assigned_to_department_manager' => 'تم الإسناد لمدير القسم',
                'assign_to_dept_manager_noop' => 'إسناد لمدير القسم (دون تغيير)',
                'owner_note_updated'          => 'تحديث ملاحظة المسؤول',
                
                'sent_to_showroom'            => 'تم الإرسال إلى المعرض',
                'sent_to_factory'             => 'تم الإرسال إلى التصنيع',
                'sent_to_department'          => 'تم الإرسال إلى القسم',
                'sent_to_purchasing'          => 'تم الإرسال إلى المشتريات',
                'sent_to_quality'             => 'تم الإرسال إلى الجودة',
                'sent_to_install'             => 'تم الإرسال إلى التركيب',

                'dept_acknowledged'           => 'تأكيد استلام القسم',
                'dept_acknowledge'            => 'تأكيد استلام القسم',
                'dept_reject_to_factory'      => 'رفض القسم وإعادة للمصنع',
                'resubmitted_to_dept_after_return' => 'إعادة إرسال للقسم (بعد الرفض)',

                'materials_request_opened'    => 'فتح طلب خامات',
                'materials_request_rejected'  => 'رفض طلب الخامات',
                'materials_request_cancelled' => 'إلغاء طلب الخامات',
                'materials_followup_opened'   => 'فتح طلب خامات تكميلي',
                'purchasing_ack'              => 'تأكيد استلام المشتريات', 
                'purchasing_ack_hint'         => 'إشارة استلام المشتريات',
                
                'materials_provided_note'     => 'توفير الخامات',
                'materials_received_ok'       => 'استلام الخامات (مكتمل)',
                'materials_received_partial'  => 'استلام الخامات (جزئي)',
                'materials_received_issue'    => 'مشكلة في استلام الخامات',
                'planning_hint_set'           => 'تحديد موعد مبدئي',

                'manufacturing_started'       => 'بدء التصنيع',
                'manufacturing_finished'      => 'إنهاء التصنيع',
                'manufacturing_ack_rework'    => 'تأكيد استلام التصنيع (إعادة عمل)',
                'manufacturing_sent_to_qa'    => 'إرسال التصنيع للجودة',
                'qa_ack_manufacturing'        => 'تأكيد استلام الجودة (للمصنع)',
                'qa_approved_manufacturing'   => 'اعتماد الجودة (بعد التصنيع)',
                'qa_rejected_manufacturing'   => 'رفض الجودة (بعد التصنيع)',
                'sent_back_to_manufacturing'  => 'إعادة للتصنيع',

                'install_acknowledged'        => 'تأكيد استلام التركيب',
                'install_ack_rework'          => 'تأكيد استلام التركيب (إعادة عمل)',
                'installation_started'        => 'بدء التركيب',
                'installation_finished'       => 'إنهاء التركيب',
                'installation_sent_to_qa'     => 'إرسال التركيب للجودة',
                'qa_ack_installation'         => 'تأكيد استلام الجودة (للتركيب)',
                'qa_approved_installation'    => 'اعتماد الجودة (بعد التركيب)',
                'qa_rejected_installation'    => 'رفض الجودة (بعد التركيب)',
                'sent_back_to_install'        => 'إعادة للتركيب',

                'owner_received'              => 'تأكيد استلام المالك',
                'ownership_received'          => 'تأكيد استلام المِلكية',
                'owner_receive_note'          => 'ملاحظة المالك/المستخدم',

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
