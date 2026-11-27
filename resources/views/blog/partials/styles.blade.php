<style>
    .wn-blog-glass {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.35);
        box-shadow: 0 10px 40px -10px rgba(0, 53, 148, 0.08), 0 2px 4px -1px rgba(0, 53, 148, 0.04);
    }
    .wn-blog-content h2 { font-size: 1.5rem; font-weight: 700; margin: 1.75rem 0 0.75rem; color: #141B2B; }
    .wn-blog-content h3 { font-size: 1.25rem; font-weight: 600; margin: 1.5rem 0 0.5rem; color: #141B2B; }
    .wn-blog-content p { margin: 0.85rem 0; line-height: 1.75; color: #434654; }
    .wn-blog-content ul, .wn-blog-content ol { margin: 0.85rem 0 0.85rem 1.35rem; color: #434654; }
    .wn-blog-content li { margin: 0.35rem 0; }
    .wn-blog-content a { color: #004ac6; text-decoration: underline; }
    .wn-blog-content img { max-width: 100%; border-radius: 16px; margin: 1.25rem 0; }
    .wn-blog-content blockquote {
        border-left: 4px solid #004ac6;
        padding-left: 1rem;
        margin: 1.25rem 0;
        color: #64748B;
        font-style: italic;
    }
    /* Tailwind preflight quita viñetas/numeración: restaurarlas en el cuerpo */
    .wn-blog-content ul { list-style: disc; }
    .wn-blog-content ol { list-style: decimal; }
    .wn-blog-content strong { color: #141B2B; font-weight: 600; }
    .wn-blog-content > :first-child { margin-top: 0; }

    /* Bloques editoriales de Sonartop (st-*). El <style> que envía Sonartop se
       elimina al sanear, así que el estilo vive aquí, con la paleta de Webnu. */
    .wn-blog-content .st-lead { font-size: 1.125rem; line-height: 1.65; color: #141B2B; }
    .wn-blog-content .st-callout { margin: 1.5rem 0; padding: 1rem 1.25rem; border-radius: 16px; border-left: 4px solid #004ac6; background: #F1F4FB; }
    .wn-blog-content .st-callout--tip { border-left-color: #0F9D74; background: #EEF8F4; }
    .wn-blog-content .st-callout--warn { border-left-color: #D97706; background: #FFF7EC; }
    .wn-blog-content .st-callout--info { border-left-color: #004ac6; }
    .wn-blog-content .st-callout p { margin: 0; }
    .wn-blog-content .st-callout p + p { margin-top: 0.5rem; }
    .wn-blog-content .st-card-grid { display: grid; gap: 1rem; margin: 1.5rem 0; }
    @media (min-width: 640px) { .wn-blog-content .st-card-grid { grid-template-columns: 1fr 1fr; } }
    .wn-blog-content .st-card { padding: 1.1rem 1.25rem; border: 1px solid #E2E8F0; border-radius: 16px; background: #fff; }
    .wn-blog-content .st-card h3 { margin: 0 0 0.4rem; font-size: 1.05rem; }
    .wn-blog-content .st-card p { margin: 0; }
    .wn-blog-content .st-quote { margin: 1.5rem 0; padding: 0.25rem 0 0.25rem 1rem; border-left: 3px solid #0F9D74; font-style: italic; color: #434654; }
    .wn-blog-content .st-steps { list-style: decimal; }
    .wn-blog-content .st-step { margin: 0 0 0.6rem; }
    .wn-blog-content .st-cta { margin: 2rem 0 0.5rem; padding: 1.25rem 1.5rem; border-radius: 20px; background: #F1F4FB; border: 1px solid #DCE4F5; text-align: center; }
    .wn-blog-content .st-cta p { margin: 0 0 0.85rem; }
    .wn-blog-content .st-btn { display: inline-block; padding: 0.7rem 1.3rem; border-radius: 999px; background: #004ac6; color: #fff !important; text-decoration: none; font-weight: 600; }
    .wn-blog-content .st-btn:hover { background: #003a9c; }
    /* FAQ legado incrustado en posts antiguos (los nuevos llegan sin él) */
    .wn-blog-content .st-faq-item { padding: 0.9rem 0; border-bottom: 1px solid #E2E8F0; }
    .wn-blog-content .st-faq-item h3 { margin: 0 0 0.35rem; font-size: 1.05rem; }
    .wn-blog-content .st-faq-item p { margin: 0; }
    .wn-blog-card:hover .wn-blog-card__image { transform: scale(1.04); }
    .wn-blog-card__image { transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1); }
    .wn-blog-breadcrumbs__sep { opacity: 0.45; user-select: none; }
    .wn-blog-category-badge { letter-spacing: 0.01em; }
    .wn-blog-sidebar__widget + .wn-blog-sidebar__widget { margin-top: 0; }
</style>
