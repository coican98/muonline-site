<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Mu Rootz' }}</title>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}?v={{ filemtime(public_path('css/styles.css')) }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;600;700;800&family=MedievalSharp&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    {{-- alert() nativo removido — feedback migrado para flash.blade.php --}}
</head>
<body>
    <canvas id="emberCanvas" class="ember-canvas" aria-hidden="true"></canvas>
    <header>
        <nav aria-label="Navegação principal">
            <a href="/" class="{{ request()->is('/') ? 'nav-active' : '' }}">Início</a>
            @if(!Auth::check())
                <a href="/cadastro" id="cadastro" class="{{ request()->is('cadastro') ? 'nav-active' : '' }}">Cadastro</a>
            @endif
            <a href="/downloads" id="downloads" class="{{ request()->is('downloads') ? 'nav-active' : '' }}">Downloads</a>
            <a href="/rankings" class="{{ request()->is('rankings') ? 'nav-active' : '' }}">Rankings</a>
            @if(Auth::check())
                <a href="/loja" class="{{ request()->is('loja') || request()->is('vip') ? 'nav-active' : '' }}">Loja</a>
            @endif
            <a href="/noticias" class="{{ request()->is('noticias*') ? 'nav-active' : '' }}">Notícias</a>
            <a href="/eventos" class="{{ request()->is('eventos') ? 'nav-active' : '' }}">Eventos</a>
        </nav>
    </header>

    {{-- Flash messages substituem alert() — role semântico por tipo --}}
    @include('partials.flash')

    <div class="main-layout">
        <main class="content">
            @yield('content')
        </main>

        <aside class="side-menu">
            @if(!Auth::check())
                <form id="login-form" method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="form-group">
                        <label for="username">Usuário</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Senha</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <a href="/forgot-password" style="color: gold; font-size: 12px;">Esqueceu sua senha?</a>
                    </div>
                    <button type="submit">Entrar</button>
                </form>
            @else
                <div id="auth-box">
                    <p>Olá, {{ Auth::user()->name }}!</p>
                    <ul class="user-menu">
                        <li><a href="{{ route('account.settings') }}">Minha Conta</a></li>
                        <li><a href="/account">Meus personagens</a></li>
                        @if(Auth::user()->global_admin == 1)
                            <li><a href="/admin" id="admin">Administração</a></li>
                        @endif
                        <li>
                            <button type="button" class="btn-danger" onclick="handleLogout()">Sair</button>
                        </li>
                    </ul>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            @endif

            <div class="server-info">
                <h3>Informações do Servidor</h3>
                @php
                    $settingsPath = storage_path('app/settings.json');
                    $serverInfos = [];
                    if (file_exists($settingsPath)) {
                        $sData = json_decode(file_get_contents($settingsPath), true);
                        if (isset($sData['server_infos'])) {
                            $serverInfos = $sData['server_infos'];
                        }
                    }
                    // Valores padrao garantidos se ausentes
                    if(empty($serverInfos)){
                        $serverInfos = [
                            ['label' => 'Versão', 'value' => env('SERVER_VERSION', 'Season 4'), 'visible' => true, 'fixed' => true],
                            ['label' => 'EXP', 'value' => env('SERVER_EXP', '100x'), 'visible' => true, 'fixed' => true],
                            ['label' => 'Drop', 'value' => env('SERVER_DROP', '60%'), 'visible' => true, 'fixed' => true],
                        ];
                    }
                @endphp
                <div class="server-info-item">
                    @foreach($serverInfos as $info)
                        @if(isset($info['visible']) && $info['visible'])
                            <p>{{ $info['label'] }}:</p>
                        @endif
                    @endforeach
                </div>
                <div class="server-info-item-value">
                    @foreach($serverInfos as $info)
                        @if(isset($info['visible']) && $info['visible'])
                            <p>{{ $info['value'] }}</p>
                        @endif
                    @endforeach
                </div>
            </div>
            <div class="event-container" id="event-container"></div>
        </aside>
    </div>

    @include('layouts.footer')
    <a href="#" id="goToTop" class="go-to-top" aria-label="Voltar ao topo">🡹</a>

    <script src="{{ asset('js/script.js') }}?v={{ filemtime(public_path('js/script.js')) }}"></script>
    <script>
        function handleLogout(){
            document.getElementById('logout-form').submit();
        }
    </script>
</body>
</html>
