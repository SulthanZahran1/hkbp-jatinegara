<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\mainController;

class authController extends Controller
{
    public function authenticate(Request $request){
        //return 'masuk fungsi';
        $name = $request->input('usrname');
        $password = $request->input('psw');
       
        //call json token dari api
        //$response = Http::get('https://jsonplaceholder.typicode.com/todos/1');
        $response = Http::asForm()->post('https://dbruas-be.hkbpjtn.web.id/users/token', [  //hit api get token
            // 'username' => 'boasmarbun',
            // 'password' => 'pass'
            'username' => $name,
            'password' => $password
        ]);
        $data = $response->json();
        if($data['status_code'] == 200){
            $token = $data['data']['access_token'];
            session(['token' => $token]);  //save token
            $response2 = Http::withToken($token)->get('https://dbruas-be.hkbpjtn.web.id/users/me');  //hit api pengguna
            $data2 =  $response2->json();
            if($data2['status_code'] == 200){
                //simpan isi data ke session
                session(['nama_depan' => $data2['data']['nama_depan']]);
                session(['nama_belakang' => $data2['data']['nama_belakang']]);
                session(['username' => $data2['data']['username']]);
                session(['email' => $data2['data']['email']]);
                session(['created_date' => $data2['data']['created_date']]);
                session(['role_id' => $data2['data']['role_id']]);
                session(['sektor_id' => $data2['data']['sektor_id']]);
                session(['status' => $data2['data']['status']]);
                session(['id' => $data2['data']['id']]);
                session(['created_date' => $data2['data']['created_date']]);
                session(['last_access' => $data2['data']['last_access']]);
                //return session('username');
                //return redirect()->action([mainController::class, 'dashboard']);
                return redirect('/dashboard');
            }
            
            //return $data['data']['access_token'];
        }else{
            return $data['status_code'];
        }
        
        //return $data['status_code'];
        //return $data['data']['access_token'];
        //return $data['access_token'] . " " . $data['token_type'];
        //return redirect('/');
    }
}
