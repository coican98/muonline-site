@extends('layouts.main')
@section('content')

<div class="ranking-filter">
    <form action="{{route('searchRankings')}}" method="POST" class="register-form">
        @csrf
        <div>
            <label for="ranking-event">Evento</label>
            <select name="ranking-event" id="ranking-event">
                <option id="default-option" value="select" selected disabled hidden>Selecione</option>
                <option value="1">Blood Castle</option>
                <option value="2">Chaos Castle</option>
                <option value="3">Devil Square</option>
                <option value="0">Battle Royale</option>
                <option value="6">Illusion Temple</option>
            </select>
        </div>
        {{-- <div>
            <label for="ranking-period">Período</label>
            <select name="ranking-period" id="ranking-period">
                <option value="select" selected disabled hidden>Selecione</option>
                <option value="ranking-monthly">Mensal</option>
                <option value="ranking-weekly">Semanal</option>
                <option value="ranking-daily">Diário</option>
            </select>
        </div> --}}
        <button id="ranking-search" type="submit" disabled>Visualizar</button>
    </form>
    @if (isset($rankingData))
        @include('partials.rankingResults')
    @endif
</div>

@endsection