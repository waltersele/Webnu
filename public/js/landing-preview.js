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

    const phone = document.getElementById('customize-phone');
    if (phone) {
        const presets = [
            {
                business: 'La Brasa del Puerto',
                template: 'Básica',
                section: 'Carta · Principales',
                dish: 'Solomillo al Pedro Ximénez',
                price: '24,50 €',
                desc: 'Reducción de Pedro Ximénez y patata confitada.',
                hint: 'Nombre del plato y precio',
                primary: '#004ac6',
                bg: '#ffffff',
                surface: '#f1f3ff',
                text: '#141b2b',
                muted: '#4b5563',
                thumb: 'linear-gradient(135deg, #dbe1ff 0%, #93c5fd 100%)',
            },
            {
                business: 'Azul Coctelería',
                template: 'Nocturno',
                section: 'Copas · Signature',
                dish: 'Negroni del Puerto',
                price: '11,00 €',
                desc: 'Gin, vermut rosso y bitter de naranja.',
                hint: 'Sección y descripción del plato',
                primary: '#7ec8ff',
                bg: '#0a0e14',
                surface: '#141b24',
                text: '#f0f4ff',
                muted: '#94a3b8',
                thumb: 'linear-gradient(135deg, #1e3a5f 0%, #7ec8ff 100%)',
            },
            {
                business: 'Fuego Otaku',
                template: 'Otaku',
                section: 'Ramen · Especial',
                dish: 'Tonkotsu Ramen',
                price: '11,95 €',
                desc: 'Caldo cremoso 12 h, chashu y huevo marinado.',
                hint: 'Color de acento y tipografía',
                primary: '#ff5500',
                bg: '#0a0a0a',
                surface: '#1a1208',
                text: '#fff5eb',
                muted: '#a8a29e',
                thumb: 'linear-gradient(135deg, #ff5500 0%, #ffb800 100%)',
            },
            {
                business: 'Burger & Go',
                template: 'Fastfood',
                section: 'Combos · XL',
                dish: 'Smash Burger XL',
                price: '9,90 €',
                desc: 'Doble carne, cheddar fundido y salsa house.',
                hint: 'Precio destacado en pill',
                primary: '#e31837',
                bg: '#fffbeb',
                surface: '#fef3c7',
                text: '#1c1917',
                muted: '#57534e',
                thumb: 'linear-gradient(135deg, #e31837 0%, #fbbf24 100%)',
            },
            {
                business: 'Marisquería Costa',
                template: 'Mar',
                section: 'Pescados · Del día',
                dish: 'Lubina a la sal',
                price: '22,00 €',
                desc: 'Con patatas panadera y alioli de ajo.',
                hint: 'Paleta oceánica personalizable',
                primary: '#0284c7',
                bg: '#f0f9ff',
                surface: '#e0f2fe',
                text: '#0c4a6e',
                muted: '#64748b',
                thumb: 'linear-gradient(135deg, #38bdf8 0%, #0284c7 100%)',
            },
            {
                business: 'Le Jardin',
                template: 'Elegance',
                section: 'Entrantes · Chef',
                dish: 'Burrata de temporada',
                price: '14,50 €',
                desc: 'Tomate confitado, pesto y reducción balsámica.',
                hint: 'Tipografía serif y dorado',
                primary: '#92702a',
                bg: '#fafaf9',
                surface: '#f5f5f4',
                text: '#292524',
                muted: '#78716c',
                thumb: 'linear-gradient(135deg, #d6d3d1 0%, #ca8a04 100%)',
            },
        ];

        const el = {
            business: document.getElementById('customize-business'),
            template: document.getElementById('customize-template'),
            section: document.getElementById('customize-section'),
            dish: document.getElementById('customize-dish'),
            price: document.getElementById('customize-price'),
            desc: document.getElementById('customize-desc'),
            hint: document.getElementById('customize-hint'),
            swatches: document.getElementById('customize-swatches'),
        };

        presets.forEach(function (preset, i) {
            const swatch = document.createElement('span');
            swatch.className = 'landing-customize-swatch' + (i === 0 ? ' is-active' : '');
            swatch.style.background = preset.primary;
            swatch.dataset.index = String(i);
            if (el.swatches) {
                el.swatches.appendChild(swatch);
            }
        });

        let current = 0;

        function applyPreset(index) {
            const p = presets[index];
            if (!p) return;

            phone.style.setProperty('--cust-primary', p.primary);
            phone.style.setProperty('--cust-bg', p.bg);
            phone.style.setProperty('--cust-surface', p.surface);
            phone.style.setProperty('--cust-text', p.text);
            phone.style.setProperty('--cust-muted', p.muted);
            phone.style.setProperty('--cust-thumb', p.thumb);
            phone.style.borderColor = p.bg === '#0a0a0a' || p.bg === '#0a0e14' ? '#2a2a2a' : '#e5e7eb';

            if (el.business) el.business.textContent = p.business;
            if (el.template) el.template.textContent = p.template;
            if (el.section) el.section.textContent = p.section;
            if (el.dish) el.dish.textContent = p.dish;
            if (el.price) el.price.textContent = p.price;
            if (el.desc) el.desc.textContent = p.desc;
            if (el.hint) el.hint.textContent = p.hint;

            if (el.swatches) {
                el.swatches.querySelectorAll('.landing-customize-swatch').forEach(function (node, i) {
                    node.classList.toggle('is-active', i === index);
                });
            }
        }

        function switchPreset() {
            phone.classList.add('is-switching');
            if (el.hint) el.hint.classList.add('is-fading');

            setTimeout(function () {
                current = (current + 1) % presets.length;
                applyPreset(current);
                phone.classList.remove('is-switching');
                if (el.hint) el.hint.classList.remove('is-fading');
            }, 320);
        }

        applyPreset(0);
        setInterval(switchPreset, 3200);
    }

    const tvpikSection = document.getElementById('tvpik');
    if (tvpikSection) {
        const slides = JSON.parse(tvpikSection.dataset.tvpikSlides || '[]');
        const tv = document.getElementById('tvpik-tv');
        const screen = document.getElementById('tvpik-screen');
        const phone = document.getElementById('tvpik-phone');
        const sync = document.getElementById('tvpik-sync');
        const dotsWrap = document.getElementById('tvpik-dots');
        const el = {
            photo: document.getElementById('tvpik-photo'),
            tag: document.getElementById('tvpik-tag'),
            title: document.getElementById('tvpik-title'),
            price: document.getElementById('tvpik-price'),
            items: document.getElementById('tvpik-items'),
            action: document.getElementById('tvpik-action'),
            phoneStatus: document.getElementById('tvpik-phone-status'),
            updated: document.getElementById('tvpik-updated'),
        };

        if (slides.length && tv && screen) {
            slides.forEach(function (_, i) {
                const dot = document.createElement('span');
                dot.className = 'landing-tvpik-dot' + (i === 0 ? ' is-active' : '');
                if (dotsWrap) dotsWrap.appendChild(dot);
            });

            let current = 0;
            let busy = false;

            function setTheme(theme) {
                screen.classList.remove('landing-tvpik-tv__screen--warm', 'landing-tvpik-tv__screen--dark', 'landing-tvpik-tv__screen--menu');
                screen.classList.add('landing-tvpik-tv__screen--' + (theme || 'warm'));
            }

            function applySlide(index) {
                const slide = slides[index];
                if (!slide) return;

                if (el.photo) el.photo.src = slide.image;
                if (el.tag) el.tag.textContent = slide.tag;
                if (el.title) el.title.textContent = slide.title;
                if (el.price) el.price.textContent = slide.price;
                if (el.action) el.action.textContent = slide.action;
                setTheme(slide.theme);

                if (el.items) {
                    el.items.innerHTML = '';
                    if (slide.theme === 'menu' && slide.items && slide.items.length) {
                        el.items.classList.remove('hidden');
                        slide.items.forEach(function (item) {
                            const li = document.createElement('li');
                            li.textContent = item;
                            el.items.appendChild(li);
                        });
                        if (el.price) el.price.classList.add('hidden');
                    } else {
                        el.items.classList.add('hidden');
                        if (el.price) el.price.classList.remove('hidden');
                    }
                }

                if (dotsWrap) {
                    dotsWrap.querySelectorAll('.landing-tvpik-dot').forEach(function (node, i) {
                        node.classList.toggle('is-active', i === index);
                    });
                }
            }

            function runSyncCycle(nextIndex) {
                if (busy) return;
                busy = true;

                if (phone) {
                    phone.classList.add('is-publishing', 'is-switching');
                }
                if (el.phoneStatus) {
                    el.phoneStatus.textContent = 'Publicando…';
                    el.phoneStatus.classList.remove('is-done');
                }
                if (sync) sync.classList.add('is-active');
                if (el.updated) el.updated.classList.remove('is-visible');

                setTimeout(function () {
                    if (tv) tv.classList.add('is-switching');
                    if (phone) phone.classList.remove('is-switching');
                }, 400);

                setTimeout(function () {
                    current = nextIndex;
                    applySlide(current);
                    if (tv) tv.classList.remove('is-switching');
                }, 750);

                setTimeout(function () {
                    if (sync) sync.classList.remove('is-active');
                    if (phone) phone.classList.remove('is-publishing');
                    if (el.phoneStatus) {
                        el.phoneStatus.textContent = 'Sincronizado con TVPik';
                        el.phoneStatus.classList.add('is-done');
                    }
                    if (el.updated) el.updated.classList.add('is-visible');
                }, 1200);

                setTimeout(function () {
                    if (el.updated) el.updated.classList.remove('is-visible');
                    busy = false;
                }, 2200);
            }

            applySlide(0);
            setInterval(function () {
                runSyncCycle((current + 1) % slides.length);
            }, 4200);
        }
    }

    window.addEventListener('scroll', function () {
        const nav = document.querySelector('[data-landing-nav]');
        if (!nav) return;
        if (window.scrollY > 20) {
            nav.classList.add('shadow-md');
        } else {
            nav.classList.remove('shadow-md');
        }
    });

    const suggestionModal = document.getElementById('suggestion-modal');
    const suggestionOpen = document.getElementById('suggestion-open');
    const suggestionForm = document.getElementById('suggestion-form');
    const suggestionError = document.getElementById('suggestion-error');
    const suggestionSuccess = document.getElementById('suggestion-success');
    const suggestionSubmit = document.getElementById('suggestion-submit');

    function openSuggestionModal() {
        if (!suggestionModal) return;
        suggestionModal.hidden = false;
        suggestionModal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        const firstInput = suggestionModal.querySelector('#suggestion-name');
        if (firstInput) {
            setTimeout(function () { firstInput.focus(); }, 50);
        }
    }

    function closeSuggestionModal() {
        if (!suggestionModal) return;
        suggestionModal.hidden = true;
        suggestionModal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    if (suggestionOpen) {
        suggestionOpen.addEventListener('click', openSuggestionModal);
    }

    if (suggestionModal) {
        suggestionModal.querySelectorAll('[data-suggestion-close]').forEach(function (el) {
            el.addEventListener('click', closeSuggestionModal);
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !suggestionModal.hidden) {
                closeSuggestionModal();
            }
        });
    }

    if (suggestionForm) {
        suggestionForm.addEventListener('submit', function (e) {
            e.preventDefault();
            if (suggestionError) {
                suggestionError.classList.add('hidden');
                suggestionError.textContent = '';
            }
            if (suggestionSuccess) {
                suggestionSuccess.classList.add('hidden');
                suggestionSuccess.textContent = '';
            }
            if (suggestionSubmit) {
                suggestionSubmit.disabled = true;
            }

            fetch(suggestionForm.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: new FormData(suggestionForm),
            })
                .then(function (res) {
                    return res.json().then(function (data) {
                        return { ok: res.ok, data: data };
                    });
                })
                .then(function (result) {
                    if (result.ok) {
                        if (suggestionSuccess) {
                            suggestionSuccess.textContent = result.data.message || '¡Gracias! Hemos recibido tu sugerencia.';
                            suggestionSuccess.classList.remove('hidden');
                        }
                        suggestionForm.reset();
                        setTimeout(closeSuggestionModal, 2200);
                    } else if (suggestionError) {
                        const msg = (result.data && result.data.message)
                            || (result.data && result.data.errors && Object.values(result.data.errors).flat().join(' '))
                            || 'Revisa los campos e inténtalo de nuevo.';
                        suggestionError.textContent = msg;
                        suggestionError.classList.remove('hidden');
                    }
                })
                .catch(function () {
                    if (suggestionError) {
                        suggestionError.textContent = 'No se pudo enviar. Comprueba tu conexión e inténtalo de nuevo.';
                        suggestionError.classList.remove('hidden');
                    }
                })
                .finally(function () {
                    if (suggestionSubmit) {
                        suggestionSubmit.disabled = false;
                    }
                });
        });
    }
})();
