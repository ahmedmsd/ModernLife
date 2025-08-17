<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Pages\Page;
use App\Models\SystemSetting;
use Filament\Notifications\Notification;
use BackedEnum;
use UnitEnum;

class SystemSettings extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $title = 'الإعدادات العامة';
    protected static ?string $navigationLabel = 'الإعدادات العامة';
    protected static UnitEnum | string | null $navigationGroup = 'إعدادات النظام';
    protected string $view = 'filament.pages.system-settings';

    public $data = [];

    public function mount(): void
    {
        $this->form->fill(
            SystemSetting::all()->pluck('setting_value', 'setting_key')->toArray()
        );
    }

    protected function getFormSchema(): array
    {
        $groups = SystemSetting::select('setting_group')->distinct()->pluck('setting_group');

        $schema = [];

        foreach ($groups as $group) {
            $settings = SystemSetting::where('setting_group', $group)->get();

            $groupFields = [];

            foreach ($settings as $setting) {
                $component = match ($setting->setting_type) {
                    'text' => Forms\Components\TextInput::make($setting->setting_key),
                    'email' => Forms\Components\TextInput::make($setting->setting_key)->email(),
                    'textarea' => Forms\Components\Textarea::make($setting->setting_key),
                    'boolean' => Forms\Components\Toggle::make($setting->setting_key),
                    'file' => Forms\Components\FileUpload::make($setting->setting_key)
                        ->label($setting->description)
                        ->directory('settings')
                        ->disk('public')
                        ->preserveFilenames()
                        ->openable()
                        ->downloadable(),
                    default => Forms\Components\TextInput::make($setting->setting_key),
                };

                $groupFields[] = $component->label(__($setting->description));
            }

            $schema[] = Forms\Components\Section::make(__("إعدادات " . ucfirst($group)))
                ->schema($groupFields)
                ->columns(2)
                ->collapsible();
        }

        return $schema;
    }

    public function submit()
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            SystemSetting::where('setting_key', $key)->update(['setting_value' => $value]);
        }

        Notification::make()
            ->title('تم حفظ الإعدادات بنجاح')
            ->success()
            ->send();
    }

    protected function getFormModel(): string
    {
        return SystemSetting::class;
    }

    protected function getFormStatePath(): string
    {
        return 'data';
    }
}
