@extends('layouts.main')
@section('content')

<section class="ranking-page">
<div class="section-heading"><h2>Rankings</h2><span>Os nomes mais fortes do continente</span></div>
<div class="ranking-filter">
    <form action="{{route('searchRankings')}}" method="POST" class="ranking-form">
        @csrf
        <div>
            <label for="ranking-event">Evento</label>
            <select name="ranking-event" id="ranking-event">
                <option id="default-option" value="select" selected disabled hidden>Selecione</option>
                @foreach($rankingTypes ?? [] as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
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
        <div class="ranking-result-title">{{ $rankingTitle ?? 'Ranking' }}</div>
        @include('partials.rankingResults')
    @endif
</div>
</section>

@endsection