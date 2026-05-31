@php
    $screenId = (string) ($screen['id'] ?? '');
    $link = $links->get($screenId);
    $selectedTemplate = $link?->template_key ?? 'menu';
    $selectedCompanyId = $link?->company_id ?? $defaultCompanyId;
    $companyName = $companies->firstWhere('id', $selectedCompanyId)?->name ?? '—';
    $templateLabel = $templates[$selectedTemplate]['label'] ?? $selectedTemplate;
    $thumb = $templates[$selectedTemplate]['thumbnail'] ?? ('img/tvpik/previews/' . ($templates[$selectedTemplate]['layout'] ?? $selectedTemplate) . '.svg');
    $supportsMenuPicker = isset($templates[$selectedTemplate])
        && ! empty($templates[$selectedTemplate]['supports_menu_selector']);
    $formId = 'wn-screen-form-' . $screenId;
@endphp
<div class="col-md-6 col-xl-4">
    <article class="wn-tvpik-screen-card h-100" data-screen-id="{{ $screenId }}">
        <div class="wn-tvpik-screen-card__header">
            <div class="wn-tvpik-screen-card__preview">
                <img src="{{ asset($thumb) }}" alt="" width="96" height="54" loading="lazy" data-tvpik-screen-thumb>
            </div>
            <div class="wn-tvpik-screen-card__meta">
                <h3 class="wn-tvpik-screen-card__title">{{ $screen['name'] ?? $screenId }}</h3>
                <p class="wn-tvpik-screen-card__summary mb-0">
                    {{ $companyName }} · {{ $templateLabel }}
                </p>
            </div>
            @if(!empty($screen['online']))
                <span class="badge bg-success wn-tvpik-screen-card__status" data-tvpik-status>Online</span>
            @else
                <span class="badge bg-label-secondary wn-tvpik-screen-card__status" data-tvpik-status>Offline</span>
            @endif
        </div>

        <div class="wn-tvpik-screen-card__actions d-flex flex-wrap gap-2 mb-2">
            @if(empty($screen['online']))
                <button type="button"
                        class="btn btn-sm btn-outline-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#wn-tvpik-pair-modal"
                        data-pair-screen-id="{{ $screenId }}"
                        data-pair-screen-name="{{ $screen['name'] ?? $screenId }}">
                    <i class="ti ti-link me-1"></i> Emparejar TV
                </button>
            @endif
            <form method="POST"
                  action="{{ route('admin.tvpik.screens.destroy') }}"
                  class="d-inline"
                  onsubmit="return confirm('¿Eliminar esta pantalla?');">
                @csrf
                @method('DELETE')
                <input type="hidden" name="screen_id" value="{{ $screenId }}">
                <button type="submit" class="btn btn-sm btn-label-secondary">
                    <i class="ti ti-trash me-1"></i> Eliminar
                </button>
            </form>
        </div>

        @if($link && $link->last_synced_at)
            <p class="wn-tvpik-screen-card__sync small text-muted mb-0">
                Última publicación: {{ $link->last_synced_at->diffForHumans() }}
                @if($link->last_error)
                    <span class="text-danger d-block">{{ Str::limit($link->last_error, 80) }}</span>
                @endif
            </p>
        @endif

        <details class="wn-tvpik-screen-card__config">
            <summary class="wn-tvpik-screen-card__config-toggle">Configurar y publicar</summary>
            <form method="POST"
                  action="{{ route('admin.tvpik.publish') }}"
                  id="{{ $formId }}"
                  class="wn-tvpik-screen-card__form"
                  data-tvpik-screen-form>
                @csrf
                <input type="hidden" name="screen_id" value="{{ $screenId }}">
                <input type="hidden" name="screen_name" value="{{ $screen['name'] ?? $screenId }}">
                <input type="hidden" name="gallery_id" value="{{ $screen['gallery_id'] ?? '' }}">

                <div class="mb-3">
                    <label class="form-label small">Carta</label>
                    <select name="company_id" class="form-select form-select-sm" required>
                        @foreach($companies as $c)
                            <option value="{{ $c->id }}"
                                {{ (int) $c->id === (int) $selectedCompanyId ? 'selected' : '' }}
                                {{ (int) $c->menu_type !== 1 ? 'disabled' : '' }}>
                                {{ $c->name }}{{ (int) $c->menu_type !== 1 ? ' (PDF)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small">Plantilla TV</label>
                    @include('admin.tvpik.partials.template-picker', [
                        'pickerId' => 'picker-' . $screenId,
                        'selectedKey' => $selectedTemplate,
                        'templates' => $templates,
                        'canTvpik' => true,
                        'canTvpikPremium' => $canTvpikPremium,
                    ])
                </div>

                <div class="mb-3 {{ $supportsMenuPicker ? '' : 'd-none' }}" data-tvpik-menu-picker>
                    <label class="form-label small">Menú a mostrar</label>
                    <select name="menu_id" class="form-select form-select-sm">
                        <option value="">Rotar entre todos los menús activos</option>
                        @foreach($companies as $c)
                            @foreach(($menusByCompany[$c->id] ?? collect()) as $m)
                                <option value="{{ $m->id }}"
                                        data-company-id="{{ $c->id }}"
                                        {{ ($link && (int) $link->menu_id === (int) $m->id) ? 'selected' : '' }}>
                                    {{ $m->name }}{{ $m->price ? ' — ' . $m->formattedPrice() : '' }}
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                    <small class="form-text text-muted">Solo con la plantilla «Menú del día».</small>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="ti ti-upload me-1"></i> Publicar
                    </button>
                    <button type="submit"
                            formaction="{{ route('admin.tvpik.preview') }}"
                            formmethod="GET"
                            class="btn btn-outline-secondary btn-sm"
                            onclick="this.form.target='_blank'">
                        <i class="ti ti-eye me-1"></i> Vista previa
                    </button>
                </div>
            </form>
        </details>
    </article>
</div>
