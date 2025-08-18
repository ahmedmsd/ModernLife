<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum TaskStatus: string implements HasLabel
{
    case Pending      = 'pending';       // قيد الإنشاء
    case Assigned     = 'assigned';      // مُسندة
    case Acknowledged = 'acknowledged';  // تأكيد الاستلام
    case InProgress   = 'in_progress';   // قيد التنفيذ
    case Blocked      = 'blocked';       // متوقفة مؤقتًا
    case UnderReview  = 'under_review';  // قيد المراجعة
    case Rework       = 'rework';        // إعادة عمل
    case Completed    = 'completed';     // مكتملة
    case Closed       = 'closed';        // مغلقة
    case Cancelled    = 'cancelled';     // ملغاة

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Pending      => 'قيد الإنشاء',
            self::Assigned     => 'مُسندة',
            self::Acknowledged => 'تأكيد الاستلام',
            self::InProgress   => 'قيد التنفيذ',
            self::Blocked      => 'متوقفة مؤقتًا',
            self::UnderReview  => 'قيد المراجعة',
            self::Rework       => 'إعادة عمل',
            self::Completed    => 'مكتملة',
            self::Closed       => 'مغلقة',
            self::Cancelled    => 'ملغاة',
        };
    }

    /** لاستخدامها في Select options بسهولة */
    public static function options(): array
    {
        $opts = [];
        foreach (self::cases() as $case) {
            $opts[$case->value] = $case->getLabel();
        }
        return $opts;
    }

    /** لون البادج في الجداول */
    public function color(): string
    {
        return match ($this) {
            self::Pending      => 'gray',
            self::Assigned     => 'info',
            self::Acknowledged => 'info',
            self::InProgress   => 'warning',
            self::Blocked      => 'danger',
            self::UnderReview  => 'purple',
            self::Rework       => 'orange',
            self::Completed    => 'success',
            self::Closed       => 'gray',
            self::Cancelled    => 'danger',
        };
    }

    /** أيقونة اختيارية */
    public function icon(): ?string
    {
        return match ($this) {
            self::Pending      => 'heroicon-m-clock',
            self::Assigned     => 'heroicon-m-user-plus',
            self::Acknowledged => 'heroicon-m-check-badge',
            self::InProgress   => 'heroicon-m-play',
            self::Blocked      => 'heroicon-m-no-symbol',
            self::UnderReview  => 'heroicon-m-eye',
            self::Rework       => 'heroicon-m-arrow-path',
            self::Completed    => 'heroicon-m-check-circle',
            self::Closed       => 'heroicon-m-lock-closed',
            self::Cancelled    => 'heroicon-m-x-circle',
        };
    }
}
