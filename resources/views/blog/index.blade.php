@extends('blog.layout')

@section('content')
    <div class="mb-8">
        <p class="text-sm font-semibold uppercase tracking-wide text-blue-700">{{ __('blog.title') }}</p>
        <h1 class="mt-2 text-4xl font-bold text-slate-900">{{ __('blog.heading') }}</h1>
        <p class="mt-3 max-w-2xl text-slate-600">{{ __('blog.intro') }}</p>
    </div>

    @if($posts->isEmpty())
        <p class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-slate-600">{{ __('blog.empty') }}</p>
    @else
        <div class="grid gap-6">
            @foreach($posts as $post)
                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:shadow-md">
                    <p class="text-sm text-slate-500">{{ optional($post->post->published_at)->format('d/m/Y') }}</p>
                    <h2 class="mt-2 text-2xl font-bold text-slate-900">
                        <a href="{{ route('blog.show', ['locale' => $locale, 'slug' => $post->slug]) }}" class="hover:text-blue-700">
                            {{ $post->title }}
                        </a>
                    </h2>
                    @if($post->excerpt)
                        <p class="mt-3 text-slate-600">{{ $post->excerpt }}</p>
                    @endif
                    <a href="{{ route('blog.show', ['locale' => $locale, 'slug' => $post->slug]) }}"
                       class="mt-4 inline-flex text-sm font-semibold text-blue-700 hover:text-blue-900">
                        {{ __('blog.read_more') }} →
                    </a>
                </article>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $posts->links() }}
        </div>
    @endif
@endsection
