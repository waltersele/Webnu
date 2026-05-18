<div class="col-6 col-md-4 col-lg-3">
    <label class="wn-template-card {{ $selected ? 'is-selected' : '' }}" data-template="{{ $key }}">
        <input type="radio" name="template_radio" value="{{ $key }}" {{ $selected ? 'checked' : '' }} class="d-none">
        <div class="wn-template-card__img">
            <img src="{{ asset($meta['preview_image'] ?? 'img/admin/templates/basic.svg') }}" alt="{{ $meta['label'] }}">
        </div>
        <span class="wn-template-card__title">{{ $meta['label'] }}</span>
        <span class="wn-template-card__desc">{{ $meta['description'] ?? '' }}</span>
        @if($selected)
            <span class="wn-template-card__check"><i class="ri-check-line"></i></span>
        @endif
    </label>
</div>
