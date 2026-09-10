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
use Illuminate\Database\QueryException;

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

            try {
                $user = User::where('memb___id', $credentials['username'])->first();
                if (!$user) {
                    return redirect()->back()->with('error', 'Conta não encontrada. Favor verificar as credenciais novamente.');
                }

                if (!hash_equals((string) $user->memb__pwd, (string) $credentials['password'])) {
                    return redirect()->back()->with('error', 'Senha incorreta. Favor verificar as credenciais novamente.');
                }

                Auth::login($user, true);
                return redirect()->back()->with('success', '');
            } catch (QueryException $exception) {
                report($exception);
                return redirect()->back()->with('error', 'Não foi possível conectar ao banco do servidor. Verifique DB_HOST, DB_PORT, DB_DATABASE e DB_PASSWORD no .env.');
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
