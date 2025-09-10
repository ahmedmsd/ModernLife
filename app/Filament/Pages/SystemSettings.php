<?php

namespace App\Filament\Pages;

use App\Models\SystemSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Arr;

class SystemSettings extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon  = 'heroicon-o-cog-6-tooth';
    protected static ?string $title            = 'الإعدادات العامة';
    protected static ?string $navigationLabel  = 'الإعدادات العامة';
    protected static ?string $navigationGroup  = 'إعدادات النظام';
    protected static string $view              = 'filament.pages.system-settings';

    public array $data = [];
    public ?string $search = null; // بحث داخل الصفحة

    public function mount(): void
    {
        // تحميل القيم، مع فك تشفير الحقول الحساسة (إن وجدت أعمدة تدل على ذلك)
        $pairs = SystemSetting::query()
            ->get()
            ->mapWithKeys(function (SystemSetting $s) {
                $val = $s->setting_value;
                if (($s->is_sensitive ?? false) || in_array($s->setting_type, ['password','secret'])) {
                    try { $val = $val ? Crypt::decryptString($val) : null; } catch (\Throwable $e) {}
                }
                return [$s->setting_key => $val];
            })
            ->toArray();

        $this->form->fill($pairs);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->buildSchema())
            ->statePath('data');
    }

    /**
     * نبني المخطط ديناميكيًا: مجموعات -> تبويبات
     */
    protected function buildSchema(): array
    {
        $groups = SystemSetting::query()
            ->select('setting_group')
            ->distinct()
            ->pluck('setting_group')
            ->toArray();

        $tabs = [];
        foreach ($groups as $group) {
            $fields = [];
            $settings = SystemSetting::where('setting_group', $group)->orderBy('setting_id')->get();

            foreach ($settings as $setting) {
                $component = $this->makeComponentFor($setting);
                // بحث: اخفِ الحقول التي لا تطابق نص البحث (إن وُضع)
                if ($this->search) {
                    $hay = mb_strtolower(($setting->description ?? '') . ' ' . $setting->setting_key);
                    $q   = mb_strtolower($this->search);
                    $component = $component->visible(str_contains($hay, $q));
                }
                $fields[] = $component->label(__($setting->description ?? $setting->setting_key));
            }

            $tabs[] = Forms\Components\Tabs\Tab::make(__($group ?: 'عام'))
                ->schema([
                    Forms\Components\Grid::make(2)->schema($fields),
                ]);
        }

        return [
            // شريط بحث أعلى الصفحة
            Forms\Components\Section::make('بحث')
                ->schema([
                    Forms\Components\TextInput::make('search')
                        ->placeholder('ابحث باسم أو وصف الإعداد...')
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn () => $this->form->fill($this->data)),
                ])
                ->collapsible()
                ->collapsed(),

            Forms\Components\Tabs::make('settings')->tabs($tabs),
        ];
    }

    /**
     * إنشاء كومبوننت مناسب لنوع الإعداد
     */
    protected function makeComponentFor(SystemSetting $s): Forms\Components\Component
    {
        $key   = $s->setting_key;
        $label = $s->description ?? $key;
        $opts  = $this->parseOptions($s->setting_options ?? null); // JSON => array
        $rules = $this->parseRules($s->validation_rules ?? null);  // "required|email"

        $base = match ($s->setting_type) {
            'text'      => Forms\Components\TextInput::make($key),
            'number'    => Forms\Components\TextInput::make($key)->numeric(),
            'email'     => Forms\Components\TextInput::make($key)->email(),
            'url'       => Forms\Components\TextInput::make($key)->url(),
            'phone'     => Forms\Components\TextInput::make($key)->tel(),
            'textarea'  => Forms\Components\Textarea::make($key)->rows(3),
            'boolean'   => Forms\Components\Toggle::make($key),
            'color'     => Forms\Components\ColorPicker::make($key),
            'tags'      => Forms\Components\TagsInput::make($key),
            'key_value' => Forms\Components\KeyValue::make($key)->addButtonLabel('إضافة'),
            'select'    => Forms\Components\Select::make($key)->options($opts)->searchable(),
            'toggle_buttons' => Forms\Components\ToggleButtons::make($key)
                ->options($opts)->inline(),
            'timezone'  => Forms\Components\Select::make($key)->options(array_combine(timezone_identifiers_list(), timezone_identifiers_list()))->searchable(),
            'locale'    => Forms\Components\Select::make($key)->options([
                'ar' => 'العربية', 'en' => 'English',
            ])->searchable(),
            'currency'  => Forms\Components\Select::make($key)->options([
                'EGP' => 'EGP', 'SAR' => 'SAR', 'USD' => 'USD', 'EUR' => 'EUR',
            ])->searchable(),
            'image'     => Forms\Components\FileUpload::make($key)
                ->image()->imageEditor()
                ->disk('public')->directory('settings')->openable()->downloadable(),
            'file'      => Forms\Components\FileUpload::make($key)
                ->disk('public')->directory('settings')->preserveFilenames()->openable()->downloadable(),
            'password', 'secret' =>
            Forms\Components\TextInput::make($key)->password()->revealable(),
            default     => Forms\Components\TextInput::make($key),
        };

        if ($rules) {
            $base = $base->rules($this->splitRules($rules));
        }
        if (!empty($s->help_text)) $base->hint($s->help_text);

        // بعض التحسينات التجميليّة
        if (in_array($s->setting_type, ['text','email','url','phone','number'])) {
            $base = $base->suffixIcon('heroicon-o-information-circle');
        }

        return $base->label(__($label));
    }
    protected function splitRules(?string $rules): array
    {
        if (! $rules) return [];

        return array_filter(array_map('trim', explode('|', $rules)));
    }
    protected function parseOptions($json): array
    {
        if (!$json) return [];
        try { $arr = is_array($json) ? $json : json_decode($json, true, 512, JSON_THROW_ON_ERROR); }
        catch (\Throwable $e) { return []; }
        return is_array($arr) ? $arr : [];
    }

    protected function parseRules(?string $rules): ?string
    {
        $rules = trim((string)$rules);
        return $rules !== '' ? $rules : null;
    }

    public function submit(): void
    {
        $state = $this->form->getState();

        $all = SystemSetting::query()->get()->keyBy('setting_key');

        foreach ($state as $key => $value) {
            /** @var SystemSetting|null $row */
            $row = $all[$key] ?? null;
            if (!$row) continue;

            $old = $row->setting_value;

            if (($row->is_sensitive ?? false) || in_array($row->setting_type, ['password','secret'])) {
                $value = $value ? Crypt::encryptString($value) : null;
            }

            if ($row->setting_type === 'image' || $row->setting_type === 'file') {
                // لا شيء إضافي — المسار محفوظ بالفعل
            }

            $row->update(['setting_value' => $value]);

            // سجل تغييرات (اختياري: أنشئ جدول system_setting_changes)
            // \App\Models\SystemSettingChange::create([
            //     'setting_key' => $key, 'old_value' => $old, 'new_value' => $value,
            //     'user_id' => auth()->id(), 'ip' => request()->ip(),
            // ]);
        }

        // فضِّ الكاش العام
        Cache::forget('system_settings_all');

        // مثال: اعكس بعض القيم على runtime بدون إعادة تشغيل
        // if (isset($state['mail_from_address'])) config(['mail.from.address' => $state['mail_from_address']]);

        Notification::make()->title('تم حفظ الإعدادات بنجاح')->success()->send();
    }

    /** أزرار أعلى الصفحة */
    protected function getHeaderActions(): array
    {
        return [
            // تصدير JSON
            \Filament\Actions\Action::make('export')
                ->label('تصدير الإعدادات')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    $data = SystemSetting::query()->get(['setting_key','setting_value','setting_group','setting_type'])->toArray();
                    $name = 'settings-'.now()->format('Ymd-His').'.json';
                    // حمّل مباشرة
                    return response()->streamDownload(function () use ($data) {
                        echo json_encode($data, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
                    }, $name, ['Content-Type' => 'application/json; charset=utf-8']);
                }),

            // استيراد JSON
            \Filament\Actions\Action::make('import')
                ->label('استيراد الإعدادات')
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    Forms\Components\FileUpload::make('file')
                        ->acceptedFileTypes(['application/json'])
                        ->required(),
                ])
                ->action(function (array $data) {
                    $path = $data['file'] ?? null;
                    if (!$path) return;

                    $json = Storage::disk('public')->get($path);
                    $rows = json_decode($json, true);
                    if (!is_array($rows)) {
                        Notification::make()->title('فشل الاستيراد: ملف غير صالح')->danger()->send();
                        return;
                    }
                    foreach ($rows as $row) {
                        if (!isset($row['setting_key'])) continue;
                        SystemSetting::where('setting_key', $row['setting_key'])
                            ->update(['setting_value' => $row['setting_value'] ?? null]);
                    }
                    Cache::forget('system_settings_all');
                    Notification::make()->title('تم الاستيراد بنجاح')->success()->send();
                }),

            // اختبار البريد
            \Filament\Actions\Action::make('test_email')
                ->label('إرسال بريد تجريبي')
                ->icon('heroicon-o-paper-airplane')
                ->form([
                    Forms\Components\TextInput::make('to')->email()->required()->label('إلى'),
                ])
                ->action(function (array $data) {
                    try {
                        \Mail::raw('اختبار إعدادات البريد في النظام', function ($m) use ($data) {
                            $m->to($data['to'])->subject('رسالة اختبار');
                        });
                        Notification::make()->title('تم إرسال البريد بنجاح')->success()->send();
                    } catch (\Throwable $e) {
                        Notification::make()->title('فشل الإرسال: '.$e->getMessage())->danger()->send();
                    }
                }),
        ];
    }
}
