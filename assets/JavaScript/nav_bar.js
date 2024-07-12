document.addEventListener("DOMContentLoaded", function() {
    const dropdownToggle = document.querySelectorAll('.dropdown-toggle');

    dropdownToggle.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const dropdown = this.parentNode;
            dropdown.classList.toggle('open');
        });
    });

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown')) {
            const openDropdowns = document.querySelectorAll('.dropdown.open');
            openDropdowns.forEach(item => {
                item.classList.remove('open');
            });
        }
    });
});