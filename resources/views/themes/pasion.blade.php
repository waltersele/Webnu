@include('themes.partials.head')
<!-- header -->
<header class="fluid-container menu-header text-center" style="background:url('{{ $company->background_header != null ? URL::to('/').'/img/'.$company->background_header : URL::to('/').'/img/default-header.jpg' }}');">
    <div class="menu-overlay-header">
        <div class="menu-header-content">
            <div class="menu-logo-box text-center" style="height:150px">
                <div class="logo-circle">
                    <img class="menu-logo-img img-responsive" src="{{ $company->logo ? asset('img/'.$company->logo) : asset('img/front/logo.png') }}" alt="{{ $company->name }}">
                </div>
            </div>
            <div class="menu-header-info container pb-4">
           
               
            </div>
        </div>
    </div>
</header>


<div class="clearfix"></div>

<!-- Menu Sections -->

    <div class="fluid-container" id="sticker">
        <div class="menu-sections">
            @foreach ($sections as $section)
                <div class="menu-sections-item">
                    <a href="#" class="linkTo" id='{{ $section->id }}'>
                        <p class="menu-sections-item-content">{{ $section->name }}</p>
                    </a>
                </div>
            @endforeach
        </div>
    </div>


<!-- Menu Content -->
<div class="container main-menu">
    <!-- 1section -->
    @foreach ($sections as $section)
        <div class="menu-dishes-section">

            <h2 id="section-{{ $section->id }}" class="menu-dishes-title">~{{ $section->name }}~</h2>
            <div class="menu-dishes-block">
                <!-- menu item -->
                @foreach ($section->products as $product)
                    <div class="menu-dishes-item">
                        <div class="row">
                            <!-- <span class="menu-dishes-item-dots"></span> -->
                            <div class="col-7">
                                <div class="menu-dishes-item-title">
                                    <h3>{{ $product->name }} @include('themes.partials.product-highlight-badge', ['product' => $product])</h3>
                                </div>
                                <div class="menu-dishes-item-desc">
                                    <p>{{ $product->description }}</p>
                                </div>
                            </div>
                            <div class="col-5">
                                <div class="menu-dishes-item-price-block row">
                                    <div class="menu-dishes-item-price {{  $product->price_portion ? 'text-center' : 'text-right' }} col-6">
                                        @if($product->price_portion)
                                        <span class="half-dish">Media: {{ $product->price_portion}}€</span>
                                        @endif
                                    </div>
                                    <div class="menu-dishes-item-price {{  $product->price_portion ? 'text-center' : 'text-right' }} col-6">
                                        <span class="whole-dish">
                                        @if($product->price_portion)
                                            Entera:
                                        @endif
                                            {{ $product->price_unit }}€</span>
                                            @include('themes.partials.product-price-suffix', ['product' => $product])

                                    </div>
                                </div>
                            </div>
                            <hr class="divider">
                            <div class="col-4">
                                <a id="moreDetails" class="hide-print details-dish btn btn-outline-secondary btn-sm" data-toggle="modal" data-target="#dishDetails{{ $product->id }}" href="#">+detalles</a>
                            </div>
                            <div class="col-8">
                                <div class="menu-dishes-allergic text-center float-right">
                                    @foreach ($product->allergens as $allergen)
                                        <img src="{{ URL::to('/').'/img/'.$allergen->image }}" alt="{{ $allergen->name }}">
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="menu-dishes-details">
                            <!-- Modal Details -->
                            <div class="details-modal modal fade bd-example-modal-lg" id="dishDetails{{ $product->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog modal-lg" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close modal-close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                            </button>
                                            @include('themes.partials.product-modal-media', ['product' => $product])
                                        </div>
                                        <div>
                                            <div class="modal.body container">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <h3>{{ $product->name }} @include('themes.partials.product-highlight-badge', ['product' => $product])</h3>
                                                    </div>
                                                </div>

                                                <p>{{ $product->description}}</p>

                                            </div>
                                        </div>
                                        <div class="row text-center">
                                            <div class="col-6 price half-dish">
                                                @if($product->price_portion)
                                                Media:
                                                {{ $product->price_portion}}€
                                                @endif
                                            </div>
                                            <div class="col-6 price whole-dish">
                                            @if($product->price_portion)
                                                Entera:
                                            @endif
                                                {{ $product->price_unit}} €

                                                @include('themes.partials.product-price-suffix', ['product' => $product])


                                            </div>
                                        </div>

                                        <div class="alergenos text-center">
                                                @foreach ($product->allergens as $allergen)
                                                    <img src="{{ URL::to('/').'/img/'.$allergen->image }}" alt="{{ $allergen->name }}">
                                                @endforeach
                                            </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-success btn-lg btn-block align-center" data-dismiss="modal">Cerrar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- END Modal Details -->
                        </div>

                @endforeach
                <!--menu-item-->
            </div>
        </div>
        <!-- END 1section -->
    @endforeach
</div>



</div>

<!-- FOOTER -->

<footer class="footer text-center container-fluid" id="footer">
    <div class="container">
    @if($company->logo)
        <img src="{{URL::to('/').'/img/'.$company->logo }}" alt="{{ $company->name }}" class="img-responsive">
    @endif
    </div>
    <div class="footer-contact-restaurant container">
        <div class="row">
            <div class="col-md-6"> <a href="tel:{{$company->phone}}">Teléfono:{{$company->phone}}</a></div>
            <div class="col-md-6"> <a href="tel:{{$company->mobile_phone}}">Teléfono Móvil:{{$company->mobile_phone}}</a></div>
        </div>
        <div class="row">
            @if($company->comments)
                <h3 class="text-center">Acerca de nuestro negocio: {{ $company->comments }}</h3>
            @endif
        </div>
        <hr class="divider">
        <div class="row" id="hide-print">
            <div class="col-md-6 col-sm-12">
                <a class="btn btn-warning btn-block col-12" href="mailto:{{ $company->email }}">Escribir una sugerencia</a>
            </div>

        </div>
    </div>
    <p class="footer-contact-address">
        <i class="fa fa-map-marker"></i> {{ $company->address }}, {{$company->city}}, {{ $company->country }}
    </p>
    <div class="copyright">
        <p>  © Webnu - Todos Los derechos reservados </p>
    </div>

</footer>

{{-- Include footer fixed bar --}}

@include('themes.partials.footerbar')

{{-- FIXED BOTTOM BAR --}}

<!-- END FOOTER -->

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.bundle.min.js" integrity="sha384-1CmrxMRARb6aLqgBO7yyAxTOQE2AKb9GfXnEo760AUcUmFx3ibVJJAzGytlQcNXd" crossorigin="anonymous"></script>
<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
<script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>

<script src="{{asset('js/jquery.sticky.js')}}"></script>

<script>
//Slick

$('.menu-sections').slick({
    infinite: true,
    slidesToShow: 7,
    arrows: false,
    infinite:true,

  responsive: [
    
    {
      breakpoint: 880,
      settings: {
        arrows: false,
        centerPadding: '10px',
        slidesToShow: 2,
        infinite:true,
        centerMode: true,
        variableWidth: true
        
      }
    }
  ]
});

//Sticky

  $(document).ready(function(){
    $("#sticker").sticky({
        topSpacing:30 
    })
  });

  //Animate
$(window).ready(function(){
    var $jq = jQuery.noConflict();
    $jq('a.linkTo').on('click',function(e){
    e.preventDefault();
    var animateTo = $jq('#section-' + this.id);
    $jq('html, body').animate({

        scrollTop: animateTo.position().top-40

    },1000);

 });

});

$(window).ready(function(){
    var $jq = jQuery.noConflict();
    $jq('a.linkToInfo').on('click',function(e){
    e.preventDefault();
    var animateTo = $jq('#footer');
    $jq('html, body').animate({

        scrollTop: animateTo.position().top-40

    },1000);

 });

});

    





</script>



</body>
</html>