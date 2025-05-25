// /Catedra/js/script.js
document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('btnTema');
    const icon = document.getElementById('iconTema');
    if (btn && icon) {
        // Preferencia guardada
        let savedTheme = localStorage.getItem('tema');
        if(savedTheme){
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
            icon.className = savedTheme === 'dark' ? 'bi bi-moon' : 'bi bi-brightness-high';
        }
        btn.addEventListener('click', function () {
            const html = document.documentElement;
            let theme = html.getAttribute('data-bs-theme');
            theme = (theme === 'dark') ? 'light' : 'dark';
            html.setAttribute('data-bs-theme', theme);
            localStorage.setItem('tema', theme);
            icon.className = theme === 'dark' ? 'bi bi-moon' : 'bi bi-brightness-high';
        });
    }
});
