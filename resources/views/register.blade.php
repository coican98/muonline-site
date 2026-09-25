@extends('layouts.main')

@section('content')
    <div class="section-heading">
        <h2>Cadastro</h2>
        <span>Inicie sua jornada</span>
    </div>
    <form action="{{route('register')}}" method="POST" class="register-form">
        @csrf
        <div class="register-form-row">
            <label for="username1">Login:</label>
            <input type="text" id="username1" name="username1" required>
            @if ($errors->has('username1'))
                <span class="text-danger">{{ $errors->first('username1') }}</span>
            @endif
        </div>
        <div class="register-form-row">
            <label for="email1">Email:</label>
            <input type="email" id="email1" name="email1" required>
            @if ($errors->has('email1'))
                <span class="text-danger">{{ $errors->first('email1') }}</span>
            @endif
        </div>
        <div class="register-form-row">
            <label for="name">Nome:</label>
            <input type="text" id="name" name="name" required>
            @if ($errors->has('name'))
                <span class="text-danger">{{ $errors->first('name') }}</span>
            @endif
        </div>
        <div class="register-form-row">
            <label for="email2">Confirme o Email:</label>
            <input type="email" id="email2" name="email2" required>
            @if ($errors->has('email2'))
                <span class="text-danger">{{ $errors->first('email2') }}</span>
            @endif
        </div>
        <div class="register-form-row">
            <label for="password1">Senha:</label>
            <input type="password" id="password1" name="password1" required>
            @if ($errors->has('password1'))
                <span class="text-danger">{{ $errors->first('password1') }}</span>
            @endif
        </div>
        <div class="register-form-row">
            <label for="password2">Confirme a senha:</label>
            <input type="password" id="password2" name="password2" required>
            @if ($errors->has('password2'))
                <span class="text-danger">{{ $errors->first('password2') }}</span>
            @endif
        </div>
        <div class="register-form-row">
            <label for="userCode">Código de usuário:</label>
            <input type="text" id="userCode" name="userCode" required>
            @if ($errors->has('userCode'))
                <span class="text-danger">{{ $errors->first('userCode') }}</span>
            @endif
        </div>
        <div class="register-form-row">
            <label for="phone">Telefone:</label>
            <input type="text" id="phone" name="phone" required>
            @if ($errors->has('phone'))
                <span class="text-danger">{{ $errors->first('phone') }}</span>
            @endif
        </div>
        <div class="register-form-row" style="grid-column: span 2; display: flex; align-items: center;">
            <label for="eula" style="display: flex; align-items: center; margin: 0;">
                <input type="checkbox" class="eula" id="eula" name="eula" required>
                Concordo com os <button type="button" onclick="document.getElementById('eulaModal').style.display='flex'" style="background: none; border: none; padding: 0; margin-left: 5px; color: var(--gold-bright); text-decoration: underline; font-size: inherit; text-transform: none; min-height: 0; box-shadow: none; font-family: inherit; font-weight: bold;">Termos de Conduta</button>
            </label>
        </div>
        <div class="register-form-row" style="grid-column: span 2;">
            <button type="submit" class="submit btn-primary">Criar Conta</button>
        </div>
    </form>

    @php
        $settingsPath = storage_path('app/settings.json');
        $sData = [];
        if (file_exists($settingsPath)) {
            $sData = json_decode(file_get_contents($settingsPath), true);
        }
        $eulaText = isset($sData['eula_text']) ? $sData['eula_text'] : 'Termos de Conduta não disponíveis.';
    @endphp

    <div id="eulaModal" class="confirm-modal-overlay" style="display: none;">
        <div class="confirm-modal" style="max-width: 600px; max-height: 80vh; overflow-y: auto; text-align: left;">
            <div class="confirm-modal-header" style="margin-bottom: 20px;">
                <h3 style="color: var(--gold-bright); margin: 0; font-family: var(--font-display);">Termos de Conduta e Uso</h3>
            </div>
            <div class="confirm-modal-body" style="font-family: var(--font-body); font-size: 14px; color: var(--text-primary); line-height: 1.6;">
                {!! $eulaText !!}
            </div>
            <div class="confirm-modal-actions" style="margin-top: 20px; justify-content: center;">
                <button type="button" class="btn btn-primary" onclick="document.getElementById('eulaModal').style.display='none'">Fechar</button>
            </div>
        </div>
    </div>
@endsection
