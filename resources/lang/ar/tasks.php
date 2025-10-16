<?php

return [

    'logs' => [
        'types' => [
            'created' => 'تم إنشاء المهمة',
            'status_changed' => 'تغيير الحالة',
            'ownership_changed' => 'تغيير المالك',
            'ownership_received' => 'تأكيد استلام المالك',
            'owner_receive_note' => 'ملاحظة صاحب الحركة',
            'assigned_changed' => 'تغيير الإسناد',
            'due_changed' => 'تعديل تاريخ التسليم',
            'plan_set' => 'تعديل المواعيد المخططة',
            'department_changed' => 'تغيير القسم',

            // التحويلات بين الأدوار
            'sent_to_showroom' => 'تم الإرسال إلى مدير المعرض',
            'sent_to_factory' => 'تم الإرسال إلى مدير المصنع',
            'sent_to_department' => 'تم الإرسال إلى مدير القسم',
            'sent_to_purchasing' => 'تم الإرسال إلى قسم المشتريات',
            'sent_to_quality' => 'تم الإرسال إلى قسم الجودة',
            'sent_to_install' => 'تم الإرسال إلى قسم التركيب',

            // الأحداث الخاصة بالتدفق
            'manufacturing_started' => 'بدء التصنيع',
            'manufacturing_finished' => 'إنهاء التصنيع',
            'purchasing_ack' => 'تأكيد استلام المشتريات',
            'purchasing_ack_hint' => 'ملاحظة استلام المشتريات',
            'materials_provided_note' => 'توريد الخامات',
            'planning_hint_set' => 'تحديد الجدول الزمني',
            'qa_approved_manufacturing' => 'اعتماد الجودة بعد التصنيع',
            'qa_rejected_manufacturing' => 'رفض الجودة بعد التصنيع',
            'qa_approved_installation' => 'اعتماد الجودة بعد التركيب',
            'qa_rejected_installation' => 'رفض الجودة بعد التركيب',
            'installation_started' => 'بدء أعمال التركيب',
            'installation_finished' => 'إنهاء أعمال التركيب',
            'client_receipt_uploaded' => 'رفع سند استلام العميل',
            'task_completed' => 'إكمال المهمة',
            'project_completed' => 'إكمال المشروع',
            'production_request_closed' => 'إغلاق طلب التصنيع',
        ],
    ],

];
