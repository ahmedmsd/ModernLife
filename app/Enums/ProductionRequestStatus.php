<?php

namespace App\Enums;

enum ProductionRequestStatus: string
{
    case Created = 'created';
    case Draft = 'draft';
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Deleted = 'deleted';

    public function label(): string
    {
        return match ($this) {
            self::Created => 'تم الإنشاء',
            self::Draft => 'مسودة',
            self::Submitted => 'تم الإرسال',
            self::UnderReview => 'قيد المراجعة',
            self::Approved => 'مقبول',
            self::Rejected => 'مرفوض',
            self::Deleted => 'محذوف',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Created => 'gray',
            self::Draft => 'gray',
            self::Submitted => 'blue',
            self::UnderReview => 'yellow',
            self::Approved => 'success',
            self::Rejected => 'danger',
            self::Deleted => 'danger',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn($case) => [
            $case->value => $case->label(),
        ])->toArray();
    }
}
