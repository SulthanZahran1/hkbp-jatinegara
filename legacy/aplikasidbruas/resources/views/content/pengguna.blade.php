
@include('layouts.header')
@include('layouts.navbar')
@include('layouts.sidebar')
{{-- @include('content.loader') --}}

{{-- <div id="loader" class="center"></div> <!-- loading page --> --}}
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>TAMBAH PENGGUNA</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Pengaturan User / pengguna</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>
    
    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">

            <div class="card">
              <!-- /.card-header -->
              <div class="card-body">
                {{-- <div class="form-group">
                    <label>Pilih Sektor</label>
                    <select class="form-control select2" style="width: 30%;">
                      <option selected="selected">All</option>
                      <option>FILIPI</option>
                      <option>GALATIA</option>
                      <option>LUKAS</option>
                      <option>JUDIKA</option>
                      <option>MARKUS</option>
                      <option>PAULUS</option>
                    </select>
                </div>

                <div class="row" style="height: 50px;">
                    <div class="col-12">
                      <button type="button" class="btn btn-primary float-right">
                        + Tambah Pengguna
                      </button>
                    </div>
                  </div> --}}

                  

                  <div class="row" style="height: 70px;">
                    <div class="col-12">
                            <select class="form-control select2" style="width: 30%;">
                                <option selected="selected">All</option>
                                @foreach ($content['list_sektor'] as $item)
                                 <option>{{ $item['nama'] }}</option>
                                @endforeach
                            </select>
                     
                      <button type="button" data-toggle="modal" data-target="#tambah-pengguna" class="btn btn-primary float-right" style="margin-right:0px; margin-top:-40px">
                       + Tambah Pengguna
                      </button>
                    </div>
                  </div>

                  
                <table id="example1" class="table table-bordered table-hover">
                  <thead style="background-color:#f5f1f1">
                  <tr>
                    <th>NO</th>
                    <th>NAMA</th>
                    <th>USERNAME</th>
                    <th>ROLE</th>
                    <th>STATUS</th>
                    <th>LAST ACCESS </th>
                    <th>TINDAKAN</th>
                  </tr>
                  </thead>
                  <tbody>
                    <?php $i = 1; ?>
                    @foreach ($content['list_all_user'] as $item)
                    {{-- <option>{{ $item['nama'] }}</option> --}}
                    
                    <tr>
                      <td>{{ $i }}</td>
                      <td style="text-align: left;">{{ $item['nama_depan'] . " " . $item['nama_belakang'] }}</td>
                      <td style="text-align: left;">{{ $item['username'] }}</td>
                      <td style="text-align: left;">{{ $item['role_id'] }}</td>
                      <td style="text-align: left;">{{ $item['status'] }}</td>
                      <td style="text-align: left;">{{ $item['last_access'] }}</td>
                      <td>
                          <a class="btn btn-primary btn-sm" id ="view_{{ $i }}" href="#" onclick="viewDetailPengguna('{{ $i }}')">
                              <i class="fas fa-search">
                              </i>
                              View
                          </a>
                          <a class="btn btn-info btn-sm" id ="edit_{{ $i }}" href="#" data-toggle="modal" data-target="#edit-pengguna">
                              <i class="fas fa-pencil-alt">
                              </i>
                              Edit
                          </a>
                          <a class="btn btn-danger btn-sm" id ="delete_{{ $i }}" href="#" data-toggle="modal" data-target="#hapus-pengguna">
                              <i class="fas fa-trash">
                              </i>
                              Delete
                          </a>
                      </td>
                    </tr>
                    <?php $i++; ?>
                   @endforeach
                    
                  
          
                  </tbody>
                  {{-- <tfoot>
                  <tr>
                    <th>Rendering engine</th>
                    <th>Browser</th>
                    <th>Platform(s)</th>
                    <th>Engine version</th>
                    <th>CSS grade</th>
                  </tr>
                  </tfoot> --}}
                </table>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->

          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->
      </div>
      <!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <!-- MODAL UNTUK TAMBAH PENGGUNA -->
  <div class="modal fade" id="tambah-pengguna">
    <div class="modal-dialog modal-lg ">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Tambah Pengguna</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
               <!-- your steps content here -->
               <div id="#" class="content" role="tabpanel" aria-labelledby="logins-part-trigger">
                <div class="form-group">
                  <label>Cari Nama</label>
                  <select class="form-control select2" style="width: 100%;">
                    <option disabled="disabled" selected="selected">-- cari pengguna --</option>
                    <option>Nama</option>
                    <option>Nama</option>
                    <option>Nama</option>
                  </select>
                </div>
              </div>
              <div class="row">
                <div class="col-sm-6">
                  <!-- text input -->
                  <div class="form-group">
                    <label>Username</label>
                    <input type="text" class="form-control" placeholder="Enter ...">
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group">
                    <label>Role</label>
                    <select class="form-control select2" style="width: 100%;">
                      <option disabled="disabled" selected="selected">-pilih role-</option>
                      @foreach ($content['list_role'] as $item)
                        <option>{{ $item['role'] }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
              </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-dismiss="modal">Kembali</button>
          <button type="button" class="btn btn-info">Simpan</button>
        </div>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
  </div>
  <!-- END OF MODAL UNTUK TAMBAH PENGGUNA -->


    <!-- MODAL UNTUK VIEW -->
    <div class="modal fade" id="view-pengguna">
      <div class="modal-dialog modal-lg ">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">Detail Pengguna</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <table class="table table-striped">
              <tr>
                <td>Nama Lengkap</td>
                <th><input type="text" class="form-control" name="namalengkap" id="namalengkap" value="" disabled></th>
              </tr>
              <tr>
                <td>Username</td>
                <td><input type="text" class="form-control" name="username" id="username" value="" disabled></td>
              </tr>
              <tr>
                <td>Status</td>
                <td><input type="text" class="form-control" name="status" id="status" value="" disabled></td>
              </tr>
              <tr>
                <td>Tgl Dibuat</td>
                <td><input type="text" class="form-control" name="tgldibuat" id="tgldibuat" value="" disabled></td>
              </tr>
              <tr>
                <td>Tgl Terakhir Login</td>
                <td><input type="text" class="form-control" name="terakhirlogin" id="terakhirlogin" value="" disabled></td>
              </tr>
              <tr>
                <td>Role</td>
                <td><input type="text" class="form-control" name="role" id="role" value="" disabled></td>
              </tr>
              {{-- <tr>
                <td>Privilege Menu</td>
                <td><input type="text" class="form-control" name="privilegemenu" id="privilegemenu" value="" disabled></td>
              </tr>
              <tr>
                <td>Privilege Tindakan</td>
                <td><input type="text" class="form-control" name="privilegetindakan" id="privilegetindakan" value="" disabled></td>
              </tr> --}}
            </table>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-dismiss="modal">Kembali</button>
            {{-- <button type="button" class="btn btn-info">Simpan</button> --}}
          </div>
        </div>
        <!-- /.modal-content -->
      </div>
      <!-- /.modal-dialog -->
    </div>
    <!-- END OF MODAL VIEW -->

        <!-- MODAL UNTUK EDIT -->
        <div class="modal fade" id="edit-pengguna">
          <div class="modal-dialog modal-dialog-scrollable modal-lg ">
            <div class="modal-content">
              <div class="modal-header">
                <h4 class="modal-title">Edit Pengguna</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <table class="table table-striped">
                  <tr>
                    <th>NO</th>
                    <th>NAMA MENU</th>
                    <th>SUB MENU</th>
                    <th>TINDAKAN</th>
                  </tr>
                  <tr>
                    <td>1</td>
                    <td>Pengaturan User</td>
                    <td>Pengguna</td>
                    <td style="text-align: left;">
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox1" value="">
                        <label for="customCheckbox1" class="custom-control-label">Insert</label>
                      </div>
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox2" value="">
                        <label for="customCheckbox2" class="custom-control-label">View</label>
                      </div>
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox3" value="">
                        <label for="customCheckbox3" class="custom-control-label">Edit</label>
                      </div>
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox4" value="">
                        <label for="customCheckbox4" class="custom-control-label">Delete</label>
                      </div>
                      {{-- <input type="checkbox" id="vehicle1" name="vehicle1" value="Bike"><label for="">Insert </label>
                      <input type="checkbox" id="vehicle1" name="vehicle1" value="Bike"><label for="">View </label>
                      <input type="checkbox" id="vehicle1" name="vehicle1" value="Bike"><label for="">Edit </label>
                      <input type="checkbox" id="vehicle1" name="vehicle1" value="Bike"><label for="">Delete </label> --}}
                    </td>
                  </tr>
                  <tr>
                    <td>2</td>
                    <td>Pengaturan User</td>
                    <td>Ganti Password</td>
                    <td style="text-align: left;">
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox5" value="">
                        <label for="customCheckbox5" class="custom-control-label">View</label>
                      </div>
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox6" value="">
                        <label for="customCheckbox6" class="custom-control-label">Edit</label>
                      </div>
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox7" value="">
                        <label for="customCheckbox7" class="custom-control-label">Delete</label>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td>3</td>
                    <td>Pengaturan User</td>
                    <td>Reset Password</td>
                    <td style="text-align: left;">
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox8" value="">
                        <label for="customCheckbox8" class="custom-control-label">View</label>
                      </div>
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox9" value="">
                        <label for="customCheckbox9" class="custom-control-label">Edit</label>
                      </div>
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox10" value="">
                        <label for="customCheckbox10" class="custom-control-label">Delete</label>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td>4</td>
                    <td>Master Data</td>
                    <td>Data Sektor</td>
                    <td style="text-align: left;">
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox11" value="">
                        <label for="customCheckbox11" class="custom-control-label">View</label>
                      </div>
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox12" value="">
                        <label for="customCheckbox12" class="custom-control-label">Edit</label>
                      </div>
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox13" value="">
                        <label for="customCheckbox13" class="custom-control-label">Delete</label>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td>5</td>
                    <td>Master Data</td>
                    <td>Data Sintua</td>
                    <td style="text-align: left;">
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox14" value="">
                        <label for="customCheckbox14" class="custom-control-label">View</label>
                      </div>
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox15" value="">
                        <label for="customCheckbox15" class="custom-control-label">Edit</label>
                      </div>
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox16" value="">
                        <label for="customCheckbox16" class="custom-control-label">Delete</label>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td>6</td>
                    <td>Master Data</td>
                    <td>Data Profesi</td>
                    <td style="text-align: left;">
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox17" value="">
                        <label for="customCheckbox17" class="custom-control-label">View</label>
                      </div>
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox18" value="">
                        <label for="customCheckbox18" class="custom-control-label">Edit</label>
                      </div>
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox19" value="">
                        <label for="customCheckbox19" class="custom-control-label">Delete</label>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td>7</td>
                    <td>Master Data</td>
                    <td>Data Pendidikan</td>
                    <td style="text-align: left;">
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox20" value="">
                        <label for="customCheckbox20" class="custom-control-label">View</label>
                      </div>
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox21" value="">
                        <label for="customCheckbox21" class="custom-control-label">Edit</label>
                      </div>
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox22" value="">
                        <label for="customCheckbox22" class="custom-control-label">Delete</label>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td>8</td>
                    <td>Master Data</td>
                    <td>Data Jenis Registrasi</td>
                    <td style="text-align: left;">
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox23" value="">
                        <label for="customCheckbox23" class="custom-control-label">View</label>
                      </div>
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox24" value="">
                        <label for="customCheckbox24" class="custom-control-label">Edit</label>
                      </div>
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox25" value="">
                        <label for="customCheckbox25" class="custom-control-label">Delete</label>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td>9</td>
                    <td>Master Data</td>
                    <td>Data Golongan Darah</td>
                    <td style="text-align: left;">
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox26" value="">
                        <label for="customCheckbox26" class="custom-control-label">View</label>
                      </div>
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox27" value="">
                        <label for="customCheckbox27" class="custom-control-label">Edit</label>
                      </div>
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox28" value="">
                        <label for="customCheckbox28" class="custom-control-label">Delete</label>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td>10</td>
                    <td>Data Ruas</td>
                    <td>Data Keluarga</td>
                    <td style="text-align: left;">
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox29" value="">
                        <label for="customCheckbox29" class="custom-control-label">View</label>
                      </div>
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox30" value="">
                        <label for="customCheckbox30" class="custom-control-label">Edit</label>
                      </div>
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox31" value="">
                        <label for="customCheckbox31" class="custom-control-label">Delete</label>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td>11</td>
                    <td>Data Ruas</td>
                    <td>Perpindahan Sektor</td>
                    <td style="text-align: left;">
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox32" value="">
                        <label for="customCheckbox32" class="custom-control-label">View</label>
                      </div>
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox33" value="">
                        <label for="customCheckbox33" class="custom-control-label">Edit</label>
                      </div>
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox34" value="">
                        <label for="customCheckbox34" class="custom-control-label">Delete</label>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td>12</td>
                    <td>Data Ruas</td>
                    <td>Perpindahan Huria</td>
                    <td style="text-align: left;">
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox35" value="">
                        <label for="customCheckbox35" class="custom-control-label">View</label>
                      </div>
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox36" value="">
                        <label for="customCheckbox36" class="custom-control-label">Edit</label>
                      </div>
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox37" value="">
                        <label for="customCheckbox37" class="custom-control-label">Delete</label>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td>13</td>
                    <td>Data Ruas</td>
                    <td>Ruas Meninggal</td>
                    <td style="text-align: left;">
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox38" value="">
                        <label for="customCheckbox38" class="custom-control-label">View</label>
                      </div>
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox39" value="">
                        <label for="customCheckbox39" class="custom-control-label">Edit</label>
                      </div>
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox40" value="">
                        <label for="customCheckbox40" class="custom-control-label">Delete</label>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td>14</td>
                    <td>Persembahan Bulanan</td>
                    <td>-</td>
                    <td style="text-align: left;">
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox41" value="">
                        <label for="customCheckbox41" class="custom-control-label">View</label>
                      </div>
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox42" value="">
                        <label for="customCheckbox42" class="custom-control-label">Edit</label>
                      </div>
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox43" value="">
                        <label for="customCheckbox43" class="custom-control-label">Delete</label>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td>15</td>
                    <td>Input Iuran DSH</td>
                    <td>-</td>
                    <td style="text-align: left;">
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox44" value="">
                        <label for="customCheckbox44" class="custom-control-label">View</label>
                      </div>
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox45" value="">
                        <label for="customCheckbox45" class="custom-control-label">Edit</label>
                      </div>
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox46" value="">
                        <label for="customCheckbox46" class="custom-control-label">Delete</label>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td>16</td>
                    <td>Laporan</td>
                    <td>Data Keluarga</td>
                    <td style="text-align: left;">
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox47" value="">
                        <label for="customCheckbox47" class="custom-control-label">View</label>
                      </div>
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox48" value="">
                        <label for="customCheckbox48" class="custom-control-label">Edit</label>
                      </div>
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox49" value="">
                        <label for="customCheckbox49" class="custom-control-label">Delete</label>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td>17</td>
                    <td>Laporan</td>
                    <td>Persembahan Bulanan</td>
                    <td style="text-align: left;">
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox50" value="">
                        <label for="customCheckbox50" class="custom-control-label">View</label>
                      </div>
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox51" value="">
                        <label for="customCheckbox51" class="custom-control-label">Edit</label>
                      </div>
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox52" value="">
                        <label for="customCheckbox52" class="custom-control-label">Delete</label>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td>18</td>
                    <td>Laporan</td>
                    <td>Iuran DSH</td>
                    <td style="text-align: left;">
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox53" value="">
                        <label for="customCheckbox53" class="custom-control-label">View</label>
                      </div>
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox54" value="">
                        <label for="customCheckbox54" class="custom-control-label">Edit</label>
                      </div>
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox55" value="">
                        <label for="customCheckbox55" class="custom-control-label">Delete</label>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td>19</td>
                    <td>Laporan</td>
                    <td>Ruas Tambah</td>
                    <td style="text-align: left;">
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox56" value="">
                        <label for="customCheckbox56" class="custom-control-label">View</label>
                      </div>
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox57" value="">
                        <label for="customCheckbox57" class="custom-control-label">Edit</label>
                      </div>
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox58" value="">
                        <label for="customCheckbox58" class="custom-control-label">Delete</label>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td>20</td>
                    <td>Laporan</td>
                    <td>Ruas Meninggal</td>
                    <td style="text-align: left;">
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox59" value="">
                        <label for="customCheckbox59" class="custom-control-label">View</label>
                      </div>
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox60" value="">
                        <label for="customCheckbox60" class="custom-control-label">Edit</label>
                      </div>
                      <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="customCheckbox61" value="">
                        <label for="customCheckbox61" class="custom-control-label">Delete</label>
                      </div>
                    </td>
                  </tr>
                </table>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Kembali</button>
                <button type="button" class="btn btn-info">Simpan</button>
              </div>
            </div>
            <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
        </div>
        <!-- END OF MODAL EDIT -->

            <!-- MODAL UNTUK DELETE -->
            <div class="modal fade" id="hapus-pengguna">
              <div class="modal-dialog modal-lg ">
                <div class="modal-content">
                  <div class="modal-header">
                    <h4 class="modal-title">Hapus Pengguna</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                  <div class="modal-body">
                    <table class="table table-striped">
                      <tr>
                        <td>Nama Lengkap</td>
                        <th>text</th>
                      </tr>
                      <tr>
                        <td>Username</td>
                        <td>text</td>
                      </tr>
                      <tr>
                        <td>Role</td>
                        <td>text</td>
                      </tr>
                      <tr>
                        <td>Status</td>
                        <td>text</td>
                      </tr>            
                    </table>
                  
                    <span align="center">
                      <h5>Klik tombol hapus untuk menghapus pengguna ini !</h5>
                    </span>
                  </div>
                 
                  <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Kembali</button>
                    <button type="button" class="btn btn-info">Hapus Pengguna</button>
                  </div>
                </div>
                <!-- /.modal-content -->
              </div>
              <!-- /.modal-dialog -->
            </div>
            <!-- END OF MODAL DELETE -->

@include('layouts.footer')

<script type="text/javascript"> 

  // document.onreadystatechange = function () {
  //   if (document.readyState !== "complete") {
  //     document.querySelector(
  //       "body").style.visibility = "hidden";
  //     document.querySelector(
  //       "#loader").style.visibility = "visible";
  //   } else {
  //     document.querySelector(
  //       "#loader").style.display = "none";
  //     document.querySelector(
  //       "body").style.visibility = "visible";
  //   }
  // };

  // onload = function() {
  //       document.querySelector(
	// 				"body").style.visibility = "hidden";
	// 			document.querySelector(
	// 				"#loader").style.visibility = "visible";
        
  //         setTimeout(() => {
  //           document.querySelector(
  //             "#loader").style.display = "none";
  //           document.querySelector(
  //             "body").style.visibility = "visible";
  //        }, 3000);
  //     }



  function viewDetailPengguna(row_id){
    //console.log(1);
    var id = parseInt(row_id-1);
    var data = @json($content['list_all_user']);
    //let username = b[1].username;
    $("#namalengkap").val( data[id].username );
    $("#username").val(data[id].nama_depan + " " + data[id].nama_belakang);
    $("#status").val(data[id].status);
    $("#tgldibuat").val(data[id].created_date);
    $("#terakhirlogin").val(data[id].last_access);
    $("#role").val(data[id].role_id);
    $('#view-pengguna').modal('show');
  }

</script>

<script>
    $(function () {
      $("#example1").DataTable({
        "responsive": true, 
        "lengthChange": false, 
        "autoWidth": false,
        "dom": 'frtipB', //hilangkan untuk mengembalikan posisi diatas
        // "buttons": ["excel", "pdf"],
        "buttons":[
          { extend: "pdf", text: 'Unduh PDF', className: "btn btn-info"},
          { extend: "excel",text: 'Unduh Excel', className: "btn btn-info"}
        ],
        "autoWidth": false,
        "columnDefs": [
            {"className": "dt-center", "targets": "_all"}
        ],
        "language": {
          "search": "Filter :"
        }
      }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');


      $('#example3').DataTable({
        "paging": true,
        "lengthChange": false,
        "searching": false,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
      });

      

        //Initialize Select2 Elements
        $('.select2').select2()

        //Initialize Select2 Elements
        $('.select2bs4').select2({
        theme: 'bootstrap4'
        })
    });


  </script>









