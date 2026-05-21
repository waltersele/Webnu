@php
    $dailyHighlights = $dailyHighlights ?? [];
    $spotlight = $dailyHighlights[0] ?? null;
@endphp
@if($spotlight && !empty($spotlight['text']))
<section class="wn-daily-highlights" aria-label="Especial de hoy">
    <article class="wn-daily-highlights__card wn-daily-highlights__card--spotlight">
        <span class="wn-daily-highlights__badge">{{ $spotlight['label'] ?? 'Especial de hoy' }}</span>
        <div class="wn-daily-highlights__body">
            <div class="wn-daily-highlights__text">
                <p class="wn-daily-highlights__title">{{ $spotlight['text'] }}</p>
                @if(!empty($spotlight['price']))
                    <p class="wn-daily-highlights__price">{{ $spotlight['price'] }} €</p>
                @endif
            </div>
        </div>
    </article>
</section>
@endif
