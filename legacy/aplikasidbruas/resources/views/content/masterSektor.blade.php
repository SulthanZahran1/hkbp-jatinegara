
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
            <h1>SEKTOR</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Data Induk / Sektor</li>
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
                                {{-- @foreach ($content['list_sektor'] as $item)
                                 <option>{{ $item['nama'] }}</option>
                                @endforeach --}}
                            </select>
                     
                      <button type="button" data-toggle="modal" data-target="#tambah-sektor" class="btn btn-primary float-right" style="margin-right:0px; margin-top:-40px">
                       + Tambah Sektor
                      </button>
                    </div>
                  </div>

                <br>
                <table id="example1" class="table table-bordered table-hover">
                  <thead style="background-color:#f5f1f1">
                  <tr>
                    <th>NO</th>
                    <th>KODE SEKTOR</th>
                    <th>NAMA SEKTOR</th>
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
                      <td style="text-align: left;">ABCD</td>
                      <td style="text-align: left;">SEKTOR</td>
                      <td>
                          <a class="btn btn-info btn-sm" id ="edit_{{ $i }}" href="#" data-toggle="modal" data-target="#edit-sektor">
                              <i class="fas fa-pencil-alt">
                              </i>
                              Edit
                          </a>
                          <a class="btn btn-danger btn-sm" id ="delete_{{ $i }}" href="#" data-toggle="modal" data-target="#hapus-sektor">
                              <i class="fas fa-trash">
                              </i>
                              Delete
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

  <!-- MODAL UNTUK TAMBAH SEKTOR -->
  <div class="modal fade" id="tambah-sektor">
    <div class="modal-dialog modal-lg ">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Tambah Sektor</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
               <!-- your steps content here -->
              <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>Nama Sektor</label>
                        <input type="text" class="form-control" placeholder="Ketik nama sektor...">
                      </div>
                  </div>
                <div class="col-sm-6">
                  <!-- text input -->
                  <div class="form-group">
                    <label>Kode Sektor</label>
                    <input type="text" class="form-control" placeholder="Ketik kode sektor...">
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
        <div class="modal fade" id="edit-sektor">
          <div class="modal-dialog modal-dialog-scrollable modal-lg ">
            <div class="modal-content">
              <div class="modal-header">
                <h4 class="modal-title">Edit Sektor</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <table class="table table-striped">
                    <tr>
                      <td>Nama Sektor</td>
                      <th><input type="text" class="form-control" name="namaSektor" id="namaSektor" value="" disabled></th>
                    </tr>
                    <tr>
                      <td>Kode Sektor</td>
                      <td><input type="text" class="form-control" name="namaSektor" id="namaSektor" value="" disabled></td>
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
            <div class="modal fade" id="hapus-sektor">
              <div class="modal-dialog modal-lg ">
                <div class="modal-content">
                  <div class="modal-header">
                    <h4 class="modal-title">Hapus Data Sektor</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                  <div class="modal-body">
                    <table class="table table-striped">
                      <tr>
                        <td>Nama Sektor</td>
                        <th>text</th>
                      </tr>
                      <tr>
                        <td>Kode Sektor</td>
                        <td>text</td>
                      </tr>         
                    </table>
                  
                    <span align="center">
                      <h5>Klik tombol hapus untuk menghapus data sektor ini !</h5>
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









