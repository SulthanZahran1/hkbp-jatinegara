
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
            <h1>LAPORAN DATA KELUARGA</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Laporan / Data Keluarga</li>
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

                  <div class="row" style="height: 70px;">
                    <div class="col-12">
                            <label for="selectsektor">Pilih Sektor</label>
                            <select class="form-control select2" style="width: 30%;">
                                <option selected="selected">All</option>
                                <option>Judika</option>
                                <option>Galatia</option>
                                {{-- @foreach ($content['list_sektor'] as $item)
                                 <option>{{ $item['nama'] }}</option>
                                @endforeach --}}
                            </select>
                     
                    </div>
                  </div>

                <br>
                <table id="example1" class="table table-bordered table-hover">
                  <thead style="background-color:#f5f1f1">
                  <tr>
                    <th>NO</th>
                    <th>SEKTOR</th>
                    <th>NO REGISTRASI</th>
                    <th>NAMA KELUARGA</th>
                    <th>ANGGOTA KELUARGA</th>
                    <th>JUMLAH</th>
                    <th>UNDUH</th>
                  </tr>
                  </thead>
                  <tbody>
                    <?php $i = 1; ?>
                    @for ($a = 1; $a < 20; $a++)
                    {{-- @foreach ($content['list_all_user'] as $item) --}}
                    {{-- <option>{{ $item['nama'] }}</option> --}}
                    
                    <tr>
                      <td>{{ $i }}</td>
                      <td style="text-align: left;">SEKTOR</td>
                      <td style="text-align: left;">ABC001</td>
                      <td style="text-align: left;">KELUARGA XXXXXXX</td>
                      <td style="text-align: left;">
                        <dl>
                          <dt><a href="" data-toggle="modal" data-target="#lap-data-keluarga">Nama Anggota dan Marga 1</a></dt>
                          <dt><a href="" data-toggle="modal" data-target="#lap-data-keluarga">Nama Anggota dan Marga 2</a></dt>
                          <dt><a href="" data-toggle="modal" data-target="#lap-data-keluarga">Nama Anggota dan Marga 3</a></dt>
                        </dl>
                      </td>
                      <td style="text-align: left;">3</td>
                      <td>
                          {{-- <a class="btn btn-primary btn-sm" id ="view_{{ $i }}" href="#" data-toggle="modal" data-target="#view-pindah-sektor">
                            <i class="fas fa-search">
                            </i>
                            View
                         </a> --}}
                          <a class="btn btn-info btn-sm" id ="edit_{{ $i }}" href="#" data-toggle="modal" data-target="#edit-pindah-sektor">
                              <i class="fas fa-download">
                                
                              </i>
                              Unduh Data Keluarga
                          </a>
                      </td>
                    </tr>
                    <?php $i++; ?>
                    {{-- @endfor --}}
                    @endfor
                    
                  
          
                  </tbody>

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

  
        <!-- MODAL UNTUK DETAIL PRIBADI -->
        <div class="modal fade" id="lap-data-keluarga">
            <div class="modal-dialog modal-xl ">
            <div class="modal-content">
                <div class="modal-header">
                <h4 class="modal-title">Detail Data Pribadi</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                </div>
                <div class="modal-body">
                    <section class="content">
                        <div class="container-fluid">
                          <div class="row">
                            <div class="col-md-3">
                  
                              <!-- Profile Image -->
                              <div class="card card-primary card-outline">
                                <div class="card-body box-profile">
                                  <div class="text-center">
                                    <img class="profile-user-img img-fluid img-circle" src="{{asset('adminlte')}}/dist/img/user2-160x160.jpg" alt="User profile picture">
                                  </div>
                  
                                  <h3 class="profile-username text-center">Togar Sianipar</h3>
                  
                                  <p class="text-muted text-center">Umur 50 Tahun</p>
                  
                                  <ul class="list-group list-group-unbordered mb-3">
                                    <li class="list-group-item">
                                      <b>Pekerjaan</b> <a class="float-right">Wirausaha</a>
                                    </li>
                                    <li class="list-group-item">
                                      <b>Pendidikan</b> <a class="float-right">S1</a>
                                    </li>
                                    <li class="list-group-item">
                                      <b>Hobi</b> <a class="float-right">Olahraga</a>
                                    </li>
                                  </ul>
                  
                                </div>
                                <!-- /.card-body -->
                              </div>
                              <!-- /.card -->
                  
                              {{-- <!-- About Me Box -->
                              <div class="card card-primary">
                                <div class="card-header">
                                  <h3 class="card-title">About Me</h3>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body">
                                  <strong><i class="fas fa-book mr-1"></i> Education</strong>
                  
                                  <p class="text-muted">
                                    B.S. in Computer Science from the University of Tennessee at Knoxville
                                  </p>
                  
                                  <hr>
                  
                                  <strong><i class="fas fa-map-marker-alt mr-1"></i> Location</strong>
                  
                                  <p class="text-muted">Malibu, California</p>
                  
                                  <hr>
                  
                                  <strong><i class="fas fa-pencil-alt mr-1"></i> Skills</strong>
                  
                                  <p class="text-muted">
                                    <span class="tag tag-danger">UI Design</span>
                                    <span class="tag tag-success">Coding</span>
                                    <span class="tag tag-info">Javascript</span>
                                    <span class="tag tag-warning">PHP</span>
                                    <span class="tag tag-primary">Node.js</span>
                                  </p>
                  
                                  <hr>
                  
                                  <strong><i class="far fa-file-alt mr-1"></i> Notes</strong>
                  
                                  <p class="text-muted">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam fermentum enim neque.</p>
                                </div>
                                <!-- /.card-body -->
                              </div> --}}
                              <!-- /.card -->
                            </div>
                            <!-- /.col -->
                            <div class="col-md-9">
                              <div class="card card-primary card-outline">
                                {{-- <div class="card-header p-2">
                                  <ul class="nav nav-pills">
                                    <li class="nav-item"><a class="nav-link active" href="#activity" data-toggle="tab">Activity</a></li>
                                    <li class="nav-item"><a class="nav-link" href="#timeline" data-toggle="tab">Timeline</a></li>
                                    <li class="nav-item"><a class="nav-link active" href="#settings" data-toggle="tab">Settings</a></li>
                                  </ul>
                                </div><!-- /.card-header --> --}}
                                <div class="card-body">
                                  <div class="tab-content">
                                    
                                    <div class="" id="settings">
                                      <form class="form-horizontal">
                                        <div class="form-group row">
                                          <label for="inputName" class="col-sm-3 col-form-label">Nama Lengkap</label>
                                          <div class="col-sm-9">
                                            <div class="form-control">Nama Lengkap dan Marga</div>
                                          </div>
                                        </div>
                                        <div class="form-group row">
                                          <label for="inputEmail" class="col-sm-3 col-form-label">Gender</label>
                                          <div class="col-sm-9">
                                            <div class="form-control">Pria / Wanita</div>
                                          </div>
                                        </div>
                                        <div class="form-group row">
                                          <label for="inputName2" class="col-sm-3 col-form-label">Tempat Lahir</label>
                                          <div class="col-sm-9">
                                            <div class="form-control">Daerah tempat lahir</div>
                                          </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputName2" class="col-sm-3 col-form-label">Tanggal Lahir</label>
                                            <div class="col-sm-9">
                                              <div class="form-control">DD-MM-YYYY</div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputName2" class="col-sm-3 col-form-label">Golongan Darah</label>
                                            <div class="col-sm-9">
                                                <div class="form-control">Gol. Darah</div>
                                            </div>
                                        </div>
                                       
                                        <div class="form-group row">
                                            <label for="inputName2" class="col-sm-3 col-form-label">No Handphone</label>
                                            <div class="col-sm-9">
                                              <div class="form-control">0812XXXXXXXXX</div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputName2" class="col-sm-3 col-form-label">Sektor</label>
                                            <div class="col-sm-9">
                                                <div class="form-control">Nama Sektor</div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputName2" class="col-sm-3 col-form-label">Alamat</label>
                                            <div class="col-sm-9">
                                              <div class="form-control">Alamat Lengkap</div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputName2" class="col-sm-3 col-form-label">Kelurahan</label>
                                            <div class="col-sm-9">
                                                <div class="form-control">Nama Kelurahan</div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputName2" class="col-sm-3 col-form-label">Kecamatan</label>
                                            <div class="col-sm-9">
                                              <div class="form-control">Nama Kecamatan</div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputName2" class="col-sm-3 col-form-label">Kota</label>
                                            <div class="col-sm-9">
                                                <div class="form-control">Nama Kota</div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputName2" class="col-sm-3 col-form-label">Propinsi</label>
                                            <div class="col-sm-9">
                                                <div class="form-control">Nama Propinsi</div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputName2" class="col-sm-3 col-form-label">Tanggal Baptis</label>
                                            <div class="col-sm-9">
                                                <div class="form-control">DD-MM-YYYY</div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputName2" class="col-sm-3 col-form-label">Gereja Tempat Baptis</label>
                                            <div class="col-sm-9">
                                                <div class="form-control">Nama Gereja</div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputName2" class="col-sm-3 col-form-label">Pendeta Pelayan Baptis</label>
                                            <div class="col-sm-9">
                                                <div class="form-control">Pdt. Nama Pendeta</div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputName2" class="col-sm-3 col-form-label">Tanggal Sidi</label>
                                            <div class="col-sm-9">
                                                <div class="form-control">DD-MM-YYYY</div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputName2" class="col-sm-3 col-form-label">Gereja Tempat Sidi</label>
                                            <div class="col-sm-9">
                                                <div class="form-control">Nama Gereja</div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputName2" class="col-sm-3 col-form-label">Pendeta Pelayan Sidi</label>
                                            <div class="col-sm-9">
                                                <div class="form-control">Pdt. Nama Pendeta</div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputName2" class="col-sm-3 col-form-label">Tanggal Perkawinan</label>
                                            <div class="col-sm-9">
                                                <div class="form-control">DD-MM-YYYY</div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputName2" class="col-sm-3 col-form-label">Gereja Tempat Perkawinan</label>
                                            <div class="col-sm-9">
                                                <div class="form-control">Nama Gereja</div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label for="inputName2" class="col-sm-3 col-form-label">Pendeta Pelayan Perkawinan</label>
                                            <div class="col-sm-9">
                                                <div class="form-control">Pdt. Nama Pendeta</div>
                                            </div>
                                        </div>
                     
                  
                                        <div class="form-group row">
                  
                                        </div>
                                        <div class="form-group row">
                       
                                        </div>
                                      </form>
                                    </div>
                                    <!-- /.tab-pane -->
                                  </div>
                                  <!-- /.tab-content -->
                                </div><!-- /.card-body -->
                              </div>
                              <!-- /.card -->
                            </div>
                            <!-- /.col -->
                          </div>
                          <!-- /.row -->
                        </div><!-- /.container-fluid -->
                      </section>
                </div>
                <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Kembali</button>
                <button type="button" class="btn btn-info">
                    <i class="fas fa-download"></i> Unduh Data Pribadi</button>
                </div>
            </div>
            <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
        <!-- END OF MODAL DETAIL PRIBADI -->

        <!-- MODAL UNTUK EDIT PINDAH SEKTOR -->
        <div class="modal fade" id="edit-pindah-sektor">
          <div class="modal-dialog modal-dialog-scrollable modal-lg ">
            <div class="modal-content">
              <div class="modal-header">
                <h4 class="modal-title">Pindah Sektor</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <table class="table table-striped">
                    <tr>
                      <td>Keluarga</td>
                      <th>Kel. XXXXX XXXXX</th>
                    </tr>
                    <tr>
                      <td>Sektor</td>
                      <td>Sektor</td>
                    </tr>
                    <tr>
                      <td>No Registrasi</td>
                      <td>ABC001</td>
                    </tr>
                    <tr>
                      <td>Pindah Sektor Ke</td>
                      <td>
                        <select class="form-control select2">
                            <option selected="selected" disabled>-- pilih sektor --</option>
                            <option>Judika</option>
                            <option>Galatia</option>
                        </select>
                      </td>
                    </tr>
                    <tr>
                        <td>Tanggal Warta Jemaat</td>
                        <td>
                            <div class="input-group date" data-target-input="nearest">
                                <input type="text" id="tglwarta" class="form-control datetimepicker-input" data-target="#tglwarta"/>
                                <div class="input-group-append" data-target="#tglwarta" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>
                            </div>
                        </td>
                      </tr>
                  </table>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-info">Konfirmasi</button>
              </div>
            </div>
            <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
        </div>
        <!-- END OF MODAL EDIT -->


@include('layouts.footer')

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









