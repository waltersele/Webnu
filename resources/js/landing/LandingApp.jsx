import React, { useEffect, useState } from 'react';
import { motion } from 'framer-motion';
import { buildLandingContent } from './data';
import {
    fadeUp,
    scaleIn,
    stagger,
    MotionSection,
    MotionDiv,
    MotionH1,
    MotionH3,
    MotionP,
    MotionUl,
    MotionArticle,
    Reveal,
    RevealChild,
    RevealVisibleProvider,
    useReveal,
    useRevealVisible,
} from './motion';

function Icon({ name }) {
    const paths = {
        brush: 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01',
        devices: 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z',
        docs: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        support: 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z',
        updates: 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
        pdf: 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z',
        save: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 10v1m0-13a9 9 0 110 18 9 9 0 010-18z',
        print: 'M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z',
        check: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        qr: 'M3 3h4v4H3V3zm14 0h4v4h-4V3zM3 17h4v4H3v-4zm8 0h2v2h-2v-2zm4 0h4v4h-4v-4z',
    };
    return (
        <svg className="wn-landing-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" aria-hidden>
            <path strokeLinecap="round" strokeLinejoin="round" d={paths[name] || paths.check} />
        </svg>
    );
}

function StatCounter({ value, suffix, label }) {
    const [display, setDisplay] = useState(0);
    const [started, setStarted] = useState(false);
    const parentVisible = useRevealVisible();

    useEffect(() => {
        if (!parentVisible || started) {
            return;
        }
        setStarted(true);
        const duration = 1400;
        const start = performance.now();
        const tick = (now) => {
            const p = Math.min((now - start) / duration, 1);
            setDisplay(Math.floor(value * (1 - Math.pow(1 - p, 3))));
            if (p < 1) {
                requestAnimationFrame(tick);
            }
        };
        requestAnimationFrame(tick);
    }, [parentVisible, started, value]);

    return (
        <RevealChild as={MotionArticle} className="wn-stat" variants={scaleIn}>
            <Icon name="qr" />
            <strong>
                {display.toLocaleString('es-ES')}
                {suffix}
            </strong>
            <span>{label}</span>
        </RevealChild>
    );
}

function TvpikScene({ content }) {
    const reveal = useReveal();

    return (
        <motion.div ref={reveal.ref} className="wn-reveal-anchor">
            <RevealVisibleProvider visible={reveal.visible}>
                <RevealChild as={MotionDiv} className="wn-tvpik-scene" variants={scaleIn}>
                    <img src={content.tvpik.barImage} alt="" className="wn-tvpik-scene__bg" />
                    <motion.div
                        className="wn-tvpik-tv"
                        animate={{ rotateY: [-6, -4, -6] }}
                        transition={{ duration: 6, repeat: Infinity, ease: 'easeInOut' }}
                    >
                        <motion.div className="wn-tvpik-tv__screen">
                            <img src={content.tvpik.dishImage} alt="Plato en pantalla" />
                            <motion.div
                                className="wn-tvpik-tv__overlay"
                                initial={{ opacity: 0, y: 12 }}
                                animate={reveal.visible ? { opacity: 1, y: 0 } : { opacity: 0, y: 12 }}
                                transition={{ delay: 0.4 }}
                            >
                                <span>Plato del día</span>
                                <strong>Solomillo con ajetes</strong>
                                <em>19,60 €</em>
                            </motion.div>
                        </motion.div>
                    </motion.div>
                </RevealChild>
            </RevealVisibleProvider>
        </motion.div>
    );
}

export default function LandingApp({ config }) {
    const content = buildLandingContent(config);
    const [navScrolled, setNavScrolled] = useState(false);
    const [scrollProgress, setScrollProgress] = useState(0);

    useEffect(() => {
        const onScroll = () => {
            setNavScrolled(window.scrollY > 24);
            const doc = document.documentElement;
            const max = doc.scrollHeight - doc.clientHeight;
            setScrollProgress(max > 0 ? window.scrollY / max : 0);
        };
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
        return () => window.removeEventListener('scroll', onScroll);
    }, []);

    const scrollTo = (id) => {
        document.getElementById(id)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    return (
        <motion.div className="wn-landing">
            <motion.div className="wn-landing__progress" style={{ scaleX: scrollProgress }} />

            <motion.div className="wn-landing__preview-banner">
                Vista previa de la nueva landing · La versión actual sigue en{' '}
                <a href={config.routes.home}>webnu.es</a>
            </motion.div>

            <motion.header
                className={`wn-nav ${navScrolled ? 'is-scrolled' : ''}`}
                initial={{ y: -80, opacity: 0 }}
                animate={{ y: 0, opacity: 1 }}
                transition={{ duration: 0.55, ease: [0.22, 1, 0.36, 1] }}
            >
                <motion.div className="wn-nav__inner">
                    <a href="#inicio" className="wn-nav__logo" onClick={(e) => { e.preventDefault(); scrollTo('inicio'); }}>
                        <img src={config.assets.logo} alt="Webnu" />
                    </a>
                    <nav className="wn-nav__links">
                        {content.nav.map((item) => (
                            <a
                                key={item.id}
                                href={`#${item.id}`}
                                onClick={(e) => {
                                    e.preventDefault();
                                    scrollTo(item.id);
                                }}
                            >
                                {item.label}
                            </a>
                        ))}
                    </nav>
                    <motion.a
                        href={config.routes.login}
                        className="wn-btn wn-btn--primary wn-btn--sm"
                        whileHover={{ scale: 1.03 }}
                        whileTap={{ scale: 0.98 }}
                    >
                        Mi cuenta
                    </motion.a>
                </motion.div>
            </motion.header>

            <MotionSection id="inicio" className="wn-hero">
                <div className="wn-hero__glow wn-hero__glow--1" />
                <motion.div
                    className="wn-hero__glow wn-hero__glow--2"
                    animate={{ x: [0, 24, 0], y: [0, -16, 0] }}
                    transition={{ duration: 14, repeat: Infinity, ease: 'easeInOut' }}
                />
                <motion.div className="wn-container wn-hero__grid">
                    <MotionDiv className="wn-hero__copy" variants={stagger} initial="hidden" animate="visible">
                        <MotionP className="wn-eyebrow" variants={fadeUp} custom={0}>
                            Carta digital para restaurantes
                        </MotionP>
                        <MotionH1 className="wn-hero__title" variants={fadeUp} custom={0.05}>
                            {content.hero.title}{' '}
                            <span>{content.hero.titleHighlight}</span> Pruébalo.
                        </MotionH1>
                        <MotionP className="wn-hero__subtitle" variants={fadeUp} custom={0.12}>
                            {content.hero.subtitle}
                        </MotionP>
                        <MotionDiv variants={fadeUp} custom={0.2}>
                            <a href="#precios" className="wn-btn wn-btn--ghost" onClick={(e) => { e.preventDefault(); scrollTo('precios'); }}>
                                Ver planes
                            </a>
                        </MotionDiv>
                    </MotionDiv>

                    <MotionDiv className="wn-hero__card" variants={scaleIn} initial="hidden" animate="visible" custom={0.15}>
                        <p className="wn-hero__card-title">
                            <Icon name="qr" /> {content.hero.formTitle}
                        </p>
                        <form id="subscription-form" action={config.routes.subscribe} method="POST" className="wn-form">
                            <input type="hidden" name="_token" value={config.csrfToken} />
                            <input type="hidden" name="payment_method" id="payment_method" />
                            <label>
                                Email
                                <input type="email" name="email" required placeholder="tu@email.com" />
                            </label>
                            <label>
                                Contraseña
                                <input type="password" name="password" required minLength={8} placeholder="Mínimo 8 caracteres" />
                            </label>
                            <label>
                                Confirmar contraseña
                                <input type="password" name="password_confirmation" required minLength={8} />
                            </label>
                            <label>
                                Suscripción
                                <select name="subscription" required defaultValue="1">
                                    <option value="1">Mensual 10€ / Mes</option>
                                    <option value="2">Anual 100€ / Año</option>
                                </select>
                            </label>
                            <label>
                                Datos de la tarjeta
                                <div id="card-element" className="wn-card-element" />
                                <div id="card-errors" className="wn-form-error" role="alert" />
                            </label>
                            <label className="wn-checkbox">
                                <input type="checkbox" name="privacy_policy" value="1" id="privacy-check" />
                                Acepto la política de privacidad
                            </label>
                            <div className="wn-form-error" id="privacy-check-not-checked" style={{ display: 'none' }}>
                                Debe aceptar la política de privacidad
                            </div>
                            <motion.button type="submit" className="wn-btn wn-btn--primary wn-btn--block" whileHover={{ scale: 1.02 }} whileTap={{ scale: 0.98 }}>
                                Contratar | 30 días GRATIS
                            </motion.button>
                        </form>
                    </MotionDiv>
                </motion.div>
            </MotionSection>

            <MotionSection id="exito" className="wn-section">
                <motion.div className="wn-container">
                    <Reveal className="wn-section-head" variants={fadeUp}>
                        <h2>{content.success.title}</h2>
                        <p>{content.success.subtitle}</p>
                    </Reveal>
                    <Reveal className="wn-split" variants={stagger}>
                        <RevealChild as={MotionDiv} className="wn-split__text" variants={fadeUp}>
                            <MotionH3>{content.success.blocks[0].title}</MotionH3>
                            <p>{content.success.blocks[0].text}</p>
                            <img src={content.success.blocks[0].image} alt="Clientes Webnu" className="wn-rounded-img" />
                            <motion.div className="wn-btn-row">
                                {content.success.blocks[0].demos.map((d) => (
                                    <a key={d.url} href={d.url} target="_blank" rel="noopener noreferrer" className="wn-btn wn-btn--outline">
                                        {d.label}
                                    </a>
                                ))}
                            </motion.div>
                        </RevealChild>
                        <RevealChild as={MotionDiv} className="wn-split__media" variants={scaleIn}>
                            <video autoPlay muted loop playsInline className="wn-phone-video">
                                <source src={content.success.video} type="video/mp4" />
                            </video>
                        </RevealChild>
                    </Reveal>
                    <Reveal className="wn-split wn-split--reverse" variants={stagger}>
                        <RevealChild as={MotionDiv} className="wn-split__media" variants={scaleIn}>
                            <img src={content.success.blocks[1].image} alt="" className="wn-rounded-img" />
                        </RevealChild>
                        <RevealChild as={MotionDiv} className="wn-split__text" variants={fadeUp}>
                            <MotionH3>{content.success.blocks[1].title}</MotionH3>
                            <p>{content.success.blocks[1].text}</p>
                        </RevealChild>
                    </Reveal>
                </motion.div>
            </MotionSection>

            <MotionSection id="ia" className="wn-section wn-section--ia">
                <Reveal className="wn-ia-card" variants={scaleIn} whileHover={{ y: -4 }}>
                    <motion.div className="wn-container wn-ia-card__inner">
                        <span className="wn-badge">{content.ia.badge}</span>
                        <h2>{content.ia.title}</h2>
                        <p>{content.ia.text}</p>
                        <ul>
                            {content.ia.points.map((pt) => (
                                <li key={pt}><Icon name="check" /> {pt}</li>
                            ))}
                        </ul>
                    </motion.div>
                </Reveal>
            </MotionSection>

            <MotionSection id="tvpik" className="wn-section wn-section--muted">
                <motion.div className="wn-container">
                    <Reveal className="wn-section-head" variants={fadeUp}>
                        <h2>{content.tvpik.title}</h2>
                        <p>{content.tvpik.subtitle}</p>
                    </Reveal>
                    <motion.div className="wn-tvpik-grid">
                        <Reveal className="wn-tvpik-copy" variants={fadeUp}>
                            <span className="wn-badge wn-badge--blue">{content.tvpik.badge}</span>
                            <MotionH3>{content.tvpik.heading}</MotionH3>
                            <p>{content.tvpik.text}</p>
                            <ul className="wn-check-list">
                                {content.tvpik.features.map((f) => (
                                    <li key={f}><Icon name="check" /> {f}</li>
                                ))}
                            </ul>
                            <a href={content.tvpik.demoUrl} target="_blank" rel="noopener noreferrer" className="wn-btn wn-btn--outline">
                                Ver carta de ejemplo
                            </a>
                        </Reveal>
                        <TvpikScene content={content} />
                    </motion.div>
                </motion.div>
            </MotionSection>

            <MotionSection id="feature" className="wn-section">
                <motion.div className="wn-container">
                    <Reveal className="wn-section-head" variants={fadeUp}>
                        <h2>{content.features.title}</h2>
                    </Reveal>
                    <Reveal className="wn-features" variants={stagger}>
                        <RevealChild as={MotionDiv} className="wn-features__col" variants={fadeUp}>
                            {content.features.left.map((f) => (
                                <motion.div key={f.title} className="wn-feature-item">
                                    <Icon name={f.icon} />
                                    <motion.div>
                                        <h4>{f.title}</h4>
                                        <p>{f.text}</p>
                                    </motion.div>
                                </motion.div>
                            ))}
                        </RevealChild>
                        <RevealChild as={MotionDiv} className="wn-features__center" variants={scaleIn}>
                            <motion.img
                                src={content.features.centerImage}
                                alt="Carta Webnu"
                                whileHover={{ scale: 1.03, rotate: 1 }}
                                transition={{ type: 'spring', stiffness: 260, damping: 18 }}
                            />
                        </RevealChild>
                        <RevealChild as={MotionDiv} className="wn-features__col" variants={fadeUp}>
                            {content.features.right.map((f) => (
                                <motion.div key={f.title} className="wn-feature-item">
                                    <Icon name={f.icon} />
                                    <motion.div>
                                        <h4>{f.title}</h4>
                                        <p>{f.text}</p>
                                    </motion.div>
                                </motion.div>
                            ))}
                        </RevealChild>
                    </Reveal>
                </motion.div>
            </MotionSection>

            <MotionSection id="ventajas" className="wn-section wn-section--muted">
                <motion.div className="wn-container">
                    <Reveal className="wn-section-head" variants={fadeUp}>
                        <h2>{content.advantages.title}</h2>
                    </Reveal>
                    <Reveal className="wn-advantage-lists" variants={stagger}>
                        {content.advantages.lists.map((list) => (
                            <RevealChild key={list.join()} as={MotionUl} className="wn-advantage-list" variants={fadeUp}>
                                {list.map((item) => (
                                    <li key={item}>{item}</li>
                                ))}
                            </RevealChild>
                        ))}
                    </Reveal>
                    <Reveal className="wn-screenshots" variants={stagger}>
                        {content.advantages.screenshots.map((src, i) => (
                            <RevealChild
                                key={src}
                                as={MotionDiv}
                                className="wn-screenshot"
                                variants={scaleIn}
                                custom={i * 0.06}
                                whileHover={{ y: -8, scale: 1.02 }}
                            >
                                <img src={src} alt={`Captura ${i + 1}`} loading="lazy" />
                            </RevealChild>
                        ))}
                    </Reveal>
                </motion.div>
            </MotionSection>

            <MotionSection id="precios" className="wn-section">
                <motion.div className="wn-container">
                    <Reveal className="wn-section-head" variants={fadeUp}>
                        <h2>{content.pricing.title}</h2>
                        <p>{content.pricing.subtitle}</p>
                    </Reveal>
                    <Reveal className="wn-pricing-grid" variants={stagger}>
                        {content.pricing.plans.map((plan) => (
                            <RevealChild
                                key={plan.id}
                                as={MotionArticle}
                                className={`wn-price-card ${plan.featured ? 'is-featured' : ''}`}
                                variants={scaleIn}
                                whileHover={{ y: -10, boxShadow: '0 28px 60px rgba(0, 116, 217, 0.18)' }}
                            >
                                <motion.div className="wn-price-card__head">
                                    <h3>{plan.name}</h3>
                                    <p>{plan.tagline}</p>
                                </motion.div>
                                <motion.div className="wn-price-card__price">
                                    <strong>{plan.price}</strong>
                                    <span>€</span>
                                    <p>{plan.period}</p>
                                </motion.div>
                                <ul>
                                    {plan.perks.map((perk) => (
                                        <li key={perk}><Icon name="check" /> {perk}</li>
                                    ))}
                                </ul>
                                <a href="#inicio" className="wn-btn wn-btn--primary wn-btn--block" onClick={(e) => { e.preventDefault(); scrollTo('inicio'); }}>
                                    ¡Contrátalo ya!
                                </a>
                            </RevealChild>
                        ))}
                    </Reveal>
                </motion.div>
            </MotionSection>

            <MotionSection className="wn-section wn-section--stats">
                <Reveal className="wn-container wn-stats-grid" variants={stagger}>
                    {content.stats.map((s) => (
                        <StatCounter key={s.label} {...s} />
                    ))}
                </Reveal>
            </MotionSection>

            <MotionSection id="contact" className="wn-section wn-section--contact">
                <Reveal className="wn-container wn-contact" variants={scaleIn}>
                    <h2>{content.contact.title}</h2>
                    <p>{content.contact.subtitle}</p>
                    <form action={config.routes.teLlamamos} method="POST" className="wn-form wn-form--inline">
                        <input type="hidden" name="_token" value={config.csrfToken} />
                        <input type="text" name="name" placeholder="Tu nombre *" required maxLength={255} />
                        <input type="email" name="email" placeholder="Tu email *" required maxLength={255} />
                        <input type="tel" name="phone" placeholder="Tu teléfono *" required maxLength={50} />
                        <motion.button type="submit" className="wn-btn wn-btn--primary" whileHover={{ scale: 1.03 }} whileTap={{ scale: 0.98 }}>
                            Te llamamos
                        </motion.button>
                    </form>
                </Reveal>
            </MotionSection>

            <footer className="wn-footer">
                <motion.div className="wn-container">
                    <p>
                        © {new Date().getFullYear()} Webnu · Todos los derechos reservados · Desarrollado con ♥ por Webnu
                    </p>
                    <a href="https://www.instagram.com/webnucartadigital/" target="_blank" rel="nofollow noopener noreferrer">
                        Instagram
                    </a>
                </motion.div>
            </footer>
        </motion.div>
    );
}
