<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use DB;

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
            $characterStatus = DB::table('Character')->where('Name', $characterData)->get()->first();
            $characterMasterLevel = DB::table('MasterSkillTree')->where('Name', $characterData)->get()->first();
            $character[] = [
                'name' => $characterStatus->Name,
                'level' => $characterStatus->cLevel,
                'masterlevel' => $characterMasterLevel->MasterLevel,
                'class' => $characterClassCodes[$characterStatus->Class],
                'resets' => $characterStatus->ResetCount,
                'masterresets' => $characterStatus->MasterResetCount,
            ];
        }

        return $character;
    }
}
