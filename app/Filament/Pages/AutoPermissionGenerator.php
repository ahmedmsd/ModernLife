<?php
namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Permission;
use Filament\Notifications\Notification;
use BackedEnum;
use UnitEnum;

class AutoPermissionGenerator extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-lock-closed';
    protected static ?string $title = 'توليد صلاحيات تلقائيًا';
    protected static ?string $navigationLabel = 'توليد الصلاحيات من الصفحات';
    protected static ?string $slug = 'auto-generate-permissions';
    protected static UnitEnum | string | null $navigationGroup = 'إدارة الصلاحيات';

    protected string $view = 'filament.pages.auto-permission-generator';

    public function generate(): void
    {
        $basePath = app_path('Filament');
        $types = ['Resources', 'Pages', 'Widgets'];
        $created = [];

        foreach ($types as $type) {
            $path = $basePath . '/' . $type;

            if (!File::exists($path)) {
                continue;
            }

            $files = File::allFiles($path);

            foreach ($files as $file) {
                $class = $this->getFullyQualifiedClassName($file->getPathname());

                if (!class_exists($class)) {
                    continue;
                }

                $shortName = Str::of(class_basename($class))->kebab()->replace('-', ' ')->lower();
                $name = "view {$shortName}";

                if (!Permission::where('name', $name)->exists()) {
                    Permission::create(['name' => $name]);
                    $created[] = $name;
                }
            }
        }

        Notification::make()
            ->title('✅ تم توليد الصلاحيات بنجاح')
            ->body(count($created) > 0 
                ? 'تم إنشاء الصلاحيات التالية: ' . implode(', ', $created)
                : 'لم يتم إنشاء صلاحيات جديدة.')
            ->success()
            ->send();
    }

    protected function getFullyQualifiedClassName(string $path): string
    {
        $relativePath = str_replace(base_path() . '/', '', $path);
        $class = str_replace(['/', '.php'], ['\\', ''], $relativePath);
        return 'App\\' . $class;
    }
}
