@extends('sales.layout')

@section('title', 'Cerrar venta')

@section('content')
<p style="margin: 0 0 0.25rem;"><a href="{{ route('sales.visit.show', $visit->id) }}" style="color: #64748b; font-size: 0.9rem;">← {{ $visit->name }}</a></p>
<h1 style="font-size: 1.25rem; margin: 0 0 1rem;">Enviar acceso al restaurante</h1>

<div class="wn-sales-card">
    <p style="color: #64748b; font-size: 0.9rem; margin: 0 0 1rem;">
        Se creará su cuenta con plan <strong>{{ $planTiers[$defaultPlanKey]['label'] ?? $defaultPlanKey }}</strong>
        durante <strong>{{ $defaultTrialDays }} días</strong>. Recibirá un email para establecer contraseña.
    </p>

    <form method="POST" action="{{ route('sales.handoff.store', $visit->id) }}" class="wn-sales-form">
        @csrf
        <label for="prospect_name">Nombre del responsable</label>
        <input type="text" id="prospect_name" name="prospect_name" value="{{ old('prospect_name') }}" required>

        <label for="prospect_email">Email del restaurante</label>
        <input type="email" id="prospect_email" name="prospect_email" value="{{ old('prospect_email', $visit->email) }}" required>

        <label for="plan_key">Plan (opcional)</label>
        <select id="plan_key" name="plan_key">
            <option value="">Por defecto ({{ $planTiers[$defaultPlanKey]['label'] ?? $defaultPlanKey }})</option>
            @foreach ($availablePlanKeys as $key)
                @if ($key !== 'free')
                    <option value="{{ $key }}" @if(old('plan_key') === $key) selected @endif>
                        {{ $planTiers[$key]['label'] ?? $key }}
                    </option>
                @endif
            @endforeach
        </select>

        <label for="trial_days">Días de prueba (opcional)</label>
        <input type="number" id="trial_days" name="trial_days" min="1" max="365" value="{{ old('trial_days', $defaultTrialDays) }}" placeholder="{{ $defaultTrialDays }}">

        <button type="submit" class="wn-sales-btn wn-sales-btn-primary">Enviar acceso por email</button>
    </form>
</div>
@endsection
