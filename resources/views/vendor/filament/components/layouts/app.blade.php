<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ config('filament.direction') }}">
<head>
    {{-- تضمين ملفات Filament الأساسية --}}
    @filamentHead
</head>

<body class="fi-body min-h-screen bg-gray-50 dark:bg-gray-950">
{{-- محتوى Filament --}}
@filamentBody

{{-- مكان لدفع سكربتات إضافية --}}
@stack('scripts')

{{-- سكربت التحكم في فتح/إغلاق قوائم الشريط الجانبي --}}
<script>
    document.addEventListener('alpine:init', () => {
        document.addEventListener('DOMContentLoaded', () => {
            const STORAGE_KEY = 'collapsedGroups';
            let collapsedGroups = JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];

            document.querySelectorAll('.fi-sidebar-group').forEach((group) => {
                const label = group.dataset.groupLabel;
                if (!label) return;

                const items = group.querySelector('.fi-sidebar-group-items');
                const toggleButton = group.querySelector('.fi-sidebar-group-collapse-button');

                // ضبط الحالة عند التحميل
                const isCollapsed = collapsedGroups.includes(label);
                items.style.display = isCollapsed ? 'none' : '';
                toggleButton?.classList.toggle('rotate-180', !isCollapsed);

                // عند الضغط على زر الفتح/الإغلاق
                toggleButton?.addEventListener('click', (e) => {
                    e.stopPropagation();

                    const currentlyCollapsed = items.style.display === 'none';
                    items.style.display = currentlyCollapsed ? '' : 'none';
                    toggleButton.classList.toggle('rotate-180', !currentlyCollapsed);

                    // تحديث التخزين
                    let updatedGroups = JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];

                    if (!currentlyCollapsed) {
                        // سيتم إغلاق المجموعة: أضفها
                        if (!updatedGroups.includes(label)) {
                            updatedGroups.push(label);
                        }
                    } else {
                        // سيتم فتح المجموعة: أزلها
                        updatedGroups = updatedGroups.filter((item) => item !== label);
                    }

                    localStorage.setItem(STORAGE_KEY, JSON.stringify(updatedGroups));
                });
            });
        });
    });
</script>
</body>
</html>
