<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>{{ config('app.name') }}</title>
  <link rel="stylesheet" href="{{ asset('adminlte/css/custom.css') }}">
  <!-- Tell the browser to be responsive to screen width -->
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="{{ asset('adminlte/plugins/fontawesome-free/css/all.min.css') }}">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="{{ asset('adminlte/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{ asset('adminlte/css/adminlte.min.css') }}">
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
</head>
<body class="hold-transition login-page">
<div class="login-box">
  <div class="login-logo">
    <a href="/"><b>Web</b>nu</a>
  </div>
  <!-- /.login-logo -->
  <div class="card">
    <div class="card-body login-card-body">
        @if(session()->has('status'))
            <p class="login-box-msg" style="font-weight:bold;">{{ Session::get('status') }}</p>
        @else
            <p class="login-box-msg">¿Olvidaste tu contraseña? Aquí puedes recuperar fácilmente una nueva contraseña.</p>

            <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="input-group mb-3">
                <input id="email" type="email" placeholder="Email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                <div class="input-group-append">
                    <div class="input-group-text">
                        <span class="fas fa-envelope"></span>
                    </div>
                </div>

                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="row">
                <div class="col-12">
                <button type="submit" class="btn btn-primary btn-block">Solicitar nueva contraseña</button>
                </div>
                <!-- /.col -->
            </div>
            </form>

            <p class="mt-3 mb-1">
            <a href="{{ route('login') }}">Volver a Inicio de sesión</a>
            </p>
            <!--<p class="mb-0">
            <a href="register.html" class="text-center">Register a new membership</a>
            </p>-->
        @endif
    </div>
    <!-- /.login-card-body -->
  </div>
</div>
<!-- /.login-box -->

<!-- jQuery -->
<script src="{{ asset('adminlte/plugins/jquery/jquery.min.js') }}"></script>
<!-- Bootstrap 4 -->
<script src="{{ asset('adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<!-- AdminLTE App -->
<script src="{{ asset('adminlte/js/adminlte.min.js') }}"></script>

</body>
</html>
