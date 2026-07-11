@php
    $postModel = $post ?? null;
    $formAction = $formAction ?? route('admin.platform.blog.store');
    $formMethod = $formMethod ?? 'POST';
    $publishedAtValue = old('published_at', optional($postModel?->published_at)->format('Y-m-d\TH:i'));
@endphp

<form method="POST" action="{{ $formAction }}" enctype="multipart/form-data">
    @csrf
    @if(($formMethod ?? 'POST') !== 'POST')
        @method($formMethod)
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3">Publicación</h5>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Estado</label>
                    <select name="status" class="form-select">
                        @foreach([\App\BlogPost::STATUS_DRAFT, \App\BlogPost::STATUS_PUBLISHED, \App\BlogPost::STATUS_SCHEDULED] as $status)
                            <option value="{{ $status }}" @selected(old('status', $postModel->status ?? \App\BlogPost::STATUS_DRAFT) === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Fecha publicación</label>
                    <input type="datetime-local" name="published_at" class="form-control" value="{{ $publishedAtValue }}">
                    <div class="form-text">Programa con estado <code>scheduled</code> o fecha futura.</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Categoría</label>
                    <select name="blog_category_id" class="form-select">
                        <option value="">— Sin categoría —</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) old('blog_category_id', $postModel->blog_category_id ?? '') === (string) $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3">Imagen destacada</h5>
            @if($postModel && $postModel->featured_image)
                <div class="mb-3">
                    <img src="{{ filter_var($postModel->featured_image, FILTER_VALIDATE_URL) ? $postModel->featured_image : asset($postModel->featured_image) }}"
                         alt="" class="img-fluid rounded" style="max-height: 180px;">
                </div>
            @endif
            <div class="mb-3">
                <label class="form-label">Subir imagen</label>
                <input type="file" name="featured_image" class="form-control" accept="image/jpeg,image/png,image/webp,image/gif">
            </div>
            <div class="mb-3">
                <label class="form-label">Texto alternativo (alt)</label>
                <input type="text" name="featured_image_alt" class="form-control" value="{{ old('featured_image_alt', $postModel->featured_image_alt ?? '') }}">
            </div>
            @if($postModel && $postModel->featured_image)
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="remove_featured_image" value="1" id="remove_featured_image">
                    <label class="form-check-label" for="remove_featured_image">Quitar imagen actual</label>
                </div>
            @endif
        </div>
    </div>

    <ul class="nav nav-tabs mb-3">
        @foreach($locales as $locale)
            <li class="nav-item">
                <button class="nav-link {{ $loop->first ? 'active' : '' }}" type="button" data-bs-toggle="tab" data-bs-target="#tab-{{ $locale }}">
                    {{ strtoupper($locale) }}
                </button>
            </li>
        @endforeach
    </ul>

    <div class="tab-content">
        @foreach($locales as $locale)
            @php $tr = $postModel?->translations?->firstWhere('locale', $locale); @endphp
            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="tab-{{ $locale }}">
                <div class="card">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Slug</label>
                            <input type="text" name="translations[{{ $locale }}][slug]" class="form-control" value="{{ old('translations.'.$locale.'.slug', $tr->slug ?? '') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Título</label>
                            <input type="text" name="translations[{{ $locale }}][title]" class="form-control" value="{{ old('translations.'.$locale.'.title', $tr->title ?? '') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Extracto</label>
                            <textarea name="translations[{{ $locale }}][excerpt]" class="form-control" rows="2">{{ old('translations.'.$locale.'.excerpt', $tr->excerpt ?? '') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contenido (HTML o Markdown)</label>
                            <textarea name="translations[{{ $locale }}][body]" class="form-control" rows="12">{{ old('translations.'.$locale.'.body', $tr->body ?? '') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Meta title</label>
                            <input type="text" name="translations[{{ $locale }}][meta_title]" class="form-control" value="{{ old('translations.'.$locale.'.meta_title', $tr->meta_title ?? '') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Meta description</label>
                            <textarea name="translations[{{ $locale }}][meta_description]" class="form-control" rows="2">{{ old('translations.'.$locale.'.meta_description', $tr->meta_description ?? '') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Focus keyword</label>
                            <input type="text" name="translations[{{ $locale }}][focus_keyword]" class="form-control" value="{{ old('translations.'.$locale.'.focus_keyword', $tr->focus_keyword ?? '') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">FAQ schema (JSON-LD)</label>
                            <textarea name="translations[{{ $locale }}][faq_schema]" class="form-control font-monospace" rows="8">{{ old('translations.'.$locale.'.faq_schema', isset($tr->faq_schema) ? json_encode($tr->faq_schema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : '') }}</textarea>
                            <div class="form-text">FAQPage con <code>mainEntity</code>. Se muestra en la página y en el head.</div>
                        </div>
                        @if($postModel)
                            <a href="{{ route('admin.platform.blog.preview', [$postModel, $locale]) }}" class="btn btn-outline-secondary btn-sm" target="_blank" rel="noopener">Vista previa {{ strtoupper($locale) }}</a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <button type="submit" class="btn btn-primary mt-3">{{ $submitLabel ?? 'Guardar' }}</button>
</form>
