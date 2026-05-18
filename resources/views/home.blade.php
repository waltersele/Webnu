@extends('layouts.app')

@section('content')
<section class="hero-area" id="home">
    <div class="container">
        @if($errors->any())
            <div class="form-group">
                @foreach($errors->all() as $error)
                    <div class="alert alert-danger">
                        <ul>
                            <li> {{ $error }} </li>
                        </ul>
                    </div>
                @endforeach
            </div>
        @endif
        <div class="row">
            <div class="col-lg-7">
                <div class="hero-area-content">
                    <h1>Totalmente GRATIS durante 30 días. <br>Tu carta digital, en un click. Pruébalo.</h1>
                    <p>Sin costes ocultos, con cambios ilimitados, sin permanencia, y tu código disponible de forma inmediata ¿ A qué esperas ? Tus clientes lo necesitan</p>
                    <a href="#pricing" class="appao-btn">Ver planes</a>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="header-form"><i class="icofont icofont-qr-code"></i> Pruébanos GRATIS durante 30 días, sin permanencia.</div>
                <form role="form" class="buyMeForm" id="subscription-form" action="{{ route('process_subscription') }}" method="POST">
                    @csrf

                    @include('partials.subscription-form-fields')
                    <button type="submit" class="appao-btn2">Contratar | 30 días GRATIS</button>
                  </form>
            </div>
        </div>
    </div>
</section><!-- hero area end -->
<!-- about section start -->
<!-- showcase section start -->
<section class="showcase-area ptb-90">
<div class="container">
<div class="row">
    <div class="col-lg-12">
        <div class="sec-title">
            <h2>Tu éxito, nuestra motivación<span class="sec-title-border"><span></span><span></span><span></span></span></h2>
            <p>Entendemos que cuánto más éxito tengas, mejor te podremos acompañar.</p>
        </div>
    </div>
</div>
<div class="row flexbox-center">
    <div class="col-lg-6">
        <div class="single-showcase-box">
            <h4>Más fácil de lo que piensas</h4>
            <p>Podrás modificar tu carta tantas veces como desees, de forma gratuita, y ofreciendo a los clientes tus productos fuera de carta, promociones, menús del día... Queremos que tu empresa sea lo más rentable posible, por eso hemos desarrollado este panel de control para que lo puedas hacer ¡GRATIS! </p>
            <div class="clientshow">
                <img style="width:100%" src="{{ asset('img/front/actual-clients.jpg') }}" alt="Clientes Webnu">
            </div>
            <hr>
            <h4 class="mb-2">¿Quieres ver cómo se vería tu carta?</h4>
            <hr> 
            <div class="row">
                <div class="col-md-6">
                    <a href="https://webnu.es/carta/webnu-test" target="_blank" class="appao-btn appao-btn2">Ver carta digital</a>
                </div>
                <div class="col-md-6">
                    <a href="https://webnu.es/carta/la-ibense" target="_blank" class="appao-btn appao-btn2">Ver carta digital PDF</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6 text-center">
        <div class="single-showcase-box">
            <video width="320" height="500" autoplay >
                <source src="{{ asset('img/front/mockup-xd.mp4') }}" type="video/mp4">
            </video>
        </div>
    </div>
</div>
<div class="row flexbox-center">
    <div class="col-lg-6">
        <div class="single-showcase-box">
            <img src="{{ asset('img/front/mock-1.png') }}" alt="showcase">
        </div>
    </div>
    <div class="col-lg-6">
        <div class="single-showcase-box">
            <h4>Tu imagen corporativa, también digital.</h4>
            <p>Para que puedas seguir teniendo tu imagen, también hemos querido ofrecerte la posibilidad de que subas tus archivos PDF para que tu identidad no se vea afectada. Entendemos que la imagen cuenta, y nos encanta que la puedas conservar. </p>
            <!-- <a href="#" class="appao-btn appao-btn2">Read More</a> -->
        </div>
    </div>
</div>
</div>
</section><!-- showcase section end -->
<section class="feature-area ptb-90" id="feature">
    <div class="container">
        <div class="row flexbox-center">
            <div class="col-lg-4">
                <div class="single-feature-box text-lg-right text-center">
                    <ul>
                        <li>
                            <div class="feature-box-info">
                                <h4>Personaliza tu carta</h4>
                                <p>Elige el estilo que más se adecúe a tu negocio</p>
                            </div>
                            <div class="feature-box-icon">
                                <i class="icofont icofont-brush"></i>
                            </div>
                        </li>
                        <li>
                            <div class="feature-box-info">
                                <h4>Diseño Adaptable</h4>
                                <p>Tu carta visible en todos los dispositivos </p>
                            </div>
                            <div class="feature-box-icon">
                                <i class="icofont icofont-computer"></i>
                            </div>
                        </li>
                        <li>
                            <div class="feature-box-info">
                                <h4>Manejo sencillo</h4>
                                <p>Videos y documentación para facilitar su uso </p>
                            </div>
                            <div class="feature-box-icon">
                                <i class="icofont icofont-law-document"></i>
                            </div>
                        </li>
                        <li>
                            <div class="feature-box-info">
                                <h4>Soporte técnico</h4>
                                <p>Equipo dedicado para que funcione a las 1000 maravillas</p>
                            </div>
                            <div class="feature-box-icon">
                                <i class="icofont icofont-heart-beat"></i>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="single-feature-box text-center">
                    <img src="{{ asset('/img/front/mock-menu-front.png') }}" alt="feature">
                </div>
            </div>
            <div class="col-lg-4">
                <div class="single-feature-box text-lg-left text-center">
                    <ul>
                        <li>
                            <div class="feature-box-icon">
                                <i class="icofont icofont-eye"></i>
                            </div>
                            <div class="feature-box-info">
                                <h4>Actualizaciones continuas</h4>
                                <p>No nos cansamos de crear nuevas funcionalidades para hacerte la vida más fácil </p>
                            </div>
                        </li>
                        <li>
                            <div class="feature-box-icon">
                                <i class="icofont icofont-sun-alt"></i>
                            </div>
                            <div class="feature-box-info">
                                <h4>También en PDF</h4>
                                <p>Si deseas continuar con tu diseño actual, sube tu PDF en el panel de control</p>
                            </div>
                        </li>
                        <li>
                            <div class="feature-box-icon">
                                <i class="icofont icofont-code-alt"></i>
                            </div>
                            <div class="feature-box-info">
                                <h4>Ahorra en costes</h4>
                                <p>Tu mismo puedes crear tu carta digital, y hacer tantos cambios como desees.</p>
                            </div>
                        </li>
                        <li>
                            <div class="feature-box-icon">
                                <i class="icofont icofont-headphone-alt"></i>
                            </div>
                            <div class="feature-box-info">
                                <h4>Imprimimos tus códigos</h4>
                                <p>Si deseas una impresión profesional, nos encargamos de la impresión. </p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section><!-- feature section end -->

<!-- video section start -->
<!-- <section class="video-area ptb-90">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="video-popup">
                    <a href="https://www.youtube.com/watch?v=RZXnugbhw_4" class="popup-youtube"><i class="icofont icofont-ui-play"></i></a>
                    <h1>Watch Video Demo</h1>
                </div>
            </div>
        </div>
    </div>
</section> -->
<!-- video section end -->
<!-- screenshots section start -->
<section class="screenshots-area ptb-90" id="screenshot">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="sec-title">
                    <h2>Así es como sería tu experiencia Webnu<span class="sec-title-border"><span></span><span></span><span></span></span></h2>
                    
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 col-xs-12 ">
                <ul class="list-group">
                    <li class="list-group-item">· Para fidelizar clientes</li>
                    <li class="list-group-item">· Para ahorrar costes</li>
                    <li class="list-group-item">· Para tener control absoluto de tu stock en todo momento</li>
                </ul>
            </div>
            <div class="col-md-6 col-xs-12">
                <ul class="list-group">
                    <li class="list-group-item">· Para incluir todos los datos necesarios sobre tus platos</li>
                    <li class="list-group-item">· Para hacer una carta dinámica y sin errores</li>
                    <li class="list-group-item">· Para dar una mayor seguridad tanto a tus empleados como a tus clientes</li>
                </ul>
            </div>
            
            
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="screenshot-wrap">
                    <div class="single-screenshot">
                        <img src="{{ asset('img/front/screenshot/screenshot1.jpg') }}" alt="screenshot" />
                    </div>
                    <div class="single-screenshot">
                        <img src="{{ asset('img/front/screenshot/screenshot2.jpg') }}" alt="screenshot" />
                    </div>
                    <div class="single-screenshot">
                        <img src="{{ asset('img/front/screenshot/screenshot3.jpg') }}" alt="screenshot" />
                    </div>
                    <div class="single-screenshot">
                        <img src="{{ asset('img/front/screenshot/screenshot4.jpg') }}" alt="screenshot" />
                    </div>
                    <div class="single-screenshot">
                        <img src="{{ asset('img/front/screenshot/screenshot5.jpg') }}" alt="screenshot" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</section><!-- screenshots section end -->
<!-- pricing section start -->
<section class="pricing-area ptb-90" id="pricing">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="sec-title">
                    <h2>Precios<span class="sec-title-border"><span></span><span></span><span></span></span></h2>
                    <p>Queremos que pagues lo menos posible, para que tu negocio sea más rentable. Te ayudamos a ahorrar.</p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6">
                <div class="single-pricing-box">
                    <div class="pricing-top">
                        <h4>Mensual</h4>
                        <p>Para los precabidos</p>
                    </div>
                    <div class="price">
                        <h1>10<span>€</span></h1>
                        <p>Al mes. I.V.A. Incluido</p>
                    </div>
                    <div class="price-details">
                        <ul>
                            <li>Generación automática de QR para que lo imprimas tu mismo</li>
                            <li>Posibilidad de impresión profesional de adhesivos con tu QR y logotipos</li>
                            <li>Carta digital inmediata</li>
                            <li>Cambios ilimitados</li>
                            <li>Sin costes ocultos</li>
                            <li>Diseño personalizable</li>
                            <li>Negocios ilimitados</li>
                        </ul>
                        <a class="appao-btn" href="#">¡Contratalo ya!</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="single-pricing-box">
                    <div class="pricing-top">
                        <h4>Anual</h4>
                        <p>¡Ahorra dos meses!</p>
                    </div>
                    <div class="price">
                        <h1>100<span>€</span></h1>
                        <p>Al año. I.V.A. Incluido</p>
                    </div>
                    <div class="price-details">
                        <ul>
                            <li>Generación automática de QR para que lo imprimas tu mismo</li>
                            <li>Posibilidad de impresión profesional de adhesivos con tu QR y logotipos</li>
                            <li>Carta digital inmediata</li>
                            <li>Cambios ilimitados</li>
                            <li>Sin costes ocultos</li>
                            <li>Diseño personalizable</li>
                            <li>Negocios ilimitados</li>
                        </ul>
                        <a class="appao-btn" href="#">¡Contratalo ya!</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section><!-- pricing section end -->
<!-- testimonial section start -->
<!-- <section class="testimonial-area ptb-90">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="sec-title">
                    <h2>Tus compañeros de gremio dicen esto...<span class="sec-title-border"><span></span><span></span><span></span></span></h2>
                    <p>Nos esforzamos por hacer un servicio realmente útil, para facilitarte la vida, y para que ahorres dinero. </p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-8 offset-lg-2">
                <div class="testimonial-wrap">
                    <div class="single-testimonial-box">
                        <div class="author-img">
                            <img src="{{ asset('img/front/author/author1.jpg') }}" alt="author" />
                        </div>
                        <h5>Mary Balogh</h5>
                        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi  aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in </p>
                        <div class="author-rating">
                            <i class="icofont icofont-star"></i>
                            <i class="icofont icofont-star"></i>
                            <i class="icofont icofont-star"></i>
                            <i class="icofont icofont-star"></i>
                            <i class="icofont icofont-star"></i>
                        </div>
                    </div>
                    <div class="single-testimonial-box">
                        <div class="author-img">
                            <img src="{{ asset('img/front/author/author2.jpg') }}" alt="author" />
                        </div>
                        <h5>Mary Balogh</h5>
                        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi  aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in </p>
                        <div class="author-rating">
                            <i class="icofont icofont-star"></i>
                            <i class="icofont icofont-star"></i>
                            <i class="icofont icofont-star"></i>
                            <i class="icofont icofont-star"></i>
                            <i class="icofont icofont-star"></i>
                        </div>
                    </div>
                    <div class="single-testimonial-box">
                        <div class="author-img">
                            <img src="{{ asset('img/front/author/author2.jpg') }}" alt="author" />
                        </div>
                        <h5>Mary Balogh</h5>
                        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi  aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in </p>
                        <div class="author-rating">
                            <i class="icofont icofont-star"></i>
                            <i class="icofont icofont-star"></i>
                            <i class="icofont icofont-star"></i>
                            <i class="icofont icofont-star"></i>
                            <i class="icofont icofont-star"></i>
                        </div>
                    </div>
                </div>
                <div class="testimonial-thumb">
                    <div class="thumb-prev">
                        <div class="author-img">
                            <img src="{{ asset('img/front/author/author2.jpg') }}" alt="author" />
                        </div>
                    </div>
                    <div class="thumb-next">
                        <div class="author-img">
                            <img src="{{ asset('img/front/author/author2.jpg') }}" alt="author" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section> -->
<!-- testimonial section end -->
<!-- counter section start -->
<section class="counter-area ptb-90">
    <div class="container">
        <div class="row">
            <div class="col-md-4 col-sm-6">
                <div class="single-counter-box">
                    <i class="icofont icofont-edit"></i>
                    <h1><span class="counter">542</span></h1>
                    <p>Clientes satisfechos</p>
                </div>
            </div>
            <div class="col-md-4 col-sm-6">
                <div class="single-counter-box">
                    <i class="icofont icofont-qr-code"></i>
                    <h1><span class="counter">12402</span></h1>
                    <p>Escaneos</p>
                </div>
            </div>
            <div class="col-md-4 col-sm-6">
                <div class="single-counter-box">
                    <i class="icofont icofont-download-alt"></i>
                    <h1><span class="counter">123141</span>K</h1>
                    <p>Modificaciones GRATIS</p>
                </div>
            </div>

        </div>
    </div>
</section><!-- counter section end -->
<!-- team section start -->
<!-- <section class="team-area ptb-90" id="team">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="sec-title">
                    <h2>Meet Our Team<span class="sec-title-border"><span></span><span></span><span></span></span></h2>
                    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt </p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-3 col-sm-6">
                <div class="single-team-member">
                    <div class="team-member-img">
                        <img src="assets/img/team/team1.jpg" alt="team">
                        <div class="team-member-icon">
                            <div class="display-table">
                                <div class="display-tablecell">
                                    <a href="#"><i class="icofont icofont-social-facebook"></i></a>
                                    <a href="#"><i class="icofont icofont-social-twitter"></i></a>
                                    <a href="#"><i class="icofont icofont-brand-linkedin"></i></a>
                                    <a href="#"><i class="icofont icofont-social-pinterest"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="team-member-info">
                        <a href="#"><h4>John Deo</h4></a>
                        <p>Web Developer</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="single-team-member">
                    <div class="team-member-img">
                        <img src="assets/img/team/team2.jpg" alt="team">
                        <div class="team-member-icon">
                            <div class="display-table">
                                <div class="display-tablecell">
                                    <a href="#"><i class="icofont icofont-social-facebook"></i></a>
                                    <a href="#"><i class="icofont icofont-social-twitter"></i></a>
                                    <a href="#"><i class="icofont icofont-brand-linkedin"></i></a>
                                    <a href="#"><i class="icofont icofont-social-pinterest"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="team-member-info">
                        <a href="#"><h4>Sharon Garcia</h4></a>
                        <p>UX Designer</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="single-team-member">
                    <div class="team-member-img">
                        <img src="assets/img/team/team3.jpg" alt="team">
                        <div class="team-member-icon">
                            <div class="display-table">
                                <div class="display-tablecell">
                                    <a href="#"><i class="icofont icofont-social-facebook"></i></a>
                                    <a href="#"><i class="icofont icofont-social-twitter"></i></a>
                                    <a href="#"><i class="icofont icofont-brand-linkedin"></i></a>
                                    <a href="#"><i class="icofont icofont-social-pinterest"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="team-member-info">
                        <a href="#"><h4>Elijah Henderson</h4></a>
                        <p>UX Designer</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="single-team-member">
                    <div class="team-member-img">
                        <img src="assets/img/team/team4.jpg" alt="team">
                        <div class="team-member-icon">
                            <div class="display-table">
                                <div class="display-tablecell">
                                    <a href="#"><i class="icofont icofont-social-facebook"></i></a>
                                    <a href="#"><i class="icofont icofont-social-twitter"></i></a>
                                    <a href="#"><i class="icofont icofont-brand-linkedin"></i></a>
                                    <a href="#"><i class="icofont icofont-social-pinterest"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="team-member-info">
                        <a href="#"><h4>Sharon Garcia</h4></a>
                        <p>UX Designer</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section> -->
<!-- team section end -->
<!-- download section start -->
<!-- <section class="download-area ptb-90">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="sec-title">
                    <h2>Download Available<span class="sec-title-border"><span></span><span></span><span></span></span></h2>
                    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt </p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <ul>
                    <li>
                        <a href="#" class="download-btn flexbox-center">
                            <div class="download-btn-icon">
                                <i class="icofont icofont-brand-android-robot"></i>
                            </div>
                            <div class="download-btn-text">
                                <p>Available on</p>
                                <h4>Android Store</h4>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="download-btn flexbox-center">
                            <div class="download-btn-icon">
                                <i class="icofont icofont-brand-apple"></i>
                            </div>
                            <div class="download-btn-text">
                                <p>Available on</p>
                                <h4>Apple Store</h4>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="download-btn flexbox-center">
                            <div class="download-btn-icon">
                                <i class="icofont icofont-brand-windows"></i>
                            </div>
                            <div class="download-btn-text">
                                <p>Available on</p>
                                <h4>Windows Store</h4>
                            </div>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section> -->
<!-- download section end -->
<!-- blog section start -->
<!-- <section class="blog-area ptb-90" id="blog">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="sec-title">
                    <h2>Our Latest Blog<span class="sec-title-border"><span></span><span></span><span></span></span></h2>
                    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt </p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-4 col-md-6">
                <div class="single-post">
                    <div class="post-thumbnail">
                        <a href="blog.html"><img src="assets/img/blog/blog1.jpg" alt="blog"></a>
                    </div>
                    <div class="post-details">
                        <div class="post-author">
                            <a href="blog.html"><i class="icofont icofont-user"></i>John</a>
                            <a href="blog.html"><i class="icofont icofont-speech-comments"></i>Comments</a>
                            <a href="blog.html"><i class="icofont icofont-calendar"></i>21 Feb 2018</a>
                        </div>
                        <h4 class="post-title"><a href="blog.html">Lorem ipsum dolor sit</a></h4>
                        <p>Lorem ipsum dolor sit amet, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="single-post">
                    <div class="post-thumbnail">
                        <a href="blog.html"><img src="assets/img/blog/blog2.jpg" alt="blog"></a>
                    </div>
                    <div class="post-details">
                        <div class="post-author">
                            <a href="blog.html"><i class="icofont icofont-user"></i>John</a>
                            <a href="blog.html"><i class="icofont icofont-speech-comments"></i>Comments</a>
                            <a href="blog.html"><i class="icofont icofont-calendar"></i>21 Feb 2018</a>
                        </div>
                        <h4 class="post-title"><a href="blog.html">Lorem ipsum dolor sit</a></h4>
                        <p>Lorem ipsum dolor sit amet, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 d-md-none d-lg-block">
                <div class="single-post">
                    <div class="post-thumbnail">
                        <a href="blog.html"><img src="assets/img/blog/blog3.jpg" alt="blog"></a>
                    </div>
                    <div class="post-details">
                        <div class="post-author">
                            <a href="blog.html"><i class="icofont icofont-user"></i>John</a>
                            <a href="blog.html"><i class="icofont icofont-speech-comments"></i>Comments</a>
                            <a href="blog.html"><i class="icofont icofont-calendar"></i>21 Feb 2018</a>
                        </div>
                        <h4 class="post-title"><a href="blog.html">Lorem ipsum dolor sit</a></h4>
                        <p>Lorem ipsum dolor sit amet, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section> -->
<!-- blog section end -->
<!-- google map area start -->
<!-- <div class="google-map"></div> -->
<!-- google map area end -->
@endsection

@push('scripts')


@include('partials.subscription-stripe-scripts')

@endpush
