(function () {
    const headline = document.getElementById('hero-headline');
    if (headline) {
        const hooks = JSON.parse(headline.dataset.hooks || '[]');
        let index = 0;
        if (hooks.length > 1) {
            setInterval(function () {
                headline.classList.add('is-fading');
                setTimeout(function () {
                    index = (index + 1) % hooks.length;
                    headline.textContent = hooks[index];
                    headline.classList.remove('is-fading');
                }, 450);
            }, 5200);
        }
    }

    window.landingNextStep = function () {
        const s1 = document.getElementById('step-1-fields');
        const s2 = document.getElementById('step-2-fields');
        const i1 = document.getElementById('step-1-indicator');
        const i2 = document.getElementById('step-2-indicator');
        if (!s1 || !s2) return;
        const restaurant = document.querySelector('[name="business_name"]');
        const email = document.querySelector('[name="email"]');
        if (restaurant && !restaurant.value.trim()) {
            restaurant.focus();
            return;
        }
        if (email && !email.value.trim()) {
            email.focus();
            return;
        }
        s1.classList.add('hidden');
        s2.classList.remove('hidden');
        if (i1) {
            i1.classList.replace('step-active', 'step-inactive');
        }
        if (i2) {
            i2.classList.replace('step-inactive', 'step-active');
        }
    };

    const form = document.getElementById('hero-registration');
    if (form) {
        form.addEventListener('submit', function () {
            const pwd = form.querySelector('[name="password"]');
            const confirm = document.getElementById('landing-pwd-confirm');
            if (pwd && confirm) {
                confirm.value = pwd.value;
            }
        });
    }

    window.toggleFAQ = function (btn) {
        const item = btn.closest('.faq-item');
        if (!item) return;
        const open = item.classList.contains('faq-open');
        document.querySelectorAll('.faq-item').forEach(function (el) {
            el.classList.remove('faq-open');
        });
        if (!open) {
            item.classList.add('faq-open');
        }
    };

    window.addEventListener('scroll', function () {
        const nav = document.querySelector('[data-landing-nav]');
        if (!nav) return;
        if (window.scrollY > 20) {
            nav.classList.add('shadow-md');
        } else {
            nav.classList.remove('shadow-md');
        }
    });
})();
