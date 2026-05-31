<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4" style="background-color:#1d7e8e">
    <!-- Brand Logo -->
    <a href="index3.html" class="brand-link">
      <img src="{{asset('img')}}/logo-hkbp.webp" alt="Logo HKBP" class="brand-image" style="opacity: .9">
      <span class="brand-text font-weight-normal">HKBP JATINEGARA</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-2 pb-2 mb-2 d-flex">
        {{-- <div class="image">
          <img src="{{asset('adminlte')}}/dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
        </div> --}}
        <div class="info">
          {{-- <a href="#" class="d-block">{{ date('D, d-M-y') }}</a> --}}
          <a href="#" class="d-block">Login as : Sekretaris Huria</a>
        </div>
      </div>

      {{-- <!-- SidebarSearch Form -->
      <div class="form-inline">
        <div class="input-group" data-widget="sidebar-search">
          <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
          <div class="input-group-append">
            <button class="btn btn-sidebar">
              <i class="fas fa-search fa-fw"></i>
            </button>
          </div>
        </div>
      </div> --}}

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->

          <li class="nav-header" style="color:#ffffff">MENU</li>
          <li class="nav-item">
            <a href="{{ url('/dashboard') }}" class="nav-link" style="color:#ffffff">
                <i class="nav-icon fas fa-tachometer-alt"></i>
                <p>
                Dashboard
                </p>
            </a>
          </li>

          <!--  Menu  -->
          <li class="nav-item">
            <a href="#" class="nav-link" style="color:#ffffff">
              <i class="nav-icon far fa fa-user"></i>
              <p>
                Pengaturan User
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{ url('/pengaturanuser/pengguna') }}" id="submenuPengguna" class="nav-link" style="color:#ffffff">
                  <i class="far fas fa-minus nav-icon"></i>
                  <p>Pengguna</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('/pengaturanuser/gantipassword') }}" id="gantiPass" class="nav-link" style="color:#ffffff">
                  <i class="far fas fa-minus nav-icon"></i>
                  <p>Ganti Password</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('/pengaturanuser/resetpassword') }}" class="nav-link" style="color:#ffffff">
                  <i class="far fas fa-minus nav-icon"></i>
                  <p>Reset Password</p>
                </a>
              </li>
            </ul>
          </li>

          <li class="nav-item">
            <a href="#" class="nav-link" style="color:#ffffff">
              <i class="nav-icon fas fa-cog"></i>
              <p>
                Data Induk
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{ url('/datainduk/sintua') }}" class="nav-link">
                  <i class="far fas fa-minus nav-icon"></i>
                  <p>Sintua</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('/datainduk/sektor') }}" class="nav-link">
                  <i class="far fas fa-minus nav-icon"></i>
                  <p>Sektor</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('/datainduk/profesi') }}" class="nav-link">
                  <i class="far fas fa-minus nav-icon"></i>
                  <p>Profesi</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="far fas fa-minus nav-icon"></i>
                  <p>Pendidikan</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="far fas fa-minus nav-icon"></i>
                  <p>Hubungan Keluarga</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="far fas fa-minus nav-icon"></i>
                  <p>Hak Akses</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="far fas fa-minus nav-icon"></i>
                  <p>Peran</p>
                </a>
              </li>
            </ul>
          </li>

          <li class="nav-item">
            <a href="#" class="nav-link" style="color:#ffffff">
              <i class="nav-icon fas fa-users"></i>
              <p>
                Data Ruas
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{ url('/dataruas/datakeluarga') }}" class="nav-link">
                  {{-- <i class="far fa-circle nav-icon"></i> --}}
                  <i class="far fas fa-minus nav-icon"></i>
                  <p>Data Keluarga</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('/dataruas/pindahsektor') }}" class="nav-link">
                  <i class="far fas fa-minus nav-icon"></i>
                  <p>Pindah Sektor</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="far fas fa-minus nav-icon"></i>
                  <p>Pindah Huria</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="far fas fa-minus nav-icon"></i>
                  <p>Ruas Meninggal</p>
                </a>
              </li>
            </ul>
          </li>

          <li class="nav-item">
            <a href="{{ url('/persembahanbulanan') }}" class="nav-link" style="color:#ffffff">
                <i class="nav-icon fas fa-plus-square"></i>
                <p>
                Persembahan Bulanan
                </p>
            </a>
          </li>

          <li class="nav-item">
            <a href="#" class="nav-link" style="color: aliceblue">
                <i class="nav-icon fas fa-plus-square"></i>
                <p>
                Input Iuran DSH
                </p>
            </a>
          </li>

          <li class="nav-item">
            <a href="#" class="nav-link" style="color:#ffffff">
              <i class="nav-icon fas fa-file"></i>
              <p>
                Laporan
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{ url('/laporan/laporandatakeluarga') }}" class="nav-link">
                  <i class="far fas fa-minus nav-icon"></i>
                  <p>Data Keluarga</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{ url('/laporan/persembahanbulanan') }}" class="nav-link">
                  <i class="far fas fa-minus nav-icon"></i>
                  <p>Persembahan Bulanan</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="far fas fa-minus nav-icon"></i>
                  <p>Iuran DSH</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="far fas fa-minus nav-icon"></i>
                  <p>Ruas Tambah</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="far fas fa-minus nav-icon"></i>
                  <p>Ruas Meninggal</p>
                </a>
              </li>
            </ul>
          </li>

 

        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>




