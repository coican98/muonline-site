<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

class VipController extends Controller
{
    public function index(Request $request){
        if(Auth::check()){
            return view('vip');
        }else{
            return redirect('/')->with('error', 'Esta página está restrita a usuários que estão logados!');
        }

    }
}
