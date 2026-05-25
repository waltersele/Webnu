@php
    $gifPath = public_path('img/onboarding/template.gif');
@endphp
<div class="wn-onb-anim wn-onb-anim--template" aria-hidden="true">
    @if(file_exists($gifPath))
        <img src="{{ asset('img/onboarding/template.gif') }}" alt="" loading="lazy" class="wn-onb-anim__img">
    @else
        <svg viewBox="0 0 240 160" class="wn-onb-anim__svg" xmlns="http://www.w3.org/2000/svg">
            <rect x="0" y="0" width="240" height="160" fill="#f7f8fb" rx="14"/>

            <g class="wn-onb-anim__tpl-card" style="--i:0">
                <rect x="20" y="22" width="64" height="116" rx="10" fill="#fff" stroke="#dbe5fb"/>
                <rect x="28" y="32" width="48" height="32" rx="6" fill="#e6f0ff"/>
                <rect x="28" y="72" width="36" height="6" rx="3" fill="#0f172a"/>
                <rect x="28" y="84" width="48" height="4" rx="2" fill="#94a3b8"/>
                <rect x="28" y="96" width="40" height="4" rx="2" fill="#94a3b8"/>
                <rect x="28" y="116" width="22" height="10" rx="5" fill="#004ac6"/>
            </g>

            <g class="wn-onb-anim__tpl-card" style="--i:1">
                <rect x="92" y="22" width="64" height="116" rx="10" fill="#fff" stroke="#fed7aa"/>
                <rect x="100" y="32" width="48" height="32" rx="6" fill="#fff3e0"/>
                <rect x="100" y="72" width="36" height="6" rx="3" fill="#0f172a"/>
                <rect x="100" y="84" width="48" height="4" rx="2" fill="#94a3b8"/>
                <rect x="100" y="96" width="40" height="4" rx="2" fill="#94a3b8"/>
                <rect x="100" y="116" width="22" height="10" rx="5" fill="#ea580c"/>
            </g>

            <g class="wn-onb-anim__tpl-card" style="--i:2">
                <rect x="164" y="22" width="64" height="116" rx="10" fill="#fff" stroke="#bbf7d0"/>
                <rect x="172" y="32" width="48" height="32" rx="6" fill="#e8f5e9"/>
                <rect x="172" y="72" width="36" height="6" rx="3" fill="#0f172a"/>
                <rect x="172" y="84" width="48" height="4" rx="2" fill="#94a3b8"/>
                <rect x="172" y="96" width="40" height="4" rx="2" fill="#94a3b8"/>
                <rect x="172" y="116" width="22" height="10" rx="5" fill="#16a34a"/>
            </g>
        </svg>
    @endif
</div>
