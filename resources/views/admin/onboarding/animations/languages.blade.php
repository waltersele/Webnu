@php
    $gifPath = public_path('img/onboarding/languages.gif');
@endphp
<div class="wn-onb-anim wn-onb-anim--languages" aria-hidden="true">
    @if(file_exists($gifPath))
        <img src="{{ asset('img/onboarding/languages.gif') }}" alt="" loading="lazy" class="wn-onb-anim__img">
    @else
        <svg viewBox="0 0 240 160" class="wn-onb-anim__svg" xmlns="http://www.w3.org/2000/svg">
            <rect x="0" y="0" width="240" height="160" fill="#f7f8fb" rx="14"/>

            <g class="wn-onb-anim__lang-globe">
                <circle cx="120" cy="80" r="46" fill="#fff" stroke="#dbe5fb"/>
                <ellipse cx="120" cy="80" rx="22" ry="46" fill="none" stroke="#cbd5e1" stroke-width="1"/>
                <line x1="74" y1="80" x2="166" y2="80" stroke="#cbd5e1" stroke-width="1"/>
                <line x1="120" y1="34" x2="120" y2="126" stroke="#cbd5e1" stroke-width="1"/>
                <ellipse cx="120" cy="60" rx="42" ry="6" fill="none" stroke="#cbd5e1" stroke-width="1"/>
                <ellipse cx="120" cy="100" rx="42" ry="6" fill="none" stroke="#cbd5e1" stroke-width="1"/>
            </g>

            <g class="wn-onb-anim__lang-bubble" style="--i:0">
                <rect x="20" y="22" width="56" height="26" rx="13" fill="#004ac6"/>
                <text x="48" y="40" font-family="Inter, sans-serif" font-size="13" font-weight="700" fill="#fff" text-anchor="middle">ES</text>
            </g>
            <g class="wn-onb-anim__lang-bubble" style="--i:1">
                <rect x="164" y="22" width="56" height="26" rx="13" fill="#dc2626"/>
                <text x="192" y="40" font-family="Inter, sans-serif" font-size="13" font-weight="700" fill="#fff" text-anchor="middle">EN</text>
            </g>
            <g class="wn-onb-anim__lang-bubble" style="--i:2">
                <rect x="20" y="112" width="56" height="26" rx="13" fill="#7c3aed"/>
                <text x="48" y="130" font-family="Inter, sans-serif" font-size="13" font-weight="700" fill="#fff" text-anchor="middle">FR</text>
            </g>
            <g class="wn-onb-anim__lang-bubble" style="--i:3">
                <rect x="164" y="112" width="56" height="26" rx="13" fill="#f59e0b"/>
                <text x="192" y="130" font-family="Inter, sans-serif" font-size="13" font-weight="700" fill="#fff" text-anchor="middle">DE</text>
            </g>
        </svg>
    @endif
</div>
