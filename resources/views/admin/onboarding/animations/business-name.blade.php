@php
    $gifPath = public_path('img/onboarding/business-name.gif');
@endphp
<div class="wn-onb-anim wn-onb-anim--business-name" aria-hidden="true">
    @if(file_exists($gifPath))
        <img src="{{ asset('img/onboarding/business-name.gif') }}" alt="" loading="lazy" class="wn-onb-anim__img">
    @else
        <svg viewBox="0 0 240 160" class="wn-onb-anim__svg" xmlns="http://www.w3.org/2000/svg">
            <rect x="0" y="0" width="240" height="160" fill="#f7f8fb" rx="14"/>
            <text x="32" y="50" font-family="Inter, sans-serif" font-size="11" fill="#94a3b8">Nombre del negocio</text>

            <g class="wn-onb-anim__bn-input">
                <rect x="32" y="58" width="176" height="38" rx="10" fill="#fff" stroke="#cbd5e1"/>
                <text x="44" y="82" font-family="Inter, sans-serif" font-size="15" font-weight="600" fill="#0f172a" class="wn-onb-anim__bn-typed">Casa María</text>
                <rect x="44" y="68" width="2" height="18" rx="1" fill="#004ac6" class="wn-onb-anim__bn-cursor"/>
            </g>

            <g class="wn-onb-anim__bn-suggestion">
                <rect x="32" y="108" width="124" height="28" rx="14" fill="#e6f0ff"/>
                <text x="44" y="126" font-family="Inter, sans-serif" font-size="12" font-weight="600" fill="#004ac6">webnu.es/casa-maria</text>
            </g>

            <circle cx="190" cy="122" r="14" fill="#16a34a" class="wn-onb-anim__bn-check"/>
            <path d="M184,122 l4,4 l8,-8" stroke="#fff" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round" class="wn-onb-anim__bn-check-path"/>
        </svg>
    @endif
</div>
