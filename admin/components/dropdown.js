document.addEventListener('DOMContentLoaded', function() {
    // Gérer les dropdowns
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    
    // Fermer tous les dropdowns quand on clique ailleurs
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown-menu')) {
            dropdownToggles.forEach(toggle => {
                const content = toggle.nextElementSibling;
                const arrow = toggle.querySelector('.fa-chevron-down');
                content.classList.add('hidden');
                arrow.style.transform = 'rotate(0deg)';
            });
        }
    });

    // Gérer le clic sur chaque dropdown
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const content = this.nextElementSibling;
            const arrow = this.querySelector('.fa-chevron-down');
            
            // Fermer les autres dropdowns
            dropdownToggles.forEach(otherToggle => {
                if (otherToggle !== toggle) {
                    const otherContent = otherToggle.nextElementSibling;
                    const otherArrow = otherToggle.querySelector('.fa-chevron-down');
                    otherContent.classList.add('hidden');
                    otherArrow.style.transform = 'rotate(0deg)';
                }
            });
            
            // Basculer le dropdown actuel
            content.classList.toggle('hidden');
            arrow.style.transform = content.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
        });
    });
});
