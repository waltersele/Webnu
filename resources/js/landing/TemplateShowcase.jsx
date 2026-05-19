import React, { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { fadeUp, scaleIn, stagger, MotionSection, Reveal, RevealChild, useReveal } from './motion';

function TemplatePhone({ url, label, active }) {
    return (
        <motion.div
            className={`wn-template-phone ${active ? 'is-active' : ''}`}
            layout
            initial={false}
            animate={{
                opacity: active ? 1 : 0.55,
                scale: active ? 1 : 0.92,
                y: active ? 0 : 12,
            }}
            transition={{ type: 'spring', stiffness: 320, damping: 28 }}
        >
            <div className="wn-template-phone__shell">
                <div className="wn-template-phone__notch" />
                <iframe title={`Carta ${label}`} src={url} loading="lazy" />
            </div>
        </motion.div>
    );
}

export default function TemplateShowcase({ content, demoBase }) {
    const templates = content.templatesList || [];
    const [activeId, setActiveId] = useState(templates[0]?.id || 'lumiere');
    const reveal = useReveal({ threshold: 0.12 });

    const active = templates.find((t) => t.id === activeId) || templates[0];
    const previewUrl = (id) => `${demoBase}?tpl=${id}`;

    if (!templates.length) {
        return null;
    }

    return (
        <MotionSection id="plantillas" className="wn-section wn-section--templates">
            <motion.div ref={reveal.ref} className="wn-container">
                <Reveal className="wn-section-head" variants={fadeUp}>
                    <motion.span
                        className="wn-eyebrow"
                        initial={{ opacity: 0, x: -12 }}
                        animate={reveal.visible ? { opacity: 1, x: 0 } : {}}
                    >
                        Diseño profesional
                    </motion.span>
                    <h2>{content.templates.title}</h2>
                    <p>{content.templates.subtitle}</p>
                </Reveal>

                <motion.div
                    className="wn-template-tabs"
                    role="tablist"
                    initial="hidden"
                    animate={reveal.visible ? 'visible' : 'hidden'}
                    variants={stagger}
                >
                    {templates.map((tpl) => (
                        <motion.button
                            key={tpl.id}
                            type="button"
                            role="tab"
                            aria-selected={activeId === tpl.id}
                            className={`wn-template-tab ${activeId === tpl.id ? 'is-active' : ''}`}
                            onClick={() => setActiveId(tpl.id)}
                            variants={fadeUp}
                            whileHover={{ y: -2 }}
                            whileTap={{ scale: 0.98 }}
                        >
                            {activeId === tpl.id && (
                                <motion.span
                                    className="wn-template-tab__pill"
                                    layoutId="template-tab-pill"
                                    transition={{ type: 'spring', stiffness: 400, damping: 30 }}
                                />
                            )}
                            <img src={tpl.preview} alt="" />
                            <span>{tpl.label}</span>
                        </motion.button>
                    ))}
                </motion.div>

                <Reveal className="wn-template-stage" variants={scaleIn}>
                    <motion.div
                        className="wn-template-stage__glow"
                        animate={{
                            scale: [1, 1.08, 1],
                            opacity: [0.4, 0.65, 0.4],
                        }}
                        transition={{ duration: 5, repeat: Infinity, ease: 'easeInOut' }}
                    />
                    <AnimatePresence mode="wait">
                        <motion.div
                            key={activeId}
                            className="wn-template-stage__copy"
                            initial={{ opacity: 0, y: 16 }}
                            animate={{ opacity: 1, y: 0 }}
                            exit={{ opacity: 0, y: -12 }}
                            transition={{ duration: 0.35 }}
                        >
                            <h3>{active?.label}</h3>
                            <p>{active?.description}</p>
                            <a
                                href={previewUrl(activeId)}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="wn-btn wn-btn--outline"
                            >
                                Abrir en pantalla completa
                            </a>
                        </motion.div>
                    </AnimatePresence>
                    <div className="wn-template-phones">
                        <AnimatePresence mode="wait">
                            <TemplatePhone
                                key={activeId}
                                label={active?.label}
                                url={previewUrl(activeId)}
                                active
                            />
                        </AnimatePresence>
                    </div>
                </Reveal>

                <Reveal className="wn-template-cta" variants={fadeUp}>
                    <a href={demoBase} target="_blank" rel="noopener noreferrer" className="wn-btn wn-btn--primary">
                        {content.templates.cta}
                    </a>
                </Reveal>
            </motion.div>
        </MotionSection>
    );
}
