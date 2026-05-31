
@include('layouts.header')
@include('layouts.navbar')
@include('layouts.sidebar')

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>RESET PASSWORD</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item active">Pengaturan User / Reset Password</li>
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
                </div> --}}

                
          
                  
                <table id="example1" class="table table-bordered table-hover">
                  <thead style="background-color:#f5f1f1">
                  <tr>
                    <th>NO</th>
                    <th>NAMA</th>
                    <th>USERNAME</th>
                    <th>ROLE</th>
                    <th>STATUS</th>
                    <th>TINDAKAN</th>
                  </tr>
                  </thead>
                  <tbody>
                    <?php $i = 1; ?>
                    @for ($a = 1; $a < 20; $a++)
                    <tr>
                        <td>{{ $i }}</td>
                        <td>NAMA USER</td>
                        <td>KELUARGA</td>
                        <td>TEST TEST TEST</td>
                        <td>AKTIF</td>
                        <td>
                            <a class="btn btn-info btn-sm" href="#">
                                RESET
                            </a>
                        </td>
                    </tr>
                    <?php $i++; ?>
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


  <div class="modal fade" id="modal-lg">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Large Modal</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p>One fine body&hellip;</p>
        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary">Save changes</button>
        </div>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
  </div>
  <!-- /.modal -->

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

