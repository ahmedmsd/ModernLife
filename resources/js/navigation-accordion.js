// Add this JavaScript to your admin panel
// This script will handle the accordion behavior for navigation groups

document.addEventListener('DOMContentLoaded', function() {
    // Function to handle navigation group clicks
    function handleNavigationAccordion() {
        const navigationGroups = document.querySelectorAll('.fi-sidebar-group');
        
        navigationGroups.forEach(group => {
            const trigger = group.querySelector('.fi-sidebar-group-button');
            
            if (trigger) {
                trigger.addEventListener('click', function(e) {
                    // Close all other groups
                    navigationGroups.forEach(otherGroup => {
                        if (otherGroup !== group) {
                            const otherItems = otherGroup.querySelector('.fi-sidebar-group-items');
                            if (otherItems) {
                                otherItems.style.display = 'none';
                                otherGroup.classList.remove('fi-sidebar-group-active');
                            }
                        }
                    });
                    
                    // Toggle current group
                    const currentItems = group.querySelector('.fi-sidebar-group-items');
                    if (currentItems) {
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
    
    // Initial setup
    handleNavigationAccordion();
    
    // Re-run when Livewire updates (for dynamic content)
    document.addEventListener('livewire:navigated', handleNavigationAccordion);
});