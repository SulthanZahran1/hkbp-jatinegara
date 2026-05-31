
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
            <h1>GANTI PASSWORD</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Pengaturan User / Ganti Password</li>
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
                    <label for="exampleInputEmail1">Masukkan Password Lama</label>
                    <input type="password" class="form-control" id="passLama" placeholder="masukkan password lama" style="width: 40%;">
                </div>
                <div class="form-group">
                    <label for="exampleInputEmail1">Masukkan Password Baru</label>
                    <input type="password" class="form-control" id="passBaru" placeholder="masukkan password Baru" style="width: 40%;">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary float-right">Submit</button>
                </div> --}}

              
                    <div class="card-body col-sm-6">
                      <div class="form-group">
                        <label for="exampleInputEmail1">Masukkan Password Lama</label>
                        <input type="password" class="form-control" id="passLama" name="passLama" placeholder="masukkan password lama" required>
                      </div>
                      <div class="form-group">
                        <label for="exampleInputEmail1">Masukkan Password Baru</label>
                        <input type="password" class="form-control" id="passBaru" name="passBaru" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" placeholder="masukkan password Baru" required>
                        <p style="font-style: italic">Password minimal 8 karakter, mengandung huruf besar, huruf kecil dan karakter</p>
                      </div>
                      <div class="form-group">
                        <label for="exampleInputEmail1">Ulangi Masukkan Password Baru</label>
                        <input type="password" class="form-control" id="passBaruRepeat" placeholder="ulangi masukkan password Baru" required>
                      </div>
                      <div class="form-group">
                        <div id="warning-box" style="display:none">
                          <p id="letter" class="invalid">Harus terdapat huruf kecil</p>
                          <p id="capital" class="invalid">Harus terdapat huruf besar</p>
                          <p id="number" class="invalid">Harus terdapat angka</b></p>
                          <p id="length" class="invalid">Minimal 8 karakter</b></p>
                        </div>
                      </div>

                      <div class="form-group">
                        <button type="submit" id="submitPass" class="btn btn-primary float-right" onclick="validasiPassword()">Simpan</button>
                      </div>
                      
                    </div>
                    <!-- /.card-body -->
    
                  
                      
        
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
  function validasiPassword(){
    passlama = document.getElementById('passLama').value;
    passBaru = document.getElementById('passBaru').value;
    passBaruRepeat = document.getElementById('passBaruRepeat').value;
    //console.log(passlama,passBaru,passBaruRepeat);
  }

  var myInput = document.getElementById("passBaru");
  var letter = document.getElementById("letter");
  var capital = document.getElementById("capital");
  var number = document.getElementById("number");
  var length = document.getElementById("length");

  // When the user starts to type something inside the password field
  myInput.onkeyup = function() {
    // Validate lowercase letters
    document.getElementById("warning-box").style.display = "block"; //show div warning-box

    var lowerCaseLetters = /[a-z]/g;
    if(myInput.value.match(lowerCaseLetters)) {  
      letter.classList.remove("invalid");
      letter.classList.add("valid");
    } else {
      letter.classList.remove("valid");
      letter.classList.add("invalid");
      //warning.innerHTML = "harus mengandung huruf kecil";
    }
    
    // Validate capital letters
    var upperCaseLetters = /[A-Z]/g;
    if(myInput.value.match(upperCaseLetters)) {  
      capital.classList.remove("invalid");
      capital.classList.add("valid");
    } else {
      capital.classList.remove("valid");
      capital.classList.add("invalid");
    }

    // Validate numbers
    var numbers = /[0-9]/g;
    if(myInput.value.match(numbers)) {  
      number.classList.remove("invalid");
      number.classList.add("valid");
    } else {
      number.classList.remove("valid");
      number.classList.add("invalid");
    }
    
    // Validate length
    if(myInput.value.length >= 8) {
      length.classList.remove("invalid");
      length.classList.add("valid");
    } else {
      length.classList.remove("valid");
      length.classList.add("invalid");
    }
  }

</script>

<style>

  

  
  /* The message box is shown when the user clicks on the password field */
  #message {
    display:none;
    background: #f1f1f1;
    color: #000;
    position: relative;
    padding: 20px;
    margin-top: 10px;
  }
  
  #message p {
    padding: 10px 35px;
    font-size: 18px;
  }
  
  /* Add a green text color and a checkmark when the requirements are right */
  .valid {
    color: green;
  }
  
  .valid:before {
    position: relative;
    content: "✔";
  }
  
  /* Add a red text color and an "x" when the requirements are wrong */
  .invalid {
    color: red;
  }
  
  .invalid:before {
    position: relative;
    content: "✖";
  }
  </style>

