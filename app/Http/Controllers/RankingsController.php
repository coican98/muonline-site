<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class RankingsController extends Controller
{
    public function index(){
        return view('rankings', ['rankingTypes' => $this->rankingTypes()]);
    }
    public function searchRankings(Request $request){
        $type = $request->input('ranking-event');
        if ($type === 'resets' || $type === 'master_resets') {
            $column = $type === 'resets' ? 'ResetCount' : 'MasterResetCount';
            $rankingData = DB::table('Character')
                ->select(['Name', $column . ' as Score'])
                ->where('CtlCode', 0)
                ->where($column, '>', 0)
                ->orderByDesc($column)
                ->orderBy('Name')
                ->limit(50)
                ->get()
                ->toArray();

            return view('rankings', [
                'rankingData' => $rankingData,
                'rankingTitle' => $type === 'resets' ? 'Ranking de Resets' : 'Ranking de Master Resets',
                'rankingTypes' => $this->rankingTypes(),
            ]);
        }

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
        abort_unless(isset($eventList[$type]), 404);
        $rankingList = $eventList[$type];
        // dd($rankingList);
        $rankingData = DB::table($rankingList)->orderBy('Score', 'desc')->get()->toArray();

        // return view('partials.rankingResults',['rankingData' =>  $rankingData]);
        return view('rankings', [
            'rankingData' => $rankingData,
            'rankingTitle' => 'Ranking de Eventos',
            'rankingTypes' => $this->rankingTypes(),
        ]);
    }

    private function rankingTypes(): array
    {
        return [
            'resets' => 'Resets',
            'master_resets' => 'Master Resets',
            0 => 'Battle Royale',
            1 => 'Blood Castle',
            2 => 'Chaos Castle',
            3 => 'Devil Square',
            6 => 'Illusion Temple',
        ];
    }
}
