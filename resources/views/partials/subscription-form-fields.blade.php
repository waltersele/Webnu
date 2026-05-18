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
    <label for="subscription">Suscripción</label>
    <select required name="subscription" class="custom-select" id="subscription">
        <option value="1" {{ old('subscription') == 1 ? 'selected' : '' }}>Mensual 10€ / Mes</option>
        <option value="2" {{ old('subscription') == 2 ? 'selected' : '' }}>Anual 100€ / Año</option>
    </select>
</div>

@include('partials.subscription-payment')

<div class="form-check">
    <input type="checkbox" name="privacy_policy" value="1" @if(old('privacy_policy') == '1') checked @endif class="form-check-input" id="privacy-check">
    <label class="form-check-label" for="privacy-check">Acepto la política de privacidad</label>
</div>
<div class="alert alert-danger" id="privacy-check-not-checked" style="display: none">
    <p>Debe aceptar la política de privacidad</p>
</div>
