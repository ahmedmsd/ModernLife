<?php

namespace App\Enums;

enum ProductionRequestStatus: string
{
    case CREATED = 'created';
    case DRAFT = 'draft';
    case SUBMITTED = 'submitted';
    case UNDER_REVIEW = 'under_review';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case DELETED = 'deleted';

    public function label(): string
    {
        return match ($this) {
            self::CREATED => 'تم الإنشاء',
            self::DRAFT => 'مسودة',
            self::SUBMITTED => 'تم الإرسال',
            self::UNDER_REVIEW => 'قيد المراجعة',
            self::APPROVED => 'مقبول',
            self::REJECTED => 'مرفوض',
            self::DELETED => 'محذوف',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::CREATED => 'gray',
            self::DRAFT => 'gray',
            self::SUBMITTED => 'blue',
            self::UNDER_REVIEW => 'yellow',
            self::APPROVED => 'success',
            self::REJECTED => 'danger',
            self::DELETED => 'danger',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn($case) => [
            $case->value => $case->label(),
        ])->toArray();
    }
}
