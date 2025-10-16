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

    /** الحركات الأساسية فقط (بدون التفاصيل الداخلية) */
    public function scopeCore(Builder $q): Builder
    {
        return $q->whereIn('type', [
            // تأسيسية
            'created',

            // تغييرات الحالة الأساسية
            'status_changed',

            // تحولات الملكية كإرسال (نكتفي بالـ sent_to_*)
            'sent_to_showroom',
            'sent_to_factory',
            'sent_to_department',
            'sent_to_purchasing',
            'sent_to_quality',
            'sent_to_install',

            // أحداث دومينية مهمة
            'purchasing_ack',          // استلام المشتريات
            'manufacturing_started',
            'manufacturing_finished',
            'installation_started',
            'installation_finished',
            'qa_approved_manufacturing',
            'qa_rejected_manufacturing',
            'qa_approved_installation',
            'qa_rejected_installation',

            // ملاحظات المالك/المستخدم
            'owner_receive_note',

            // إكمال المهمة/مستند العميل
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
            return __($key) !== $key
                ? __($key)
                : str($this->type)->replace('_', ' ')->title();
        });
    }

    public function statusLabel(): array|\Illuminate\Contracts\Translation\Translator|\Illuminate\Foundation\Application|string|null
    {
        $status = data_get($this->data, 'status');
        if (!$status) return null;
        $key = "tasks.statuses.$status";
        return __($key) !== $key ? __($key) : $status;
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
