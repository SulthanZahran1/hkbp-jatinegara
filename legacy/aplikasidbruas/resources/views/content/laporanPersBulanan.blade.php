
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
            <h1>LAPORAN PERSEMBAHAN BULANAN</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Laporan / Persembahan Bulanan</li>
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
                    {{-- <div class="col-12">
            
                        <div class="card-body">
                          <div class="form-group row">
                            <label for="inputEmail3" class="col-sm-2 col-form-label">Tahun</label>
                            <div class="col-sm-10">
                              <input type="email" class="form-control" id="inputEmail3" placeholder="Email">
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="inputPassword3" class="col-sm-2 col-form-label">Sektor</label>
                            <div class="col-sm-10">
                              <input type="password" class="form-control" id="inputPassword3" placeholder="Password">
                            </div>
                          </div>
                          <div class="form-group row">
                            <button type="submit" class="btn btn-info float-right">Sign in</button>
                          </div>
                        </div>
                        <!-- /.card-body -->
                        <div class="card-footer">
                          <button type="submit" class="btn btn-info float-right">Sign in</button>
                        </div>
                        <!-- /.card-footer -->
                      
                    </div> --}}
                    
                        <div class="card-body col-sm-8">
                            <div class="form-group row">
                                <label for="inputEmail3" class="col-sm-2 col-form-label">Tahun</label>
                                <div class="col-sm-6">
                                    <select class="form-control select2">
                                        <option disabled="disabled" selected="selected">-pilih tahun-</option>
                                        <option>2022</option>
                                        <option>2023</option>
                                        <option>2024</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="inputPassword3" class="col-sm-2 col-form-label">Sektor</label>
                                <div class="col-sm-6">
                                    <select class="form-control select2">
                                        <option disabled="disabled" selected="selected">-pilih sektor-</option>
                                        <option>GALATIA</option>
                                        <option>FILIPI</option>
                                        <option>PAULUS</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="inputPassword3" class="col-sm-2 col-form-label">Nama Keluarga</label>
                                <div class="col-sm-6">
                                    <select class="form-control select2">
                                        <option disabled="disabled" selected="selected">-pilih nama Keluarga-</option>
                                        <option>KEL. ABCD XXXXX</option>
                                        <option>KEL. EFGH XXXXX</option>
                                        <option>KEL. IJKL XXXXX</option>
                                    </select>
                                </div>
                                
                            </div>
    
                            <div class="form-group col-sm-8">
                            <button type="submit" id="submitPersBl" class="btn btn-primary float-right">Cari</button>
                            </div>
                            
                        </div>
                    
                  </div>
                  
                <br><br><br><br><br><br><br><br>
                <table id="example1" class="table table-bordered table-hover">
                  <thead style="background-color:#f5f1f1">
                  <tr>
                    <th>NO</th>
                    <th>TGL WJ</th>
                    <th>JAN</th>
                    <th>FEB</th>
                    <th>MAR</th>
                    <th>APR</th>
                    <th>MEI</th>
                    <th>JUN</th>
                    <th>JUL</th>
                    <th>AGU</th>
                    <th>SEP</th>
                    <th>OKT</th>
                    <th>NOV</th>
                    <th>DES</th>
                    <th>TOTAL</th>
                  </tr>
                  </thead>
                  <tbody>
                    <?php $i = 1; ?>
                    @for ($a = 1; $a < 20; $a++)
                    {{-- @foreach ($content['list_all_user'] as $item) --}}
                    {{-- <option>{{ $item['nama'] }}</option> --}}
                    
                    <tr>
                      <td>{{ $i }}</td>
                      <td style="text-align: left;">DD-MM-YY</td>
                      <td style="text-align: left;">100.000</td>
                      <td style="text-align: left;">100.000</td>
                      <td style="text-align: left;">100.000</td>
                      <td style="text-align: left;">100.000</td>
                      <td style="text-align: left;">100.000</td>
                      <td style="text-align: left;">100.000</td>
                      <td style="text-align: left;">100.000</td>
                      <td style="text-align: left;">100.000</td>
                      <td style="text-align: left;">100.000</td>
                      <td style="text-align: left;">100.000</td>
                      <td style="text-align: left;">100.000</td>
                      <td style="text-align: left;">100.000</td>
                      <td style="text-align: left;">1.200.000</td>
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

  <!-- MODAL UNTUK TAMBAH SINTUA -->
  <div class="modal fade" id="tambah-sintua">
    <div class="modal-dialog modal-lg ">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Tambah Sintua</h4>
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
                    <div class="form-group">
                      <label>Role</label>
                      <select class="form-control select2" style="width: 100%;">
                        <option disabled="disabled" selected="selected">-pilih role-</option>
                        {{-- @foreach ($content['list_role'] as $item)
                          <option>{{ $item['role'] }}</option>
                        @endforeach --}}
                      </select>
                    </div>
                  </div>
                <div class="col-sm-6">
                  <!-- text input -->
                  <div class="form-group">
                    <label>No Hp</label>
                    <input type="text" class="form-control" placeholder="Enter ...">
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


        <!-- MODAL UNTUK EDIT -->
        <div class="modal fade" id="edit-sintua">
          <div class="modal-dialog modal-dialog-scrollable modal-lg ">
            <div class="modal-content">
              <div class="modal-header">
                <h4 class="modal-title">Edit Sintua</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <table class="table table-striped">
                    <tr>
                      <td>Nama Sintua</td>
                      <th><input type="text" class="form-control" name="namalengkap" id="namalengkap" value="" disabled></th>
                    </tr>
                    <tr>
                      <td>Kode Sektor</td>
                      <td><input type="text" class="form-control" name="username" id="username" value="" disabled></td>
                    </tr>
                    <tr>
                      <td>Nama Sektor</td>
                      <td><input type="text" class="form-control" name="status" id="status" value="" disabled></td>
                    </tr>
                    <tr>
                      <td>No Hp Sintua</td>
                      <td><input type="text" class="form-control" name="tgldibuat" id="tgldibuat" value="" disabled></td>
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
            <div class="modal fade" id="hapus-sintua">
              <div class="modal-dialog modal-lg ">
                <div class="modal-content">
                  <div class="modal-header">
                    <h4 class="modal-title">Hapus Data Sintua</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                  <div class="modal-body">
                    <table class="table table-striped">
                      <tr>
                        <td>Nama Sintua</td>
                        <th>text</th>
                      </tr>
                      <tr>
                        <td>Role</td>
                        <td>text</td>
                      </tr>
                      <tr>
                        <td>No Hp</td>
                        <td>text</td>
                      </tr>            
                    </table>
                  
                    <span align="center">
                      <h5>Klik tombol hapus untuk menghapus data sintua ini !</h5>
                    </span>
                  </div>
                 
                  <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Kembali</button>
                    <button type="button" class="btn btn-info">Hapus Data</button>
                  </div>
                </div>
                <!-- /.modal-content -->
              </div>
              <!-- /.modal-dialog -->
            </div>
            <!-- END OF MODAL DELETE -->

@include('layouts.footer')

<script>
    $(function () {
      $("#example1").DataTable({
        "searching": false,
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









