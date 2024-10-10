<?php

namespace App\Http\Controllers;

use Hash;
use Illuminate\Http\Request;
use DB;
use Auth;
use App\Models\User;
use Log;

class RegisterController extends Controller
{
    public function register(Request $request){
        $validated = $request->validate([
            'username1' => 'required|unique:MEMB_INFO,memb___id|max:20',
            'email1' => 'required|email|unique:MEMB_INFO,mail_addr|max:255',
            'email2' => 'required|same:email1',
            'password1' => 'required|min:4|max:20',
            'password2' => 'required|same:password1',
            'name' => 'required|string',
            'eula' => 'accepted',
            'userCode' => 'required|max:7',
            'phone' => 'required|max:15',
        ]);

        $insertMembData = DB::table('MEMB_INFO')->insertGetId([
            'memb___id'=>$request->username1,
            'memb__pwd'=>$request->password1,
            'memb_name'=>$request->name,
            'mail_addr'=>$request->email1,
            'ctl1_code'=>1,
            'AccountLevel'=>1,
            'AccountExpireDate'=>now()->addDays(3),
            'sno__numb'=>$request->userCode,
            'bloc_code'=>0,
            'mail_chek'=>0,
            'phon_numb'=> $request->phone,
        ]);

        if($insertMembData){
            $user = User::create([
                'id'=> $insertMembData,
                'username'=>$request->username1,
                'password'=>Hash::make($request->password1),
                'name'=>$request->name,
                'created_at'=>now(),
            ]);
        }

        if($user){
            return redirect()->route('home')->with('success', "Conta {$user->name} criada com sucesso!");
        }else{
            return redirect()->back()->with("error",'Houve um erro no cadastro');
        }
    }

    public function registerPage (){
        if(Auth::check()){
            return redirect('/');
        }else{
            $title = 'Mu Rootz - Cadastro';
            return view('register',compact('title'));
        }
    }
}
