@if($showHeader ?? true)
    <header class="wn-tv-header">
        @if($logoUrl)
            <img src="{{ $logoUrl }}" alt="" class="wn-tv-header__logo">
        @endif
        <div class="wn-tv-header__text">
            <h1 class="wn-tv-header__name">{{ $company->name }}</h1>
            @if($company->chef_name)
                <p class="wn-tv-header__chef">{{ $company->chef_name }}</p>
            @endif
        </div>
        @if(!empty($templateMeta['label']))
            <span class="wn-tv-header__mode">{{ $templateMeta['label'] }}</span>
        @endif
    </header>
@endif
