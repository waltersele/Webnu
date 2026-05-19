import React from 'react';
import { motion } from 'framer-motion';
import { fadeUp, scaleIn, stagger, MotionSection, Reveal, RevealChild, useReveal } from './motion';

function FeatureIcon({ name }) {
    const paths = {
        scan: 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z',
        brush: 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01',
        qr: 'M3 3h4v4H3V3zm14 0h4v4h-4V3zM3 17h4v4H3v-4zm8 0h2v2h-2v-2zm4 0h4v4h-4v-4z',
        devices: 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z',
        docs: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
        updates: 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
        pdf: 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z',
        support: 'M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z',
    };
    return (
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" aria-hidden>
            <path strokeLinecap="round" strokeLinejoin="round" d={paths[name] || paths.brush} />
        </svg>
    );
}

export default function FeaturesHub({ content, demoUrl }) {
    const items = content.features.items || [];
    const coreReveal = useReveal({ threshold: 0.2 });

    return (
        <MotionSection id="feature" className="wn-section wn-section--features-hub">
            <div className="wn-features-hub__bg">
                <motion.div
                    className="wn-features-hub__orb wn-features-hub__orb--1"
                    animate={{ x: [0, 30, 0], y: [0, -20, 0] }}
                    transition={{ duration: 12, repeat: Infinity, ease: 'easeInOut' }}
                />
                <motion.div
                    className="wn-features-hub__orb wn-features-hub__orb--2"
                    animate={{ x: [0, -24, 0], y: [0, 16, 0] }}
                    transition={{ duration: 10, repeat: Infinity, ease: 'easeInOut' }}
                />
            </div>

            <div className="wn-container">
                <Reveal className="wn-section-head wn-section-head--light" variants={fadeUp}>
                    <h2>{content.features.title}</h2>
                    <p>{content.features.subtitle}</p>
                </Reveal>

                <div className="wn-features-hub">
                    <motion.div
                        ref={coreReveal.ref}
                        className="wn-features-hub__core"
                        initial={{ opacity: 0, scale: 0.85 }}
                        animate={coreReveal.visible ? { opacity: 1, scale: 1 } : { opacity: 0, scale: 0.85 }}
                        transition={{ duration: 0.7, ease: [0.22, 1, 0.36, 1] }}
                    >
                        {[0, 1, 2].map((ring) => (
                            <motion.span
                                key={ring}
                                className="wn-features-hub__ring"
                                animate={{ rotate: 360 }}
                                transition={{
                                    duration: 18 + ring * 6,
                                    repeat: Infinity,
                                    ease: 'linear',
                                }}
                                style={{ animationDelay: `${ring * 2}s` }}
                            />
                        ))}
                        <motion.div
                            className="wn-features-hub__phone"
                            animate={{ y: [0, -8, 0] }}
                            transition={{ duration: 4, repeat: Infinity, ease: 'easeInOut' }}
                        >
                            <iframe title="Carta Webnu demo" src={`${demoUrl}?tpl=lumiere`} loading="lazy" />
                        </motion.div>
                        <motion.span
                            className="wn-features-hub__badge"
                            animate={{ scale: [1, 1.05, 1] }}
                            transition={{ duration: 2.5, repeat: Infinity }}
                        >
                            Webnu
                        </motion.span>
                    </motion.div>

                    <Reveal className="wn-features-hub__grid" variants={stagger}>
                        {items.map((item, i) => (
                            <RevealChild
                                key={item.title}
                                as={motion.article}
                                className={`wn-feature-card ${item.highlight ? 'is-highlight' : ''}`}
                                variants={scaleIn}
                                custom={i * 0.04}
                                whileHover={{
                                    y: -6,
                                    boxShadow: '0 20px 48px rgba(0, 116, 218, 0.22)',
                                }}
                            >
                                <motion.div
                                    className="wn-feature-card__icon"
                                    whileHover={{ rotate: [0, -8, 8, 0] }}
                                    transition={{ duration: 0.5 }}
                                >
                                    <FeatureIcon name={item.icon} />
                                </motion.div>
                                <h4>{item.title}</h4>
                                <p>{item.text}</p>
                            </RevealChild>
                        ))}
                    </Reveal>
                </div>
            </div>
        </MotionSection>
    );
}
