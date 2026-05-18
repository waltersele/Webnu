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
    <script type="text/javascript" src="{{ asset('js/wowbook/wow_book.min.js') }}"></script>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <!-- Favicon Icon -->
    <link rel="icon" type="image/png" href="{{ asset('img/front/favicon.png') }}" />

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
    <img src="{{ asset('img/front/logo.png') }}" style="position:absolute;top:15px;left:15px;margin:0 auto" height="40" alt="">

    <div class="container">
        <div id="mybook">

        </div>
    </div>
    <script type="text/javascript">
    $(document).ready(function() {
        $('#mybook').wowBook({

            pdf: "{{ asset('img/'.$company->menu_type_2_pdf) }}",
            height   : 500
            ,width    : 800
            ,maxWidth : 800
            ,maxHeight : 600

            ,centeredWhenClosed : true
            ,hardcovers : true
            ,pageNumbers: false
            ,toolbar : "left, right ,zoomin, zoomout, slideshow, fullscreen"
            ,thumbnailsPosition : 'left'
            ,responsiveHandleWidth : 50

            ,container: window
            ,containerPadding: "20px"

        });

    });
    </script>
</body>
</html>
