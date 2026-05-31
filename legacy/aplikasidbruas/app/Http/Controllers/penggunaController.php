<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class penggunaController extends Controller
{
    public function __construct() {
        //echo 'coba construct';
     }

    public function pengguna(Request $request){
        //return "masuk sini";

        // $data = [
        //     'nama' => session('nama_depan') . " " . session('nama_belakang'),
        //     'username' => session('username')
        // ];

        // //return redirect('/');
        // return view('layouts.master')->with($data);
        //return view('content.loader');

        $list_sektor = '';
        $list_role = '';
        $list_all_user = '';
        
        //hit api sektor
        $response = Http::withToken(session('token'))->get('https://dbruas-be.hkbpjtn.web.id/sektor/');  //hit api sektor
        $data =  $response->json();
        if($data['status_code'] == 200){
            //return view('content.pengguna')->with('content',$data['data']);
            $list_sektor = $data['data'];
            //return view('content.pengguna')->with('content',compact(['data1', 'data1']));
            
        }else{
            //return "gagal api sektor";
            return view('content.login');
        }

        //hit api role
        $response = Http::withToken(session('token'))->get('https://dbruas-be.hkbpjtn.web.id/role');  //hit api sektor
        $data =  $response->json();
        if($data['status_code'] == 200){
            $list_role = $data['data'];
        }else{
            //return "gagal api role";
            return view('content.login');
        }

        //hit api all user
        $response = Http::withToken(session('token'))->get('https://dbruas-be.hkbpjtn.web.id/users/all');  //hit api sektor
        $data =  $response->json();
        if($data['status_code'] == 200){
            $list_all_user = $data['data'];
        }else{
            //return "gagal api all user";
            return view('content.login');
        }

        return view('content.pengguna')->with('content',compact(['list_sektor', 'list_role', 'list_all_user']));
    }
}
