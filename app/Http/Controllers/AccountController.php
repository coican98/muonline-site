<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use DB;

use App\Services\PlayerDisconnectService;
class AccountController extends Controller
{
    public function index(){
        if(Auth::check()){
            $userData = $this->getCharacters();
            return view('account',['characterData' => $userData]);
        }else{
            return redirect()->back()-with('error', 'Esta página está restrita a usuários logados!');
        }
    }

    public function getCharacters(){
        $authUser = Auth::user()->username;
        $characters = DB::table('AccountCharacter')->where('Id', $authUser)->get()->first();
        if (!$characters) {
            return [];
        }
        $characterList = [$characters->GameID1,$characters->GameID2,$characters->GameID3,$characters->GameID4,$characters->GameID5];
        $characterClassCodes = [
            0=>'Dark Wizard',
            1=>'Soul Master',
            2=>'Grand Master',
            3=>'Soul Wizard',
            16=>'Dark Knight',
            17=>'Blade Knight',
            18=>'Blade Master',
            19=>'Dragon Knight',
            32=>'Elf',
            33=>'Muse Elf',
            34=>'High Elf',
            35=>'Noble Elf',
            48=>'Magic Gladiator',
            50=>'Duel Master',
            51=>'Magic Knight',
            64=>'Dark Lord',
            66=>'Lord Emperor',
            67=>'Empire Lord',
            80=>'Summoner',
            81=>'Bloody Summoner',
            82=>'Dimension Master',
            83=>'Dimension Summoner',
            96=>'Rage Fighter',
            98=>'Fist Master',
            99=>'Fist Blazer',
            112=>'Grow Lancer',
            114=>'Mirage Lancer',
            115=>'Shining Lancer',
            128=>'Rune Wizard',
            129=>'Rune Spell Master',
            130=>'Grand Rune Master',
            131=>'Majestic Rune Wizard',
            144=>'Slayer',
            145=>'Royal Slayer',
            146=>'Master Slayer',
            147=>'Slaughterer',
            160=>'Gun Crusher',
            161=>'Gun Breaker',
            162=>'Master Gun Breaker',
            163=>'Highest Gun Crusher',
            176=>'White Mage: Kundun',
            177=>'Light Master',
            178=>'Shine Wizard',
            179=>'Luminous Wizard',
            192=>'Wizard: Lemuria',
            193=>'Warmage',
            194=>'Archmage',
            195=>'Mystic Mage',
        ];
        foreach($characterList as $characterData){
            if (!$characterData) {
                continue;
            }
            $characterStatus = DB::table('Character')->where('Name', $characterData)->get()->first();
            $characterMasterLevel = DB::table('MasterSkillTree')->where('Name', $characterData)->get()->first();
            if (!$characterStatus) {
                continue;
            }
            $character[] = [
                'name' => $characterStatus->Name,
                'level' => $characterStatus->cLevel,
                'masterlevel' => $characterMasterLevel->MasterLevel ?? 0,
                'class' => $characterClassCodes[$characterStatus->Class] ?? 'Desconhecida',
                'resets' => $characterStatus->ResetCount,
                'masterresets' => $characterStatus->MasterResetCount,
            ];
        }

        return $character;
    }

    public function disconnect(Request $request, PlayerDisconnectService $disconnectService)
    {
        if (!Auth::check()) {
            return redirect('/')->with('error', 'Faça login para continuar.');
        }

        try {
            $disconnectService->disconnect(Auth::user()->username);
            return redirect()->route('account')->with('success', 'Sua conta foi desconectada do jogo.');
        } catch (\Throwable $exception) {
            report($exception);
            return redirect()->route('account')->with('error', $exception->getMessage());
        }
    }

    public function settings()
    {
        return Auth::check()
            ? view('account-settings', ['user' => Auth::user()])
            : redirect('/')->with('error', 'Faça login para continuar.');
    }

    public function updateSettings(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/')->with('error', 'Faça login para continuar.');
        }

        $user = Auth::user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'personal_id' => ['required', 'string', 'max:7'],
            'email' => ['required', 'email', 'max:255', 'unique:MEMB_INFO,mail_addr,' . $user->getKey() . ',memb_guid'],
            'phone' => ['required', 'string', 'max:15'],
            'current_password' => ['required', 'string'],
            'password' => ['nullable', 'string', 'min:4', 'max:20', 'same:password_confirmation'],
            'password_confirmation' => ['nullable', 'string'],
        ]);

        if (!hash_equals((string) $user->memb__pwd, (string) $validated['current_password'])) {
            return back()->withErrors(['current_password' => 'A senha atual está incorreta.'])->withInput();
        }

        $data = [];
        $changes = [
            'memb_name' => $validated['name'],
            'sno__numb' => $validated['personal_id'],
            'mail_addr' => $validated['email'],
            'tel__numb' => $validated['phone'],
        ];
        foreach ($changes as $column => $value) {
            if ((string) $user->{$column} !== (string) $value) {
                $data[$column] = $value;
            }
        }
        if (!empty($validated['password'])) {
            $data['memb__pwd'] = $validated['password'];
        }

        if ($data !== []) {
            DB::table('MEMB_INFO')->where('memb_guid', $user->getKey())->update($data);
        }
        Auth::setUser($user->fresh());

        return back()->with('success', $data === []
            ? 'Nenhuma alteração foi necessária.'
            : 'Dados da conta atualizados com sucesso.');
    }
}
