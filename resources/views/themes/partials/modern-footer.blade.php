@php
    $variant = $variant ?? 'lumiere';
@endphp

<footer class="wn-modern-footer wn-modern-footer--{{ $variant }}" id="footer">
    @if($company->logo)
        <img src="{{ URL::to('/') . '/img/' . $company->logo }}" alt="{{ $company->name }}" class="wn-modern-footer__logo">
    @endif

    <div class="wn-modern-footer__links">
        @if($company->phone)<a href="tel:{{ $company->phone }}">@include('themes.partials.icons.svg-phone') {{ $company->phone }}</a>@endif
        @if($company->mobile_phone)<a href="tel:{{ $company->mobile_phone }}">@include('themes.partials.icons.svg-mobile') {{ $company->mobile_phone }}</a>@endif
        @if($company->email)<a href="mailto:{{ $company->email }}">@include('themes.partials.icons.svg-envelope') {{ $company->email }}</a>@endif
        @if($company->web)<a href="{{ $company->web }}" target="_blank" rel="noopener">@include('themes.partials.icons.svg-globe') Web</a>@endif
    </div>
    @if($company->schedule)
        <p class="wn-modern-footer__meta">@include('themes.partials.icons.svg-clock') {{ $company->schedule }}</p>
    @endif
    @if($company->address)
        <p class="wn-modern-footer__meta">@include('themes.partials.icons.svg-map-pin') {{ $company->address }}@if($company->city), {{ $company->city }}@endif</p>
    @endif
    @if($company->comments)
        <p class="wn-modern-footer__meta wn-modern-footer__about">{{ $company->comments }}</p>
    @endif
    @include('themes.partials.webnu-badge', ['showWebnuBadge' => $showWebnuBadge ?? false])
    @if(empty($showWebnuBadge))
        <p class="wn-modern-footer__copy">© Webnu</p>
    @endif
</footer>
