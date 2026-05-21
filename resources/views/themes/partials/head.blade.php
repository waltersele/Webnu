<!DOCTYPE html>

<html lang="{{ $menuLocale ?? 'es' }}">

<head>

    <meta charset="UTF-8">

    <title>Carta Digital de {{ $company->name }} - Ver nuestros productos.</title>

<meta name="description" content="Carta digital de {{$company->name}}. Aquí puedes encontrar todos nuestros productos y precios. Además de contactar con nostoros y realizar reservas.">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" type="text/css" href="{{asset('css/themes/generic.css')}}">

    <link rel="stylesheet" type="text/css" href="{{ asset('css/themes/front-modern.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/themes/front-menu-ui.css') }}">
    <link rel="stylesheet" type="text/css" href="{{asset('css/themes/front-'.$company->template.'.css')}}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/themes/product-media.css') }}">

    @include('themes.partials.theme-vars')

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous"/>

    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    @include('themes.partials.theme-modal-overrides')

    <link rel="icon" type="image/png" href="{{ \App\PlatformSetting::brandUrl('favicon') }}" />

    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-167367604-1"></script>

    <script>

    window.dataLayer = window.dataLayer || [];

    function gtag(){dataLayer.push(arguments);}

    gtag('js', new Date());

    gtag('config', 'UA-167367604-1');

    </script>

</head>

<body class="wn-theme-{{ $company->template }}">

