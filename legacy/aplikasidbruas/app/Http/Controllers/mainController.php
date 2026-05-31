<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class mainController extends Controller
{
    public function dashboard(Request $request){
        //return "masuk dashboard";
        //return session('username');

        $data = [
            'nama' => session('nama_depan') . " " . session('nama_belakang'),
            'username' => session('username')
        ];

        //return redirect('/');
        return view('layouts.master')->with($data);
    }
    
}
