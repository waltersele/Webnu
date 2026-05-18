<!-- footer section start -->
<footer class="footer" id="contact">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="contact-form">
                    @if (Session::has('te-llamamos-ok'))
                        <div class="alert alert-success">
                        <p>{{Session::get('te-llamamos-ok') }}</p>
                        </div>
                    @endif
                    @if (Session::has('te-llamamos-failure'))
                        <div class="alert alert-danger">
                        <p>{{ Session::get('te-llamamos-failure') }}</p>
                        </div>
                    @endif

                    <h4>Te llamamos</h4>
                    <p class="form-message"></p>
                    <form id="contact-form" action="{{ route('te_llamamos') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <input type="text" name="name" placeholder="Introduce tu nombre">
                        </div>
                        <div class="form-group">
                            <input type="email" name="email" placeholder="Introduce tu email">
                        </div>
                        <div class="form-group">
                            <input type="text" name="phone" placeholder="Introduce tu teléfono">
                        </div>

                        <button type="submit" name="submit">Solicitar llamada</button>
                    </form>
                </div>
            </div>

        </div>
        <!-- <div class="row">
            <div class="col-lg-12">
                <div class="subscribe-form">
                    <form action="#">
                        <input type="text" placeholder="Your email address here">
                        <button type="submit">Subcribe</button>
                    </form>
                </div>
            </div>
        </div> -->
        <div class="row">
            <div class="col-lg-12">
                <div class="copyright-area">
                    <!-- <ul>
                        <li><a href="#"><i class="icofont icofont-social-facebook"></i></a></li>
                        <li><a href="#"><i class="icofont icofont-social-twitter"></i></a></li>
                        <li><a href="#"><i class="icofont icofont-brand-linkedin"></i></a></li>
                        <li><a href="#"><i class="icofont icofont-social-pinterest"></i></a></li>
                        <li><a href="#"><i class="icofont icofont-social-google-plus"></i></a></li>
                    </ul> -->
                    <p>&copy;
Copyright &copy;{{ now()->year }} Todos los derechos reservados | Desarrollado con <i class="fa fa-heart-o" aria-hidden="true"></i> por Webnu
</p>
                </div>
            </div>
        </div>
    </div>
</footer><!-- footer section end -->
