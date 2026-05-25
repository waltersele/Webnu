@php
    $gifPath = public_path('img/onboarding/menu-scan.gif');
@endphp
<div class="wn-onb-anim wn-onb-anim--menu-scan" aria-hidden="true">
    @if(file_exists($gifPath))
        <img src="{{ asset('img/onboarding/menu-scan.gif') }}" alt="" loading="lazy" class="wn-onb-anim__img">
    @else
        <svg viewBox="0 0 240 160" class="wn-onb-anim__svg" xmlns="http://www.w3.org/2000/svg">
            <rect x="0" y="0" width="240" height="160" fill="#f7f8fb" rx="14"/>

            <g class="wn-onb-anim__scan-paper">
                <rect x="20" y="22" width="86" height="116" rx="8" fill="#fff" stroke="#dbe5fb"/>
                <rect x="30" y="34" width="64" height="8" rx="2" fill="#0f172a"/>
                <rect x="30" y="48" width="56" height="4" rx="2" fill="#94a3b8"/>
                <rect x="30" y="58" width="64" height="4" rx="2" fill="#94a3b8"/>
                <rect x="30" y="76" width="64" height="8" rx="2" fill="#0f172a"/>
                <rect x="30" y="90" width="56" height="4" rx="2" fill="#94a3b8"/>
                <rect x="30" y="100" width="48" height="4" rx="2" fill="#94a3b8"/>
                <rect x="30" y="118" width="56" height="6" rx="2" fill="#004ac6"/>
            </g>

            <rect x="20" y="20" width="86" height="3" rx="1.5" fill="#004ac6" class="wn-onb-anim__scan-line"/>

            <g class="wn-onb-anim__scan-dish" style="--i:0">
                <rect x="124" y="26" width="96" height="30" rx="8" fill="#fff" stroke="#dbe5fb"/>
                <rect x="132" y="32" width="22" height="18" rx="4" fill="#e6f0ff"/>
                <rect x="160" y="34" width="44" height="6" rx="3" fill="#0f172a"/>
                <rect x="160" y="44" width="28" height="4" rx="2" fill="#94a3b8"/>
            </g>
            <g class="wn-onb-anim__scan-dish" style="--i:1">
                <rect x="124" y="66" width="96" height="30" rx="8" fill="#fff" stroke="#dbe5fb"/>
                <rect x="132" y="72" width="22" height="18" rx="4" fill="#fff3e0"/>
                <rect x="160" y="74" width="44" height="6" rx="3" fill="#0f172a"/>
                <rect x="160" y="84" width="28" height="4" rx="2" fill="#94a3b8"/>
            </g>
            <g class="wn-onb-anim__scan-dish" style="--i:2">
                <rect x="124" y="106" width="96" height="30" rx="8" fill="#fff" stroke="#dbe5fb"/>
                <rect x="132" y="112" width="22" height="18" rx="4" fill="#e8f5e9"/>
                <rect x="160" y="114" width="44" height="6" rx="3" fill="#0f172a"/>
                <rect x="160" y="124" width="28" height="4" rx="2" fill="#94a3b8"/>
            </g>
        </svg>
    @endif
</div>
