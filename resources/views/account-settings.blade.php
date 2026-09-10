@extends('layouts.main')

@section('content')
<section class="account-settings">
    <div class="section-heading">
        <h2>Minha conta</h2>
        <span>Atualize seus dados do jogo</span>
    </div>
    <div class="account-settings-shell">
        <div class="account-settings-intro">
            <span class="eyebrow">Painel do aventureiro</span>
            <h3>Dados da conta</h3>
            <p>Atualize seus dados do jogo. A senha atual e necessaria para confirmar qualquer alteracao.</p>
        </div>
        <form class="account-settings-form" method="POST" action="{{ route('account.settings.update') }}">
        @csrf
        @method('PUT')
        <div class="register-form-row"><label for="name">Nome</label><input id="name" name="name" value="{{ old('name', $user->memb_name) }}" required></div>
        <div class="register-form-row"><label for="personal_id">Código pessoal</label><input id="personal_id" name="personal_id" maxlength="7" value="{{ old('personal_id', $user->sno__numb) }}" required></div>
        <div class="register-form-row"><label for="email">E-mail</label><input id="email" type="email" name="email" value="{{ old('email', $user->mail_addr) }}" required></div>
        <div class="register-form-row"><label for="phone">Telefone</label><input id="phone" name="phone" value="{{ old('phone', $user->tel__numb) }}" required></div>
        <div class="register-form-row"><label for="current_password">Senha atual</label><input id="current_password" type="password" name="current_password" required></div>
        <div class="register-form-row"><label for="password">Nova senha</label><input id="password" type="password" name="password"></div>
        <div class="register-form-row"><label for="password_confirmation">Confirmar nova senha</label><input id="password_confirmation" type="password" name="password_confirmation"></div>
        <button type="submit">Salvar alterações</button>
        </form>
    </div>
</section>
@endsection