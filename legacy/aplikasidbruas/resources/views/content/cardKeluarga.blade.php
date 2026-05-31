<script>
    var check_card_istri = 0;
    var check_card_anak = 0;
    var isian = "TEST PENDIDIKAN"

    function addCardIstri(countCard){
    const cards_istri = '' + 
    '' + 
    '<div class="card card-default">' + 
    '    <div class="card-header">' + 
    '      <h3 class="card-title">Istri</h3>' + 
    '' + 
    '      <div class="card-tools">' + 
    '        <button type="button" class="btn btn-tool" data-card-widget="collapse">' + 
    '          <i class="fas fa-minus"></i>' + 
    '        </button>' + 
    '        <button type="button" class="btn btn-tool" data-card-widget="remove" onclick=removeIstri()>' + 
    '          <i class="fas fa-times"></i>' + 
    '        </button>' + 
    '      </div>' + 
    '    </div>' + 
    '    <!-- /.card-header -->' + 
    '    <div class="card-body">' + 
    '        <div class="form-row">' + 
    '            <div class="form-group col-md-6">' + 
    '              <label for="inputNama">Nama Lengkap</label>' + 
    '              <input type="text" class="form-control" id="inputNamaIstri" placeholder="Nama">' + 
    '            </div>' + 
    '            <div class="form-group col-md-3">' + 
    '                <label for="inputMarga">Marga</label>' + 
    '                <input type="text" class="form-control" id="inputMargaIstri" placeholder="Marga">' + 
    '              </div>' + 
    '            <div class="form-group col-md-3">' + 
    '                <label for="inputMarga">Gender</label>' + 
    '                <div class="form-check">' + 
    '                    <input class="form-check-input radio-inline" type="radio" name="inputGenderPerempuan" id="" value="Laki-laki" disabled>' + 
    '                    <label class="form-check-label">Laki-laki' + 
    '                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' + 
    '                    </label>' + 
    '                    ' + 
    '                    <input class="form-check-input radio-inline" type="radio" name="inputGenderPerempuan" id="inputGenderIstri" value="Perempuan">' + 
    '                    <label class="form-check-label">Perempuan</label>' + 
    '                   ' + 
    '                </div>' + 
    '            </div>' + 
    '        </div>' + 
    '        <div class="form-row">' + 
    '            <div class="form-group col-md-3">' + 
    '              <label for="tempatLahir">Tempat Lahir</label>' + 
    '              <input type="text" class="form-control" id="inputTempatLahirIstri" placeholder="Tempat Lahir">' + 
    '            </div>' + 
    '            <div class="form-group col-md-3">' + 
    '                <label>Tanggal Lahir</label>' + 
    '                <div class="input-group date" data-target-input="nearest">' + 
    '                    <input type="text" id="inputTgllahirIstri" class="form-control datetimepicker-input" data-target="#inputTgllahirIstri"/>' + 
    '                    <div class="input-group-append" data-target="#inputTgllahirIstri" data-toggle="datetimepicker">' + 
    '                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>' + 
    '                    </div>' + 
    '                </div>' + 
    '            </div>' + 
    '            <div class="form-group col-md-3">' + 
    '                <label>Golongan Darah</label>' + 
    '                <select id="inputGolDarahIstri" class="form-control select2" style="width: 100%;">' + 
    '                  <option disabled="disabled" selected="selected">-PILIH-</option>' + 
    '                  <option value="A">A</option>' + 
    '                  <option value="B">B</option>' + 
    '                  <option value="O">O</option>' + 
    '                  <option value="AB">AB</option>' + 
    '                </select>' + 
    '            </div>' + 
    '            <div class="form-group col-md-3">' + 
    '                <label>Hubungan Keluarga</label>' + 
    '                <select id="inputHubKeluargaIstri" class="form-control select2" style="width: 100%;">' + 
    '                  <option disabled="disabled" selected="selected">-PILIH-</option>' + 
    '                  <option value="Kepala Keluarga">Kepala Keluarga</option>' + 
    '                  <option value="Istri">Istri</option>' + 
    '                  <option value="Anak">Anak</option>' + 
    '                </select>' + 
    '            </div>' + 
    '        </div>' + 
    '' + 
    '        <div class="form-row">' + 
    '            <div class="form-group col-md-3">' + 
    '                <label>Pendidikan Terakhir</label>' + 
    '                <select id="inputPendidikanIstri"class="form-control select2" style="width: 100%;">' + 
    '                  <option disabled="disabled" selected="selected">-PILIH-</option>' + 
    '                  <option value="SD">SD</option>' + 
    '                  <option value="SMP">SMP</option>' + 
    '                  <option value="SMA">SMA</option>' + 
    '                  <option value="D3">D3</option>' + 
    '                  <option value="S1">S1</option>' + 
    '                  <option value="S2">S2</option>' + 
    '                  <option value="S3">S3</option>' + 
    '                </select>' + 
    '            </div>' + 
    '            <div class="form-group col-md-3">' + 
    '                <label>Pekerjaan</label>' + 
    '                <select id="inputPekerjaanIstri"class="form-control select2" style="width: 100%;">' + 
    '                  <option disabled="disabled" selected="selected">-PILIH-</option>' + 
    '                  <option value="Pegawai Swasta">Pegawai Swasta</option>' + 
    '                  <option value="Pegawai Negeri Sipil">Pegawai Negeri Sipil</option>' + 
    '                  <option value="Ibu Rumah Tangga">Ibu Rumah Tangga</option>' + 
    '                  <option value="Wirausaha">Wirausaha</option>' + 
    '                  <option value="Belum Bekerja">Belum Bekerja</option>' + 
    '                </select>' + 
    '            </div>' + 
    '            <div class="form-group col-md-3">' + 
    '                <label for="inputTalentaIstri">Talenta</label>' + 
    '                <input type="text" class="form-control" id="inputTalentaIstri" placeholder="Talenta">' + 
    '            </div>' + 
    '            <div class="form-group col-md-3">' + 
    '                <label for="inputNohpIstri">No Handphone</label>' + 
    '                <input type="text" class="form-control" id="inputNohpIstri" placeholder="No Hp">' + 
    '            </div>' + 
    '        </div>' + 
    '' + 

    '        <div class="form-row">' + 
    '            <div class="form-group col-md-3">' + 
    '                <label>Tanggal Baptis</label>' + 
    '                <div class="input-group date" data-target-input="nearest">' + 
    '                    <input type="text" id="inputTglbaptisIstri" class="form-control datetimepicker-input" data-target="#tglbaptis"/>' + 
    '                    <div class="input-group-append" data-target="#tglbaptis" data-toggle="datetimepicker">' + 
    '                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>' + 
    '                    </div>' + 
    '                </div>' + 
    '            </div>' + 
    '            <div class="form-group col-md-3">' + 
    '                <label for="gerejaBaptisIstri">Gereja Tempat Baptis</label>' + 
    '                <input type="text" class="form-control" id="inputGerejaBaptisIstri" placeholder="Gereja Tempat Baptis ?">' + 
    '            </div>' + 
    '            <div class="form-group col-md-6">' + 
    '                <label for="pendetaBaptis">Pendeta Yang Melayani Baptis</label>' + 
    '                <input type="text" class="form-control" id="inputPendetaBaptisIstri" placeholder="Pendeta Yang Melayani Baptis ?">' + 
    '            </div>' + 
    '        </div>' + 
    '' + 
    '        <div class="form-row">' + 
    '            <div class="form-group col-md-3">' + 
    '                <label>Tanggal Sidi</label>' + 
    '                <div class="input-group date" data-target-input="nearest">' + 
    '                    <input type="text" id="inputTglsidiIstri" class="form-control datetimepicker-input" data-target="#tglsidi"/>' + 
    '                    <div class="input-group-append" data-target="#tglsidi" data-toggle="datetimepicker">' + 
    '                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>' + 
    '                    </div>' + 
    '                </div>' + 
    '            </div>' + 
    '            <div class="form-group col-md-3">' + 
    '                <label for="gerejaSidi">Gereja Tempat Sidi</label>' + 
    '                <input type="text" class="form-control" id="inputGerejaSidiIstri" placeholder="Gereja Tempat Sidi ?">' + 
    '            </div>' + 
    '            <div class="form-group col-md-3">' + 
    '                <label for="pendetaSidi">Pendeta Yang Melayani Sidi</label>' + 
    '                <input type="text" class="form-control" id="inputPendetaSidiIstri" placeholder="Pendeta Yang Melayani Sidi ?">' + 
    '            </div>' + 
    '            <div class="form-group col-md-3">' + 
    '                <label for="natsSidi">Nats Sidi</label>' + 
    '                <input type="text" class="form-control" id="inputNatsSidiIstri" placeholder="Nats Sidi ?">' + 
    '            </div>' + 
    '        </div>' + 
    '' + 

    '        <div class="form-row">' + 
    '            <div class="form-group col-md-4">' + 
    '                <label for="unggahFoto">Unggah Foto</label>' + 
    '                <input type="file" class="form-control-file" id="inputUnggahFotoIstri">' + 
    '            </div>' + 
    '        </div>' + 
    '' + 
    '' + 
    '    </div>' + 
    '' + 
    '  </div>' + 
    '';

    const newContent = document.createElement('div');
    newContent.innerHTML = cards_istri;
    document.getElementById('addcontent').appendChild(newContent);
    $('#inputTgllahirIstri').datetimepicker({
            format: 'L'
    });
    $("#add-keluarga").modal("hide"); //hide modal setelah append card baru
    }
    

    function addCardAnak(countCard){
    const cards_anak = '' + 
    '' + 
    '<div class="card card-default">' + 
    '    <div class="card-header">' + 
    '      <h3 class="card-title">Anak Ke-'+countCard.toString()+'</h3>' + 
    '' + 
    '      <div class="card-tools">' + 
    '        <button type="button" class="btn btn-tool" data-card-widget="collapse">' + 
    '          <i class="fas fa-minus"></i>' + 
    '        </button>' + 
    '        <button type="button" class="btn btn-tool" data-card-widget="remove" onclick=removeAnak()>' + 
    '          <i class="fas fa-times"></i>' + 
    '        </button>' + 
    '      </div>' + 
    '    </div>' + 
    '    <!-- /.card-header -->' + 
    '    <div class="card-body">' + 
    '        <div class="form-row">' + 
    '            <div class="form-group col-md-6">' + 
    '              <label for="inputNama">Nama Lengkap</label>' + 
    '              <input type="text" class="form-control" id="inputNamaAnak'+countCard.toString()+'" placeholder="Nama">' + 
    '            </div>' + 
    '            <div class="form-group col-md-3">' + 
    '                <label for="inputMarga">Marga</label>' + 
    '                <input type="text" class="form-control" id="inputMargaAnak'+countCard.toString()+'" placeholder="Marga">' + 
    '              </div>' + 
    '            <div class="form-group col-md-3">' + 
    '                <label for="inputMarga">Gender</label>' + 
    '                <div class="form-check">' + 
    '                    <input class="form-check-input radio-inline" type="radio" name="inputGenderAnak'+countCard.toString()+'" id="inputGenderAnakLaki'+countCard.toString()+'" value="Laki-laki">' + 
    '                    <label class="form-check-label">Laki-laki' + 
    '                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' + 
    '                    </label>' + 
    '                    ' + 
    '                    <input class="form-check-input radio-inline" type="radio" name="inputGenderAnak'+countCard.toString()+'" id="inputGenderAnakPerempuan'+countCard.toString()+'" value="Perempuan">' + 
    '                    <label class="form-check-label">Perempuan</label>' + 
    '                   ' + 
    '                </div>' + 
    '            </div>' + 
    '        </div>' + 
    '        <div class="form-row">' + 
    '            <div class="form-group col-md-3">' + 
    '              <label for="tempatLahir">Tempat Lahir</label>' + 
    '              <input type="text" class="form-control" id="inputTempatLahirAnak'+countCard.toString()+'" placeholder="Tempat Lahir">' + 
    '            </div>' + 
    '            <div class="form-group col-md-3">' + 
    '                <label>Tanggal Lahir</label>' + 
    '                <div class="input-group date" data-target-input="nearest">' + 
    '                    <input type="text" id="inputTgllahirAnak" class="form-control datetimepicker-input" data-target="#inputTgllahirAnak"/>' + 
    '                    <div class="input-group-append" data-target="#inputTgllahirAnak" data-toggle="datetimepicker">' + 
    '                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>' + 
    '                    </div>' + 
    '                </div>' + 
    '            </div>' + 
    '            <div class="form-group col-md-3">' + 
    '                <label>Golongan Darah</label>' + 
    '                <select id="inputGolDarahAnak" class="form-control select2" style="width: 100%;">' + 
    '                  <option disabled="disabled" selected="selected">-PILIH-</option>' + 
    '                  <option value="A">A</option>' + 
    '                  <option value="B">B</option>' + 
    '                  <option value="O">O</option>' + 
    '                  <option value="AB">AB</option>' + 
    '                </select>' + 
    '            </div>' + 
    '            <div class="form-group col-md-3">' + 
    '                <label>Hubungan Keluarga</label>' + 
    '                <select id="inputHubKeluargaAnak" class="form-control select2" style="width: 100%;">' + 
    '                  <option disabled="disabled" selected="selected">-PILIH-</option>' + 
    '                  <option value="Kepala Keluarga">Kepala Keluarga</option>' + 
    '                  <option value="Istri">Istri</option>' + 
    '                  <option value="Anak">Anak</option>' + 
    '                </select>' + 
    '            </div>' + 
    '        </div>' + 
    '' + 
    '        <div class="form-row">' + 
    '            <div class="form-group col-md-3">' + 
    '                <label>Pendidikan Terakhir</label>' + 
    '                <select id="inputPendidikanAnak"class="form-control select2" style="width: 100%;">' + 
    '                  <option disabled="disabled" selected="selected">-PILIH-</option>' + 
    '                  <option value="SD">SD</option>' + 
    '                  <option value="SMP">SMP</option>' + 
    '                  <option value="SMA">SMA</option>' + 
    '                  <option value="D3">D3</option>' + 
    '                  <option value="S1">S1</option>' + 
    '                  <option value="S2">S2</option>' + 
    '                  <option value="S3">S3</option>' + 
    '                </select>' + 
    '            </div>' + 
    '            <div class="form-group col-md-3">' + 
    '                <label>Pekerjaan</label>' + 
    '                <select id="inputPekerjaanAnak"class="form-control select2" style="width: 100%;">' + 
    '                  <option disabled="disabled" selected="selected">-PILIH-</option>' + 
    '                  <option value="Pegawai Swasta">Pegawai Swasta</option>' + 
    '                  <option value="Pegawai Negeri Sipil">Pegawai Negeri Sipil</option>' + 
    '                  <option value="Ibu Rumah Tangga">Ibu Rumah Tangga</option>' + 
    '                  <option value="Wirausaha">Wirausaha</option>' + 
    '                  <option value="Belum Bekerja">Belum Bekerja</option>' + 
    '                </select>' + 
    '            </div>' + 
    '            <div class="form-group col-md-3">' + 
    '                <label for="inputTalentaAnak">Talenta</label>' + 
    '                <input type="text" class="form-control" id="inputTalentaIstri" placeholder="Talenta">' + 
    '            </div>' + 
    '            <div class="form-group col-md-3">' + 
    '                <label for="inputNohpAnak">No Handphone</label>' + 
    '                <input type="text" class="form-control" id="inputNohpIstri" placeholder="No Hp">' + 
    '            </div>' + 
    '        </div>' + 
    '' + 

    '        <div class="form-row">' + 
    '            <div class="form-group col-md-3">' + 
    '                <label>Tanggal Baptis</label>' + 
    '                <div class="input-group date" data-target-input="nearest">' + 
    '                    <input type="text" id="inputTglBaptisAnak" class="form-control datetimepicker-input" data-target="#inputTglBaptisAnak"/>' + 
    '                    <div class="input-group-append" data-target="#inputTglBaptisAnak" data-toggle="datetimepicker">' + 
    '                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>' + 
    '                    </div>' + 
    '                </div>' + 
    '            </div>' + 
    '            <div class="form-group col-md-3">' + 
    '                <label for="gerejaBaptis">Gereja Tempat Baptis</label>' + 
    '                <input type="text" class="form-control" id="inputGerejaBaptisAnak" placeholder="Gereja Tempat Baptis ?">' + 
    '            </div>' + 
    '            <div class="form-group col-md-6">' + 
    '                <label for="pendetaBaptis">Pendeta Yang Melayani Baptis</label>' + 
    '                <input type="text" class="form-control" id="inputPendetaBaptisAnak" placeholder="Pendeta Yang Melayani Baptis ?">' + 
    '            </div>' + 
    '        </div>' + 
    '' + 
    '        <div class="form-row">' + 
    '            <div class="form-group col-md-3">' + 
    '                <label>Tanggal Sidi</label>' + 
    '                <div class="input-group date" data-target-input="nearest">' + 
    '                    <input type="text" id="inputTglSidiAnak" class="form-control datetimepicker-input" data-target="#inputTglSidiAnak"/>' + 
    '                    <div class="input-group-append" data-target="#inputTglSidiAnak" data-toggle="datetimepicker">' + 
    '                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>' + 
    '                    </div>' + 
    '                </div>' + 
    '            </div>' + 
    '            <div class="form-group col-md-3">' + 
    '                <label for="gerejaSidi">Gereja Tempat Sidi</label>' + 
    '                <input type="text" class="form-control" id="inputGerejaSidiAnak" placeholder="Gereja Tempat Sidi ?">' + 
    '            </div>' + 
    '            <div class="form-group col-md-3">' + 
    '                <label for="pendetaSidi">Pendeta Yang Melayani Sidi</label>' + 
    '                <input type="text" class="form-control" id="inputPendetaSidiAnak" placeholder="Pendeta Yang Melayani Sidi ?">' + 
    '            </div>' + 
    '            <div class="form-group col-md-3">' + 
    '                <label for="natsSidi">Nats Sidi</label>' + 
    '                <input type="text" class="form-control" id="inputNatsSidiAnak" placeholder="Nats Sidi ?">' + 
    '            </div>' + 
    '        </div>' + 
    '' + 

    '        <div class="form-row">' + 
    '            <div class="form-group col-md-4">' + 
    '                <label for="unggahFoto">Unggah Foto</label>' + 
    '                <input type="file" class="form-control-file" id="inputUnggahFotoAnak">' + 
    '            </div>' + 
    '        </div>' + 
    '' + 
    '' + 
    '    </div>' + 
    '' + 
    '  </div>' + 
    '';

    const newContent = document.createElement('div');
    newContent.innerHTML = cards_anak;
    document.getElementById('addcontent').appendChild(newContent);
    $("#add-keluarga").modal("hide"); //hide modal setelah append card baru
    }
</script>