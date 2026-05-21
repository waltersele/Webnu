@if(auth()->check())
    @php
        $planPresentation = app(\App\Services\UserPlanService::class)->planPresentation(auth()->user());
    @endphp
    @if(!empty($planPresentation['trial_active']))
        <div class="alert alert-info d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4" role="status">
            <div>
                <strong>Prueba Plus gratis</strong>
                — Te quedan {{ $planPresentation['trial_days_remaining'] }} {{ $planPresentation['trial_days_remaining'] === 1 ? 'día' : 'días' }}
                @if(!empty($planPresentation['trial_ends_at_formatted']))
                    (hasta {{ $planPresentation['trial_ends_at_formatted'] }}).
                @endif
                Disfruta de vídeos, traducciones e IA sin límite.
            </div>
            <a href="{{ route('admin.settings') }}" class="btn btn-sm btn-primary shrink-0">Activar suscripción</a>
        </div>
    @elseif(!empty($planPresentation['trial_expired']))
        <div class="alert alert-warning d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4" role="status">
            <div>
                <strong>Tu prueba Plus ha terminado.</strong>
                Has vuelto al plan Gratis: sin vídeos en platos, sin traducciones y escaneos IA limitados.
            </div>
            <a href="{{ route('admin.settings') }}" class="btn btn-sm btn-warning shrink-0">Recuperar Plus</a>
        </div>
    @endif
@endif
