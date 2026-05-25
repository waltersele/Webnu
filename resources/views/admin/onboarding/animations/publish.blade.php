@php
    $gifPath = public_path('img/onboarding/publish.gif');
@endphp
<div class="wn-onb-anim wn-onb-anim--publish" aria-hidden="true">
    @if(file_exists($gifPath))
        <img src="{{ asset('img/onboarding/publish.gif') }}" alt="" loading="lazy" class="wn-onb-anim__img">
    @else
        <svg viewBox="0 0 240 160" class="wn-onb-anim__svg" xmlns="http://www.w3.org/2000/svg">
            <rect x="0" y="0" width="240" height="160" fill="#f7f8fb" rx="14"/>

            <g class="wn-onb-anim__pub-qr" transform="translate(64,16)">
                <rect x="0" y="0" width="112" height="112" rx="10" fill="#fff" stroke="#dbe5fb"/>
                @php
                    $cells = [
                        [0,0],[1,0],[2,0],[0,1],[2,1],[0,2],[1,2],[2,2],
                        [4,0],[5,0],[7,0],
                        [0,4],[2,4],[3,4],[5,4],[6,4],[7,4],
                        [0,5],[3,5],[5,5],
                        [1,6],[2,6],[4,6],[5,6],[6,6],
                        [0,7],[2,7],[3,7],[6,7],[7,7],
                        [4,1],[6,1],[4,2],[6,2],[4,3],[5,3],
                        [1,4],[1,5],[2,5],
                    ];
                @endphp
                @foreach($cells as $i => $cell)
                    <rect x="{{ 12 + $cell[0] * 11 }}" y="{{ 12 + $cell[1] * 11 }}" width="10" height="10" rx="2" fill="#0f172a" class="wn-onb-anim__pub-cell" style="--i:{{ $i }}"/>
                @endforeach
            </g>

            <rect x="64" y="14" width="112" height="3" rx="1.5" fill="#004ac6" class="wn-onb-anim__pub-scan"/>

            <g class="wn-onb-anim__pub-check">
                <circle cx="120" cy="138" r="14" fill="#16a34a"/>
                <path d="M113,138 l5,5 l9,-9" stroke="#fff" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
            </g>
        </svg>
    @endif
</div>
