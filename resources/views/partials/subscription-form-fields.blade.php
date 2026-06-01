@if (Session::has('success'))
    <div class="alert alert-success">
        <p>{{ Session::get('success') }}</p>
    </div>
@endif
@if (Session::has('failure'))
    <div class="alert alert-danger">
        <p>{{ Session::get('failure') }}</p>
    </div>
@endif
@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    $tvpikMin = (int) config('tvpik_pricing.min_professional_screens', 2);
    $tvpikMax = (int) config('tvpik_pricing.max_screens', 20);
@endphp

<div class="form-group">
    <label for="subscription-email">Email</label>
    <input required type="email" name="email" value="{{ old('email') }}" class="form-control" id="subscription-email" placeholder="Introduce tu email">
</div>
<div class="form-group">
    <label for="subscription-password">Contraseña</label>
    <input required type="password" name="password" class="form-control" id="subscription-password" placeholder="Introduce tu contraseña" minlength="8">
</div>
<div class="form-group">
    <label for="password_confirmation">Confirmar contraseña</label>
    <input required type="password" name="password_confirmation" class="form-control" id="password_confirmation" placeholder="Repite tu contraseña" minlength="8">
</div>
<div class="form-group">
    <label for="plan_tier">Plan</label>
    <select required name="plan_tier" class="custom-select" id="plan_tier">
        <option value="pro" {{ old('plan_tier', 'pro') === 'pro' ? 'selected' : '' }}>Pro — {{ config('billing.display.pro_monthly', '9,90 €/mes') }}</option>
        <option value="plus" {{ old('plan_tier') === 'plus' ? 'selected' : '' }}>Plus — {{ config('billing.display.plus_monthly', '19,90 €/mes') }} · carta + 1 pantalla TV</option>
    </select>
    <small class="form-text text-muted">Plus: 19,90 €/mes incluye 1 pantalla. Elige más pantallas abajo si las necesitas.</small>
</div>
<div class="form-group">
    <label for="billing_cycle">Facturación</label>
    <select required name="billing_cycle" class="custom-select" id="billing_cycle">
        <option value="monthly" {{ old('billing_cycle', 'monthly') === 'monthly' ? 'selected' : '' }}>Mensual</option>
        <option value="yearly" {{ old('billing_cycle') === 'yearly' ? 'selected' : '' }}>Anual (−20 % en TVPik)</option>
    </select>
</div>
<div class="form-group" id="tvpik-screens-group">
    <label for="tvpik_screens">Pantallas TVPik</label>
    <select name="tvpik_screens" class="custom-select" id="tvpik_screens">
        <option value="0" {{ old('tvpik_screens', '') === '' || old('tvpik_screens') === '0' ? 'selected' : '' }} data-plus-label="Solo la incluida (1)" data-pro-label="Sin pantallas TV">—</option>
        @for ($n = 1; $n <= $tvpikMax; $n++)
            @if ($n === 1)
                <option value="1" {{ old('tvpik_screens') === '1' ? 'selected' : '' }} data-plus-only="1">1 pantalla (incluida en Plus)</option>
            @elseif ($n < $tvpikMin)
                @continue
            @else
                <option value="{{ $n }}" {{ old('tvpik_screens') == (string) $n ? 'selected' : '' }}>{{ $n }} pantallas</option>
            @endif
        @endfor
    </select>
    <p class="form-text text-muted mb-1" id="tvpik-pricing-hint">
        Tarifa TVPik: 2–3 pantallas 8 €/ud · 4–5 a 7 €/ud · 6–20 a 6 €/ud (misma que tvpik.es).
    </p>
    <p class="form-text mb-0"><strong id="tvpik-quote-line" class="text-primary"></strong></p>
</div>

@include('partials.subscription-payment')

<div class="form-check">
    <input type="checkbox" name="privacy_policy" value="1" @if(old('privacy_policy') == '1') checked @endif class="form-check-input" id="privacy-check">
    <label class="form-check-label" for="privacy-check">Acepto la política de privacidad</label>
</div>
<div class="alert alert-danger" id="privacy-check-not-checked" style="display: none">
    <p>Debe aceptar la política de privacidad</p>
</div>

<script>
(function () {
    var planEl = document.getElementById('plan_tier');
    var cycleEl = document.getElementById('billing_cycle');
    var screensEl = document.getElementById('tvpik_screens');
    var quoteEl = document.getElementById('tvpik-quote-line');
    var zeroOpt = screensEl && screensEl.options[0];
    if (!planEl || !screensEl || !quoteEl) return;

    function syncScreenOptions() {
        var isPlus = planEl.value === 'plus';
        for (var i = 0; i < screensEl.options.length; i++) {
            var opt = screensEl.options[i];
            if (opt.getAttribute('data-plus-only') === '1') {
                opt.hidden = !isPlus;
                opt.disabled = !isPlus;
            }
            if (opt.value === '0' && zeroOpt) {
                opt.textContent = isPlus ? zeroOpt.getAttribute('data-plus-label') : zeroOpt.getAttribute('data-pro-label');
            }
        }
        if (!isPlus && screensEl.value === '1') {
            screensEl.value = '0';
        }
        fetchQuote();
    }

    function fetchQuote() {
        var screens = screensEl.value || '0';
        var url = '{{ url('/api/tvpik/pricing/quote') }}'
            + '?tier=' + encodeURIComponent(planEl.value)
            + '&cycle=' + encodeURIComponent(cycleEl.value)
            + '&screens=' + encodeURIComponent(screens);
        fetch(url, { headers: { Accept: 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.valid && parseInt(screens, 10) > 0) {
                    quoteEl.textContent = 'Selecciona un número válido de pantallas.';
                    return;
                }
                if (data.addon_cents > 0) {
                    quoteEl.textContent = 'Suplemento TVPik: ' + data.addon_label
                        + (data.rate_per_screen_eur ? ' (' + data.rate_per_screen_eur + ' €/pantalla)' : '');
                } else if (parseInt(screens, 10) > 0 || planEl.value === 'plus') {
                    quoteEl.textContent = 'Pantallas TV incluidas en tu plan; sin suplemento TVPik.';
                } else {
                    quoteEl.textContent = '';
                }
            })
            .catch(function () { quoteEl.textContent = ''; });
    }

    planEl.addEventListener('change', syncScreenOptions);
    cycleEl.addEventListener('change', fetchQuote);
    screensEl.addEventListener('change', fetchQuote);
    syncScreenOptions();
})();
</script>
