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
        <div class="section-heading">
            <h2>Notícias</h2><span>Últimas movimentações do servidor</span>
        </div>
        <div class="news-grid">
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
        </div>
    </section>
@endsection