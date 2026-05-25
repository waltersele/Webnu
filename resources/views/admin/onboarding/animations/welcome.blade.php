@php
    $gifPath = public_path('img/onboarding/welcome.gif');
@endphp
<div class="wn-onb-anim wn-onb-anim--welcome" aria-hidden="true">
    @if(file_exists($gifPath))
        <img src="{{ asset('img/onboarding/welcome.gif') }}" alt="" loading="lazy" class="wn-onb-anim__img">
    @else
        <svg viewBox="0 0 240 160" class="wn-onb-anim__svg" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <radialGradient id="welcomeGlow" cx="50%" cy="50%" r="55%">
                    <stop offset="0%" stop-color="#e6f0ff" stop-opacity="0.95"/>
                    <stop offset="100%" stop-color="#e6f0ff" stop-opacity="0"/>
                </radialGradient>
            </defs>
            <rect x="0" y="0" width="240" height="160" fill="url(#welcomeGlow)"/>

            <g class="wn-onb-anim__welcome-card">
                <rect x="62" y="48" width="116" height="74" rx="14" fill="#fff" stroke="#dbe5fb"/>
                <circle cx="86" cy="74" r="10" fill="#004ac6"/>
                <rect x="104" y="68" width="62" height="6" rx="3" fill="#0f172a"/>
                <rect x="104" y="80" width="48" height="5" rx="2.5" fill="#94a3b8"/>
                <rect x="74" y="100" width="92" height="10" rx="5" fill="#e6f0ff"/>
            </g>

            <g class="wn-onb-anim__welcome-confetti">
                <rect x="22" y="30" width="6" height="10" rx="1.5" fill="#004ac6" class="wn-onb-anim__welcome-piece" style="--i:0"/>
                <rect x="200" y="22" width="6" height="10" rx="1.5" fill="#f59e0b" class="wn-onb-anim__welcome-piece" style="--i:1"/>
                <rect x="40" y="120" width="6" height="10" rx="1.5" fill="#16a34a" class="wn-onb-anim__welcome-piece" style="--i:2"/>
                <rect x="186" y="118" width="6" height="10" rx="1.5" fill="#dc2626" class="wn-onb-anim__welcome-piece" style="--i:3"/>
                <rect x="120" y="14" width="6" height="10" rx="1.5" fill="#7c3aed" class="wn-onb-anim__welcome-piece" style="--i:4"/>
                <circle cx="56" cy="60" r="3" fill="#004ac6" class="wn-onb-anim__welcome-piece" style="--i:5"/>
                <circle cx="190" cy="80" r="3" fill="#f59e0b" class="wn-onb-anim__welcome-piece" style="--i:6"/>
                <circle cx="34" cy="86" r="3" fill="#16a34a" class="wn-onb-anim__welcome-piece" style="--i:7"/>
            </g>
        </svg>
    @endif
</div>
