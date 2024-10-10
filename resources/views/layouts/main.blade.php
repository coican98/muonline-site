<!DOCTYPE html>
<html lang="en">
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
    <header>
        <nav>
            <a href="/">Início</a>
            @if(!Auth::check())
                <a href="/cadastro" id="cadastro" >Cadastro</a>
            @endif
            <a href="/downloads" id="downloads">Downloads</a>
            <a href="/rankings">Rankings</a>
            @if(Auth::check())
                <a href="/vip">VIP</a>
            @endif
            <a href="/eventos">Eventos</a>
        </nav>
    </header>
    <aside class="side-menu">
    @if(!Auth::check())
        <form id="login-form" method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <a href="/forgot-password" style="color: gold; font-size: 12px;">Forgot your password?</a>
            </div>
            <button type="submit">Login</button>
        </form>
    @else
        <div id="auth-box">
            <p>Olá, {{ Auth::user()->name}}!</p>
            <ul>
                <li><a href="/account" class="account-btn">Meus personagens</a></li>
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
        <h3>Server Info</h3>
        <div class="server-info-item">
            <p>Versão:</p>
            <p>EXP:</p>
            <p>Drop:</p>
            <p></p>
            <p></p>
        </div>
        <div class="server-info-item-value">
            <p>Season 4</p>
            <p>100x</p>
            <p>60%</p>
            <p></p>
            <p></p>
        </div>
    </div>
    <div class="event-container" id="event-container">

    </div>
    </aside>
    <main class="content">
        @yield('content')
    </main>
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
