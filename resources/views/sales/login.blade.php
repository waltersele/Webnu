@extends('sales.layout')

@section('hide_header', '1')
@section('title', 'Acceso comercial')

@section('content')
<div class="wn-sales-card" style="margin-top: 2rem;">
    <h1 style="font-size: 1.35rem; margin: 0 0 0.25rem;">Acceso comercial</h1>
    <p style="color: #64748b; margin: 0 0 1.25rem;">Ventas en restaurante</p>

    <form method="POST" action="{{ route('sales.login') }}" class="wn-sales-form">
        @csrf
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">

        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" required autocomplete="current-password">

        <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: normal;">
            <input type="checkbox" name="remember" style="width: auto; margin: 0;">
            Recordarme
        </label>

        <button type="submit" class="wn-sales-btn wn-sales-btn-primary">Entrar</button>
    </form>
</div>
@endsection
