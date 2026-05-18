<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ route('admin.dashboard') }}" class="brand-link">
      <img src="{{ asset('adminlte/img/webnu.png') }}" alt="Webnu" class="brand-img">
      <span class="brand-text font-weight-light">&nbsp;</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            <!-- Add icons to the links using the .nav-icon class
                    with font-awesome or any other icon font library -->
            <!--<li class="nav-item has-treeview menu-open">
                <a href="#" class="nav-link active">
                <i class="nav-icon fas fa-tachometer-alt"></i>
                <p>
                    Starter Pages
                    <i class="right fas fa-angle-left"></i>
                </p>
                </a>
                <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="#" class="nav-link active">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Active Page</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Inactive Page</p>
                    </a>
                </li>
                </ul>
            </li>-->
            <li class="user-panel nav-item">
                <a href="{{ route('admin.companies.index') }}" class="nav-link {{ request()->is('admin/companies') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-store"></i>
                    <p>Negocios</p>
                </a>
            </li>
            @if (!empty($selected_company))
                <li class="nav-item">
                    <div class="mb-3">
                        <label class="current-company-label">Negocio actual</label>
                        <form role="form" method="POST" action="{{ route('admin.companies.changecompany', '0') }}" id="company-selection-form">
                            {{ csrf_field() }}
                            <div class="input-group">
                                <select class="custom-select" name="company_selection" id="company_selection">
                                    @foreach ($available_companies as $company)
                                        <option value="{{ $company->id }}" {{ $company->id == $selected_company ? 'selected="selected"' : '' }}>{{ $company->name }}</option>
                                    @endforeach
                                </select>
                            </div>  
                            
                        </form>
                    </div>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.sections.index') }}" class="nav-link {{ request()->is('admin/sections') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-th"></i>
                        <p>Carta</p>
                    </a>
                </li>
            @endif
            <li class="nav-item">
                <a href="{{ route('admin.integrations.index') }}" class="nav-link {{ request()->is('admin/integrations') || request()->is('admin/signage') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-plug"></i>
                    <p>Integraciones</p>
                </a>
            </li>
        </ul>
    </nav>
    <!-- /.sidebar-menu -->
  </div>
  <!-- /.sidebar -->
</aside>

@push('scripts')
    <script>
        $( document ).ready(function() {
            $( "#company_selection" ).change(function() {
                $('#company-selection-form').submit();
            });
        });
    </script>
@endpush
