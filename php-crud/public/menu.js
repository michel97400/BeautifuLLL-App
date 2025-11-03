// Menu burger toggle
const burgerMenu = document.getElementById('burgerMenu');
const mainNav = document.getElementById('mainNav');

burgerMenu.addEventListener('click', () => {
    burgerMenu.classList.toggle('active');
    mainNav.classList.toggle('active');
    document.body.classList.toggle('menu-open');
});

// Gérer le dropdown sur mobile
const dropdowns = document.querySelectorAll('.dropdown');
dropdowns.forEach(dropdown => {
    const toggle = dropdown.querySelector('.dropdown-toggle');
    if (toggle) {
        toggle.addEventListener('click', (e) => {
            if (window.innerWidth <= 768) {
                e.preventDefault();
                dropdown.classList.toggle('active');
            }
        });
    }
});

// Fermer le menu en cliquant sur un lien (sauf dropdown toggle)
mainNav.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', () => {
        burgerMenu.classList.remove('active');
        mainNav.classList.remove('active');
        document.body.classList.remove('menu-open');
    });
});

// Fermer le menu en cliquant en dehors
document.addEventListener('click', (e) => {
    if (!mainNav.contains(e.target) && !burgerMenu.contains(e.target)) {
        burgerMenu.classList.remove('active');
        mainNav.classList.remove('active');
        document.body.classList.remove('menu-open');
    }
});

// Réinitialiser les dropdowns lors du redimensionnement
window.addEventListener('resize', () => {
    if (window.innerWidth > 768) {
        burgerMenu.classList.remove('active');
        mainNav.classList.remove('active');
        document.body.classList.remove('menu-open');
        dropdowns.forEach(dropdown => dropdown.classList.remove('active'));
    }
});
