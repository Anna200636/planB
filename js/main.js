document.addEventListener('DOMContentLoaded', function () {
    const carouselElement = document.querySelector('#coursesCarousel');
    if (carouselElement) {
        new bootstrap.Carousel(carouselElement, {
            interval: 3000,
            ride: 'carousel',
            pause: 'hover'
        });
    }

    document.querySelectorAll('.confirm-action').forEach(function (button) {
        button.addEventListener('click', function (event) {
            const isConfirmed = confirm('Подтвердить действие?');
            if (!isConfirmed) {
                event.preventDefault();
            }
        });
    });

    const registerForm = document.querySelector('form[novalidate]');
    if (registerForm) {
        registerForm.addEventListener('submit', function (event) {
            const login = document.getElementById('login');
            const password = document.getElementById('password');
            const fio = document.getElementById('fio');
            const phone = document.getElementById('phone');
            const email = document.getElementById('email');

            const loginRegex = /^[A-Za-z0-9]{6,}$/;
            const fioRegex = /^[А-Яа-яЁё\s]+$/;
            const phoneRegex = /^8\(\d{3}\)\d{3}-\d{2}-\d{2}$/;
            let valid = true;

            if (!loginRegex.test(login.value.trim())) {
                login.classList.add('is-invalid');
                valid = false;
            }
            if (password.value.trim().length < 8) {
                password.classList.add('is-invalid');
                valid = false;
            }
            if (!fioRegex.test(fio.value.trim())) {
                fio.classList.add('is-invalid');
                valid = false;
            }
            if (!phoneRegex.test(phone.value.trim())) {
                phone.classList.add('is-invalid');
                valid = false;
            }
            if (!email.value.trim()) {
                email.classList.add('is-invalid');
                valid = false;
            }

            if (!valid) {
                event.preventDefault();
            }
        });
    }
});
