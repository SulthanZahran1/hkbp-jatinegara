<?php

use App\Http\Controllers\menuController;
use App\Http\Controllers\authController;
use App\Http\Controllers\mainController;
use App\Http\Controllers\penggunaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

//
Route::post('/authenticate', [authController::class, 'authenticate']);
Route::get('/dashboard', [mainController::class, 'dashboard']);


// Route::get('/', function () {
//     return view('layouts.master');
// });

Route::get('/testloader', function () {
    return view('content.testloader');
});

//Menu Pengguna
Route::get('/pengaturanuser/pengguna', [penggunaController::class, 'pengguna']);
Route::get('/pengaturanuser/gantipassword', function () {
    return view('content.gantiPassword');
});

Route::get('/pengaturanuser/resetpassword', function () {
    return view('content.resetPassword');
});

//Menu Data Induk
Route::get('/datainduk/sintua', function () {
    return view('content.masterSintua');
});
Route::get('/datainduk/sektor', function () {
    return view('content.masterSektor');
});
Route::get('/datainduk/profesi', function () {
    return view('content.masterProfesi');
});

//Menu Data Ruas
Route::get('/dataruas/datakeluarga', function () {
    return view('content.dataKeluarga');
});
Route::get('/dataruas/inputdatakeluarga', function () {
    return view('content.inputDataKeluarga');
});
Route::get('/dataruas/pindahsektor', function () {
    return view('content.pindahSektor');
});

//Menu Persembahan Bulanan
Route::get('/persembahanbulanan', function () {
    return view('content.persembahanBulanan');
});

//Menu Laporan
Route::get('laporan/laporandatakeluarga', function () {
    return view('content.laporanDataKeluarga');
});
Route::get('laporan/persembahanbulanan', function () {
    return view('content.laporanPersBulanan');
});


Route::get('/', function () {
    return view('content.login');
});

// Route::get('/dash', function () {
//     return view('layouts.master');
// });

// Route::get('/tables', function () {
//     return view('content.datatables');
// });

Route::get('/login', function () {
    return view('content.login');
});

// Route::get('/pengguna', function () {
//     return view('content.pengguna');
// });





Route::get('/inputkeluarga', function () {
    return view('content.inputKeluarga');
});

Route::get('/test1', [menuController::class, 'setmenu']);
