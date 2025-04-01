document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    const navbar = document.querySelector('.navbar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const isMobile = window.innerWidth <= 768;

    // Function to toggle sidebar
    function toggleSidebar() {
        if (isMobile) {
            sidebar.classList.toggle('show');
            navbar.classList.toggle('expanded');
        } else {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        }
    }

    // Event listener for sidebar toggle button
    sidebarToggle.addEventListener('click', toggleSidebar);

    // Close sidebar on mobile when clicking outside
    document.addEventListener('click', function(event) {
        if (isMobile && sidebar.classList.contains('show')) {
            const isClickInside = sidebar.contains(event.target) || sidebarToggle.contains(event.target);
            if (!isClickInside) {
                sidebar.classList.remove('show');
                navbar.classList.remove('expanded');
            }
        }
    });

    // Handle window resize
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            const newIsMobile = window.innerWidth <= 768;
            if (newIsMobile !== isMobile) {
                if (newIsMobile) {
                    sidebar.classList.remove('collapsed');
                    mainContent.classList.remove('expanded');
                    sidebar.classList.remove('show');
                    navbar.classList.remove('expanded');
                }
            }
        }, 250);
    });

    // Add fade-in animation to cards
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        card.classList.add('fade-in');
    });

    // Add smooth scrolling to sidebar links
    const sidebarLinks = document.querySelectorAll('.sidebar .nav-link');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (isMobile) {
                sidebar.classList.remove('show');
                navbar.classList.remove('expanded');
            }
        });
    });
}); 