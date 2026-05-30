@extends('tv.layout')

@section('tv_content')
@php $presenter = app(\App\Services\TvMenuPresenter::class); @endphp
@include('tv.partials.video-zone', [
    'presenter' => $presenter,
    'videos' => $videos,
    'compact' => false,
    'showCaption' => true,
    'interval' => $rotateSeconds ?? 15,
    'emptyMessage' => 'Añade vídeos cortos a tus platos para mostrarlos en TV.',
])
@endsection

@push('tv_scripts')
<script src="{{ asset('js/webnu-tv.js') }}"></script>
<script>WebnuTv.initCarousel({ video: true, lazyVideo: true });</script>
@endpush
