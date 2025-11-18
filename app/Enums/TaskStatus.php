<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Pending             = 'pending';
    case Assigned            = 'assigned';
    case Acknowledged        = 'acknowledged';
    case Received            = 'received';
    case InProgress          = 'in_progress';

    case MaterialsWait       = 'materials_wait';
    case MaterialsPrep       = 'materials_prep';
    case MaterialsDone       = 'materials_done';
    case MaterialsIssue      = 'materials_issue';

    case WaitingProduction   = 'waiting_production';

    case UnderReview         = 'under_review';
    case QaReviewManufacture = 'qa_review_manufacture';
    case QaReviewInstall     = 'qa_review_install';
    case Approved            = 'approved';
    case QaApproved          = 'qa_approved';

    case InstallInProgress   = 'install_in_progress';

    case Rework              = 'rework';
    case ReturnedToFactory   = 'returned_to_factory';

    case OnHold              = 'on_hold';
    case Completed           = 'completed';
    case Cancelled           = 'cancelled';
    case Rejected            = 'rejected';

    public function ar(): string
    {
        return match ($this) {
            self::Pending             => 'قيد الإنشاء',
            self::Assigned            => 'مُسندة',
            self::Acknowledged        => 'تأكيد الاستلام',
            self::Received            => 'مستلمة',
            self::InProgress          => 'قيد التنفيذ',

            self::MaterialsWait       => 'انتظار خامات',
            self::MaterialsPrep       => 'تحضير خامات',
            self::MaterialsDone       => 'خامات مكتملة',
            self::MaterialsIssue      => 'مشكلة خامات',

            self::WaitingProduction   => 'بانتظار التصنيع',

            self::UnderReview         => 'قيد المراجعة',
            self::QaReviewManufacture => 'مراجعة جودة بعد التصنيع',
            self::QaReviewInstall     => 'مراجعة جودة بعد التركيب',
            self::Approved            => 'معتمد قبل التركيب',
            self::QaApproved          => 'اعتماد الجودة النهائي',

            self::InstallInProgress   => 'تركيب جارٍ',

            self::Rework              => 'إعادة عمل',
            self::ReturnedToFactory   => 'معاد لمدير المصنع',

            self::OnHold              => 'موقوفة مؤقتًا',
            self::Completed           => 'مكتملة',
            self::Cancelled           => 'ملغاة',
            self::Rejected            => 'مرفوضة',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending             => 'gray',
            self::Assigned            => 'primary',
            self::Acknowledged        => 'info',
            self::Received            => 'info',
            self::InProgress          => 'warning',

            self::MaterialsWait       => 'warning',
            self::MaterialsPrep       => 'info',
            self::MaterialsDone       => 'success',
            self::MaterialsIssue      => 'danger',

            self::WaitingProduction   => 'info',

            self::UnderReview         => 'purple',
            self::QaReviewManufacture => 'purple',
            self::QaReviewInstall     => 'purple',
            self::Approved            => 'success',
            self::QaApproved          => 'success',

            self::InstallInProgress   => 'warning',

            self::Rework              => 'warning',
            self::ReturnedToFactory   => 'danger',

            self::OnHold              => 'gray',
            self::Completed           => 'success',
            self::Cancelled           => 'danger',
            self::Rejected            => 'danger',
        };
    }

    public static function fromScalar(null|string|\BackedEnum $v): ?self
    {
        if ($v instanceof \BackedEnum) $v = $v->value;
        if (!is_string($v) || $v === '') return null;
        return self::tryFrom($v);
    }
}
