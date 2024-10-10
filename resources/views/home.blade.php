@extends('layouts.main')

@section('content')
    <div class="banner">
        <h1>Castle Siege</h1>
        <p id="cs-guild-owner">Owner Guild:
            @if (isset($CSOwner))
            {{$CSOwner}}
            @else
            Castle Siege sem dono
            @endif
        </p>
        <p id="cs-next-battle">Next Battle: [Date]</p>
    </div>
    <section class="news">
        <h2>Recent News</h2>
        <div class="news-item">News 1</div>
        <div class="news-item">News 2</div>
        <div class="news-item">News 3</div>
        <div class="news-item">News 4</div>
        <div class="news-item">News 5</div>
        <div class="news-item">News 1</div>
        <div class="news-item">News 2</div>
        <div class="news-item">News 3</div>
        <div class="news-item">News 4</div>
        <div class="news-item">News 5</div>
        <div class="news-item">News 1</div>
        <div class="news-item">News 2</div>
        <div class="news-item">News 3</div>
        <div class="news-item">News 4</div>
        <div class="news-item">News 5</div>
        <div class="news-item">News 1</div>
        <div class="news-item">News 2</div>
        <div class="news-item">News 3</div>
        <div class="news-item">News 4</div>
        <div class="news-item">News 5</div>
        <div class="news-item">News 1</div>
        <div class="news-item">News 2</div>
        <div class="news-item">News 3</div>
        <div class="news-item">News 4</div>
        <div class="news-item">News 5</div>
        <a href="/forum" style="float: right;">More News</a>
    </section>
@endsection
