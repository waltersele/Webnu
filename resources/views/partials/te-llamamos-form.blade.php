@if (Session::has('te-llamamos-ok'))
    <div class="alert alert-success mb-3" role="alert">
        <p class="mb-0">{{ Session::get('te-llamamos-ok') }}</p>
    </div>
@endif
@if (Session::has('te-llamamos-failure'))
    <div class="alert alert-danger mb-3" role="alert">
        <p class="mb-0">{{ Session::get('te-llamamos-failure') }}</p>
    </div>
@endif
@if (!$errors->has('password') && ($errors->has('name') || $errors->has('phone') || $errors->has('email')))
    <div class="alert alert-danger mb-3" role="alert">
        <ul class="mb-0 pl-3">
            @foreach (['name', 'email', 'phone'] as $field)
                @error($field)
                    <li>{{ $message }}</li>
                @enderror
            @endforeach
        </ul>
    </div>
@endif

<form id="contact-form" action="{{ route('te_llamamos') }}" method="POST" class="te-llamamos-form">
    @csrf
    <div class="form-group">
        <input type="text" name="name" value="{{ old('name') }}" placeholder="Tu nombre *" required maxlength="255">
    </div>
    <div class="form-group">
        <input type="email" name="email" value="{{ old('email') }}" placeholder="Tu email *" required maxlength="255">
    </div>
    <div class="form-group">
        <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="Tu teléfono *" required maxlength="50">
    </div>
    <button type="submit" name="submit">Te llamamos</button>
</form>
