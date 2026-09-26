@extends('layouts.main')

@section('content')
    <div class="hero">
        <div class="hero-copy">
            <div class="eyebrow">Evento principal</div>
            <h1>Castle Siege</h1>
            <p>Prepare sua guild para a próxima batalha pelo castelo.</p>
            <p>Apenas os mais fortes prevalecem!</p>
            <div class="hero-meta">
                <span class="hero-chip">Castelo dominado por <strong>{{ $CSOwner ?? 'Sem dono' }}</strong></span>
                <span class="hero-chip" id="cs-next-battle">Próxima batalha</span>
            </div>
        </div>
    </div>
    <section>
        <div class="section-heading">
            <h2>Acesso rápido</h2><span>Encontre o que precisa</span>
        </div>
        <div class="feature-grid">
            <a class="feature-card" href="/rankings"><i class="fas fa-crown"></i><span>
                    <h3>Rankings</h3>
                    <p>Veja os melhores jogadores e guildas.</p>
                </span></a>
            <a class="feature-card" href="/downloads"><i class="fas fa-scroll"></i><span>
                    <h3>Downloads</h3>
                    <p>Baixe o cliente e os arquivos do servidor.</p>
                </span></a>
            @if(Auth::check())
                <a class="feature-card" href="/account"><i class="fas fa-user"></i><span>
                    <h3>Meus personagens</h3>
                    <p>Gerencie seus personagens.</p>
                </span></a>
            @else
            <a class="feature-card" href="/cadastro"><i class="fas fa-shield-alt"></i><span>
                    <h3>Cadastro</h3>
                    <p>Crie sua conta e comece a jogar.</p>
                </span></a>
            @endif
        </div>
    </section>
    <section>
        <div class="section-heading" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 10px;">
            <div>
                <h2>Notícias</h2>
                <span>Últimas movimentações e comunicados do servidor</span>
            </div>
            <div>
                <a href="/noticias" class="btn-secondary" style="padding: 6px 14px; font-size: 11px; text-decoration: none; border-color: rgba(201,166,90,0.5);">
                    <i class="fas fa-newspaper" style="margin-right: 6px; color: var(--gold);"></i> Ver Todas
                </a>
            </div>
        </div>

        <div class="news-grid">
            @forelse($recentNews as $newsItem)
                <a href="/noticias/{{ $newsItem->slug ?: $newsItem->id }}" style="text-decoration: none; color: inherit; display: block;">
                    <article class="news-card" style="height: 100%; box-sizing: border-box; display: flex; flex-direction: column;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <time>{{ $newsItem->category ?? 'Servidor' }}</time>
                            <span style="font-size: 11px; color: var(--text-muted);">
                                {{ \Carbon\Carbon::parse($newsItem->published_at ?? $newsItem->created_at)->format('d/m/Y') }}
                            </span>
                        </div>
                        <h3 style="margin-bottom: 8px;">{{ $newsItem->title }}</h3>
                        <p style="flex-grow: 1;">
                            {{ $newsItem->summary ?: \Illuminate\Support\Str::limit(strip_tags($newsItem->content), 120) }}
                        </p>
                        <span style="color: var(--gold-bright); font-size: 12px; margin-top: 12px; font-weight: bold; display: inline-flex; align-items: center; gap: 5px;">
                            Ler mais <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
                        </span>
                    </article>
                </a>
            @empty
                <article class="news-card"><time>Servidor</time>
                    <h3>Fase BETA</h3>
                    <p>Data de lançamento ainda não definida, mas teremos bônus para quem participar dos testes!</p>
                </article>
                <article class="news-card"><time>Guilds</time>
                    <h3>Prepare sua guild</h3>
                    <p>Bônus ao trazer a sua guild com 10 ou mais membros!</p>
                </article>
                <article class="news-card"><time>Eventos</time>
                    <h3>Confira os horários</h3>
                    <p>Acompanhe os eventos ativos no painel lateral em tempo real.</p>
                </article>
            @endforelse
        </div>
    </section>

    {{-- MODAL AUTOMÁTICO DE NOTÍCIA EM DESTAQUE NA PÁGINA INICIAL COM EFEITO DE CHAMAS --}}
    @if(isset($homeModalNews) && $homeModalNews)
        <div id="home-news-modal-overlay" class="news-modal-overlay" onclick="handleNewsOverlayClick(event)">
            <div class="news-modal-flame" onclick="event.stopPropagation()">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1px solid rgba(255, 90, 0, 0.4); padding-bottom: 12px; margin-bottom: 16px;">
                    <div>
                        <span style="color: #ff6a00; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 2px; font-family: var(--font-display); text-shadow: 0 0 10px rgba(255,106,0,0.6);">
                            <i class="fas fa-fire-alt" style="margin-right: 5px;"></i> {{ $homeModalNews->category ?? 'Comunicado' }}
                        </span>
                        <h2 style="color: var(--gold-bright); font-family: var(--font-display); margin: 6px 0 0; font-size: 1.5em; text-shadow: 0 2px 8px rgba(0,0,0,0.8);">
                            {{ $homeModalNews->title }}
                        </h2>
                    </div>
                    <button type="button" onclick="closeHomeNewsModal()" style="background: rgba(255,69,0,0.1); border: 1px solid rgba(255,69,0,0.3); border-radius: 4px; color: #ff8c00; font-size: 16px; cursor: pointer; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; transition: all 0.2s;" onmouseover="this.style.background='rgba(255,69,0,0.3)'; this.style.color='#fff';" onmouseout="this.style.background='rgba(255,69,0,0.1)'; this.style.color='#ff8c00';" title="Fechar">✕</button>
                </div>

                @if(!empty($homeModalNews->image_url))
                    <div style="margin-bottom: 18px; border-radius: 4px; overflow: hidden; max-height: 260px; border: 1px solid rgba(255,90,0,0.3); box-shadow: 0 4px 15px rgba(0,0,0,0.7);">
                        <img src="{{ $homeModalNews->image_url }}" alt="{{ $homeModalNews->title }}" style="width: 100%; height: auto; object-fit: cover; display: block;">
                    </div>
                @endif

                <div class="news-body-content" style="margin-bottom: 22px; max-height: 320px; overflow-y: auto; padding-right: 8px;">
                    @if(!empty($homeModalNews->summary))
                        <div style="background: rgba(255, 90, 0, 0.08); border-left: 3px solid #ff5a1f; padding: 10px 14px; margin-bottom: 14px; font-style: italic; color: #fed7aa; font-size: 14px;">
                            {!! $homeModalNews->summary !!}
                        </div>
                    @endif

                    {{-- Permite formatação rica em HTML com quebras de linha e tags --}}
                    {!! nl2br($homeModalNews->content) !!}
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(255, 90, 0, 0.3); padding-top: 16px;">
                    <a href="/noticias/{{ $homeModalNews->slug ?: $homeModalNews->id }}" class="btn-primary" style="padding: 9px 20px; font-size: 13px; text-decoration: none; box-shadow: 0 0 12px rgba(255,69,0,0.4);">
                        <i class="fas fa-book-open" style="margin-right: 6px;"></i> Ler Notícia Completa
                    </a>
                    <button type="button" class="btn-secondary" onclick="closeHomeNewsModal()" style="padding: 9px 18px; font-size: 12px;">
                        Fechar
                    </button>
                </div>
            </div>
        </div>

        <script>
            // Abre com animação de entrada suave
            window.addEventListener('DOMContentLoaded', () => {
                const overlay = document.getElementById('home-news-modal-overlay');
                if (overlay) {
                    setTimeout(() => {
                        overlay.classList.add('active');
                    }, 120);
                }
            });

            function closeHomeNewsModal() {
                const overlay = document.getElementById('home-news-modal-overlay');
                if (overlay) {
                    overlay.classList.remove('active');
                    setTimeout(() => {
                        overlay.style.display = 'none';
                    }, 350);
                }
            }

            function handleNewsOverlayClick(event) {
                if (event.target.id === 'home-news-modal-overlay') {
                    closeHomeNewsModal();
                }
            }

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeHomeNewsModal();
                }
            });
        </script>
    @endif
@endsection