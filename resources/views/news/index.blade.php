@extends('layouts.main')

@section('content')
<div class="section-heading" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 15px;">
    <div>
        <h2>Notícias & Comunicados</h2>
        <span>Fique por dentro de todas as novidades, atualizações e eventos do servidor</span>
    </div>
    @if(!empty($isAdmin))
        <div>
            <a href="/admin#news-management" class="btn-primary" style="padding: 6px 14px; font-size: 12px; text-decoration: none;">
                <i class="fas fa-plus" style="margin-right: 6px;"></i> Nova Notícia
            </a>
        </div>
    @endif
</div>

<div class="news-list-container" style="margin-top: 25px; display: flex; flex-direction: column; gap: 20px;">
    @forelse($newsList as $item)
        <article class="news-card" style="border-left-width: 5px; position: relative;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 10px; margin-bottom: 8px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <time>{{ $item->category ?? 'Servidor' }}</time>
                    <span style="font-size: 12px; color: var(--steel);">
                        <i class="far fa-calendar-alt" style="margin-right: 4px; color: var(--gold);"></i>
                        {{ \Carbon\Carbon::parse($item->published_at ?? $item->created_at)->format('d/m/Y H:i') }}
                    </span>
                    @if(!empty($item->author_name) || !empty($item->author))
                        <span style="font-size: 12px; color: var(--steel);">
                            <i class="far fa-user" style="margin-right: 4px; color: var(--gold);"></i>
                            {{ $item->author_name ?? $item->author }}
                        </span>
                    @endif
                </div>

                @if(!empty($isAdmin))
                    <div style="display: flex; gap: 8px; align-items: center;">
                        @if($item->active)
                            <span style="background: rgba(20, 83, 45, 0.6); border: 1px solid #22c55e; color: #86efac; padding: 2px 7px; border-radius: 3px; font-size: 10px; font-weight: bold; text-transform: uppercase;">Ativo</span>
                        @else
                            <span style="background: rgba(127, 29, 29, 0.6); border: 1px solid #ef4444; color: #fca5a5; padding: 2px 7px; border-radius: 3px; font-size: 10px; font-weight: bold; text-transform: uppercase;">Inativo</span>
                        @endif

                        @if($item->show_on_home_modal)
                            <span style="background: rgba(201, 166, 90, 0.2); border: 1px solid var(--gold); color: var(--gold-bright); padding: 2px 7px; border-radius: 3px; font-size: 10px; font-weight: bold; text-transform: uppercase;" title="Exibida no Modal da Home">Modal Home</span>
                        @endif

                        @if($item->published_at && \Carbon\Carbon::parse($item->published_at)->isFuture())
                            <span style="background: rgba(30, 58, 138, 0.6); border: 1px solid #3b82f6; color: #93c5fd; padding: 2px 7px; border-radius: 3px; font-size: 10px; font-weight: bold; text-transform: uppercase;" title="Agendada para o futuro">Agendada</span>
                        @endif
                    </div>
                @endif
            </div>

            <h3 style="font-size: 1.4em; margin-bottom: 10px;">
                <a href="/noticias/{{ $item->slug ?: $item->id }}" style="color: var(--gold-bright); text-decoration: none;">
                    {{ $item->title }}
                </a>
            </h3>

            @if(!empty($item->image_url))
                <div style="margin: 12px 0; border-radius: 4px; overflow: hidden; max-height: 280px; border: 1px solid rgba(201,166,90,0.2);">
                    <img src="{{ $item->image_url }}" alt="{{ $item->title }}" style="width: 100%; height: auto; object-fit: cover;">
                </div>
            @endif

            <p style="color: var(--steel); font-size: 14px; line-height: 1.6; margin-bottom: 15px;">
                {{ $item->summary ?: \Illuminate\Support\Str::limit(strip_tags($item->content), 220) }}
            </p>

            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(201,166,90,0.15); padding-top: 12px; margin-top: 10px;">
                <a href="/noticias/{{ $item->slug ?: $item->id }}" class="btn-secondary" style="padding: 5px 14px; font-size: 12px; text-decoration: none;">
                    Ler Notícia Completa <i class="fas fa-arrow-right" style="margin-left: 5px; font-size: 11px;"></i>
                </a>

                @if(!empty($isAdmin))
                    <div style="display: flex; gap: 8px;">
                        <a href="/admin#news-management" class="btn-secondary" style="padding: 5px 10px; font-size: 11px; text-decoration: none;">
                            <i class="fas fa-edit"></i> Gerenciar no Painel
                        </a>
                    </div>
                @endif
            </div>
        </article>
    @empty
        <div class="news-card" style="text-align: center; padding: 40px 20px;">
            <i class="fas fa-scroll" style="font-size: 32px; color: var(--gold); margin-bottom: 12px;"></i>
            <h3 style="color: var(--gold-bright); margin-bottom: 8px;">Nenhuma notícia encontrada</h3>
            <p style="color: var(--text-muted); font-size: 14px;">Fique atento, logo novas atualizações serão postadas aqui!</p>
        </div>
    @endforelse
</div>

@if(method_exists($newsList, 'links'))
    <div style="margin-top: 25px; display: flex; justify-content: center;">
        {{ $newsList->links() }}
    </div>
@endif

@endsection
