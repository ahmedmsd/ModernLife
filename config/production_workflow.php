<?php

use App\Enums\{ProductionRequestPhase as Phase, PhaseStatus as S};

return [

    /*
    |--------------------------------------------------------------------------
    | مالك المرحلة (دور المسؤول الحالي)
    |--------------------------------------------------------------------------
    */
    'phase_owner' => [
        Phase::SalesIntake->value              => 'sales',
        Phase::ShowroomReview->value           => 'showroom_manager',
        Phase::FactoryIntake->value            => 'factory_manager',
        Phase::DepartmentAssignment->value     => 'factory_manager',
        Phase::Purchasing->value               => 'purchasing_manager',
        Phase::Manufacturing->value            => 'department_manager',
        Phase::QualityAfterManufacture->value  => 'quality_manager',
        Phase::Installation->value             => 'installation_manager',
        Phase::QualityAfterInstallation->value => 'quality_manager',
        Phase::Closed->value                   => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | أزرار الإجراءات (Actions)
    | - كل عنصر يحدد: متى يظهر، لمن يظهر (roles)، وما الانتقال الذي ينفذه.
    | - الدوران admin/super-admin يمتلكان صلاحية تنفيذ كل الإجراءات.
    | - role = "*owner*" يعني: نفس الدور المكتوب في current_owner_role للسجل.
    |--------------------------------------------------------------------------
    */
    'actions' => [

        // عامة
        [
            'key'   => 'receive',
            'label' => 'تأكيد الاستلام',
            'icon'  => 'heroicon-o-hand-thumb-up',
            'color' => 'success',
            'type'  => 'receive',              // ينادي workflow->markReceived()
            'roles' => ['*owner*'],
            'when'  => [
                ['status' => S::Pending->value],
            ],
        ],
        [
            'key'   => 'start_review',
            'label' => 'بدء المراجعة',
            'icon'  => 'heroicon-o-play',
            'color' => 'primary',
            'type'  => 'status',               // تغيير حالة فقط ضمن نفس المرحلة
            'to'    => ['status' => S::UnderReview->value],
            'roles' => ['*owner*'],
            'when'  => [
                ['status' => S::Received->value],
            ],
        ],
        [
            'key'   => 'on_hold',
            'label' => 'إيقاف مؤقت',
            'icon'  => 'heroicon-o-pause-circle',
            'color' => 'warning',
            'type'  => 'status',
            'to'    => ['status' => S::OnHold->value],
            'roles' => ['*owner*'],
            'when'  => [
                ['status_not_in' => [S::Completed->value, S::Cancelled->value]],
            ],
        ],
        [
            'key'   => 'resume',
            'label' => 'استئناف',
            'icon'  => 'heroicon-o-play-circle',
            'color' => 'success',
            'type'  => 'status',
            'to'    => ['status' => S::UnderReview->value],
            'roles' => ['*owner*'],
            'when'  => [
                ['status' => S::OnHold->value],
            ],
        ],
        [
            'key'   => 'reject',
            'label' => 'رفض',
            'icon'  => 'heroicon-o-x-circle',
            'color' => 'danger',
            'type'  => 'reject',               // يطلب سبب ويضبط status=rejected
            'roles' => ['*owner*'],
            'when'  => [
                ['status_in' => [S::UnderReview->value, S::InProgress->value]],
            ],
        ],

        // غير مباشر: إرسال من المعرض إلى المصنع
        [
            'key'   => 'send_to_factory',
            'label' => 'إرسال إلى مدير المصنع',
            'icon'  => 'heroicon-o-paper-airplane',
            'color' => 'info',
            'type'  => 'transition',           // انتقال إلى مرحلة جديدة
            'to'    => [
                'phase'  => Phase::FactoryIntake->value,
                'status' => S::Pending->value,
                'owner'  => 'factory_manager',
                'touch_sent' => true,
            ],
            'roles' => ['showroom_manager'],
            'when'  => [
                ['phase' => Phase::ShowroomReview->value, 'status' => S::Approved->value],
            ],
        ],

        // مباشر/غير مباشر: خطوات المصنع
        [
            'key'   => 'approve_factory',
            'label' => 'اعتماد المصنع',
            'icon'  => 'heroicon-o-check-badge',
            'color' => 'success',
            'type'  => 'status',
            'to'    => ['status' => S::Approved->value],
            'roles' => ['factory_manager'],
            'when'  => [
                ['phase' => Phase::FactoryIntake->value, 'status' => S::UnderReview->value],
            ],
        ],
        [
            'key'   => 'send_to_department',
            'label' => 'إرسال لمدير القسم',
            'icon'  => 'heroicon-o-user-group',
            'color' => 'primary',
            'type'  => 'transition',
            'to'    => [
                'phase'  => Phase::DepartmentAssignment->value,
                'status' => S::Pending->value,
                'owner'  => 'factory_manager',     // يبقى المالك حتى يُسنّد القسم فعليًا (اختياري)
                'touch_sent' => true,
            ],
            'roles' => ['factory_manager'],
            'when'  => [
                ['phase' => Phase::FactoryIntake->value, 'status' => S::Approved->value],
            ],
        ],

        // المشتريات
        [
            'key'   => 'send_to_purchasing',
            'label' => 'إرسال للمشتريات',
            'icon'  => 'heroicon-o-truck',
            'color' => 'warning',
            'type'  => 'transition',
            'to'    => [
                'phase'  => Phase::Purchasing->value,
                'status' => S::Pending->value,
                'owner'  => 'purchasing_manager',
                'touch_sent' => true,
            ],
            'roles' => ['department_manager','factory_manager'],
            'when'  => [
                ['phase_in' => [Phase::DepartmentAssignment->value, Phase::Manufacturing->value]],
            ],
        ],
        [
            'key'   => 'materials_prep',
            'label' => 'بدء تجهيز الخامات',
            'icon'  => 'heroicon-o-cog-8-tooth',
            'color' => 'primary',
            'type'  => 'status',
            'to'    => ['status' => S::MaterialsPrep->value],
            'roles' => ['purchasing_manager'],
            'when'  => [
                ['phase' => Phase::Purchasing->value, 'status_in' => [S::Received->value, S::UnderReview->value]],
            ],
        ],
        [
            'key'   => 'materials_done',
            'label' => 'الخامات جاهزة',
            'icon'  => 'heroicon-o-check',
            'color' => 'success',
            'type'  => 'status',
            'to'    => ['status' => S::MaterialsDone->value],
            'roles' => ['purchasing_manager'],
            'when'  => [
                ['phase' => Phase::Purchasing->value, 'status' => S::MaterialsPrep->value],
            ],
        ],
        [
            'key'   => 'start_manufacturing',
            'label' => 'بدء التصنيع',
            'icon'  => 'heroicon-o-wrench-screwdriver',
            'color' => 'success',
            'type'  => 'transition',
            'to'    => [
                'phase'  => Phase::Manufacturing->value,
                'status' => S::InProgress->value,
                'owner'  => 'department_manager',
                'touch_sent' => true,
            ],
            'roles' => ['department_manager','factory_manager'],
            'when'  => [
                ['phase' => Phase::Purchasing->value, 'status' => S::MaterialsDone->value],
            ],
        ],

        // جودة ما بعد التصنيع
        [
            'key'   => 'send_qa_after_manu',
            'label' => 'إرسال للجودة (ما بعد التصنيع)',
            'icon'  => 'heroicon-o-arrow-right-circle',
            'color' => 'info',
            'type'  => 'transition',
            'to'    => [
                'phase'  => Phase::QualityAfterManufacture->value,
                'status' => S::Pending->value,
                'owner'  => 'quality_manager',
                'touch_sent' => true,
            ],
            'roles' => ['department_manager'],
            'when'  => [
                ['phase' => Phase::Manufacturing->value, 'status' => S::InProgress->value],
            ],
        ],
        [
            'key'   => 'qa_after_manu_approve',
            'label' => 'اعتماد الجودة (ما بعد التصنيع)',
            'icon'  => 'heroicon-o-badge-check',
            'color' => 'success',
            'type'  => 'status',
            'to'    => ['status' => S::Approved->value],
            'roles' => ['quality_manager'],
            'when'  => [
                ['phase' => Phase::QualityAfterManufacture->value, 'status' => S::UnderReview->value],
            ],
        ],
        [
            'key'   => 'qa_after_manu_reject',
            'label' => 'رفض الجودة (إعادة تصنيع)',
            'icon'  => 'heroicon-o-arrow-uturn-left',
            'color' => 'danger',
            'type'  => 'reject_and_back', // يرفض ويرجع للتصنيع
            'to'    => [
                'phase'  => Phase::Manufacturing->value,
                'status' => S::InProgress->value,
                'owner'  => 'department_manager',
                'touch_sent' => true,
            ],
            'roles' => ['quality_manager'],
            'when'  => [
                ['phase' => Phase::QualityAfterManufacture->value, 'status_in' => [S::UnderReview->value, S::Approved->value]],
            ],
        ],

        // التركيب + جودة ما بعد التركيب
        [
            'key'   => 'send_to_installation',
            'label' => 'إرسال للتركيب',
            'icon'  => 'heroicon-o-arrow-right',
            'color' => 'primary',
            'type'  => 'transition',
            'to'    => [
                'phase'  => Phase::Installation->value,
                'status' => S::InProgress->value,
                'owner'  => 'installation_manager',
                'touch_sent' => true,
            ],
            'roles' => ['factory_manager','quality_manager'],
            'when'  => [
                ['phase' => Phase::QualityAfterManufacture->value, 'status_in' => [S::Approved->value, S::Completed->value]],
            ],
        ],
        [
            'key'   => 'send_qa_after_install',
            'label' => 'مراجعة الجودة (ما بعد التركيب)',
            'icon'  => 'heroicon-o-arrow-right-circle',
            'color' => 'info',
            'type'  => 'transition',
            'to'    => [
                'phase'  => Phase::QualityAfterInstallation->value,
                'status' => S::Pending->value,
                'owner'  => 'quality_manager',
                'touch_sent' => true,
            ],
            'roles' => ['installation_manager'],
            'when'  => [
                ['phase' => Phase::Installation->value, 'status' => S::InProgress->value],
            ],
        ],
        [
            'key'   => 'qa_after_install_approve',
            'label' => 'اعتماد الجودة (ما بعد التركيب) وإغلاق',
            'icon'  => 'heroicon-o-badge-check',
            'color' => 'success',
            'type'  => 'transition',
            'to'    => [
                'phase'  => Phase::Closed->value,
                'status' => S::Completed->value,
                'owner'  => null,
                'touch_sent' => false,
            ],
            'roles' => ['quality_manager'],
            'when'  => [
                ['phase' => Phase::QualityAfterInstallation->value, 'status' => S::UnderReview->value],
            ],
        ],
        [
            'key'   => 'qa_after_install_reject',
            'label' => 'رفض الجودة (إعادة تركيب)',
            'icon'  => 'heroicon-o-arrow-uturn-left',
            'color' => 'danger',
            'type'  => 'reject_and_back',
            'to'    => [
                'phase'  => Phase::Installation->value,
                'status' => S::InProgress->value,
                'owner'  => 'installation_manager',
                'touch_sent' => true,
            ],
            'roles' => ['quality_manager'],
            'when'  => [
                ['phase' => Phase::QualityAfterInstallation->value, 'status_in' => [S::UnderReview->value, S::Approved->value]],
            ],
        ],
    ],
];
