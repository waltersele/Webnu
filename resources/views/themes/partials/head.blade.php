<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carta Digital de {{ $company->name }} - Ver nuestros productos.</title>
<meta name="description" content="Carta digital de {{$company->name}}. Aquí puedes encontrar todos nuestros productos y precios. Además de contactar con nostoros y realizar reservas.">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="{{asset('css/themes/generic.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('css/themes/front-'.$company->template.'.css')}}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/themes/product-media.css') }}">
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous"/>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <!-- Favicon Icon -->
    <link rel="icon" type="image/png" href="{{asset('img/front/favicon.png') }}" />
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-167367604-1"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'UA-167367604-1');
    </script>
</head>
<body>
