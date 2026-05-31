
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
            <h1>PERSEMBAHAN BULANAN</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Persembahan Bulanan</li>
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
                     
                      <button type="button" data-toggle="modal" data-target="#tambah-PersBulanan" class="btn btn-primary float-right" style="margin-right:0px; margin-top:-40px">
                       + Tambah Persembahan Bulanan
                      </button>
                    </div>
                  </div>

                <br>
                <table id="example1" class="table table-bordered table-hover">
                  <thead style="background-color:#f5f1f1">
                  <tr>
                    <th>NO</th>
                    <th>SEKTOR</th>
                    <th>NO REGIST</th>
                    <th>NAMA KELUARGA</th>
                    <th>DARI BULAN</th>
                    <th>SAMPAI BULAN</th>
                    <th>WJ</th>
                    <th>NOMINAL</th>
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
                      <td style="text-align: left;">KEL. AAAA BBBB CCCC DDDD</td>
                      <td style="text-align: left;">JAN 2023</td>
                      <td style="text-align: left;">DES 2023</td>
                      <td style="text-align: left;">10 JAN 2023</td>
                      <td style="text-align: left;">1000000</td>
                      <td>
                          {{-- <a class="btn btn-info btn-sm" id ="edit_{{ $i }}" href="#" data-toggle="modal" data-target="#edit-sintua">
                              <i class="fas fa-pencil-alt">
                              </i>
                              Edit
                          </a> --}}
                          <a class="btn btn-danger btn-sm" id ="delete_{{ $i }}" href="#" data-toggle="modal" data-target="#hapus-persBulanan">
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

  <!-- MODAL UNTUK TAMBAH PERSEMBAHAN BULANAN -->
  <div class="modal fade" id="tambah-PersBulanan">
    <div class="modal-dialog modal-lg ">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Tambah Persembahan Bulanan</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
              <div class="card bg-light mb-3">
                <div class="card-body">
                    <div class="form-group row">
                        <label for="cariKeluarga" class="col-sm-2 col-form-label">Cari Keluarga</label>
                        <div class="col-sm-10">
                            <select id="inputNamaKeluarga" class="form-control select2" style="width: 70%;">
                                <option selected></option>
                                <option value="Kel. AAAAA AAAAAA">Kel. AAAAA AAAAAA</option>
                                <option value="Kel. BBBBB BBBBBB">Kel. BBBBB BBBBBB</option>
                                <option value="Kel. CCCCC CCCCCC">Kel. CCCCC CCCCCC</option>
                            </select>
                        </div>
                    </div>
                </div>
              </div>

              <div class="card bg-light mb-3">
                <div class="card-body">
                    <div class="form-group">
                        <label for="tempatLahir">Nama Keluarga</label>
                        <input type="text" class="form-control" id="namaKeluarga" style="border:none;" disabled>
                    </div>
                    <p>Masukkan data periode pembayaran dan nominal pembayaran</p>
                    <div class="row">
                        <div class="col-sm-6">
                          <!-- text input -->
                          <div class="form-group">
                            <label>Periode Pembayaran Awal</label>
                            <div class="input-group date" data-target-input="nearest">
                                <input type="text" id="inputPeriodeAwal" class="form-control datetimepicker-input" data-target="#inputPeriodeAwal"/>
                                <div class="input-group-append" data-target="#inputPeriodeAwal" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>
                            </div>
                          </div>
                        </div>
                        <div class="col-sm-6">
                          <div class="form-group">
                            <label>Periode Pembayaran Akhir</label>
                            <div class="input-group date" data-target-input="nearest">
                                <input type="text" id="inputPeriodeAkhir" class="form-control datetimepicker-input" data-target="#inputPeriodeAkhir"/>
                                <div class="input-group-append" data-target="#inputPeriodeAkhir" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-sm-6">
                          <!-- text input -->
                          <div class="form-group">
                            <label>Tanggal Warta Jemaat</label>
                            <div class="input-group date" data-target-input="nearest">
                                <input type="text" id="TanggalWartaJemaat" class="form-control datetimepicker-input" data-target="#TanggalWartaJemaat"/>
                                <div class="input-group-append" data-target="#TanggalWartaJemaat" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>
                            </div>
                          </div>
                        </div>
                        <div class="col-sm-6">
                          <div class="form-group">
                            <label>Nominal Pembayaran (Rp)</label>
                            <input type="text" class="form-control" placeholder="Masukkan Nominal" data-type="currency" onkeypress="return onlyNumberKey(event)"/>
                          </div>
                        </div>
                      </div>
                </div>
              </div>
                
        </div>
        
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-info">Simpan</button>
        </div>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
  </div>
  <!-- END OF MODAL UNTUK TAMBAH PERSEMBAHAN BULANAN -->


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

            <!-- MODAL UNTUK DELETE PERSEMBAHAN BULANAN -->
            <div class="modal fade" id="hapus-persBulanan">
              <div class="modal-dialog modal-lg ">
                <div class="modal-content">
                  <div class="modal-header">
                    <h4 class="modal-title">Hapus Data Persembahan Bulanan</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                  <div class="modal-body">
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <div class="form-group">
                                <label for="tempatLahir">Nama Keluarga</label>
                                <input type="text" class="form-control" id="putKeluarga" style="border:none;" value="" disabled>
                            </div>
                            <div class="form-group">
                                <label for="tempatLahir">Dari Bulan</label>
                                <input type="text" class="form-control" id="putDariBulan" style="border:none;" value="" disabled>
                            </div>
                            <div class="form-group">
                                <label for="tempatLahir">Sampai Bulan</label>
                                <input type="text" class="form-control" id="putSampaiBulan" style="border:none;" value="" disabled>
                            </div>
                            <div class="form-group">
                                <label for="tempatLahir">Nominal</label>
                                <input type="text" class="form-control" id="putNominal" style="border:none;" value="" disabled>
                            </div>
                        </div>
                      </div>
                  
                    <span align="center">
                      <h5>Klik tombol hapus untuk menghapus data persembahan bulanan ini !</h5>
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
            <!-- END OF MODAL DELETE PERSEMBAHAN BULANAN -->

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

    $(function(){  //triger dan set value ke input nama keluarga
        $('#inputNamaKeluarga').trigger('change'); //This event will fire the change event. 
        $('#inputNamaKeluarga').change(function(){
        var data= $(this).val();
        document.getElementById('namaKeluarga').value = data;        
        });
    });


    //Datemask dd/mm/yyyy
    $('#datemask').inputmask('dd/mm/yyyy', { 'placeholder': 'dd/mm/yyyy' })
        //Datemask2 mm/dd/yyyy
        $('#datemask2').inputmask('mm/dd/yyyy', { 'placeholder': 'mm/dd/yyyy' })
        //Money Euro
        $('[data-mask]').inputmask()

        //Date picker
        $('#reservationdate').datetimepicker({
            format: 'L'
        });

        $('#inputPeriodeAwal').datetimepicker({
            format: 'L'
        });

        $('#inputPeriodeAkhir').datetimepicker({
            format: 'L'
        });

        $('#TanggalWartaJemaat').datetimepicker({
            format: 'L'
        });


        //Date and time picker
        $('#reservationdatetime').datetimepicker({ icons: { time: 'far fa-clock' } });

        //Date range picker
        $('#reservation').daterangepicker()
        //Date range picker with time picker
        $('#reservationtime').daterangepicker({
        timePicker: true,
        timePickerIncrement: 30,
        locale: {
            format: 'MM/DD/YYYY hh:mm A'
        }
        })
        //Date range as a button
        $('#daterange-btn').daterangepicker(
        {
            ranges   : {
            'Today'       : [moment(), moment()],
            'Yesterday'   : [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days' : [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month'  : [moment().startOf('month'), moment().endOf('month')],
            'Last Month'  : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            startDate: moment().subtract(29, 'days'),
            endDate  : moment()
        },
        function (start, end) {
            $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'))
        }
        )

        //Timepicker
        $('#timepicker').datetimepicker({
        format: 'LT'
        })
        
    


  </script>









