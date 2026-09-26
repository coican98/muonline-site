@extends('layouts.main')

@section('content')
<div class="news-single-shell" style="max-width: 850px; margin: 0 auto;">
    <div style="margin-bottom: 20px;">
        <a href="/noticias" class="btn-secondary" style="padding: 6px 14px; font-size: 11px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
            <i class="fas fa-chevron-left" style="font-size: 10px;"></i> Voltar para Notícias
        </a>
    </div>

    <article class="news-card" style="border-left-width: 5px; padding: 30px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 12px; border-bottom: 1px solid rgba(201,166,90,0.25); padding-bottom: 12px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <time style="font-size: 12px;">{{ $news->category ?? 'Servidor' }}</time>
                <span style="font-size: 13px; color: var(--steel);">
                    <i class="far fa-calendar-alt" style="margin-right: 4px; color: var(--gold);"></i>
                    {{ \Carbon\Carbon::parse($news->published_at ?? $news->created_at)->format('d/m/Y H:i') }}
                </span>
                @if(!empty($news->author_name) || !empty($news->author))
                    <span style="font-size: 13px; color: var(--steel);">
                        <i class="far fa-user" style="margin-right: 4px; color: var(--gold);"></i>
                        {{ $news->author_name ?? $news->author }}
                    </span>
                @endif
            </div>

            @if(!empty($isAdmin))
                <div style="display: flex; gap: 8px;">
                    @if($news->active)
                        <span style="background: rgba(20, 83, 45, 0.6); border: 1px solid #22c55e; color: #86efac; padding: 3px 8px; border-radius: 3px; font-size: 11px; font-weight: bold; text-transform: uppercase;">Ativo</span>
                    @else
                        <span style="background: rgba(127, 29, 29, 0.6); border: 1px solid #ef4444; color: #fca5a5; padding: 3px 8px; border-radius: 3px; font-size: 11px; font-weight: bold; text-transform: uppercase;">Inativo</span>
                    @endif

                    @if($news->show_on_home_modal)
                        <span style="background: rgba(201, 166, 90, 0.2); border: 1px solid var(--gold); color: var(--gold-bright); padding: 3px 8px; border-radius: 3px; font-size: 11px; font-weight: bold; text-transform: uppercase;">Modal Home</span>
                    @endif
                </div>
            @endif
        </div>

        <h1 style="color: var(--gold-bright); font-family: var(--font-display); font-size: 2em; line-height: 1.3; margin: 15px 0 20px;">
            {{ $news->title }}
        </h1>

        @if(!empty($news->image_url))
            <div style="margin-bottom: 25px; border-radius: 6px; overflow: hidden; border: 1px solid rgba(201,166,90,0.3); box-shadow: 0 4px 15px rgba(0,0,0,0.5);">
                <img src="{{ $news->image_url }}" alt="{{ $news->title }}" style="width: 100%; height: auto; display: block;">
            </div>
        @endif

        @if(!empty($news->summary))
            <div style="background: rgba(201,166,90,0.08); border-left: 3px solid var(--gold); padding: 12px 18px; margin-bottom: 25px; font-style: italic; color: #e2e8f0; font-size: 15px;">
                {{ $news->summary }}
            </div>
        @endif

        <div class="news-body-content" style="color: var(--text-primary); font-size: 15px; line-height: 1.8; margin-bottom: 30px;">
            {!! nl2br($news->content) !!}
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(201,166,90,0.2); padding-top: 20px; margin-top: 30px;">
            <a href="/noticias" class="btn-secondary" style="padding: 7px 16px; font-size: 12px; text-decoration: none;">
                <i class="fas fa-arrow-left" style="margin-right: 6px;"></i> Todas as Notícias
            </a>
            
            @if(!empty($isAdmin))
                <a href="/admin#news-management" class="btn-secondary" style="padding: 7px 16px; font-size: 12px; text-decoration: none;">
                    <i class="fas fa-tools" style="margin-right: 6px;"></i> Gerenciar no Painel Admin
                </a>
            @endif
        </div>
    </article>
</div>
@endsection
