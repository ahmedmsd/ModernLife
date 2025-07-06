document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        const groups = document.querySelectorAll('.fi-sidebar-group');
        
        groups.forEach(group => {
            const isActive = group.querySelector('.fi-active');
            const isExpanded = group.querySelector('button[aria-expanded="true"]');
            
            if (!isActive && isExpanded) {
                isExpanded.click(); 
            }
        });
    }, 300); 
});