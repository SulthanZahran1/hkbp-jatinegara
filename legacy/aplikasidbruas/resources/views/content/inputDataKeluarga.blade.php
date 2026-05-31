
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
              <li class="breadcrumb-item active">Data Ruas / Input keluarga</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        
            <!-- SELECT2 EXAMPLE -->
            <div id="cardelement">
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
                                <input class="form-check-input radio-inline" type="radio" name="inputGender" id="inputGenderLaki" value="Laki-laki">
                                <label class="form-check-label">Laki-laki
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                </label>
                                
                                <input class="form-check-input radio-inline" type="radio" name="inputGender" id="inputGenderPerempuan" value="Perempuan">
                                <label class="form-check-label">Perempuan</label>
                               
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-3">
                          <label for="tempatLahir">Tempat Lahir</label>
                          {{-- <input type="text" class="form-control" id="inputTempatLahir" placeholder="Tempat Lahir" onkeypress="return onlyOneWhiteSpace(event,this.id)"> --}}
                          <input type="text" class="form-control" id="inputTempatLahir" placeholder="Tempat Lahir">
                        </div>
                  
                        <div class="form-group col-md-3">
                          <label>Tanggal Lahir</label>
                          <div class="input-group date" data-target-input="nearest">
                              <input type="text" id="inputTgllahir" class="form-control datetimepicker-input" data-target="#inputTgllahir"/>
                              <div class="input-group-append" data-target="#inputTgllahir" data-toggle="datetimepicker">
                                  <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                              </div>
                          </div>
                      </div>
                        <div class="form-group col-md-3">
                            <label>Golongan Darah</label>
                            <select id="inputGolonganDarah" class="form-control select2" style="width: 100%;">
                              <option disabled="disabled" selected="selected">-PILIH-</option>
                              <option value="A">A</option>
                              <option value="B">B</option>
                              <option value="O">O</option>
                              <option value="AB">AB</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Hubungan Keluarga</label>
                            <select id="inputHubKeluarga" class="form-control select2" style="width: 100%;">
                              <option disabled="disabled" selected="selected">-PILIH-</option>
                              <option value="Kepala Keluarga">Kepala Keluarga</option>
                              <option value="Istri">Istri</option>
                              <option value="Anak">Anak</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>Pendidikan Terakhir</label>
                            <select id="inputPendidikan" class="form-control select2" style="width: 100%;">
                              <option disabled="disabled" selected="selected">-PILIH-</option>
                              <option value="SD">SD</option>
                              <option value="SMP">SMP</option>
                              <option value="SMA">SMA</option>
                              <option value="D3">D3</option>
                              <option value="S1">S1</option>
                              <option value="S2">S2</option>
                              <option value="S3">S3</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Pekerjaan</label>
                            <select id="inputPekerjaan" class="form-control select2" style="width: 100%;">
                              <option disabled="disabled" selected="selected">-PILIH-</option>
                              <option value="Pegawai Swasta">Pegawai Swasta</option>
                              <option value="PNS">Pegawai Negeri Sipil</option>
                              <option value="IRT">Ibu Rumah Tangga</option>
                              <option value="Wirausaha">Wirausaha</option>
                              <option value="Belum Bekerja">Belum Bekerja</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="inputTalenta">Talenta</label>
                            <input type="text" class="form-control" id="inputTalenta" placeholder="Talenta">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="inputNohp">No Handphone</label>
                            <input type="text" class="form-control" id="inputNohp" placeholder="No Hp" onkeypress="return onlyNumberKey(event)">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="inputAlamat">Alamat</label>
                            <input type="text" class="form-control" id="inputAlamat" placeholder="Alamat Lengkap">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Sektor</label>
                            <select id="inputSektor" class="form-control select2" style="width: 100%;">
                              <option disabled="disabled" selected="selected">-PILIH-</option>
                              <option value="Judika">Judika</option>
                              <option value="Galatia">Galatia</option>
                              <option value="Kolose">Kolose</option>
                              <option value="Markus">Markus</option>
                              <option value="Diaspora">Diaspora</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>Propinsi</label>
                            <select id="inputPropinsi" class="form-control select2" style="width: 100%;">
                              <option disabled="disabled" selected="selected">-PILIH-</option>
                              <option value="DKI Jakarta">DKI Jakarta</option>
                              <option value="Jawa Barat">Jawa Barat</option>
                              <option value="Banten">Banten</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Kota</label>
                            <select id="inputKota" class="form-control select2" style="width: 100%;">
                              <option disabled="disabled" selected="selected">-PILIH-</option>
                              <option value="Jakarta Timur">Jakarta Timur</option>
                              <option value="Jakarta Barat">Jakarta Barat</option>
                              <option value="Jakarta Pusat">Jakarta Pusat</option>
                              <option value="Jakarta Utara">Jakarta Utara</option>
                              <option value="Jakarta Selatan">Jakarta Selatan</option>
                            </select>
                        </div>
                        <div class="form-group col-md-2">
                            <label>Kecamatan</label>
                            <select id="inputKecamatan" class="form-control select2" style="width: 100%;">
                              <option disabled="disabled" selected="selected">-PILIH-</option>
                              <option value="Jatinegara">Jatinegara</option>
                            </select>
                        </div>
                        <div class="form-group col-md-2">
                            <label>Kelurahan</label>
                            <select id="inputKelurahan" class="form-control select2" style="width: 100%;">
                              <option disabled="disabled" selected="selected">-PILIH-</option>
                              <option>Cipinang Muara</option>
                            </select>
                        </div>
                        <div class="form-group col-md-2">
                            <label for="kodePos">kode Pos</label>
                            <input type="text" class="form-control" id="inputkodePos" placeholder="Kode Pos">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>Tanggal Baptis</label>
                            <div class="input-group date" data-target-input="nearest">
                                <input type="text" id="inputTglBaptis" class="form-control datetimepicker-input" data-target="#inputTglBaptis"/>
                                <div class="input-group-append" data-target="#inputTglBaptis" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="gerejaBaptis">Gereja Tempat Baptis</label>
                            <input type="text" class="form-control" id="inputGerejaBaptis" placeholder="Gereja Tempat Baptis ?">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="pendetaBaptis">Pendeta Yang Melayani Baptis</label>
                            <input type="text" class="form-control" id="inputPendetaBaptis" placeholder="Pendeta Yang Melayani Baptis ?">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>Tanggal Sidi</label>
                            <div class="input-group date" data-target-input="nearest">
                                <input type="text" id="inputTglSidi" class="form-control datetimepicker-input" data-target="#inputTglSidi"/>
                                <div class="input-group-append" data-target="#inputTglSidi" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="gerejaSidi">Gereja Tempat Sidi</label>
                            <input type="text" class="form-control" id="inputGerejaSidi" placeholder="Gereja Tempat Sidi ?">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="pendetaSidi">Pendeta Yang Melayani Sidi</label>
                            <input type="text" class="form-control" id="inputPendetaSidi" placeholder="Pendeta Yang Melayani Sidi ?">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="natsSidi">Nats Sidi</label>
                            <input type="text" class="form-control" id="inputNatsSidi" placeholder="Nats Sidi ?">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>Tanggal Pemberkatan Perkawinan</label>
                            <div class="input-group date" data-target-input="nearest">
                                <input type="text" id="inputTglPerkawinan" class="form-control datetimepicker-input" data-target="#inputTglPerkawinan"/>
                                <div class="input-group-append" data-target="#inputTglPerkawinan" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="gerejaPerkawinan">Gereja Tempat Perkawinan</label>
                            <input type="text" class="form-control" id="inputGerejaPerkawinan" placeholder="Gereja Tempat Perkawinan ?">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="pendetaPerkawinan">Pendeta Yang Melayani Perkawinan</label>
                            <input type="text" class="form-control" id="inputPendetaPerkawinan" placeholder="Pendeta Yang Melayani Perkawinan ?">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="natsPerkawinan">Nats Perkawinan</label>
                            <input type="text" class="form-control" id="inputNatsPerkawinan" placeholder="Nats Perkawinan ?">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="unggahFoto">Unggah Foto</label>
                            <input type="file" class="form-control-file" id="inputUnggahFoto">
                        </div>
                    </div>
        

                </div>
                <!-- /.card-body -->
                {{-- <div class="card-footer">
                test footer
                </div> --}}
              </div>
            </div>

              <div id="addcontent"></div> <!-- /coba clone element card -->
              <!-- /.card -->
              <div class="form-row">
                <div class="form-group col-md-12">
                    <button type="submit" id ="tambahKeluarga" class="btn btn-danger float-right" data-toggle="modal" data-target="#add-keluarga" onclick=checkCount()>Tambah Keluarga +</button>
                    {{-- <button type="submit" id ="tambahKeluarga" class="btn btn-danger float-right" onclick=addKeluarga()>Tambah Keluarga +</button> --}}
                </div>
               </div>
                    <button type="submit" class="btn btn-info" onclick=submitDataKeluarga()>Submit Data Keluarga</button>
              <br><br>
        <!-- /.row -->
      </div>
      <!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->


     <!-- MODAL UNTUK ADD KELUARGA -->
     <div class="modal fade" id="add-keluarga">
        <div class="modal-dialog modal-dialog-scrollable modal-lg ">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title">Tambah Anggota Keluarga</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
                <!-- your steps content here -->
               <div id="#" class="content" role="tabpanel" aria-labelledby="logins-part-trigger">
                <div class="form-group">
                  <label>Hubungan Keluarga</label>
                  <select id="selectKeluarga" class="form-control select2" style="width: 100%;">
                    <option disabled="disabled" selected="selected">-- pilih keluarga --</option>
                    <option value="1">Istri</option>
                    <option value="2">Anak</option>
                  </select>
                </div>
              </div>
              <br>
              <br>
              <br>
 
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-danger" data-dismiss="modal">Kembali</button>
              <button type="button" class="btn btn-info" onclick=addKeluarga()>Tambahkan</button>
            </div>
          </div>
          <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
      </div>
      <!-- END OF MODAL ADD KELUARGA -->

@include('layouts.footer')
@include('content.cardKeluarga')

  
<script>
    function checkCount(){
      if(check_card_istri >= 1){
        document.getElementById('selectKeluarga').options[1].disabled = true; //disable istri jika lebih dari 1
      }
    }

    function removeIstri(){
      check_card_istri = 0;
      document.getElementById('selectKeluarga').options[1].disabled = false; //enable istri count jadi 0 
    }

    function removeAnak(){
      console.log("remove anak");
      check_card_anak-=1; 
    }

    function addKeluarga(){
        //console.log("berhasil tekan keluarga");
        // var secondDivContent = document.getElementById('testclone');
        // secondDivContent.appendChild = '<p>tes aja</p>';
        //secondDivContent.innerHTML = cards_istri;
        // $('#testclone').load("content/cardKeluarga.php");

        const select  = document.getElementById('selectKeluarga').value;
        if(select == '1'){
          check_card_istri+=1;
          addCardIstri(check_card_istri);
          // const newContent = document.createElement('div');
          // newContent.innerHTML = cards_istri;
          // document.getElementById('addcontent').appendChild(newContent);
          // $("#add-keluarga").modal("hide"); //hide modal setelah append card baru
        }else{
          check_card_anak+=1;
          addCardAnak(check_card_anak);
        }
        // const newContent = document.createElement('div');
        // newContent.innerHTML = cards_istri;
        // document.getElementById('testclone').appendChild(newContent);
    }

    function submitDataKeluarga(){
      let inputNama  = document.getElementById('inputNama').value;
      let inputMarga  = document.getElementById('inputMarga').value;
      //let inputGender  = document.getElementById('inputGenderLaki').value;
      var inputGender;
      if(document.getElementById('inputGenderLaki').checked){
        inputGender = document.getElementById('inputGenderLaki').value;
      }else{
        inputGender = document.getElementById('inputGenderPerempuan').value;
      }
     
      let inputTempatLahir  = document.getElementById('inputTempatLahir').value;
      let inputTgllahir  = $('#inputTgllahir').val();
      let inputGolonganDarah  = document.getElementById('inputGolonganDarah').value;
      let inputHubKeluarga  = document.getElementById('inputHubKeluarga').value;
      let inputPendidikan  = document.getElementById('inputPendidikan').value;
      let inputPekerjaan  = document.getElementById('inputPekerjaan').value;
      let inputTalenta  = document.getElementById('inputTalenta').value;
      let inputNohp  = document.getElementById('inputNohp').value;
      let inputAlamat  = document.getElementById('inputAlamat').value;
      let inputSektor  = document.getElementById('inputSektor').value;
      let inputPropinsi  = document.getElementById('inputPropinsi').value;
      let inputKota  = document.getElementById('inputKota').value;
      let inputKecamatan  = document.getElementById('inputKecamatan').value;
      let inputKelurahan  = document.getElementById('inputKelurahan').value;
      let inputkodePos  = document.getElementById('inputkodePos').value;
      let inputTglBaptis  = $('#inputTglBaptis').val();
      let inputGerejaBaptis  = document.getElementById('inputGerejaBaptis').value;
      let inputPendetaBaptis  = document.getElementById('inputPendetaBaptis').value;
      let inputTglSidi  = $('#inputTglSidi').val();
      let inputGerejaSidi  = document.getElementById('inputGerejaSidi').value;
      let inputPendetaSidi  = document.getElementById('inputPendetaSidi').value;
      let inputNatsSidi  = document.getElementById('inputNatsSidi').value;
      let inputTglPerkawinan  = $('#inputTglPerkawinan').val();
      let inputGerejaPerkawinan  = document.getElementById('inputGerejaPerkawinan').value;
      let inputPendetaPerkawinan  = document.getElementById('inputPendetaPerkawinan').value;
      let inputNatsPerkawinan  = document.getElementById('inputNatsPerkawinan').value;
      let inputUnggahFoto  = document.getElementById('inputUnggahFoto').value;

      const data =[];
      data.push(inputNama);
      data.push(inputMarga);
      data.push(inputGender);
      data.push(inputTempatLahir);
      data.push(inputTgllahir);
      data.push(inputGolonganDarah);
      data.push(inputHubKeluarga);
      data.push(inputPendidikan);
      data.push(inputPekerjaan);
      data.push(inputTalenta);
      data.push(inputNohp);
      data.push(inputAlamat);
      data.push(inputSektor);
      data.push(inputPropinsi);
      data.push(inputKota);
      data.push(inputKecamatan);

      data.push(inputKelurahan);
      data.push(inputkodePos);
      data.push(inputTglBaptis);
      data.push(inputGerejaBaptis);
      data.push(inputPendetaBaptis);
      data.push(inputTglSidi);
      data.push(inputGerejaSidi);
      data.push(inputPendetaSidi);
      data.push(inputNatsSidi);

      data.push(inputTglPerkawinan);
      data.push(inputGerejaPerkawinan);
      data.push(inputPendetaPerkawinan);
      data.push(inputNatsPerkawinan);
      data.push(inputUnggahFoto);

      console.log(data);

      if(check_card_istri > 0){
        console.log("ada istri")

        let inputNamaIstri  = document.getElementById('inputNamaIstri').value;
        let inputMargaIstri  = document.getElementById('inputMargaIstri').value;
        let inputGenderIstri  = document.getElementById('inputGenderIstri').value;
        let inputTempatLahirIstri  = document.getElementById('inputTempatLahirIstri').value;
        let inputTgllahirIstri  = document.getElementById('inputTgllahirIstri').value;
        let inputGolDarahIstri  = document.getElementById('inputGolDarahIstri').value;

        const data_istri =[];
        data_istri.push(inputNamaIstri);
        data_istri.push(inputMargaIstri);
        data_istri.push(inputGenderIstri);
        data_istri.push(inputTempatLahirIstri);
        data_istri.push(inputTgllahirIstri);
        data_istri.push(inputGolDarahIstri);
        console.log(data_istri);

      }

      if(check_card_anak > 0){
        console.log("ada anak")
        for(i=1; i<=check_card_anak; i++){
          let inputNamaAnak  = document.getElementById('inputNamaAnak'+i.toString()).value;
          let inputMargaAnak  = document.getElementById('inputMargaAnak'+i.toString()).value;
          var inputGenderAnak;
          if(document.getElementById('inputGenderAnakLaki'+i.toString()).checked){
            inputGenderAnak = document.getElementById('inputGenderAnakLaki'+i.toString()).value;
          }else{
            inputGenderAnak = document.getElementById('inputGenderAnakPerempuan'+i.toString()).value;
          }
          let inputTempatLahirAnak  = document.getElementById('inputTempatLahirAnak'+i.toString()).value;

 

          const data_anak =[i];
          data_anak.push(inputNamaAnak);
          data_anak.push(inputMargaAnak);
          data_anak.push(inputGenderAnak);
          data_anak.push(inputTempatLahirAnak);
          console.log(data_anak);
        }
      }
      // console.log(namaKepalaKeluarga);
      // console.log(tgllahirKepalaKeluarga);
      //console.log(namaIstri);
    }

    function onlyNumberKey(evt) {                                      
      // Only ASCII character in that range allowed
      var ASCIICode = (evt.which) ? evt.which : evt.keyCode
      if (ASCIICode > 31 && (ASCIICode < 48 || ASCIICode > 57))
      return false;
      return true;
    }

    // function onlyOneWhiteSpace(event, id){
    //   var count = 0;
    //   console.log(id);
    //     if (window.event)
    //     {
    //         event = window.event;
    //     }
       
    //     if(event.keyCode==32)
    //     {
    //       count = count + 2;
    //       console.log(count);            
    //     }

    //     if(count <=2){
    //           console.log("bener sih");
    //           var val = document.getElementById(id);
    //           val.value = val.value.replace(/ +(?= )/g,'');
    //           document.getElementById(id).onkeyup=val.value;
    //           return true;
    //           var count= 0;
    //         }
        
    // }

    // processKeyUp = function(event){    
    //     // MSIE hack
    //     if (window.event)
    //     {
    //         event = window.event;
    //     }

    //     if(event.keyCode==32)
    //     {
    //         var val = document.getElementById('inputNama');
    //         var val = document.getElementById('inputMarga');
    //         var val = document.getElementById('inputTempatLahir');
    //         val.value = val.value.replace(/ +(?= )/g,'');
    //     }
       
    // }; 
    // document.getElementById("inputNama").onkeyup=processKeyUp;
    // document.getElementById("inputMarga").onkeyup=processKeyUp;
    // document.getElementById("tempatLahir").onkeyup=processKeyUp;



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

        $('#inputTgllahir').datetimepicker({
            format: 'L'
        });

        $('#inputTglBaptis').datetimepicker({
            format: 'L'
        });

        $('#inputTglSidi').datetimepicker({
            format: 'L'
        });

        $('#inputTglPerkawinan').datetimepicker({
            format: 'L'
        });

        $('#inputTgllahirIstri').datetimepicker({
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

