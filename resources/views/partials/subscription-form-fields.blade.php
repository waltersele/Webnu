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
        <option value="plus" {{ old('plan_tier') === 'plus' ? 'selected' : '' }}>Plus — {{ config('billing.display.plus_monthly', '19,90 €/mes') }}</option>
    </select>
</div>
<div class="form-group">
    <label for="billing_cycle">Facturación</label>
    <select required name="billing_cycle" class="custom-select" id="billing_cycle">
        <option value="monthly" {{ old('billing_cycle', 'monthly') === 'monthly' ? 'selected' : '' }}>Mensual</option>
        <option value="yearly" {{ old('billing_cycle') === 'yearly' ? 'selected' : '' }}>Anual</option>
    </select>
</div>
<div class="form-group">
    <label for="tvpik_addon">TVPik (opcional en Pro)</label>
    <select name="tvpik_addon" class="custom-select" id="tvpik_addon">
        <option value="" {{ old('tvpik_addon', '') === '' ? 'selected' : '' }}>Sin pantallas extra</option>
        <option value="screen_1" {{ old('tvpik_addon') === 'screen_1' ? 'selected' : '' }}>+1 pantalla — 5 €/mes</option>
        <option value="pack_5" {{ old('tvpik_addon') === 'pack_5' ? 'selected' : '' }}>Pack 5 pantallas — 20 €/mes</option>
    </select>
    <small class="form-text text-muted">Plus incluye 1 pantalla. En Pro, TVPik es add-on.</small>
</div>

@include('partials.subscription-payment')

<div class="form-check">
    <input type="checkbox" name="privacy_policy" value="1" @if(old('privacy_policy') == '1') checked @endif class="form-check-input" id="privacy-check">
    <label class="form-check-label" for="privacy-check">Acepto la política de privacidad</label>
</div>
<div class="alert alert-danger" id="privacy-check-not-checked" style="display: none">
    <p>Debe aceptar la política de privacidad</p>
</div>
