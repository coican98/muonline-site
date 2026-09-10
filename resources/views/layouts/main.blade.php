<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Mu Rootz' }}</title>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    @if(session('error'))
        <script>alert("{{ session('error') }}");</script>
    @endif
    @if(session('success'))
            <script>alert("{{ session('success') }}");</script>
    @endif
</head>
<body>
    <div class="ember-field" aria-hidden="true"></div>
    <header>
        <a class="brand" href="/">
            <span class="brand-mark">M</span>
            <span>Mu Rootz <small>Season 4</small></span>
        </a>
        <nav>
            <a href="/">Início</a>
            @if(!Auth::check())
                <a href="/cadastro" id="cadastro" >Cadastro</a>
            @endif
            <a href="/downloads" id="downloads">Downloads</a>
            <a href="/rankings">Rankings</a>
            @if(Auth::check())
                <a href="/vip">VIP</a>
                <a href="{{ route('account.settings') }}">Minha conta</a>
            @endif
            <a href="#event-container">Eventos</a>
        </nav>
    </header>
    <div class="layout">
    <aside class="side-menu">
    @if(!Auth::check())
        <form id="login-form" method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label for="username">Conta Mu</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Senha do jogo</label>
                <input type="password" id="password" name="password" required>
            </div>
                <div class="form-group"><a href="/forgot-password">Esqueci minha senha</a></div>
            <button type="submit">Login</button>
        </form>
    @else
        <div id="auth-box">
            <p>Olá, {{ Auth::user()->name}}!</p>
            <ul>
                <li><a href="/account" class="account-btn">Meus personagens</a></li>
                <li><a href="{{ route('account.settings') }}" class="account-btn">Minha conta</a></li>
                <li><a href="/alterar-senha" class="reset-password-btn">Alterar senha</a></li>
                @if(Auth::user()->global_admin == 1)
                    <li><a href="/admin" id="admin">Administração</a></li>
                @endif
                <li>
                    <button onclick="handleLogout()">
                        Sair</button>
                </li>
            </ul>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </div>
    @endif

    <div class="server-info">
        <h3>Detalhes do servidor</h3>
        <div class="server-info-grid">
            @foreach(config('site.server_info') as $label => $value)
                <div class="server-stat"><span>{{ $label }}</span><strong>{{ $value }}</strong></div>
            @endforeach
        </div>
    </div>
    <div class="event-container" id="event-container">

    </div>
    </aside>
    <main class="content">
        @yield('content')
    </main>
    </div>
    @include('layouts.footer') 
    <a href="#" id="goToTop" class="go-to-top">🡹</a>
</body>
<script src="{{ asset('js/script.js') }}"></script>
<script>
    function handleLogout(){
        document.getElementById('logout-form').submit();
    }
</script>
</html>
