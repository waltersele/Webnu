@extends('admin.layout')

@section('page_title', 'Editar menú')
@section('page_subtitle', 'Configura precio, secciones libres y el texto "incluye". Cada sección puede tener platos de la carta, texto libre y foto.')

@section('content')
@php
    $productOptions = $sections->flatMap(function ($section) {
        return $section->products->map(function ($p) use ($section) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'section' => $section->name,
                'image' => $p->image ? url('img/' . ltrim($p->image, '/')) : null,
            ];
        });
    })->values();
@endphp

<form method="POST" action="{{ route('admin.menus.update', $menu) }}" class="wn-menu-editor" id="menu-editor-form"
      data-upload-url="{{ route('admin.menus.items.upload_image', $menu) }}">
    @csrf
    @method('PUT')

    @if(session('flash'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('flash') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <a href="{{ route('admin.menus.index') }}" class="btn btn-sm btn-label-secondary">
            <i class="ri ri-arrow-left-line"></i> Volver
        </a>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="ri ri-save-line me-1"></i> Guardar menú
            </button>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Datos del menú</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label" for="menu-name">Nombre</label>
                        <input type="text" class="form-control" id="menu-name" name="name" maxlength="120" required
                               value="{{ old('name', $menu->name) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="menu-subtitle">Título mostrado en TV</label>
                        <input type="text" class="form-control" id="menu-subtitle" name="subtitle" maxlength="140"
                               value="{{ old('subtitle', $menu->subtitle) }}"
                               placeholder="Ej. Menú del día">
                        <small class="form-text text-muted">Si lo dejas vacío, usa el nombre.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="menu-price">Precio (€)</label>
                        <input type="number" step="0.01" min="0" max="99999.99" class="form-control" id="menu-price"
                               name="price" value="{{ old('price', $menu->price) }}" placeholder="14.50">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="menu-includes">Incluye</label>
                        <input type="text" class="form-control" id="menu-includes" name="includes" maxlength="200"
                               value="{{ old('includes', $menu->includes) }}"
                               placeholder="Incluye pan, bebida y café">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="menu-notes">Notas internas (no visibles)</label>
                        <textarea id="menu-notes" name="notes" class="form-control" rows="3"
                                  maxlength="2000">{{ old('notes', $menu->notes) }}</textarea>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="menu-enabled" name="enabled" value="1"
                               {{ old('enabled', $menu->enabled) ? 'checked' : '' }}>
                        <label class="form-check-label" for="menu-enabled">Menú activo</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Secciones y platos</h5>
                    <p class="text-muted small mb-0">
                        Crea las secciones que quieras (aperitivos, primer plato, segundo, postres, tabla de quesos...).
                        Cada plato puede llevar foto.
                    </p>
                </div>
                <div class="card-body">
                    <div id="wn-menu-sections" data-menu-sections>
                        @foreach($menu->sections as $section)
                            @include('admin.menus.partials.section-card', [
                                'section' => $section,
                                'items' => $section->items,
                            ])
                        @endforeach
                    </div>

                    <button type="button" class="btn btn-outline-primary mt-3" data-add-section>
                        <i class="ri ri-add-line"></i> Añadir sección
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<select class="d-none" data-product-catalog>
    <option value="">— Selecciona un plato de la carta —</option>
    @foreach($productOptions as $opt)
        <option value="{{ $opt['id'] }}" data-name="{{ $opt['name'] }}" data-image="{{ $opt['image'] }}">
            {{ $opt['name'] }} ({{ $opt['section'] }})
        </option>
    @endforeach
</select>

<template id="section-card-template">
    <article class="wn-section-card" data-section-card data-section-id="__SID__">
        <header class="wn-section-card__head">
            <i class="ti ti-grip-vertical wn-section-card__handle" aria-hidden="true"></i>
            <input type="text"
                   class="form-control form-control-sm wn-section-card__name"
                   name="sections[__SID__][name]"
                   value=""
                   maxlength="80"
                   placeholder="Ej. Primer plato, Postre, Aperitivos, Tabla de quesos..."
                   required>
            <input type="hidden" name="sections[__SID__][position]" value="0" data-section-position>
            <button type="button"
                    class="btn btn-sm btn-outline-danger wn-section-card__remove"
                    data-remove-section
                    title="Eliminar sección"
                    aria-label="Eliminar sección">
                <i class="ri ri-delete-bin-line"></i>
            </button>
        </header>
        <ul class="wn-section-card__list" data-section-items></ul>
        <footer class="wn-section-card__foot">
            <button type="button" class="btn btn-sm btn-outline-primary" data-add-product data-section-id="__SID__">
                <i class="ri ri-add-line"></i> Plato de la carta
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-add-label data-section-id="__SID__">
                <i class="ri ri-text"></i> Texto libre
            </button>
        </footer>
    </article>
</template>

<input type="file" accept="image/*" capture="environment" class="d-none" id="wn-menu-photo-input">
@endsection

@push('styles')
<style>
.wn-section-card {
    background: #fff;
    border: 1px solid var(--bs-border-color, #e5e7eb);
    border-radius: 12px;
    padding: 14px;
    margin-bottom: 14px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.wn-section-card__head {
    display: flex;
    align-items: center;
    gap: 8px;
}
.wn-section-card__handle {
    color: #9ca3af;
    font-size: 18px;
    cursor: grab;
}
.wn-section-card__name {
    flex-grow: 1;
    font-weight: 600;
    font-size: 15px;
}
.wn-section-card__list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-height: 32px;
}
.wn-section-card__foot {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.wn-item-row {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 8px;
    background: #f8f9fb;
    border: 1px solid var(--bs-border-color, #e5e7eb);
    border-radius: 8px;
    font-size: 14px;
}
.wn-item-row--label { background: #fffbeb; border-color: #fde68a; }
.wn-item-row__handle {
    color: #9ca3af;
    font-size: 16px;
    cursor: grab;
    flex-shrink: 0;
}
.wn-item-row__photo {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    border: 1px dashed #cbd5e1;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #94a3b8;
    cursor: pointer;
    padding: 0;
    flex-shrink: 0;
    overflow: hidden;
    transition: border-color .15s ease, color .15s ease;
}
.wn-item-row__photo:hover { border-color: var(--bs-primary, #004ac6); color: var(--bs-primary, #004ac6); }
.wn-item-row__photo.has-image { border-style: solid; padding: 0; }
.wn-item-row__photo img { width: 100%; height: 100%; object-fit: cover; }
.wn-item-row__name {
    flex-grow: 1;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.wn-item-row__input {
    flex-grow: 1;
    min-width: 0;
    border: none;
    background: transparent;
    font-size: 14px;
    padding: 4px 0;
}
.wn-item-row__input:focus { outline: none; box-shadow: none; }
.wn-item-row__remove {
    border: none;
    background: transparent;
    color: #ef4444;
    font-size: 16px;
    cursor: pointer;
    padding: 0 4px;
    flex-shrink: 0;
}
.wn-item-row__remove:hover { color: #b91c1c; }

.wn-product-picker {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 8px;
    background: #eff6ff;
    border: 1px dashed #93c5fd;
    border-radius: 8px;
}
.wn-product-picker select { flex-grow: 1; }
</style>
@endpush

@push('scripts')
<script>
(function () {
    const form = document.getElementById('menu-editor-form');
    if (!form) return;

    const sectionsList = form.querySelector('[data-menu-sections]');
    const catalogSelect = document.querySelector('[data-product-catalog]');
    const tpl = document.getElementById('section-card-template');
    const photoInput = document.getElementById('wn-menu-photo-input');
    const uploadUrl = form.getAttribute('data-upload-url');
    const csrfToken = form.querySelector('input[name="_token"]')?.value || '';

    let _next = 1_000_000;
    const nextIdx = () => ++_next;
    let _newSec = 0;
    const newSectionId = () => 'new-' + (++_newSec);

    let pendingPhotoButton = null;

    function escapeHtml(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, (c) => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        }[c]));
    }

    function reindexAll() {
        sectionsList.querySelectorAll('[data-section-card]').forEach((card, sIdx) => {
            const posInput = card.querySelector('[data-section-position]');
            if (posInput) posInput.value = sIdx;
            const itemRows = card.querySelectorAll('[data-item-row]');
            itemRows.forEach((row, iIdx) => {
                const posI = row.querySelector('input[name$="[position]"]');
                if (posI) posI.value = iIdx;
            });
        });
    }

    function addSectionCard() {
        if (!tpl) return;
        const sid = newSectionId();
        const html = tpl.innerHTML.replace(/__SID__/g, sid);
        const wrapper = document.createElement('div');
        wrapper.innerHTML = html.trim();
        const card = wrapper.firstElementChild;
        sectionsList.appendChild(card);
        const nameInput = card.querySelector('.wn-section-card__name');
        if (nameInput) {
            nameInput.value = '';
            nameInput.focus();
        }
        reindexAll();
    }

    function removeSectionCard(target) {
        const card = target.closest('[data-section-card]');
        if (!card) return;
        const hasItems = card.querySelectorAll('[data-item-row]').length > 0;
        if (hasItems && !confirm('Esta sección tiene platos. ¿Eliminarla con todos sus platos?')) {
            return;
        }
        card.remove();
        reindexAll();
    }

    function buildItemRow({ sid, productId = null, productName = '', productImage = null, label = '' }) {
        const idx = nextIdx();
        const isProduct = !!productId;
        const li = document.createElement('li');
        li.className = 'wn-item-row ' + (isProduct ? 'wn-item-row--product' : 'wn-item-row--label');
        li.setAttribute('data-item-row', '1');

        const photoBtn = `
            <button type="button" class="wn-item-row__photo" data-add-photo
                    title="Añadir foto" aria-label="Añadir foto">
                <i class="ri ri-camera-line"></i>
            </button>
        `;

        const nameBlock = isProduct
            ? `<span class="wn-item-row__name">${escapeHtml(productName)}</span>
               <input type="hidden" name="items[${idx}][product_id]" value="${productId}">`
            : `<input type="text" class="wn-item-row__input" name="items[${idx}][label]"
                      value="${escapeHtml(label)}" maxlength="200"
                      placeholder="Ej. Fruta de temporada" required>`;

        li.innerHTML = `
            <i class="ti ti-grip-vertical wn-item-row__handle" aria-hidden="true"></i>
            ${photoBtn}
            <input type="hidden" name="items[${idx}][section_client_id]" value="${escapeHtml(sid)}">
            <input type="hidden" name="items[${idx}][position]" value="0">
            <input type="hidden" name="items[${idx}][image]" value="" data-item-image-input>
            ${nameBlock}
            <button type="button" class="wn-item-row__remove" data-remove aria-label="Eliminar">
                <i class="ri ri-close-line"></i>
            </button>
        `;

        if (isProduct && productImage) {
            const photo = li.querySelector('[data-add-photo]');
            const imgInput = li.querySelector('[data-item-image-input]');
            photo.classList.add('has-image');
            photo.innerHTML = `<img src="${escapeHtml(productImage)}" alt="" data-item-photo>`;
        }

        return li;
    }

    function openProductPicker(btn) {
        const card = btn.closest('[data-section-card]');
        const list = card.querySelector('[data-section-items]');
        const sid = card.getAttribute('data-section-id');

        const wrap = document.createElement('li');
        wrap.className = 'wn-product-picker';
        wrap.setAttribute('data-item-row', '1');

        const select = catalogSelect.cloneNode(true);
        select.className = 'form-select form-select-sm';
        select.removeAttribute('data-product-catalog');

        const cancel = document.createElement('button');
        cancel.type = 'button';
        cancel.className = 'wn-item-row__remove';
        cancel.innerHTML = '<i class="ri ri-close-line"></i>';
        cancel.addEventListener('click', () => { wrap.remove(); reindexAll(); });

        wrap.appendChild(select);
        wrap.appendChild(cancel);
        list.appendChild(wrap);
        select.focus();

        select.addEventListener('change', () => {
            if (!select.value) { wrap.remove(); reindexAll(); return; }
            const opt = select.options[select.selectedIndex];
            const row = buildItemRow({
                sid,
                productId: select.value,
                productName: opt.getAttribute('data-name') || '',
                productImage: opt.getAttribute('data-image') || null,
            });
            list.replaceChild(row, wrap);
            reindexAll();
        });
    }

    function addFreeTextRow(btn) {
        const card = btn.closest('[data-section-card]');
        const list = card.querySelector('[data-section-items]');
        const sid = card.getAttribute('data-section-id');
        const row = buildItemRow({ sid });
        list.appendChild(row);
        const input = row.querySelector('input[type="text"]');
        if (input) input.focus();
        reindexAll();
    }

    function removeItemRow(target) {
        const row = target.closest('[data-item-row]');
        if (row) {
            row.remove();
            reindexAll();
        }
    }

    function openPhotoPicker(btn) {
        pendingPhotoButton = btn;
        photoInput.value = '';
        photoInput.click();
    }

    photoInput.addEventListener('change', () => {
        const file = photoInput.files && photoInput.files[0];
        if (!file || !pendingPhotoButton) return;
        const btn = pendingPhotoButton;
        pendingPhotoButton = null;

        const row = btn.closest('[data-item-row]');
        if (!row) return;
        const imageInput = row.querySelector('[data-item-image-input]');

        btn.classList.add('is-loading');
        btn.disabled = true;

        const fd = new FormData();
        fd.append('image', file);
        fd.append('_token', csrfToken);

        fetch(uploadUrl, {
            method: 'POST',
            body: fd,
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            credentials: 'same-origin',
        })
            .then(r => r.json())
            .then(res => {
                if (res && res.success && res.image_url) {
                    if (imageInput) imageInput.value = res.path || '';
                    btn.classList.add('has-image');
                    btn.innerHTML = `<img src="${res.image_url}" alt="" data-item-photo>`;
                    btn.setAttribute('title', 'Cambiar foto');
                    btn.setAttribute('aria-label', 'Cambiar foto');
                } else {
                    alert('No se pudo subir la imagen.');
                }
            })
            .catch(() => alert('Error subiendo la imagen.'))
            .finally(() => {
                btn.disabled = false;
                btn.classList.remove('is-loading');
            });
    });

    form.addEventListener('click', (e) => {
        if (e.target.closest('[data-add-section]'))    { e.preventDefault(); return addSectionCard(); }
        if (e.target.closest('[data-remove-section]')) { e.preventDefault(); return removeSectionCard(e.target); }
        if (e.target.closest('[data-add-product]'))    { e.preventDefault(); return openProductPicker(e.target.closest('[data-add-product]')); }
        if (e.target.closest('[data-add-label]'))      { e.preventDefault(); return addFreeTextRow(e.target.closest('[data-add-label]')); }
        if (e.target.closest('[data-remove]'))         { e.preventDefault(); return removeItemRow(e.target); }
        if (e.target.closest('[data-add-photo]'))      { e.preventDefault(); return openPhotoPicker(e.target.closest('[data-add-photo]')); }
    });

    // Asegurar reindex al enviar (por si el usuario reordena en el futuro)
    form.addEventListener('submit', () => { reindexAll(); });

    // Reindex inicial por si Blade no asignó posiciones consistentes
    reindexAll();
})();
</script>
@endpush
