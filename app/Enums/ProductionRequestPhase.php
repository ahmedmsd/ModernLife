<?php

namespace App\Enums;

enum ProductionRequestPhase: string {
    case SalesIntake              = 'sales_intake';
    case ShowroomReview           = 'showroom_review';
    case FactoryIntake            = 'factory_intake';
    case DepartmentAssignment     = 'department_assignment';
    case Purchasing               = 'purchasing';
    case Manufacturing            = 'manufacturing';
    case QualityAfterManufacture  = 'quality_after_manufacture';
    case Installation             = 'installation';
    case QualityAfterInstallation = 'quality_after_installation';
    case Closed                   = 'closed';

    public function label(): string {
        return match($this) {
            self::SalesIntake => 'استلام المبيعات',
            self::ShowroomReview => 'مراجعة المعرض',
            self::FactoryIntake => 'استلام المصنع',
            self::DepartmentAssignment => 'إسناد القسم',
            self::Purchasing => 'المشتريات',
            self::Manufacturing => 'التصنيع',
            self::QualityAfterManufacture => 'مراجعة ما بعد التصنيع',
            self::Installation => 'التركيب',
            self::QualityAfterInstallation => 'مراجعة ما بعد التركيب',
            self::Closed => 'مغلق',
        };
    }
}
