<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class menuController extends Controller
{
    public function setmenu(){

        $data = [
           'menus' => [
                [
                    'name'      => 'Dashboard',
                    'href'      => 'http://',
                    'submenu'   => null
                ],
                [
                    'name'      => 'Master Data',
                    'href'      => 'http://',
                    'submenu'   => null
                ],
                [
                    'name'      => 'Laporan',
                    'href'      => 'http://',
                    'submenu'   =>  [
                        'menu1' => 'Data Keluarga',
                        'menu2' => 'Perpindahan Sektor',
                        'menu3' => 'Perpindahan Huria'
                    ]
                ]
           ]
      
        ];

        $data2 = [
            [
                'name'      => 'Dashboard',
                'href'      => 'http://'
            ],
            [
                'name'      => 'Master Data',
                'href'      => 'http://'
            ],
            [
                'name'      => 'Data Ruas',
                'href'      => 'http://',
                'submenu'   =>  [
                    'menu1' => 'Data Keluarga',
                    'menu2' => 'Perpindahan Sektor',
                    'menu3' => 'Perpindahan Huria'
                ]
            ]
            
        ];

        $menu = json_encode($data);
        //echo $menu;
        $menus = json_decode($menu, true);
        //var_dump($menus['menus'][0]['name']);

        return view('testing')->with('menu',$data);

        // $nama = "Badia Felix";
    	// return view('testing',['nama' => $data]);
       
        
        //return 'Masuk halaman home';
        //return $data;
        //return view('layouts.sidebar')->with($data);

    }
}
