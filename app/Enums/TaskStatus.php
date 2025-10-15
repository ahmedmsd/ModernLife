<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Pending            = 'pending';
    case Assigned           = 'assigned';
    case Received           = 'received';
    case UnderReview        = 'under_review';
    case Approved           = 'approved';
    case Rejected           = 'rejected';
    case InProgress         = 'in_progress';
    case MaterialsWait      = 'materials_wait';
    case MaterialsPrep      = 'materials_prep';
    case MaterialsDone      = 'materials_done';
    case OnHold             = 'on_hold';
    case Completed          = 'completed';
    case Cancelled          = 'cancelled';
    case WaitingProduction  = 'waiting_production';
    case Acknowledged = 'acknowledged';
    case Rework       = 'rework';
    public function ar(): string
    {
        return match ($this) {
            self::Pending           => 'قيد الإنشاء',
            self::Assigned          => 'مُسندة',
            self::Received          => 'مستلمة',
            self::UnderReview       => 'قيد المراجعة',
            self::Approved          => 'معتمدة',
            self::Rejected          => 'مرفوضة',
            self::InProgress        => 'قيد التنفيذ',
            self::Acknowledged => 'تأكيد الاستلام',
            self::Rework       => 'إعادة عمل',
            self::MaterialsWait     => 'انتظار خامات',
            self::MaterialsPrep     => 'تحضير خامات',
            self::MaterialsDone     => 'خامات مكتملة',
            self::OnHold            => 'موقوفة مؤقتًا',
            self::Completed         => 'مكتملة',
            self::Cancelled         => 'ملغاة',
            self::WaitingProduction => 'بانتظار التصنيع',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending           => 'gray',
            self::Assigned          => 'primary',
            self::Received          => 'info',
            self::UnderReview       => 'purple',
            self::Approved          => 'success',
            self::Rejected          => 'danger',
            self::InProgress        => 'warning',
            self::MaterialsWait     => 'warning',
            self::MaterialsPrep     => 'info',
            self::MaterialsDone     => 'success',
            self::OnHold            => 'gray',
            self::Completed         => 'success',
            self::Cancelled         => 'danger',
            self::WaitingProduction => 'secondary',
            self::Acknowledged      => 'info',
            self::Rework            => 'warning',
        };
    }

    public static function fromScalar(null|string|\BackedEnum $v): ?self
    {
        if ($v instanceof \BackedEnum) $v = $v->value;
        if (!is_string($v) || $v==='') return null;
        return self::tryFrom($v);
    }
}
