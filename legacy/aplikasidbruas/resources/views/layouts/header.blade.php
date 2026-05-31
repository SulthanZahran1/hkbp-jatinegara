<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>HKBP Jatinegara | </title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="{{asset('adminlte')}}/plugins/fontawesome-free/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Tempusdominus Bootstrap 4 -->
  <link rel="stylesheet" href="{{asset('adminlte')}}/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <!-- iCheck -->
  <link rel="stylesheet" href="{{asset('adminlte')}}/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- JQVMap -->
  <link rel="stylesheet" href="{{asset('adminlte')}}/plugins/jqvmap/jqvmap.min.css">
  
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="{{asset('adminlte')}}/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="{{asset('adminlte')}}/plugins/daterangepicker/daterangepicker.css">
  <!-- summernote -->
  <link rel="stylesheet" href="{{asset('adminlte')}}/plugins/summernote/summernote-bs4.min.css">
  <!-- DataTables -->
  <link rel="stylesheet" href="{{asset('adminlte')}}/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="{{asset('adminlte')}}/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
  <link rel="stylesheet" href="{{asset('adminlte')}}/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
  
  <!-- ddddd -->
  <!-- Bootstrap Color Picker -->
  <link rel="stylesheet" href="{{asset('adminlte')}}/plugins/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css">
  <!-- Select2 -->
  <link rel="stylesheet" href="{{asset('adminlte')}}/plugins/select2/css/select2.min.css">
  <link rel="stylesheet" href="{{asset('adminlte')}}/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
  <!-- Bootstrap4 Duallistbox -->
  <link rel="stylesheet" href="{{asset('adminlte')}}/plugins/bootstrap4-duallistbox/bootstrap-duallistbox.min.css">
  <!-- BS Stepper -->
  <link rel="stylesheet" href="{{asset('adminlte')}}/plugins/bs-stepper/css/bs-stepper.min.css">
  <!-- dropzonejs -->
  <link rel="stylesheet" href="{{asset('adminlte')}}/plugins/dropzone/min/dropzone.min.css">
  <!-- ddddd -->

  <!-- Theme style -->
  <link rel="stylesheet" href="{{asset('adminlte')}}/dist/css/adminlte.min.css">
  <style>

    #loader {
      /* display: none; */
      position: fixed;
			border: 12px solid #1d7e8e;
			border-radius: 50%;
			border-top: 12px solid #e24821;
			width: 70px;
			height: 70px;
			animation: spin 1s linear infinite;
      z-index: 99999;
      overflow: hidden
		}

    #bgloader{
      /* display: none; */
      width: 100%;
      height: 100%;
      position: fixed;
      background: #f5f2f2;
      opacity: 0.9;
      z-index: 99998;
    }

		.center {
			top: 0;
			bottom: 0;
			left: 0;
			right: 0;
			margin: auto;
      
		}

		@keyframes spin {
			100% {
				transform: rotate(360deg);
			}
		}

  </style>

</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div id="bgloader"></div><!-- loading page -->
<div id="loader" class="center"></div> <!-- loading page -->
<div class="wrapper">

  <!-- Preloader -->
  {{-- <div class="preloader flex-column justify-content-center align-items-center">
    <img class="animation__shake" src="{{asset('adminlte')}}/dist/img/AdminLTELogo.png" alt="AdminLTELogo" height="60" width="60">
  </div> --}}