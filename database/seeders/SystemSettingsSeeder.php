<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            // ===================== عام =====================
            r('general', 'app_name', 'text', 'اسم النظام', 'ModernLife'),
            r('general', 'company_name', 'text', 'اسم الشركة', 'ModernLife Co.'),
            r('general', 'support_email', 'email', 'بريد الدعم', 'support@example.com', rules: 'nullable|email', help: 'ستُستخدم لاستقبال رسائل الدعم'),
            r('general', 'support_phone', 'text', 'هاتف الدعم', '', rules: 'nullable|string|max:30'),
            r('general', 'base_url', 'url', 'رابط النظام', env('APP_URL', 'http://127.0.0.1:8000'), rules: 'required|url'),

            // ===================== الهوية (العلامة) =====================
            r('branding', 'brand_logo', 'image', 'شعار الشركة', null, help: 'يفضل PNG بخلفية شفافة'),
            r('branding', 'brand_favicon', 'image', 'Favicon', null, help: 'مقاس 32x32 أو 64x64'),
            r('branding', 'primary_color', 'color', 'اللون الأساسي', '#0ea5e9'),
            r('branding', 'secondary_color', 'color', 'اللون الثانوي', '#22c55e'),

            // ===================== الإعدادات المحلية =====================
            r('locale', 'locale', 'locale', 'لغة الواجهة', 'ar',
                options: ['ar' => 'العربية', 'en' => 'English'],
                rules: 'required|in:ar,en',
                help: 'تؤثر على لغة واجهة فيلامنت والتواريخ.'
            ),
            r('locale', 'timezone', 'timezone', 'المنطقة الزمنية', 'Africa/Cairo', rules: 'required|string'),
            r('locale', 'date_format', 'select', 'تنسيق التاريخ', 'Y-m-d',
                options: [
                    'Y-m-d' => '2025-09-09',
                    'd/m/Y' => '09/09/2025',
                    'm/d/Y' => '09/09/2025',
                    'M j, Y' => 'Sep 9, 2025',
                ],
                rules: 'required|string',
                help: 'تأثيره يظهر في أعمدة/حقول التاريخ.'
            ),
            r('locale', 'time_format', 'select', 'تنسيق الوقت', 'H:i',
                options: [
                    'H:i'   => '23:15',
                    'h:i A' => '11:15 PM',
                ],
                rules: 'required|string'
            ),
            r('locale', 'week_starts_on', 'select', 'أول أيام الأسبوع', 'saturday',
                options: [
                    'saturday' => 'السبت',
                    'sunday'   => 'الأحد',
                    'monday'   => 'الاثنين',
                ],
                rules: 'required|in:saturday,sunday,monday'
            ),

            // ===================== المشاريع والمهام =====================
            r('projects', 'project_due_soon_days', 'number', 'إنذار قبل انتهاء المشروع (أيام)', '7', rules: 'required|integer|min:0'),
            r('projects', 'task_overdue_grace_hours', 'number', 'ساعات سماح لتأخير المهام', '24', rules: 'required|integer|min:0'),

            // ===================== الملفات =====================
            r('files', 'max_upload_mb', 'number', 'الحد الأقصى لحجم الملف (MB)', '50', rules: 'required|integer|min:1|max:200'),
            r('files', 'allowed_mime_extra', 'textarea', 'أنواع MIME إضافية مسموحة', '',
                help: "سطر لكل نوع، مثال:\napplication/zip\ntext/csv"
            ),

            // ===================== التنبيهات =====================
            r('notifications', 'notify_in_app', 'boolean', 'تفعيل تنبيهات داخل النظام', '1'),
            r('notifications', 'notify_email', 'boolean', 'تفعيل تنبيهات البريد', '0'),
            r('notifications', 'notify_slack', 'boolean', 'تفعيل تنبيهات Slack', '0'),
            r('notifications', 'notify_telegram', 'boolean', 'تفعيل تنبيهات Telegram', '0'),
            r('notifications', 'quiet_hours_start', 'text', 'بدء الساعات الهادئة (HH:mm)', '21:00',
                rules: 'nullable|regex:/^\d{2}:\d{2}$/', help: 'لن تُرسل إشعارات فورية خلال هذه المدة.'
            ),
            r('notifications', 'quiet_hours_end', 'text', 'انتهاء الساعات الهادئة (HH:mm)', '08:00',
                rules: 'nullable|regex:/^\d{2}:\d{2}$/'
            ),
            r('notifications', 'daily_digest_time', 'text', 'موعد الملخص اليومي (HH:mm)', '09:00',
                rules: 'nullable|regex:/^\d{2}:\d{2}$/'
            ),
            r('notifications', 'weekly_digest_day', 'select', 'يوم الملخص الأسبوعي', 'sunday',
                options: [
                    'saturday' => 'السبت',
                    'sunday'   => 'الأحد',
                    'monday'   => 'الاثنين',
                    'tuesday'  => 'الثلاثاء',
                    'wednesday'=> 'الأربعاء',
                    'thursday' => 'الخميس',
                    'friday'   => 'الجمعة',
                ],
                rules: 'nullable|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday'
            ),
            r('notifications', 'weekly_digest_time', 'text', 'موعد الملخص الأسبوعي (HH:mm)', '09:00',
                rules: 'nullable|regex:/^\d{2}:\d{2}$/'
            ),

            // ===================== البريد (SMTP) =====================
            r('mail', 'mail_from_name', 'text', 'اسم المرسل', 'ModernLife', rules: 'required|string|max:100'),
            r('mail', 'mail_from_address', 'email', 'بريد المرسل', 'no-reply@example.com', rules: 'required|email'),
            r('mail', 'smtp_host', 'text', 'SMTP Host', '', rules: 'nullable|string'),
            r('mail', 'smtp_port', 'number', 'SMTP Port', '587', rules: 'nullable|integer|min:1'),
            r('mail', 'smtp_username', 'text', 'SMTP Username', '', rules: 'nullable|string'),
            r('mail', 'smtp_password', 'secret', 'SMTP Password', '', sensitive: true, rules: 'nullable|string', help: 'يُحفظ مُشفّرًا من صفحة الإعدادات'),
            r('mail', 'smtp_encryption', 'select', 'التشفير', 'tls',
                options: ['tls' => 'TLS', 'ssl' => 'SSL', 'none' => 'بدون'],
                rules: 'nullable|in:tls,ssl,none'
            ),

            // ===================== التكاملات =====================
            r('integrations', 'slack_webhook_url', 'url', 'Slack Webhook URL', '', rules: 'nullable|url'),
            r('integrations', 'telegram_bot_token', 'secret', 'توكن بوت تيليجرام', '', sensitive: true, rules: 'nullable|string'),
            r('integrations', 'telegram_chat_id', 'text', 'رقم محادثة تيليجرام', '', rules: 'nullable|string'),
            r('integrations', 'webhook_url', 'url', 'Webhook عام', '', rules: 'nullable|url'),
        ];

        foreach ($rows as $r) {
            SystemSetting::updateOrCreate(
                ['setting_key' => $r['setting_key']],
                [
                    'setting_group'     => $r['setting_group'],
                    'setting_type'      => $r['setting_type'],
                    'description'       => $r['description'],
                    'setting_value'     => $r['setting_value'],
                    'setting_options'   => $r['setting_options'] ?? null,
                    'is_sensitive'      => $r['is_sensitive'] ?? false,
                    'validation_rules'  => $r['validation_rules'] ?? null,
                    'help_text'         => $r['help_text'] ?? null,
                ]
            );
        }
    }
}

/**
 * Helper: يُنشئ صف إعداد مع حقوله الإضافية
 */
if (! function_exists('r')) {
    /**
     * @param string            $group
     * @param string            $key
     * @param string            $type
     * @param string            $label
     * @param mixed|null        $value
     * @param array|null        $options  // سيحوَّل إلى JSON
     * @param bool              $sensitive
     * @param string|null       $rules
     * @param string|null       $help
     */
    function r(
        string $group,
        string $key,
        string $type,
        string $label,
               $value = null,
        ?array $options = null,
        bool $sensitive = false,
        ?string $rules = null,
        ?string $help = null
    ): array {
        return [
            'setting_group'    => $group,
            'setting_key'      => $key,
            'setting_type'     => $type,
            'description'      => $label,
            'setting_value'    => $value,
            'setting_options'  => $options ? json_encode($options, JSON_UNESCAPED_UNICODE) : null,
            'is_sensitive'     => $sensitive,
            'validation_rules' => $rules,
            'help_text'        => $help,
        ];
    }
}
