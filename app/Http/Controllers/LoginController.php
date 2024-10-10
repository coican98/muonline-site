<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cookie;
use Session;
use DB;

class LoginController extends Controller
{

    public function login(Request $request)
    {
        if ($request->isMethod('get')) {
            return view('home');
        } else {

            $credentials = [
                'username' => $request->input('username'),
                'password' => $request->input('password'),
            ];

            $user = User::where('username', $credentials['username'])->first();
            if ($user) {
                if (Hash::check($credentials['password'], $user->password)) {

                    Auth::login($user, true);
                    return redirect()->back()->with('success', '');
                } else {
                    return redirect()->back()->with('error', 'Senha incorreta. Favor verificar as credenciais novamente.');
                }
            } else {
                return redirect()->back()->with('error', 'Conta não encontrada. Favor verificar as credenciais novamente.');
            }
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        Cookie::forget('laravel_session');

        return redirect('/')->withCookie(cookie()->forget('laravel_session'));
    }
}
