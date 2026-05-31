<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Hkbp Jatinegara - Login</title>

    <!-- Custom fonts for this template-->
    
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="{{asset('loginFile')}}/css/sb-admin-2.css" rel="stylesheet">

</head>

<body class="bg-gradient-primary">

    <div class="container">

        <!-- Outer Row -->
        <div class="row justify-content-center">
            <section class="h-100 gradient-form" >
  <div class="container py-5 h-100">
    <div class="row d-flex justify-content-center align-items-center h-100">
      <div class="col-xl-10">
        <div class="card rounded-3 text-black">
          <div class="row g-0">
            <div class="col-lg-6">
              <div class="card-body p-md-5 mx-md-4">

                <div class="text-center">
                  <img src="{{asset('img')}}/logo-hkbp.webp"
                    style="width: 90px;" alt="logo">
                  <h4 class="mt-3 mb-5 pb-1">Aplikasi HKBP Jatinegara</h4>
                </div>

                <form action="{{ url('authenticate') }}" method="POST">
                  @csrf
                  <p>Silahkan login melalui akun anda </p>

                  <div class="form-outline mb-4">
                    <input type="text" id="usrname" name="usrname" class="form-control" placeholder="Username" oninvalid="this.setCustomValidity('Masukkan username yang valid')" oninput="setCustomValidity('')" required>
                  </div>

                  <div class="form-outline mb-4">
                    {{-- <input type="password" id="psw" name="psw" class="form-control" placeholder="Password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" oninvalid="this.setCustomValidity('password terdiri dari 8 karakter, terdapat 1 angka, huruf besar dan huruf kecil')" oninput="setCustomValidity('')" onchange="setCustomValidity('')" required> --}}
                    <input type="password" id="psw" name="psw" class="form-control" placeholder="Password" required>
                  </div>

                  <div class="text-center pt-1 mb-5 pb-1">
                    <button class="btn btn-primary btn-block fa-lg gradient-custom-2 mb-3" type="submit">Log in</button>
                    {{-- <input type="submit" value="Submit"> --}}
                  </div>

                  <div class="d-flex align-items-center justify-content-center pb-4">
                    <p class="mb-0 me-2">Copyright 2023 HKBP Jatinegara</p>
                    <!-- <button type="button" class="btn btn-outline-danger">Create new</button> -->
                  </div>

                </form>

              </div>
            </div>
            <div class="col-lg-6 d-flex align-items-center gradient-custom-2">
              <div class="text-white px-3 py-4 p-md-5 mx-md-4">
                    <div id="carouselContent" class="carousel slide" data-ride="carousel">
                        <div class="carousel-inner" role="listbox">
                            <div class="carousel-item active text-left">
                              <h4 class="mb-4">Yohanes 12:26</h4>
                              <p class="small mb-0">"Barangsiapa melayani Aku, ia harus mengikut Aku dan di mana Aku berada, 
                                di situ pun pelayan-Ku akan berada. Barangsiapa melayani Aku, ia akan dihormati Bapa."</p>
                            </div>
                            <div class="carousel-item text-left">
                              <h4 class="mb-4">Roma 14:17-18</h4>
                              <p class="small mb-0">"Sebab Kerajaan Allah bukanlah soal makanan dan minuman, 
                                tetapi soal kebenaran, damai sejahtera dan sukacita oleh Roh Kudus. Karena 
                                barangsiapa melayani Kristus dengan cara ini, ia berkenan pada Allah dan 
                                dihormati oleh manusia."</p>
                            </div>
                            <div class="carousel-item text-left">
                              <h4 class="mb-4">Galatia 5:13</h4>
                              <p class="small mb-0">"Saudara-saudara, memang kamu telah dipanggil untuk merdeka. 
                                Tetapi janganlah kamu mempergunakan kemerdekaan itu sebagai kesempatan untuk kehidupan dalam dosa, 
                                melainkan layanilah seorang akan yang lain oleh kasih."</p>
                            </div>
                        </div>
 
                    </div>
                

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

        </div>

    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="{{asset('loginFile')}}/vendor/jquery/jquery.min.js"></script>
    <script src="{{asset('loginFile')}}/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Custom scripts for all pages-->
    {{-- <script src="{{asset('loginFile')}}/js/sb-admin-2.min.js"></script> --}}

</body>

</html>