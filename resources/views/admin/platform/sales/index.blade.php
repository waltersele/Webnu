@extends('admin.layout')

@section('page_title', 'Comercial')
@section('page_subtitle', 'Equipo comercial, visitas y cierres de venta')

@section('page_actions')
    <a href="{{ route('sales.login') }}" class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener">
        <i class="ri-external-link-line me-1"></i> Portal comercial
    </a>
    <a href="{{ route('admin.platform.dashboard') }}" class="btn btn-outline-secondary btn-sm">Volver</a>
    <a href="{{ route('admin.platform.sales.export', request()->query()) }}" class="btn btn-outline-primary btn-sm">
        <i class="ri-download-line me-1"></i> Exportar CSV
    </a>
@endsection

@section('content')
@php
    $activeTab = request('tab', 'comerciales');
@endphp

<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link {{ $activeTab === 'comerciales' ? 'active' : '' }}" data-bs-toggle="tab" href="#tab-comerciales">Comerciales ({{ $reps->count() }})</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $activeTab === 'config' ? 'active' : '' }}" data-bs-toggle="tab" href="#tab-config">Configuración</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $activeTab === 'envios' ? 'active' : '' }}" data-bs-toggle="tab" href="#tab-envios">Envíos ({{ $metrics['total'] }})</a>
    </li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade {{ $activeTab === 'comerciales' ? 'show active' : '' }}" id="tab-comerciales">
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card h-100">
                    <div class="card-header"><h5 class="mb-0">Nuevo comercial</h5></div>
                    <div class="card-body">
                        <p class="text-muted small">
                            Crea un acceso solo para el portal <strong>/comercial</strong> (visitas y cierre de ventas).
                            No es un restaurante con carta propia.
                        </p>
                        <form method="POST" action="{{ route('admin.platform.sales.reps.store') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label" for="rep-name">Nombre</label>
                                <input type="text" name="name" id="rep-name" class="form-control" value="{{ old('name') }}" required maxlength="255">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="rep-email">Email</label>
                                <input type="email" name="email" id="rep-email" class="form-control" value="{{ old('email') }}" required maxlength="255">
                                <div class="form-text">Si el email ya existe en Webnu, solo se añadirá el rol comercial.</div>
                            </div>
                            <div class="form-check mb-3">
                                <input type="hidden" name="send_access_email" value="0">
                                <input type="checkbox" name="send_access_email" id="send_access_email" class="form-check-input" value="1" checked>
                                <label class="form-check-label" for="send_access_email">
                                    Enviar email para crear contraseña
                                </label>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Crear acceso comercial</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Equipo comercial</h5>
                        <span class="text-muted small">Login: /comercial/login</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Email</th>
                                        <th class="text-center">Visitas activas</th>
                                        <th class="text-center">Cierres</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($reps as $rep)
                                        <tr>
                                            <td>{{ $rep->name }}</td>
                                            <td><code class="small">{{ $rep->email }}</code></td>
                                            <td class="text-center">{{ $rep->active_visits_count ?? 0 }}</td>
                                            <td class="text-center">{{ $rep->handoffs_count ?? 0 }}</td>
                                            <td class="text-end text-nowrap">
                                                <form method="POST" action="{{ route('admin.platform.sales.reps.resend-access', $rep) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-primary" title="Reenviar email de contraseña">Acceso</button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.platform.users.revoke-sales-rep', $rep) }}" class="d-inline" onsubmit="return confirm('¿Quitar acceso comercial a {{ $rep->name }}?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Quitar</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-muted p-4">
                                                Aún no hay comerciales. Crea el primero con el formulario.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade {{ $activeTab === 'config' ? 'show active' : '' }}" id="tab-config">
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <span class="text-muted small">Envíos totales</span>
                        <h3 class="mb-0">{{ $metrics['total'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-primary">
                    <div class="card-body">
                        <span class="text-muted small">Este mes</span>
                        <h3 class="mb-0 text-primary">{{ $metrics['month'] }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.platform.sales.update') }}" class="card mb-4">
            @csrf
            @method('PUT')
            <div class="card-header"><h5 class="mb-0">Plan al enviar acceso al restaurante</h5></div>
            <div class="card-body">
                <p class="text-muted">Cuando un comercial cierra una venta, el restaurante recibe este plan durante el periodo de prueba indicado.</p>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label" for="sales_handoff_plan_key">Plan por defecto</label>
                        <select name="sales_handoff_plan_key" id="sales_handoff_plan_key" class="form-select" required>
                            @foreach ($availablePlanKeys as $key)
                                @if ($key !== 'free')
                                    <option value="{{ $key }}" @if($salesSettings['sales_handoff_plan_key'] === $key) selected @endif>
                                        {{ $planTiers[$key]['label'] ?? $key }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="sales_handoff_trial_days">Días de prueba</label>
                        <input type="number" name="sales_handoff_trial_days" id="sales_handoff_trial_days" class="form-control"
                               min="1" max="365" value="{{ old('sales_handoff_trial_days', $salesSettings['sales_handoff_trial_days']) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="sales_demo_max_photo_products">Máx. platos con foto en demo</label>
                        <input type="number" name="sales_demo_max_photo_products" id="sales_demo_max_photo_products" class="form-control"
                               min="1" max="10" value="{{ old('sales_demo_max_photo_products', $salesSettings['sales_demo_max_photo_products']) }}" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Guardar configuración</button>
            </div>
        </form>

        @if ($metrics['by_rep']->isNotEmpty())
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Cierres por comercial</h6></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Comercial</th>
                            <th>Envíos</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($metrics['by_rep'] as $row)
                            <tr>
                                <td>{{ optional($row->salesRep)->name }}</td>
                                <td><strong>{{ $row->total }}</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    <div class="tab-pane fade {{ $activeTab === 'envios' ? 'show active' : '' }}" id="tab-envios">
        <form method="GET" class="row g-2 mb-3 align-items-end">
            <input type="hidden" name="tab" value="envios">
            <div class="col-md-3">
                <label class="form-label">Comercial</label>
                <select name="rep" class="form-select">
                    <option value="">Todos</option>
                    @foreach ($reps as $rep)
                        <option value="{{ $rep->id }}" @if(request('rep') == $rep->id) selected @endif>{{ $rep->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Desde</label>
                <input type="date" name="from" class="form-control" value="{{ request('from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Hasta</label>
                <input type="date" name="to" class="form-control" value="{{ request('to') }}">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Comercial</th>
                        <th>Restaurante</th>
                        <th>Email</th>
                        <th>Plan</th>
                        <th>Días</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($handoffs as $row)
                        <tr>
                            <td>{{ optional($row->sent_at)->format('d/m/Y H:i') }}</td>
                            <td>{{ optional($row->salesRep)->name }}</td>
                            <td>{{ optional($row->company)->name }}</td>
                            <td>{{ $row->prospect_email }}</td>
                            <td>{{ $planTiers[$row->plan_key]['label'] ?? $row->plan_key }}</td>
                            <td>{{ $row->trial_days }}</td>
                            <td><span class="badge bg-success">{{ $row->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-muted">Sin envíos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $handoffs->appends(request()->query())->links() }}
    </div>
</div>
@endsection
