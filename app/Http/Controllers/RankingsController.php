<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class RankingsController extends Controller
{
    public function index(){
        return view('rankings');
    }
    public function searchRankings(Request $request){
        $eventList = [
            0 => 'RankingBattleRoyale',
            1 => 'RankingBloodCastle',
            2 => 'RankingChaosCastle',
            3 => 'RankingDevilSquare',
            4 => 'RankingDuel',
            5 => 'RankingGvG',
            6 => 'RankingIllusionTemple',
            7 => 'RankingKingGuild',
            8 => 'RankingKingPlayer',
            9 => 'RankingTvT'
        ];
        // dd($request);
        $rankingList = $eventList[
            $request['ranking-event']
        ];
        // dd($rankingList);
        $rankingData = DB::table($rankingList)->orderBy('Score', 'desc')->get()->toArray();

        // return view('partials.rankingResults',['rankingData' =>  $rankingData]);
        return view('rankings', ['rankingData' => $rankingData]);
    }
}
