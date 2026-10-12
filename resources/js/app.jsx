import './games.js';

document.querySelectorAll('[data-filter]').forEach(button => {
    button.addEventListener('click', () => {
        document.querySelectorAll('[data-filter]').forEach(item => {
            const active = item === button;
            item.classList.toggle('is-active', active);
            item.setAttribute('aria-pressed', String(active));
        });
        document.querySelectorAll('[data-category]').forEach(tile => {
            tile.hidden = button.dataset.filter !== 'all' && tile.dataset.category !== button.dataset.filter;
        });
    });
});

document.getElementById('dashboard-search')?.addEventListener('input', event => {
    const query = event.target.value.trim().toLocaleLowerCase('fr');
    document.querySelectorAll('[data-username]').forEach(row => {
        row.hidden = !row.dataset.username.toLocaleLowerCase('fr').includes(query);
    });
});

document.querySelectorAll('[data-confirm]').forEach(form => {
    form.addEventListener('submit', event => {
        if (!window.confirm(form.dataset.confirm)) event.preventDefault();
    });
});

const registration = document.querySelector('[data-registration]');
if (registration) {
    const password = registration.elements.password;
    const confirmation = registration.elements.confirmPassword;
    const validate = () => confirmation.setCustomValidity(confirmation.value && confirmation.value !== password.value ? 'Les mots de passe ne correspondent pas.' : '');
    password.addEventListener('input', validate);
    confirmation.addEventListener('input', validate);
}
