(function () {
    // Rotador para [data-cycle]. Dos modos según data-cycle-mode:
    //   - "typewriter": escribe/borra letra a letra con cursor (business).
    //   - "slide": loop continuo lento; el saliente sube con fade-out mientras
    //     el entrante asoma desde abajo en paralelo (feature).
    const dwellTimeForCycle = function (kind) {
        switch (kind) {
            case 'business': return 2400;   // pausa entre palabras escritas
            case 'feature': return 2600;    // espera con la frase totalmente visible
            default: return 2800;
        }
    };
    const prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const cycleNodes = document.querySelectorAll('[data-cycle]');

    const initTypewriter = function (root, items) {
        const textEl = root.querySelector('.hero-cycle__text');
        if (!textEl) return null;
        const kind = root.getAttribute('data-cycle');
        const dwell = dwellTimeForCycle(kind);
        const eraseSpeed = 35;
        const typeSpeed = 70;
        let index = 0;
        let pending = null;
        let stopped = false;

        const sleep = function (ms) {
            return new Promise(function (resolve) {
                pending = window.setTimeout(resolve, ms);
            });
        };
        const erase = function () {
            return new Promise(function (resolve) {
                const tick = function () {
                    if (stopped) return resolve();
                    const txt = textEl.textContent;
                    if (txt.length === 0) return resolve();
                    textEl.textContent = txt.slice(0, -1);
                    pending = window.setTimeout(tick, eraseSpeed);
                };
                tick();
            });
        };
        const type = function (target) {
            return new Promise(function (resolve) {
                let i = textEl.textContent.length;
                const tick = function () {
                    if (stopped) return resolve();
                    if (i >= target.length) return resolve();
                    i++;
                    textEl.textContent = target.slice(0, i);
                    pending = window.setTimeout(tick, typeSpeed);
                };
                tick();
            });
        };

        const loop = async function () {
            while (!stopped) {
                await sleep(dwell);
                if (stopped) break;
                await erase();
                if (stopped) break;
                index = (index + 1) % items.length;
                await type(items[index]);
            }
        };

        return {
            start: function () {
                stopped = false;
                loop();
            },
            stop: function () {
                stopped = true;
                if (pending) { window.clearTimeout(pending); pending = null; }
            },
        };
    };

    const initSlide = function (root, items) {
        const kind = root.getAttribute('data-cycle');
        const dwell = dwellTimeForCycle(kind);
        const viewport = root.querySelector('.hero-cycle__viewport') || root;
        let current = viewport.querySelector('.hero-cycle__item.is-active');
        let index = 0;
        let pendingTimeout = null;
        let stopped = false;
        const transitionMs = 1100;

        const advance = function () {
            if (stopped) return;
            const nextIndex = (index + 1) % items.length;
            const incoming = document.createElement('span');
            incoming.className = 'hero-cycle__item is-entering';
            incoming.textContent = items[nextIndex];
            viewport.appendChild(incoming);
            void incoming.offsetWidth;

            if (current) {
                current.classList.remove('is-active');
                current.classList.add('is-leaving');
            }
            incoming.classList.remove('is-entering');
            incoming.classList.add('is-active');

            const previous = current;
            current = incoming;
            index = nextIndex;

            window.setTimeout(function () {
                if (previous && previous.parentNode === viewport) {
                    viewport.removeChild(previous);
                }
                if (!stopped) {
                    pendingTimeout = window.setTimeout(advance, dwell);
                }
            }, transitionMs + 80);
        };

        const scheduleLoop = function () {
            pendingTimeout = window.setTimeout(advance, dwell);
        };

        return {
            start: function () {
                stopped = false;
                scheduleLoop();
            },
            stop: function () {
                stopped = true;
                if (pendingTimeout !== null) {
                    window.clearTimeout(pendingTimeout);
                    pendingTimeout = null;
                }
            },
        };
    };

    cycleNodes.forEach(function (root) {
        let items;
        try {
            items = JSON.parse(root.getAttribute('data-cycle-items') || '[]');
        } catch (_) {
            items = [];
        }
        if (!Array.isArray(items) || items.length < 2) return;
        if (prefersReducedMotion) return;

        const mode = root.getAttribute('data-cycle-mode') || 'slide';
        const controller = mode === 'typewriter'
            ? initTypewriter(root, items)
            : initSlide(root, items);
        if (!controller) return;

        const heroSection = root.closest('section');
        if (heroSection) {
            heroSection.addEventListener('mouseenter', controller.stop);
            heroSection.addEventListener('mouseleave', controller.start);
        }
        controller.start();
    });

    // Hero chips — cada chip rota de forma independiente con sus propias variantes
    (function initHeroChips() {
        var chipEls = document.querySelectorAll('[data-hero-chip]');
        if (!chipEls.length) return;

        chipEls.forEach(function (el) {
            var variants;
            try { variants = JSON.parse(el.getAttribute('data-hero-chip-variants') || '[]'); } catch (_) { variants = []; }
            var interval = parseInt(el.getAttribute('data-hero-chip-interval') || '4000', 10);
            var offset   = parseInt(el.getAttribute('data-hero-chip-offset')   || '0',    10);
            var idx = 0;
            var timerId = null;
            var isLight = el.classList.contains('hero-chip--light');

            function applyVariant(v) {
                var iconEl   = el.querySelector('.hero-chip__icon .material-symbols-outlined');
                var textEl   = el.querySelector('.hero-chip__text');
                var labelEl  = el.querySelector('.hero-chip__label');
                var valueEl  = el.querySelector('.hero-chip__value');
                if (iconEl && v.icon) iconEl.textContent = v.icon;
                if (textEl && v.text !== undefined) textEl.textContent = v.text;
                if (labelEl && v.label !== undefined) labelEl.textContent = v.label;
                if (valueEl && v.value !== undefined) valueEl.textContent = v.value;
            }

            function showChip() {
                el.classList.remove('is-hiding');
                el.classList.add('is-visible');
            }
            function hideAndSwap() {
                if (variants.length < 2) return;
                el.classList.remove('is-visible');
                el.classList.add('is-hiding');
                setTimeout(function () {
                    idx = (idx + 1) % variants.length;
                    applyVariant(variants[idx]);
                    el.classList.remove('is-hiding');
                    el.classList.add('is-visible');
                }, 500);
            }

            if (prefersReducedMotion) {
                el.classList.add('is-visible');
                return;
            }

            // Mostrar al inicio con offset
            setTimeout(function () {
                showChip();
                timerId = window.setInterval(hideAndSwap, interval);
            }, offset);

            // Pausa al hover en la sección
            var heroSection = el.closest('section');
            if (heroSection) {
                heroSection.addEventListener('mouseenter', function () {
                    if (timerId) { clearInterval(timerId); timerId = null; }
                });
                heroSection.addEventListener('mouseleave', function () {
                    if (!timerId) timerId = window.setInterval(hideAndSwap, interval);
                });
            }
        });
    }());

    // Proceso — animaciones secuenciales activadas por IntersectionObserver
    (function initProcessAnimations() {
        var slides = Array.from(document.querySelectorAll('[data-process-slide]'));
        if (!slides.length) return;

        if (prefersReducedMotion) {
            slides.forEach(function (s) { s.classList.add('is-animated'); });
            return;
        }

        // En desktop (md+) activamos los tres en secuencia cuando el proceso entra en vista
        // En móvil, activamos cada slide cuando entra en el viewport del scroll horizontal
        var isMobile = function () { return window.innerWidth < 768; };

        // Delays secuenciales solo para pasos 1 y 3 (foto/PDF y QR)
        var animateSlides = slides.filter(function (s) {
            return s.hasAttribute('data-process-animate');
        });
        var stepDelays = [0, 1200];

        // Observa el contenedor del proceso para desktop
        var processSection = document.getElementById('process');
        var activated = false;

        function activateAll() {
            if (activated) return;
            activated = true;
            animateSlides.forEach(function (slide, i) {
                setTimeout(function () {
                    slide.classList.add('is-animated');
                }, stepDelays[i] || 0);
            });
        }

        if ('IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting) return;
                    if (!isMobile()) {
                        // Desktop: animar los tres en secuencia
                        activateAll();
                    } else {
                        // Móvil: animar solo slides con ilustración animada visibles
                        animateSlides.forEach(function (slide) {
                            var r = slide.getBoundingClientRect();
                            if (r.left < window.innerWidth && r.right > 0) {
                                slide.classList.add('is-animated');
                            }
                        });
                    }
                    // Reiniciar si vuelven a salir de viewport (desktop)
                    if (!isMobile()) observer.disconnect();
                });
            }, { threshold: 0.25 });

            if (processSection) observer.observe(processSection);

            // En móvil también observar cada slide individualmente
            animateSlides.forEach(function (slide) {
                var mobileObs = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting && isMobile()) {
                            slide.classList.add('is-animated');
                            mobileObs.disconnect();
                        }
                    });
                }, { threshold: 0.5 });
                mobileObs.observe(slide);
            });
        } else {
            // Fallback: activar todo de golpe
            activateAll();
        }
    }());

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
        var textPresets = [];
        try {
            var customizeWrap = document.getElementById('personalizable');
            textPresets = JSON.parse(customizeWrap.getAttribute('data-customize-presets') || '[]');
        } catch (e) {
            textPresets = [];
        }

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
                section: 'Burgers',
                dish: 'Double Smash',
                price: '8,90 €',
                desc: 'Doble carne, cheddar y salsa house.',
                hint: 'Logo y cabecera',
                primary: '#e31837',
                bg: '#fffbeb',
                surface: '#fef3c7',
                text: '#1c1917',
                muted: '#57534e',
                thumb: 'linear-gradient(135deg, #e31837 0%, #fbbf24 100%)',
            },
        ];

        presets.forEach(function (preset, i) {
            if (textPresets[i]) {
                Object.assign(preset, textPresets[i]);
            }
        });
        presets.length = Math.min(presets.length, 3);

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

        if (el.swatches) {
            el.swatches.innerHTML = '';
        }
        presets.forEach(function (preset, i) {
            const swatch = document.createElement('span');
            swatch.className = 'landing-customize-swatch' + (i === 0 ? ' is-active' : '');
            swatch.style.background = preset.primary;
            swatch.dataset.index = String(i);
            if (el.swatches) {
                el.swatches.appendChild(swatch);
            }
        });

        const pickerRoot = document.querySelector('[data-template-picker]');
        const pickerCards = pickerRoot
            ? Array.from(pickerRoot.querySelectorAll('.landing-template-picker__card'))
            : [];

        let current = 0;
        let autoplayId = null;

        function applyPreset(index) {
            const p = presets[index];
            if (!p) return;

            if (p.primary) phone.style.setProperty('--cust-primary', p.primary);
            if (p.bg)      phone.style.setProperty('--cust-bg', p.bg);
            if (p.surface) phone.style.setProperty('--cust-surface', p.surface);
            if (p.text)    phone.style.setProperty('--cust-text', p.text);
            if (p.muted)   phone.style.setProperty('--cust-muted', p.muted);
            if (p.thumb)   phone.style.setProperty('--cust-thumb', p.thumb);
            if (p.preview) {
                phone.style.setProperty('--cust-thumb-img', 'url("' + p.preview + '")');
            } else {
                phone.style.removeProperty('--cust-thumb-img');
            }
            // solo asignamos borderColor si no es el teléfono real (que usa un marco fijo oscuro)
            if (!phone.classList.contains('tpl-phone')) {
                phone.style.borderColor = p.bg === '#0a0a0a' || p.bg === '#0a0e14' ? '#2a2a2a' : '#e5e7eb';
            }

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

        function setActiveTemplate(index) {
            if (index < 0 || index >= presets.length) return;
            current = index;
            applyPreset(index);
            pickerCards.forEach(function (card, i) {
                const active = i === index;
                card.classList.toggle('is-active', active);
                card.setAttribute('aria-selected', active ? 'true' : 'false');
            });
        }

        function switchPreset() {
            phone.classList.add('is-switching');
            if (el.hint) el.hint.classList.add('is-fading');

            setTimeout(function () {
                setActiveTemplate((current + 1) % presets.length);
                phone.classList.remove('is-switching');
                if (el.hint) el.hint.classList.remove('is-fading');
            }, 320);
        }

        function startAutoplay() {
            if (prefersReducedMotion || presets.length < 2) return;
            if (autoplayId !== null) return;
            autoplayId = window.setInterval(switchPreset, 3200);
        }

        function stopAutoplay() {
            if (autoplayId !== null) {
                window.clearInterval(autoplayId);
                autoplayId = null;
            }
        }

        setActiveTemplate(0);
        startAutoplay();

        pickerCards.forEach(function (card) {
            card.addEventListener('click', function () {
                const idx = parseInt(card.getAttribute('data-template-index'), 10);
                if (isNaN(idx) || idx === current) return;
                stopAutoplay();
                phone.classList.add('is-switching');
                if (el.hint) el.hint.classList.add('is-fading');
                setTimeout(function () {
                    setActiveTemplate(idx);
                    phone.classList.remove('is-switching');
                    if (el.hint) el.hint.classList.remove('is-fading');
                    startAutoplay();
                }, 320);
            });
        });

        const personalizeSection = document.getElementById('personalizable');
        if (personalizeSection) {
            personalizeSection.addEventListener('mouseenter', stopAutoplay);
            personalizeSection.addEventListener('mouseleave', startAutoplay);
        }
    }

    // Slider unificado de plantillas TV (Hero, Tapas, Daily, Video, Menu)
    document.querySelectorAll('[data-tv-show]').forEach(function (root) {
        const slides = root.querySelectorAll('[data-tv-slide]');
        const dots = root.querySelectorAll('[data-tv-dot]');
        const prevBtn = root.querySelector('[data-tv-prev]');
        const nextBtn = root.querySelector('[data-tv-next]');
        if (!slides.length) return;

        const total = slides.length;
        let current = 0;
        let timer = null;

        const reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const autoplay = root.dataset.tvAutoplay !== 'false' && !reducedMotion;
        const intervalMs = 4200;

        function goTo(next) {
            if (next === current) return;
            next = (next + total) % total;
            slides.forEach(function (slide, i) {
                const active = i === next;
                slide.classList.toggle('is-active', active);
                slide.setAttribute('aria-hidden', active ? 'false' : 'true');
            });
            dots.forEach(function (dot, i) {
                const active = i === next;
                dot.classList.toggle('is-active', active);
                dot.setAttribute('aria-selected', active ? 'true' : 'false');
            });
            current = next;
        }

        function restartTimer() {
            if (!autoplay) return;
            if (timer) clearInterval(timer);
            timer = setInterval(function () { goTo(current + 1); }, intervalMs);
        }

        dots.forEach(function (dot, i) {
            dot.addEventListener('click', function () { goTo(i); restartTimer(); });
        });
        if (prevBtn) prevBtn.addEventListener('click', function () { goTo(current - 1); restartTimer(); });
        if (nextBtn) nextBtn.addEventListener('click', function () { goTo(current + 1); restartTimer(); });

        root.addEventListener('mouseenter', function () { if (timer) clearInterval(timer); });
        root.addEventListener('mouseleave', restartTimer);

        restartTimer();
    });

    // Slider de funciones: drag con ratón en escritorio, flechas, dots y autoplay 5s
    document.querySelectorAll('[data-feat-slider]').forEach(function (wrap) {
        const track = wrap.querySelector('[data-feat-track]');
        if (!track) return;

        const slides = Array.from(track.querySelectorAll('[data-feat-slide]'));
        if (!slides.length) return;

        const prevBtn = wrap.querySelector('[data-feat-prev]');
        const nextBtn = wrap.querySelector('[data-feat-next]');
        const dotsHost = wrap.parentElement ? wrap.parentElement.querySelector('[data-feat-dots]') : null;
        const dots = dotsHost ? Array.from(dotsHost.querySelectorAll('[data-feat-dot]')) : [];

        const reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const isDesktop = function () {
            return window.matchMedia && window.matchMedia('(min-width: 768px)').matches;
        };

        function indexFromScroll() {
            const center = track.scrollLeft + (track.clientWidth / 2);
            let best = 0;
            let bestDist = Infinity;
            slides.forEach(function (slide, i) {
                const mid = slide.offsetLeft + (slide.offsetWidth / 2);
                const dist = Math.abs(mid - center);
                if (dist < bestDist) { bestDist = dist; best = i; }
            });
            return best;
        }

        function updateActive(idx) {
            dots.forEach(function (dot, i) {
                const active = i === idx;
                dot.classList.toggle('is-active', active);
                dot.setAttribute('aria-selected', active ? 'true' : 'false');
            });
            if (prevBtn) prevBtn.setAttribute('aria-disabled', idx === 0 ? 'true' : 'false');
            if (nextBtn) nextBtn.setAttribute('aria-disabled', idx === slides.length - 1 ? 'true' : 'false');
        }

        function goTo(idx, behavior) {
            idx = Math.max(0, Math.min(slides.length - 1, idx));
            const slide = slides[idx];
            if (!slide) return;
            const left = slide.offsetLeft - 16; // respeta padding inicial
            track.scrollTo({ left: left, behavior: behavior || (reducedMotion ? 'auto' : 'smooth') });
            updateActive(idx);
        }

        function nextSlide(loop) {
            const cur = indexFromScroll();
            if (cur >= slides.length - 1) {
                if (loop) goTo(0);
                return;
            }
            goTo(cur + 1);
        }

        function prevSlide() {
            const cur = indexFromScroll();
            goTo(cur - 1);
        }

        if (prevBtn) prevBtn.addEventListener('click', function () { prevSlide(); restartAutoplay(); });
        if (nextBtn) nextBtn.addEventListener('click', function () { nextSlide(false); restartAutoplay(); });

        dots.forEach(function (dot, i) {
            dot.addEventListener('click', function () { goTo(i); restartAutoplay(); });
        });

        let scrollSyncTimer = null;
        track.addEventListener('scroll', function () {
            if (scrollSyncTimer) clearTimeout(scrollSyncTimer);
            scrollSyncTimer = setTimeout(function () {
                updateActive(indexFromScroll());
            }, 80);
        }, { passive: true });

        // Drag con ratón (solo desktop). En móvil dejamos el scroll-touch nativo.
        let isDown = false;
        let startX = 0;
        let startScroll = 0;
        let movedDuringDrag = false;

        function onMouseDown(e) {
            if (e.button !== 0) return;
            if (!isDesktop()) return;
            isDown = true;
            movedDuringDrag = false;
            startX = e.pageX - track.offsetLeft;
            startScroll = track.scrollLeft;
            track.classList.add('is-grabbing');
            pauseAutoplay();
        }
        function onMouseMove(e) {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - track.offsetLeft;
            const dx = x - startX;
            if (Math.abs(dx) > 4) movedDuringDrag = true;
            track.scrollLeft = startScroll - dx;
        }
        function endDrag() {
            if (!isDown) return;
            isDown = false;
            track.classList.remove('is-grabbing');
            // Si hubo drag real, snap al slide más cercano para corregir el suelta.
            if (movedDuringDrag) {
                goTo(indexFromScroll());
            }
            restartAutoplay();
        }

        track.addEventListener('mousedown', onMouseDown);
        track.addEventListener('mousemove', onMouseMove);
        track.addEventListener('mouseup', endDrag);
        track.addEventListener('mouseleave', endDrag);

        // Si el usuario soltó pero el cursor terminó fuera, cazar globalmente.
        document.addEventListener('mouseup', endDrag);

        // Evita que el click "post-drag" abra enlaces si los hubiera.
        track.addEventListener('click', function (e) {
            if (movedDuringDrag) {
                e.preventDefault();
                e.stopPropagation();
                movedDuringDrag = false;
            }
        }, true);

        // Autoplay desktop: 5s por item, pausa con hover/focus/drag/visibilitychange
        let autoplayTimer = null;
        const intervalMs = 5000;

        function autoplayActive() {
            return isDesktop() && !reducedMotion;
        }
        function pauseAutoplay() { if (autoplayTimer) { clearInterval(autoplayTimer); autoplayTimer = null; } }
        function startAutoplay() {
            pauseAutoplay();
            if (!autoplayActive()) return;
            autoplayTimer = setInterval(function () { nextSlide(true); }, intervalMs);
        }
        function restartAutoplay() {
            pauseAutoplay();
            if (!autoplayActive()) return;
            // pequeño retraso para que la interacción del usuario tenga prioridad
            setTimeout(startAutoplay, 1200);
        }

        wrap.addEventListener('mouseenter', pauseAutoplay);
        wrap.addEventListener('mouseleave', startAutoplay);
        wrap.addEventListener('focusin', pauseAutoplay);
        wrap.addEventListener('focusout', startAutoplay);

        document.addEventListener('visibilitychange', function () {
            if (document.hidden) pauseAutoplay(); else startAutoplay();
        });

        if (window.matchMedia) {
            const mq = window.matchMedia('(min-width: 768px)');
            const onMqChange = function () { startAutoplay(); };
            if (mq.addEventListener) mq.addEventListener('change', onMqChange);
            else if (mq.addListener) mq.addListener(onMqChange);
        }

        updateActive(0);
        startAutoplay();
    });

    document.querySelectorAll('[data-process-slider]').forEach(function (wrap) {
        const track = wrap.querySelector('[data-process-track]');
        if (!track) return;

        const slides = Array.from(track.querySelectorAll('[data-process-slide]'));
        if (!slides.length) return;

        const prevBtn = wrap.querySelector('[data-process-prev]');
        const nextBtn = wrap.querySelector('[data-process-next]');
        const dotsHost = wrap.querySelector('[data-process-dots]');
        const reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const isMobile = function () {
            return window.matchMedia && window.matchMedia('(max-width: 767px)').matches;
        };

        if (dotsHost && !dotsHost.children.length) {
            slides.forEach(function (_, i) {
                const dot = document.createElement('button');
                dot.type = 'button';
                dot.className = 'wn-process-slider__dot' + (i === 0 ? ' is-active' : '');
                dot.setAttribute('data-process-dot', '');
                dot.setAttribute('aria-label', 'Paso ' + (i + 1));
                dotsHost.appendChild(dot);
            });
        }
        const dots = dotsHost ? Array.from(dotsHost.querySelectorAll('[data-process-dot]')) : [];

        function indexFromScroll() {
            const center = track.scrollLeft + (track.clientWidth / 2);
            let best = 0;
            let bestDist = Infinity;
            slides.forEach(function (slide, i) {
                const mid = slide.offsetLeft + (slide.offsetWidth / 2);
                const dist = Math.abs(mid - center);
                if (dist < bestDist) { bestDist = dist; best = i; }
            });
            return best;
        }

        function updateActive(idx) {
            dots.forEach(function (dot, i) {
                dot.classList.toggle('is-active', i === idx);
            });
            if (prevBtn) prevBtn.setAttribute('aria-disabled', idx === 0 ? 'true' : 'false');
            if (nextBtn) nextBtn.setAttribute('aria-disabled', idx === slides.length - 1 ? 'true' : 'false');
        }

        function goTo(idx, behavior) {
            if (!isMobile()) return;
            idx = Math.max(0, Math.min(slides.length - 1, idx));
            const slide = slides[idx];
            if (!slide) return;
            track.scrollTo({ left: slide.offsetLeft - 8, behavior: behavior || (reducedMotion ? 'auto' : 'smooth') });
            updateActive(idx);
        }

        if (prevBtn) prevBtn.addEventListener('click', function () { goTo(indexFromScroll() - 1); });
        if (nextBtn) nextBtn.addEventListener('click', function () { goTo(indexFromScroll() + 1); });
        dots.forEach(function (dot, i) {
            dot.addEventListener('click', function () { goTo(i); });
        });

        let scrollSyncTimer = null;
        track.addEventListener('scroll', function () {
            if (!isMobile()) return;
            if (scrollSyncTimer) clearTimeout(scrollSyncTimer);
            scrollSyncTimer = setTimeout(function () {
                updateActive(indexFromScroll());
            }, 80);
        }, { passive: true });

        updateActive(0);
    });

    (function initLandingLangSelect() {
        if (!window.__landingLangSelectGlobals) {
            window.__landingLangSelectGlobals = true;
            document.addEventListener('click', function (e) {
                document.querySelectorAll('[data-landing-lang].is-open').forEach(function (root) {
                    if (!root.contains(e.target)) {
                        closeLandingLangSelect(root);
                    }
                });
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    document.querySelectorAll('[data-landing-lang].is-open').forEach(closeLandingLangSelect);
                }
            });
        }

        function closeLandingLangSelect(root) {
            const menu = root.querySelector('.landing-lang-select__menu');
            const btn = root.querySelector('.landing-lang-select__trigger');
            root.classList.remove('is-open');
            if (menu) {
                menu.hidden = true;
            }
            if (btn) {
                btn.setAttribute('aria-expanded', 'false');
            }
        }

        function openLandingLangSelect(root) {
            document.querySelectorAll('[data-landing-lang].is-open').forEach(function (other) {
                if (other !== root) {
                    closeLandingLangSelect(other);
                }
            });
            const menu = root.querySelector('.landing-lang-select__menu');
            const btn = root.querySelector('.landing-lang-select__trigger');
            root.classList.add('is-open');
            if (menu) {
                menu.hidden = false;
            }
            if (btn) {
                btn.setAttribute('aria-expanded', 'true');
            }
        }

        document.querySelectorAll('[data-landing-lang]').forEach(function (root) {
            if (root.dataset.langReady === '1') {
                return;
            }
            root.dataset.langReady = '1';

            const btn = root.querySelector('.landing-lang-select__trigger');
            const menu = root.querySelector('.landing-lang-select__menu');
            if (!btn || !menu) {
                return;
            }

            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                if (root.classList.contains('is-open')) {
                    closeLandingLangSelect(root);
                } else {
                    openLandingLangSelect(root);
                }
            });
        });
    })();

    document.querySelectorAll('[data-landing-user-menu]').forEach(function (wrap) {
        const toggle = wrap.querySelector('[data-landing-user-menu-toggle]');
        const panel = wrap.querySelector('[data-landing-user-menu-panel]');
        if (!toggle || !panel) return;

        function closeMenu() {
            wrap.classList.remove('is-open');
            panel.classList.add('hidden');
            toggle.setAttribute('aria-expanded', 'false');
        }

        function openMenu() {
            wrap.classList.add('is-open');
            panel.classList.remove('hidden');
            toggle.setAttribute('aria-expanded', 'true');
        }

        toggle.addEventListener('click', function (e) {
            e.stopPropagation();
            if (wrap.classList.contains('is-open')) {
                closeMenu();
            } else {
                openMenu();
            }
        });

        document.addEventListener('click', function (e) {
            if (!wrap.contains(e.target)) {
                closeMenu();
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeMenu();
            }
        });
    });

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
