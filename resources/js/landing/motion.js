import React, { createContext, useContext, useEffect, useRef, useState } from 'react';
import { motion } from 'framer-motion';

const RevealVisibleContext = createContext(true);

export function useRevealVisible() {
    return useContext(RevealVisibleContext);
}

export function RevealVisibleProvider({ visible, children }) {
    return (
        <RevealVisibleContext.Provider value={visible}>
            {children}
        </RevealVisibleContext.Provider>
    );
}

/** Framer Motion 4 no incluye whileInView; usamos Intersection Observer. */
export function useReveal({ threshold = 0.05, once = true, rootMargin = '0px 0px -40px 0px' } = {}) {
    const ref = useRef(null);
    const [visible, setVisible] = useState(false);

    useEffect(() => {
        const node = ref.current;
        if (!node) {
            return undefined;
        }

        const markVisible = () => setVisible(true);

        const checkNow = () => {
            const rect = node.getBoundingClientRect();
            if (rect.top < window.innerHeight && rect.bottom > 0) {
                markVisible();
                return true;
            }
            return false;
        };

        if (checkNow() && once) {
            return undefined;
        }

        const observer = new IntersectionObserver(
            ([entry]) => {
                if (entry.isIntersecting) {
                    markVisible();
                    if (once) {
                        observer.disconnect();
                    }
                } else if (!once) {
                    setVisible(false);
                }
            },
            { threshold, rootMargin }
        );

        observer.observe(node);
        return () => observer.disconnect();
    }, [threshold, once, rootMargin]);

    return {
        ref,
        visible,
        initial: 'hidden',
        animate: visible ? 'visible' : 'hidden',
    };
}

export function Reveal({ children, variants, className, threshold, once, rootMargin, ...rest }) {
    const reveal = useReveal({ threshold, once, rootMargin });

    return (
        <RevealVisibleContext.Provider value={reveal.visible}>
            <div ref={reveal.ref} className="wn-reveal-anchor">
                <motion.div
                    className={className}
                    variants={variants}
                    initial={reveal.initial}
                    animate={reveal.animate}
                    {...rest}
                >
                    {children}
                </motion.div>
            </div>
        </RevealVisibleContext.Provider>
    );
}

/** Hijo animado dentro de un Reveal (stagger, fadeUp, etc.) */
export function RevealChild({ as: Component = motion.div, children, variants, className, custom, ...rest }) {
    const parentVisible = useRevealVisible();

    return (
        <Component
            className={className}
            variants={variants}
            initial="hidden"
            animate={parentVisible ? 'visible' : 'hidden'}
            custom={custom}
            {...rest}
        >
            {children}
        </Component>
    );
}

export const fadeUp = {
    hidden: { opacity: 0, y: 28 },
    visible: (delay = 0) => ({
        opacity: 1,
        y: 0,
        transition: { duration: 0.6, delay, ease: [0.22, 1, 0.36, 1] },
    }),
};

export const fadeIn = {
    hidden: { opacity: 0 },
    visible: (delay = 0) => ({
        opacity: 1,
        transition: { duration: 0.55, delay },
    }),
};

export const scaleIn = {
    hidden: { opacity: 0, scale: 0.94 },
    visible: (delay = 0) => ({
        opacity: 1,
        scale: 1,
        transition: { duration: 0.5, delay, ease: [0.22, 1, 0.36, 1] },
    }),
};

export const stagger = {
    hidden: {},
    visible: {
        transition: { staggerChildren: 0.08, delayChildren: 0.06 },
    },
};

export const MotionSection = motion.section;
export const MotionDiv = motion.div;
export const MotionH1 = motion.h1;
export const MotionH2 = motion.h2;
export const MotionH3 = motion.h3;
export const MotionP = motion.p;
export const MotionUl = motion.ul;
export const MotionLi = motion.li;
export const MotionArticle = motion.article;
