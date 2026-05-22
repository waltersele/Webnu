@php
    $dailyHighlights = $dailyHighlights ?? [];
@endphp
@if(count($dailyHighlights) > 0)
<section class="wn-daily-highlights" aria-label="Destacados del día">
    @foreach($dailyHighlights as $item)
        @php
            $text = trim((string) ($item['text'] ?? ''));
            if ($text === '') {
                continue;
            }
            $isMenu = ($item['type'] ?? 'spotlight') === 'menu_del_dia';
            $label = $item['label'] ?? ($isMenu ? 'Menú del día' : 'Especial de hoy');
        @endphp
        <article class="wn-daily-highlights__card {{ $isMenu ? 'wn-daily-highlights__card--menu' : 'wn-daily-highlights__card--spotlight' }}">
            <span class="wn-daily-highlights__badge">{{ $label }}</span>
            <div class="wn-daily-highlights__body">
                <div class="wn-daily-highlights__text">
                    @if($isMenu)
                        <div class="wn-daily-highlights__menu-lines">
                            @foreach(preg_split('/\r\n|\r|\n/', $text) as $line)
                                @if(trim($line) !== '')
                                    <p class="wn-daily-highlights__menu-line">{{ trim($line) }}</p>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <p class="wn-daily-highlights__title">{{ $text }}</p>
                    @endif
                    @if(!empty($item['price']))
                        <p class="wn-daily-highlights__price">{{ $item['price'] }} €</p>
                    @endif
                </div>
            </div>
        </article>
    @endforeach
</section>
@endif
