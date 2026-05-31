@php
    $pickerId = $pickerId ?? 'wn-tvpik-picker';
    $inputName = $inputName ?? 'template_key';
    $selectedKey = $selectedKey ?? array_key_first($templates);
@endphp
<div class="wn-tvpik-template-picker" data-tvpik-picker="{{ $pickerId }}">
    <input type="hidden" name="{{ $inputName }}" value="{{ $selectedKey }}" data-tvpik-template-input required>
    <div class="wn-tvpik-template-picker__grid" role="listbox" aria-label="Plantilla TV">
        @foreach($templates as $key => $tpl)
            @php
                $isPremiumTpl = ! empty($tpl['premium']);
                $tplLocked = ($isPremiumTpl && ! ($canTvpikPremium ?? false)) || ! ($canTvpik ?? true);
                $thumb = $tpl['thumbnail'] ?? ('img/tvpik/previews/' . ($tpl['layout'] ?? $key) . '.svg');
                $isSelected = $key === $selectedKey;
            @endphp
            <button type="button"
                    class="wn-tvpik-template-picker__item {{ $isSelected ? 'is-active' : '' }} {{ $tplLocked ? 'is-locked' : '' }}"
                    data-template-key="{{ $key }}"
                    data-supports-menu="{{ ! empty($tpl['supports_menu_selector']) ? '1' : '0' }}"
                    {{ $tplLocked ? 'disabled' : '' }}
                    role="option"
                    aria-selected="{{ $isSelected ? 'true' : 'false' }}">
                <span class="wn-tvpik-template-picker__thumb">
                    <img src="{{ asset($thumb) }}" alt="" width="160" height="90" loading="lazy">
                    @if($tplLocked)
                        <span class="wn-tvpik-template-picker__lock"><i class="ti ti-lock"></i></span>
                    @endif
                </span>
                <span class="wn-tvpik-template-picker__label">{{ $tpl['label'] }}</span>
                @if($isPremiumTpl)
                    <span class="wn-tvpik-template-picker__tag">Premium</span>
                @endif
            </button>
        @endforeach
    </div>
</div>
