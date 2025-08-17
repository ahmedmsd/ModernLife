// Add this JavaScript to your admin panel
// This script will handle the accordion behavior for navigation groups

document.addEventListener('DOMContentLoaded', function () {
    function handleNavigationAccordion() {
        const navigationGroups = document.querySelectorAll('.fi-sidebar-group');
        const activeItem = document.querySelector('.fi-sidebar-item-active');
        const activeGroup = activeItem ? activeItem.closest('.fi-sidebar-group') : null;

        navigationGroups.forEach(group => {
            const trigger = group.querySelector('.fi-sidebar-group-button');
            const items = group.querySelector('.fi-sidebar-group-items');

            if (items) {
                if (group === activeGroup) {
                    items.style.display = 'block';
                    group.classList.add('fi-sidebar-group-active');
                } else {
                    items.style.display = 'none';
                    group.classList.remove('fi-sidebar-group-active');
                }
            }

            if (trigger) {
                trigger.addEventListener('click', function () {
                    navigationGroups.forEach(otherGroup => {
                        if (otherGroup !== group && otherGroup !== activeGroup) {
                            const otherItems = otherGroup.querySelector('.fi-sidebar-group-items');
                            if (otherItems) {
                                otherItems.style.display = 'none';
                                otherGroup.classList.remove('fi-sidebar-group-active');
                            }
                        }
                    });

                    const currentItems = group.querySelector('.fi-sidebar-group-items');
                    if (currentItems && group !== activeGroup) {
                        if (currentItems.style.display === 'none') {
                            currentItems.style.display = 'block';
                            group.classList.add('fi-sidebar-group-active');
                        } else {
                            currentItems.style.display = 'none';
                            group.classList.remove('fi-sidebar-group-active');
                        }
                    }
                });
            }
        });
    }

    handleNavigationAccordion();

    document.addEventListener('livewire:navigated', handleNavigationAccordion);
});

