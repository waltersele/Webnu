<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex" />
    <title>Carta {{ $company->name }}</title>
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script type="text/javascript" src="{{ asset('js/wowbook/wow_book.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('js/wowbook/wow_book.css') }}" type="text/css" />
    <script type="text/javascript" src="{{ asset('js/wowbook/pdf.combined.min.js') }}"></script>
    <link rel="icon" type="image/png" href="{{ asset('img/front/favicon.png') }}" />
    <style>
        body {
            margin: 0;
            background: #141b2b;
            color: #fff;
            font-family: system-ui, sans-serif;
        }
        .menu-pdf-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 18px;
            background: rgba(0, 0, 0, 0.35);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        .menu-pdf-header img {
            height: 36px;
        }
        .menu-pdf-header__title {
            font-size: 0.9375rem;
            font-weight: 600;
            margin: 0;
        }
        .menu-pdf-header__url {
            font-size: 0.75rem;
            opacity: 0.65;
            margin: 0;
        }
        #carta-pdf-viewer {
            min-height: calc(100vh - 68px);
        }
    </style>
</head>
<body>
    <header class="menu-pdf-header">
        <img src="{{ asset('img/front/logo.png') }}" alt="Webnu">
        <div class="text-end">
            <p class="menu-pdf-header__title">{{ $company->name }}</p>
            <p class="menu-pdf-header__url">{{ url('/carta/' . $company->slug) }}</p>
        </div>
    </header>

    <div id="carta-pdf-viewer"></div>

    <script type="text/javascript">
    $(document).ready(function () {
        $('#carta-pdf-viewer').wowBook({
            pdf: @json(asset('img/' . $company->menu_type_2_pdf)),
            height: 500,
            width: 800,
            maxWidth: 800,
            maxHeight: 600,
            centeredWhenClosed: true,
            hardcovers: true,
            pageNumbers: false,
            toolbar: 'left, right, zoomin, zoomout, slideshow, fullscreen',
            thumbnailsPosition: 'left',
            responsiveHandleWidth: 50,
            container: window,
            containerPadding: '20px',
            updateBrowserURL: false,
        });
    });
    </script>
</body>
</html>
