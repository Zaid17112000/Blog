const sidebarToggle = document.querySelector('.sidebar-toggle');
const sidebar = document.querySelector('.sidebar');
const overlay = document.createElement('div');
overlay.className = 'sidebar-overlay';
document.body.appendChild(overlay);

sidebarToggle.addEventListener('click', () => {
    sidebar.classList.toggle('active');
});

overlay.addEventListener('click', () => {
    sidebar.classList.remove('active');
});

// Close sidebar when clicking on a nav link
const navLinks = document.querySelectorAll('.nav-link');
navLinks.forEach(link => {
    link.addEventListener('click', () => {
        if (window.innerWidth <= 668) {
            sidebar.classList.remove('active');
        }
    });
});