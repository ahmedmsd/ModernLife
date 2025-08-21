<?php

namespace App\Enums;

enum PhaseStatus: string {
    case Pending       = 'pending';
    case Received      = 'received';
    case UnderReview   = 'under_review';
    case Approved      = 'approved';
    case Rejected      = 'rejected';
    case InProgress    = 'in_progress';
    case MaterialsWait = 'materials_wait';
    case MaterialsPrep = 'materials_prep';
    case MaterialsDone = 'materials_done';
    case OnHold        = 'on_hold';
    case Completed     = 'completed';
    case Cancelled     = 'cancelled';

    public function label(): string {
        return match($this) {
            self::Pending => 'بانتظار الاستلام',
            self::Received => 'تم الاستلام',
            self::UnderReview => 'قيد المراجعة',
            self::Approved => 'مُعتمد',
            self::Rejected => 'مرفوض',
            self::InProgress => 'قيد التنفيذ',
            self::MaterialsWait => 'بانتظار الخامات',
            self::MaterialsPrep => 'تجهيز الخامات',
            self::MaterialsDone => 'الخامات جاهزة',
            self::OnHold => 'متوقفة مؤقتًا',
            self::Completed => 'مكتملة',
            self::Cancelled => 'ملغاة',
        };
    }
}
