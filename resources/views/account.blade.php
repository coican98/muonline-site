@extends('layouts.main')
@section('content')

<div class="container-main">
@if(isset($characterData))
<div class="section-heading">
    <h2>Meus Personagens</h2>
    <span>Gerencie os heróis da sua conta</span>
</div>

<div class="admin-card admin-card--danger account-action-panel">
    <h2>Conexão do Jogo</h2>
    <p class="admin-card-desc">Você está conectado ao jogo? Desconecte para acessar alguns serviços do site.</p>
    <form action="{{ route('account.disconnect') }}" method="POST" style="display: flex; gap: 10px;">
        @csrf
        <button type="submit" class="btn-danger">Desconectar do jogo</button>
    </form>
</div>

<div class="character-container">
    @foreach ($characterData as $char)
        <div class="character-item">
            @if(in_array($char['class'], ['Dark Wizard', 'Soul Master', 'Grand Master', 'Soul Wizard']))
                <img src="{{asset('img/character-icons/sm.jpg')}}" alt="Dark Wizard" class="char-icon">
            @elseif(in_array($char['class'], ['Dark Knight', 'Blade Knight', 'Blade Master', 'Dragon Knight']))
                <img src="{{asset('img/character-icons/bk.jpg')}}" alt="Dark Knight" class="char-icon">
            @elseif(in_array($char['class'], ['Elf', 'Muse Elf', 'High Elf', 'Noble Elf']))
                <img src="{{asset('img/character-icons/elf.jpg')}}" alt="Elf" class="char-icon">
            @elseif(in_array($char['class'], ['Magic Gladiator', 'Duel Master', 'Magic Knight']))
                <img src="{{asset('img/character-icons/mg.jpg')}}" alt="Magic Gladiator" class="char-icon">
            @elseif(in_array($char['class'], ['Dark Lord', 'Lord Emperor', 'Empire Lord']))
                <img src="{{asset('img/character-icons/dl.jpg')}}" alt="Dark Lord" class="char-icon">
            @elseif(in_array($char['class'], ['Summoner', 'Bloody Summoner', 'Dimension Master', 'Dimension Summoner']))
                <img src="{{asset('img/character-icons/sum.jpg')}}" alt="Summoner" class="char-icon">
            @elseif(in_array($char['class'], ['Rage Fighter', 'Fist Master', 'Fist Blazer']))
                <img src="{{asset('img/character-icons/rf.jpg')}}" alt="Rage Fighter" class="char-icon">
            @endif
            
            <div class="char-name"><a href="#">{{$char['name']}}</a></div>
            <div class="char-class">{{$char['class']}}</div>
            
            <div class="char-level-group">
                <span class="char-level-label">Level</span>
                <span class="char-level">{{$char['level']}}</span>
            </div>
            
            <div class="char-stat">
                <label>Master Level:</label>
                <span>{{$char['masterlevel']}}</span>
            </div>
            <div class="char-stat">
                <label>Resets:</label>
                <span>{{$char['resets']}}</span>
            </div>
            <div class="char-stat">
                <label>Master Resets:</label>
                <span>{{$char['masterresets']}}</span>
            </div>
        </div>
    @endforeach
</div>

@else
<div class="character-container" style="display: block; text-align: center; padding: 40px 20px;">
    <p>Está vazio aqui... Crie um personagem dentro do jogo e ele aparecerá aqui para você poder gerenciar!</p>
</div>
@endif
</div>

@endsection