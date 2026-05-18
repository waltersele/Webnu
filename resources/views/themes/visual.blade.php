@include('themes.partials.head')
<!-- header -->

<header >
        <div class="barname text-center">
            @if ($company->logo)
                <img src="{{URL::to('/').'/img/'.$company->logo }}" alt="{{ $company->name }}" class="logo img-responsive">
            @else
            <h1>{{ $company->name }}</h1>
            @endif
        </div>
        <div class="clearfix"></div>
        <div class="fluid-container" id="sticker" >
            <div class="visual-menu-sections">
                @foreach ($sections as $section)
                    <div class="visual-menu-sections-item">
                        <a href="#" class="linkTo" id='{{ $section->id }}'>
                            <p class="visual-menu-sections-item-content">{{ $section->name }}</p>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>

</header>

<div class="clearfix"></div>

{{-- Menu Content --}}

@foreach ($sections as $section) <!--Start Foreach-->
<div class="container">
    <h2 id="section-{{ $section->id }}" class="menu-dishes-title">{{ $section->name }}</h2>
</div>


<div class="container menu-dishes-block">
    @foreach ($section->products as $product)
        <div class="row menu-dishes-item-block">
            <div class="col-7">
                <div class="menu-dishes-item-title">{{ $product->name }} @include('themes.partials.product-highlight-badge', ['product' => $product])</div>
                <div class="menu-dishes-item-description">{{ $product->description }}</div>
                <div class="row container">

                        <div class="row price">
                            @if($product->price_portion)
                                <div class="col-12">
                                Media: {{ $product->price_portion}} €</div>
                            @endif
                            @if($product->price_unit)
                                <div class="col-12">
                                @if($product->price_portion)
                                Entera: @endif {{$product->price_unit}} € 
                                @include('themes.partials.product-price-suffix', ['product' => $product])
                                </div>
                            @endif

                        </div>



                        {{-- @if($product->price_portion)
                        Media:
                        {{ $product->price_portion}}€
                        @endif
                    </div>
                    <div class="col-6 price whole-dish">
                    @if($product->price_portion)
                        Entera:
                    @endif
                        {{ $product->price_unit}} €

                                @include('themes.partials.product-price-suffix', ['product' => $product]) --}}


                  
                </div>
                <div class="allergens">
                    @foreach ($product->allergens as $allergen)
                        <img src="{{ URL::to('/').'/img/'.$allergen->image }}" alt="{{ $allergen->name }}">
                    @endforeach
                </div>
            </div>
            <div class="col-5">
                <div class="product-img">
                    @include('themes.partials.product-inline-thumb', ['product' => $product])
                </div>
            </div>
            @if ($product->video)
                @include('themes.partials.product-modal', ['product' => $product])
            @endif
        </div>
    @endforeach
</div>

@endforeach  <!--End Foreach-->






<!-- FOOTER -->

<footer class="footer text-center container-fluid" id="footer">
    <div class="container">
    @if($company->logo)
        <img src="{{URL::to('/').'/img/'.$company->logo }}" alt="{{ $company->name }}" class="img-responsive logo">
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
        
    </div>
    <div>
        @if($company->schedule)
            <p class="schedule"><i class="fas fa-clock"></i>Horario:- {{ $company->schedule }} -</p>
        @endif
    </div>

    <p class="footer-contact-address">
        <i class="fa fa-map-marker"></i> {{ $company->address }}, {{$company->city}}, {{ $company->country }}
    </p>
    <div class="copyright">
        <p>  © Webnu - Todos Los derechos reservados </p>
    </div>

</footer>

<!-- END FOOTER -->

{{-- Include footer fixed bar --}}

@include('themes.partials.footerbar')

{{-- FIXED BOTTOM BAR --}}

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.bundle.min.js" integrity="sha384-1CmrxMRARb6aLqgBO7yyAxTOQE2AKb9GfXnEo760AUcUmFx3ibVJJAzGytlQcNXd" crossorigin="anonymous"></script>
<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
<script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>

<script src="{{asset('js/jquery.sticky.js')}}"></script>

<script>

//Slick

$('.visual-menu-sections').slick({
    slidesToShow: 1,
    variableWidth: true,
    arrows:false,
    cancelable:false
});

//Sticky

  $(document).ready(function(){
    $("#sticker").sticky({
        
    })
  });

  $(document).ready(function(){
    $(".fixed-bottom-bar").sticky({
        bottomSpacing:0
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
