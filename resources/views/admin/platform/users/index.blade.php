@extends('admin.layout')

@section('page_title', 'Clientes')
@section('page_subtitle', 'Todas las cuentas y estado de suscripción')

@push('styles')
<link rel="stylesheet" href="{{ asset('materio/css/webnu-platform.css') }}">
@endpush

@section('page_actions')
    <a href="{{ route('admin.platform.dashboard') }}" class="btn btn-outline-secondary btn-sm">Dashboard plataforma</a>
@endsection

@section('content')
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 wn-platform-users-table">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Negocios</th>
                        <th>Suscripción</th>
                        <th>Plan</th>
                        <th>Tarjeta</th>
                        <th>Alta</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>
                                <div class="fw-medium">{{ $user->email }}</div>
                                <small class="text-muted">{{ $user->name }}</small>
                            </td>
                            <td>{{ $user->companies_count }}</td>
                            <td>
                                <span class="badge {{ $presenter->statusBadgeClass($user) }}">
                                    {{ $presenter->statusLabel($user) }}
                                </span>
                            </td>
                            <td>{{ $presenter->effectivePlanLabel($user) }}</td>
                            <td>{{ $presenter->cardSummary($user) }}</td>
                            <td>{{ $user->created_at->format('d/m/Y') }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.platform.users.show', $user) }}" class="btn btn-sm btn-outline-primary">Ver</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No hay usuarios registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($users->hasPages())
        <div class="card-footer">{{ $users->links() }}</div>
    @endif
</div>
@endsection

