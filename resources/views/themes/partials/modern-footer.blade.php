@php
    $variant = $variant ?? 'lumiere';
@endphp

<footer class="wn-modern-footer wn-modern-footer--{{ $variant }}" id="footer">
    @if($company->logo)
        <img src="{{ URL::to('/') . '/img/' . $company->logo }}" alt="{{ $company->name }}" class="wn-modern-footer__logo">
    @endif

    <div class="wn-modern-footer__links">
        @if($company->phone)<a href="tel:{{ $company->phone }}"><i class="fas fa-phone"></i> {{ $company->phone }}</a>@endif
        @if($company->mobile_phone)<a href="tel:{{ $company->mobile_phone }}"><i class="fas fa-mobile-alt"></i> {{ $company->mobile_phone }}</a>@endif
        @if($company->email)<a href="mailto:{{ $company->email }}"><i class="fas fa-envelope"></i> {{ $company->email }}</a>@endif
        @if($company->web)<a href="{{ $company->web }}" target="_blank" rel="noopener"><i class="fas fa-globe"></i> Web</a>@endif
    </div>
    @if($company->schedule)
        <p class="wn-modern-footer__meta"><i class="fas fa-clock"></i> {{ $company->schedule }}</p>
    @endif
    @if($company->address)
        <p class="wn-modern-footer__meta"><i class="fas fa-map-marker-alt"></i> {{ $company->address }}@if($company->city), {{ $company->city }}@endif</p>
    @endif
    @if($company->comments)
        <p class="wn-modern-footer__meta wn-modern-footer__about">{{ $company->comments }}</p>
    @endif
    @include('themes.partials.webnu-badge', ['showWebnuBadge' => $showWebnuBadge ?? false])
    @if(empty($showWebnuBadge))
        <p class="wn-modern-footer__copy">© Webnu</p>
    @endif
</footer>
