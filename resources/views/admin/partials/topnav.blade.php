<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <!--
      <li class="nav-item d-none d-sm-inline-block">
        <a href="index3.html" class="nav-link">Inicio</a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="#" class="nav-link">Contacto</a>
      </li>
      -->
    </ul>

    <!-- SEARCH FORM
    <form class="form-inline ml-3">
      <div class="input-group input-group-sm">
        <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
        <div class="input-group-append">
          <button class="btn btn-navbar" type="submit">
            <i class="fas fa-search"></i>
          </button>
        </div>
      </div>
    </form>-->

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      <!-- Notifications Dropdown Menu -->
      <li class="nav-item dropdown">
        <a class="nav-link" data-bs-toggle="dropdown" href="#">
          Hola, {{ auth()->user()->name }} <i class="fas fa-chevron-down"></i>
          <!--<span class="badge badge-warning navbar-badge">15</span>-->
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
            <!--<a href="#" class="dropdown-item">
                <i class="fas fa-user"></i> Mi cuenta
            </a>-->
            <div class="dropdown-divider"></div>
            <form method="POST" action="{{ route('logout') }}" class="d-inline w-100">
                @csrf
                <button type="submit" class="dropdown-item border-0 bg-transparent w-100 text-left">
                    <i class="fas fa-power-off"></i> Cerrar sesión
                </button>
            </form>
        </div>
      </li>
    </ul>
  </nav>
  <!-- /.navbar -->
