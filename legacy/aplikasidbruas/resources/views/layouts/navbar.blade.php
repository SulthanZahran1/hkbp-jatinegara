  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      {{-- <li class="nav-item d-none d-sm-inline-block">
        <a href="index3.html" class="nav-link">Home</a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="#" class="nav-link">Contact</a>
      </li> --}}
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">

        <div class="user-panel mt-1 pb-1 mb-1 d-flex">
            <div class="image">
              <img src="{{asset('adminlte')}}/dist/img/user2-160x160.jpg" class="img-circle" alt="User Image">
            </div>
            <div class="info">
              <a href="#" class="d-block" style="color: black">{{ session('nama_depan') . " " . session('nama_belakang') }}</a>
            </div>
          </div>
      <!-- Notifications Dropdown Menu -->
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
            <i class="fas fa-th-large"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item" onclick=logout();>
            <i class="fas fa-sign-out-alt"></i> Logout
            {{-- <span class="float-right text-muted text-sm">LOGOUT</span> --}}
          </a>
          <div class="dropdown-divider"></div>
          {{-- <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a> --}}
        </div>
      </li>


      {{-- <li class="nav-item">
        <a class="nav-link" data-widget="control-sidebar" data-controlsidebar-slide="true" href="#" role="button">
          <i class="fas fa-th-large"></i>
        </a>
      </li> --}}
    </ul>
  </nav>
  <!-- /.navbar -->