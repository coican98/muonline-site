<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Auth;

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

        $settingsPath = storage_path('app/settings.json');
        $settings = file_exists($settingsPath) ? json_decode(file_get_contents($settingsPath), true) : [];
        $regBonus = $settings['registration_bonus'] ?? [
            'enabled' => true,
            'vip_type' => 1,
            'vip_days' => 3,
            'coin1' => 0,
            'coin2' => 0,
            'coin3' => 0,
        ];

        $accountLevel = 0;
        $expireDate = now();
        if (!empty($regBonus['enabled']) && !empty($regBonus['vip_type'])) {
            $accountLevel = (int)$regBonus['vip_type'];
            $days = (int)($regBonus['vip_days'] ?? 0);
            $expireDate = now()->addDays($days);
        }

        $created = DB::table('MEMB_INFO')->insert([
            'memb___id' => $request->username1,
            'memb__pwd' => $request->password1,
            'memb_name' => $request->name,
            'mail_addr' => $request->email1,
            'ctl1_code' => 1,
            'AccountLevel' => $accountLevel,
            'AccountExpireDate' => $expireDate,
            'sno__numb' => $request->phone,
            'bloc_code' => 0,
            'mail_chek' => 0,
        ]);

        // Se houver moedas de bônus configuradas, adiciona na tabela CashShopData (se existir)
        if ($created && !empty($regBonus['enabled'])) {
            $coin1 = (int)($regBonus['coin1'] ?? 0);
            $coin2 = (int)($regBonus['coin2'] ?? 0);
            $coin3 = (int)($regBonus['coin3'] ?? 0);

            if ($coin1 > 0 || $coin2 > 0 || $coin3 > 0) {
                try {
                    // Verifica se a tabela CashShopData existe e insere/atualiza
                    DB::table('CashShopData')->updateOrInsert(
                        ['AccountID' => $request->username1],
                        ['WCoinC' => $coin1, 'WCoinP' => $coin2, 'GoblinPoint' => $coin3]
                    );
                } catch (\Throwable $e) {
                    // Log silencioso para não interromper criação caso a engine use nomes diferentes
                    report($e);
                }
            }
        }

        if ($created) {
            return redirect()->route('home')->with('success', "Conta {$request->name} criada com sucesso!");
        } else {
            return redirect()->back()->with("error", 'Houve um erro no cadastro');
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
