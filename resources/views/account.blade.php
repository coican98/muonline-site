@extends('layouts.main')
@section('content')

<div class="container-main">
@if(isset($characterData))
<h1>Personagens</h1>
<div class="character-container">
    @foreach ($characterData as $char)
        <div class="character-item">
            @if($char['class'] == 'Dark Wizard' || $char['class'] == 'Soul Master' || $char['class'] == 'Grand Master' || $char['class'] == 'Soul Wizard')
                <img src="{{asset('img/character-icons/sm.jpg')}}" alt="" class="char-icon">
            @elseif($char['class'] == 'Dark Knight' || $char['class'] == 'Blade Knight' || $char['class'] == 'Blade Master' || $char['class'] == 'Dragon Knight')
                <img src="{{asset('img/character-icons/bk.jpg')}}" alt="" class="char-icon">
            @elseif($char['class'] == 'Elf' || $char['class'] == 'Muse Elf' || $char['class'] == 'High Elf' || $char['class'] == 'Noble Elf')
                <img src="{{asset('img/character-icons/elf.jpg')}}" alt="" class="char-icon">
            @elseif($char['class'] == 'Magic Gladiator' || $char['class'] == 'Duel Master' || $char['class'] == 'Magic Knight')
                <img src="{{asset('img/character-icons/mg.jpg')}}" alt="" class="char-icon">
            @elseif($char['class'] == 'Dark Lord' || $char['class'] == 'Lord Emperor' || $char['class'] == 'Empire Lord')
                <img src="{{asset('img/character-icons/dl.jpg')}}" alt="" class="char-icon">
            @elseif($char['class'] == 'Summoner' || $char['class'] == 'Bloody Summoner' || $char['class'] == 'Dimension Master' || $char['class'] == 'Dimension Summoner')
                <img src="{{asset('img/character-icons/sum.jpg')}}" alt="" class="char-icon">
            @elseif($char['class'] == 'Rage Fighter' || $char['class'] == 'Fist Master' || $char['class'] == 'Fist Blazer')
                <img src="{{asset('img/character-icons/rf.jpg')}}" alt="" class="char-icon">
            @endif
            <b><a href="#">{{$char['name']}}</a></b>
            <div>
                <label for="char-level">Level:</label>
                <span>{{$char['level']}}</span>
            </div>
            <div>
                <label for="char-class">Classe:</label>
                <span>{{$char['class']}}</span>
            </div>
            <div>
                <label for="char-master-level">Master Level:</label>
                <span>{{$char['masterlevel']}}</span>
            </div>
            <div>
                <label for="char-resets">Resets:</label>
                <span>{{$char['resets']}}</span>
            </div>
            <div>
                <label for="char-master-resets">Master Resets:</label>
                <span>{{$char['masterresets']}}</span>
            </div>
        </div>
    @endforeach
</div>

@else
<div class="character-container">
    <p>Está vazio aqui... Crie um personagem dentro do jogo e ele aparecerá aqui para você poder gerenciar!</p>
</div>
@endif
</div>

<style>
    .account-btn{
        font-weight: bold;
    }
</style>
@endsection