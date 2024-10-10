@extends('layouts.main')

@section('content')
    <h1>Cadastro</h1>
    <form action="{{route('register')}}" method="POST" class="register-form">
        @csrf
        <div class="register-form-row">
            <label for="username1">Login:</label>
            <input type="text" id="username1" name="username1" required>
            @if ($errors->has('username1'))
                <small class="text-danger">{{ $errors->first('username1') }}</small>
            @endif
        </div>
        <div class="register-form-row">
            <label for="email1">Email:</label>
            <input type="email" id="email1" name="email1" required>
            @if ($errors->has('email1'))
                <small class="text-danger">{{ $errors->first('email1') }}</small>
            @endif
        </div>
        <div class="register-form-row">
            <label for="name">Nome:</label>
            <input type="text" id="name" name="name" required>
            @if ($errors->has('name'))
                <small class="text-danger">{{ $errors->first('name') }}</small>
            @endif
        </div>
        <div class="register-form-row">
            <label for="email2">Confirme o Email:</label>
            <input type="email" id="email2" name="email2" required>
            @if ($errors->has('email2'))
                <small class="text-danger">{{ $errors->first('email2') }}</small>
            @endif
        </div>
        <div class="register-form-row">
            <label for="password1">Senha:</label>
            <input type="password" id="password1" name="password1" required>
            @if ($errors->has('password1'))
                <small class="text-danger">{{ $errors->first('password1') }}</small>
            @endif
        </div>
        <div class="register-form-row">
            <label for="password2">Confirme a senha:</label>
            <input type="password" id="password2" name="password2" required>
            @if ($errors->has('password2'))
                <small class="text-danger">{{ $errors->first('password2') }}</small>
            @endif
        </div>
        <div class="register-form-row">
            <label for="userCode">Código de usuário:</label>
            <input type="text" id="userCode" name="userCode" required>
            @if ($errors->has('userCode'))
                <small class="text-danger">{{ $errors->first('userCode') }}</small>
            @endif
        </div>
        <div class="register-form-row">
            <label for="phone">Telefone:</label>
            <input type="text" id="phone" name="phone" required>
            @if ($errors->has('phone'))
                <small class="text-danger">{{ $errors->first('phone') }}</small>
            @endif
        </div>
        <div class="register-form-row">
            <label for="eula">
                <input type="checkbox" class="eula" id="eula" name="eula" required>
                Concordo com os <a href="/eula">Termos de Conduta</a>
            </label>
        </div>
        <div class="register-form-row">
            <button type="submit" class="submit">Enviar</button>
        </div>
    </form>
@endsection
