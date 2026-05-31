
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
            <h1>PINDAH SEKTOR</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Data Ruas / Pindah Sektor</li>
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
                    <th>TINDAKAN</th>
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
                          <dt><a href="">Nama Anggota dan Marga 1</a></dt>
                          <dt><a href="">Nama Anggota dan Marga 2</a></dt>
                          <dt><a href="">Nama Anggota dan Marga 3</a></dt>
                        </dl>
                      </td>
                      <td style="text-align: left;">3</td>
                      <td>
                          <a class="btn btn-primary btn-sm" id ="view_{{ $i }}" href="#" data-toggle="modal" data-target="#view-pindah-sektor">
                            <i class="fas fa-search">
                            </i>
                            View
                         </a>
                          <a class="btn btn-info btn-sm" id ="edit_{{ $i }}" href="#" data-toggle="modal" data-target="#edit-pindah-sektor">
                              <i class="fas fa-pencil-alt">
                              </i>
                              Edit
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

  
        <!-- MODAL UNTUK VIEW -->
        <div class="modal fade" id="view-pindah-sektor">
            <div class="modal-dialog modal-lg ">
            <div class="modal-content">
                <div class="modal-header">
                <h4 class="modal-title">Detail Keluarga</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                </div>
                <div class="modal-body">
                <table class="table table-striped">
                    <tr>
                    <td>Nama Keluarga</td>
                    <th><div class="form-control">Kel. XXXXX XXXXXXX</div></th>
                    </tr>
                    <tr>
                        <td>Sektor</td>
                        <td><div class="form-control">Sektor</div></td>
                    </tr>
                    <tr>
                        <td>NO REGISTRASI</td>
                        <td><div class="form-control">ABC001</div></td>
                    </tr>
                    <tr>
                        <td>ANGGOTA KELUARGA 1</td>
                        <td><div class="form-control">Nama Anggota Keluarga 1</div></td>
                    </tr>
                    <tr>
                        <td>ANGGOTA KELUARGA 2</td>
                        <td><div class="form-control">Nama Anggota Keluarga 2</div></td>
                    </tr>
                    <tr>
                        <td>ANGGOTA KELUARGA 3</td>
                        <td><div class="form-control">Nama Anggota Keluarga 3</div></td>
                    </tr>
                    <tr>
                        <td>JUMLAH</td>
                        <td><div class="form-control">3</div></td>
                    </tr>
                    <tr>
    
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









