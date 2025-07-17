<?php

namespace App\Enums;

enum ProductionRequestStatus: string
{
    case Draft = 'draft';
    case SentToManager = 'sent_to_manager';
    case UnderReview = 'under_review';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'مسودة',
            self::SentToManager => 'تم الإرسال للمدير',
            self::UnderReview => 'قيد المراجعة',
            self::Approved => 'مقبول',
            self::Rejected => 'مرفوض',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::SentToManager => 'blue',
            self::UnderReview => 'yellow',
            self::Approved => 'success',
            self::Rejected => 'danger',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($case) => [
            $case->value => $case->label()
        ])->toArray();
    }
}
