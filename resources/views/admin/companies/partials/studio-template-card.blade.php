@php
    $locked = $locked ?? false;
    $currentTemplate = $currentTemplate ?? null;
@endphp
<div class="col-6 col-md-4 col-xl-3">
    @if ($locked)
        <div class="wn-template-card wn-template-card--locked {{ $selected ? 'is-selected' : '' }}"
             data-template="{{ $key }}"
             data-upgrade-trigger="templates"
             role="button"
             tabindex="0">
            <div class="wn-template-card__preview">
                @include('admin.companies.partials.studio-template-mock', ['key' => $key])
            </div>
            <span class="wn-template-card__title">{{ $meta['label'] }}</span>
            <span class="wn-template-card__desc">{{ $meta['description'] ?? '' }}</span>
            @include('admin.partials.plan-pro-badge', ['label' => 'Pro', 'size' => 'xs'])
            @if ($selected)
                <span class="wn-template-card__check"><i class="ri-check-line"></i></span>
            @endif
        </div>
    @else
        <label class="wn-template-card {{ $selected ? 'is-selected' : '' }}" data-template="{{ $key }}">
            <input type="radio" name="template_radio" value="{{ $key }}" {{ $selected ? 'checked' : '' }} class="d-none">
            <div class="wn-template-card__preview">
                @include('admin.companies.partials.studio-template-mock', ['key' => $key])
            </div>
            <span class="wn-template-card__title">{{ $meta['label'] }}</span>
            <span class="wn-template-card__desc">{{ $meta['description'] ?? '' }}</span>
            @if ($selected)
                <span class="wn-template-card__check"><i class="ri-check-line"></i></span>
            @endif
            @if (!empty($meta['recommended']))
                <span class="wn-template-card__badge">Recomendada</span>
            @endif
        </label>
    @endif
</div>
