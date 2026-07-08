@extends('blog.layout')

@section('content')
    <article>
        <a href="{{ route('blog.index', ['locale' => $locale]) }}" class="text-sm font-semibold text-blue-700 hover:text-blue-900">
            ← {{ __('blog.back_to_list') }}
        </a>

        <p class="mt-4 text-sm text-slate-500">{{ optional($post->published_at)->format('d/m/Y') }}</p>
        <h1 class="mt-2 text-4xl font-bold text-slate-900">{{ $translation->title }}</h1>

        @if($alternateTranslations->count() > 1)
            <div class="mt-4 flex flex-wrap gap-2 text-sm">
                @foreach($alternateTranslations as $alt)
                    @if($alt->locale !== $locale)
                        <a href="{{ $alt->publicUrl() }}" class="rounded-full border border-slate-200 px-3 py-1 text-slate-700 hover:bg-slate-50">
                            {{ strtoupper($alt->locale) }}
                        </a>
                    @endif
                @endforeach
            </div>
        @endif

        <div class="wn-blog-content mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            {!! $translation->renderedBody() !!}
        </div>
    </article>
@endsection
