import React from 'react';
import { createRoot } from 'react-dom/client';
import LandingApp from './LandingApp';

const el = document.getElementById('webnu-landing-root');
const config = window.WEBNU_LANDING || {};

if (el) {
    createRoot(el).render(
        <React.StrictMode>
            <LandingApp config={config} />
        </React.StrictMode>
    );
}
