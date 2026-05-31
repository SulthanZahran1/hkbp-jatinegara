
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
            <h1>INPUT DATA KELUARGA</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">inputkeluarga</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        
            <!-- SELECT2 EXAMPLE -->
            <div class="card card-default">
                <div class="card-header">
                  <h3 class="card-title">Kepala Keluarga</h3>
      
                  <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                      <i class="fas fa-minus"></i>
                    </button>
                    <button type="button" class="btn btn-tool" data-card-widget="remove">
                      <i class="fas fa-times"></i>
                    </button>
                  </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                          <label for="inputNama">Nama Lengkap</label>
                          <input type="text" class="form-control" id="inputNama" placeholder="Nama">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="inputMarga">Marga</label>
                            <input type="text" class="form-control" id="inputMarga" placeholder="Marga">
                          </div>
                        <div class="form-group col-md-3">
                            <label for="inputMarga">Gender</label>
                            <div class="form-check">
                                <input class="form-check-input radio-inline" type="radio" name="gridRadios" id="flexRadioDefault1" value="Laki-laki">
                                <label class="form-check-label">Laki-laki
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                </label>
                                
                                <input class="form-check-input radio-inline" type="radio" name="gridRadios" id="flexRadioDefault1" value="Perempuan">
                                <label class="form-check-label">Perempuan</label>
                               
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-3">
                          <label for="tempatLahir">Tempat Lahir</label>
                          <input type="text" class="form-control" id="tempatLahir" placeholder="Tempat Lahir">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Tanggal Lahir</label>
                            <div class="input-group date" id="tgllahir" data-target-input="nearest">
                                <input type="text" class="form-control datetimepicker-input" data-target="#tgllahir"/>
                                <div class="input-group-append" data-target="#tgllahir" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Golongan Darah</label>
                            <select class="form-control select2" style="width: 100%;">
                              <option disabled="disabled" selected="selected">-PILIH-</option>
                              <option>A</option>
                              <option>B</option>
                              <option>O</option>
                              <option>AB</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Hubungan Keluarga</label>
                            <select class="form-control select2" style="width: 100%;">
                              <option disabled="disabled" selected="selected">-PILIH-</option>
                              <option>Kepala Keluarga</option>
                              <option>Istri</option>
                              <option>Anak</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>Pendidikan Terakhir</label>
                            <select class="form-control select2" style="width: 100%;">
                              <option disabled="disabled" selected="selected">-PILIH-</option>
                              <option>SD</option>
                              <option>SMP</option>
                              <option>SMA</option>
                              <option>D3</option>
                              <option>S1</option>
                              <option>S2</option>
                              <option>S3</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Pekerjaan</label>
                            <select class="form-control select2" style="width: 100%;">
                              <option disabled="disabled" selected="selected">-PILIH-</option>
                              <option>Pegawai Swasta</option>
                              <option>Pegawai Negeri Sipil</option>
                              <option>Ibu Rumah Tangga</option>
                              <option>Wirausaha</option>
                              <option>Belum Bekerja</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="inputTalenta">Talenta</label>
                            <input type="text" class="form-control" id="inputTalenta" placeholder="Talenta">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="inputNohp">No Handphone</label>
                            <input type="text" class="form-control" id="inputNohp" placeholder="No Hp">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="inputAlamat">Alamat</label>
                            <input type="text" class="form-control" id="inputAlamat" placeholder="Alamat Lengkap">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Sektor</label>
                            <select class="form-control select2" style="width: 100%;">
                              <option disabled="disabled" selected="selected">-PILIH-</option>
                              <option>Judika</option>
                              <option>Galatia</option>
                              <option>Kolose</option>
                              <option>Markus</option>
                              <option>Diaspora</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>Propinsi</label>
                            <select class="form-control select2" style="width: 100%;">
                              <option disabled="disabled" selected="selected">-PILIH-</option>
                              <option>DKI Jakarta</option>
                              <option>Jawa Barat</option>
                              <option>Banten</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Kota</label>
                            <select class="form-control select2" style="width: 100%;">
                              <option disabled="disabled" selected="selected">-PILIH-</option>
                              <option>Jakarta Timur</option>
                              <option>Jakarta Barat</option>
                              <option>Jakarta Pusat</option>
                              <option>Jakarta Utara</option>
                              <option>Jakarta Selatan</option>
                            </select>
                        </div>
                        <div class="form-group col-md-2">
                            <label>Kecamatan</label>
                            <select class="form-control select2" style="width: 100%;">
                              <option disabled="disabled" selected="selected">-PILIH-</option>
                              <option>Jatinegara</option>
                            </select>
                        </div>
                        <div class="form-group col-md-2">
                            <label>Kelurahan</label>
                            <select class="form-control select2" style="width: 100%;">
                              <option disabled="disabled" selected="selected">-PILIH-</option>
                              <option>Cipinang Muara</option>
                            </select>
                        </div>
                        <div class="form-group col-md-2">
                            <label for="kodePos">kode Pos</label>
                            <input type="text" class="form-control" id="kodePos" placeholder="Kode Pos">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>Tanggal Baptis</label>
                            <div class="input-group date" id="tglbaptis" data-target-input="nearest">
                                <input type="text" class="form-control datetimepicker-input" data-target="#tglbaptis"/>
                                <div class="input-group-append" data-target="#tglbaptis" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="gerejaBaptis">Gereja Tempat Baptis</label>
                            <input type="text" class="form-control" id="gerejaBaptis" placeholder="Gereja Tempat Baptis ?">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="pendetaBaptis">Pendeta Yang Melayani Baptis</label>
                            <input type="text" class="form-control" id="pendetaBaptis" placeholder="Pendeta Yang Melayani Baptis ?">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>Tanggal Sidi</label>
                            <div class="input-group date" id="tglsidi" data-target-input="nearest">
                                <input type="text" class="form-control datetimepicker-input" data-target="#tglsidi"/>
                                <div class="input-group-append" data-target="#tglsidi" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="gerejaSidi">Gereja Tempat Sidi</label>
                            <input type="text" class="form-control" id="gerejaSidi" placeholder="Gereja Tempat Sidi ?">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="pendetaSidi">Pendeta Yang Melayani Sidi</label>
                            <input type="text" class="form-control" id="pendetaSidi" placeholder="Pendeta Yang Melayani Sidi ?">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="natsSidi">Nats Sidi</label>
                            <input type="text" class="form-control" id="natsSidi" placeholder="Nats Sidi ?">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>Tanggal Pemberkatan Pernikahan</label>
                            <div class="input-group date" id="tglperkawinan" data-target-input="nearest">
                                <input type="text" class="form-control datetimepicker-input" data-target="#tglperkawinan"/>
                                <div class="input-group-append" data-target="#tglperkawinan" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="gerejaPerkawinan">Gereja Tempat Perkawinan</label>
                            <input type="text" class="form-control" id="gerejaPerkawinan" placeholder="Gereja Tempat Perkawinan ?">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="pendetaPerkawinan">Pendeta Yang Melayani Perkawinan</label>
                            <input type="text" class="form-control" id="pendetaPerkawinan" placeholder="Pendeta Yang Melayani Perkawinan ?">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="natsPerkawinan">Nats Perkawinan</label>
                            <input type="text" class="form-control" id="natsPerkawinan" placeholder="Nats Perkawinan ?">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="unggahFoto">Unggah Foto</label>
                            <input type="file" class="form-control-file" id="unggahFoto">
                        </div>
                    </div>
        

                </div>
                <!-- /.card-body -->
                {{-- <div class="card-footer">
                test footer
                </div> --}}
              </div>
              <!-- /.card -->
              <div class="form-row">
                <div class="form-group col-md-12">
                    <button type="submit" class="btn btn-danger float-right">Tambah Keluarga +</button>
                </div>
               </div>
                            <button type="submit" class="btn btn-info">Submit Data Keluarga</button>
              <br><br>
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

        $('#tgllahir').datetimepicker({
            format: 'L'
        });

        $('#tglbaptis').datetimepicker({
            format: 'L'
        });

        $('#tglsidi').datetimepicker({
            format: 'L'
        });

        $('#tglperkawinan').datetimepicker({
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
        
        });


  </script>

