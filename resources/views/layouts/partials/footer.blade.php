<!-- footer section start -->
<footer class="footer" id="contact">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="contact-form">
                    <h4>Te llamamos</h4>
                    <p class="text-muted mb-3">Déjanos tu contacto y te llamamos sin compromiso.</p>
                    <p class="form-message"></p>
                    @include('partials.te-llamamos-form')
                </div>
            </div>

        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="copyright-area">
                    <p>&copy;
Copyright &copy;{{ now()->year }} Todos los derechos reservados | Desarrollado con <i class="fa fa-heart-o" aria-hidden="true"></i> por Webnu
</p>
                </div>
            </div>
        </div>
    </div>
</footer><!-- footer section end -->
