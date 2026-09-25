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
            <p>Atualize seus dados do jogo. A senha atual é necessária para confirmar qualquer alteração.</p>
        </div>
        <form class="account-settings-form" method="POST" action="{{ route('account.settings.update') }}">
            @csrf
            @method('PUT')
            <div class="register-form-row">
                <label for="name">Nome</label>
                <input id="name" type="text" name="name" value="{{ old('name', $user->memb_name) }}" required>
            </div>
            <div class="register-form-row">
                <label for="personal_id">Código pessoal</label>
                <input id="personal_id" type="text" name="personal_id" maxlength="7" value="{{ old('personal_id', $user->sno__numb) }}" required>
            </div>
            <div class="register-form-row">
                <label for="email">E-mail</label>
                <input id="email" type="email" name="email" value="{{ old('email', $user->mail_addr) }}" required>
            </div>
            <div class="register-form-row">
                <label for="phone">Telefone</label>
                <input id="phone" type="text" name="phone" value="{{ old('phone', $user->tel__numb) }}" required>
            </div>
            <div class="register-form-row full-width" style="margin-top: 10px;">
                <label for="current_password">Senha atual</label>
                <input id="current_password" type="password" name="current_password" required>
                <span class="alert-required">Sua senha atual é obrigatória para confirmar qualquer alteração.</span>
            </div>
            <div class="register-form-row">
                <label for="password">Nova senha (opcional)</label>
                <input id="password" type="password" name="password">
            </div>
            <div class="register-form-row">
                <label for="password_confirmation">Confirmar nova senha</label>
                <input id="password_confirmation" type="password" name="password_confirmation">
            </div>
            <div class="submit-row">
                <button type="submit" class="btn-primary">Salvar alterações</button>
            </div>
        </form>
    </div>

    <!-- TABELA DE HISTÓRICO DE TRANSAÇÕES E PAGAMENTOS -->
    <div style="margin-top: 40px;">
        <div class="section-heading">
            <h2>Histórico de Pagamentos</h2>
            <span>Acompanhe suas faturas, comprovantes e ativações de pacotes</span>
        </div>

        @if(isset($orders) && count($orders) > 0)
            <div style="overflow-x: auto;">
                <table style="margin-top: 15px;">
                    <thead>
                        <tr>
                            <th style="width: 140px;">Data</th>
                            <th>Pacote</th>
                            <th style="text-align: center;">Método</th>
                            <th style="text-align: right;">Valor</th>
                            <th style="text-align: center;">Status</th>
                            <th style="text-align: center;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $ord)
                            <tr>
                                <td style="font-size: 13px; color: var(--steel);">
                                    {{ \Carbon\Carbon::parse($ord->created_at)->format('d/m/Y H:i') }}
                                </td>
                                <td>
                                    <strong style="color: var(--gold-bright);">{{ $ord->package_name }}</strong>
                                    <div style="font-size: 11px; color: var(--text-muted); font-family: monospace;">Ref: {{ $ord->external_reference }}</div>
                                </td>
                                <td style="text-align: center;">
                                    @if($ord->payment_method === 'pix')
                                        <span style="background: rgba(14, 116, 144, 0.4); border: 1px solid #06b6d4; color: #67e8f9; padding: 3px 8px; border-radius: 3px; font-size: 11px; font-weight: bold; text-transform: uppercase;">
                                            PIX
                                        </span>
                                    @else
                                        <span style="background: rgba(109, 40, 217, 0.4); border: 1px solid #8b5cf6; color: #c4b5fd; padding: 3px 8px; border-radius: 3px; font-size: 11px; font-weight: bold; text-transform: uppercase;">
                                            Cartão
                                        </span>
                                    @endif
                                </td>
                                <td style="text-align: right; font-weight: bold; color: #4ade80; font-family: monospace; font-size: 15px;">
                                    R$ {{ number_format($ord->amount, 2, ',', '.') }}
                                </td>
                                <td style="text-align: center;">
                                    @if($ord->status === 'approved')
                                        <span style="background: rgba(20, 83, 45, 0.6); border: 1px solid #22c55e; color: #86efac; padding: 4px 10px; border-radius: 3px; font-size: 11px; font-weight: bold; text-transform: uppercase;">
                                            Aprovado
                                        </span>
                                    @elseif($ord->status === 'pending')
                                        <span style="background: rgba(113, 63, 18, 0.6); border: 1px solid #eab308; color: #fde047; padding: 4px 10px; border-radius: 3px; font-size: 11px; font-weight: bold; text-transform: uppercase;">
                                            Pendente
                                        </span>
                                    @else
                                        <span style="background: rgba(127, 29, 29, 0.6); border: 1px solid #ef4444; color: #fca5a5; padding: 4px 10px; border-radius: 3px; font-size: 11px; font-weight: bold; text-transform: uppercase;">
                                            Cancelado
                                        </span>
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    @if($ord->status === 'pending')
                                        <div style="display: flex; gap: 8px; justify-content: center; align-items: center;">
                                            <a href="{{ route('shop.order', ['externalReference' => $ord->external_reference]) }}" class="btn-primary" style="padding: 5px 12px; font-size: 11px; text-decoration: none; display: inline-block;">
                                                Pagar / Ver PIX
                                            </a>
                                            <form action="{{ route('shop.order.cancel', ['externalReference' => $ord->external_reference]) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja cancelar esta fatura?');" style="margin: 0;">
                                                @csrf
                                                <button type="submit" class="btn-danger" style="padding: 5px 10px; font-size: 11px; cursor: pointer;">
                                                    Cancelar
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <a href="{{ route('shop.order', ['externalReference' => $ord->external_reference]) }}" style="font-size: 12px; color: var(--gold); text-decoration: underline;">
                                            Detalhes
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div style="background: rgba(15, 15, 18, 0.7); border: 1px dashed var(--border-default); border-radius: var(--radius-sm); padding: 25px; text-align: center;">
                <p style="color: var(--steel); font-size: 13px; margin: 0 0 10px 0;">
                    Você ainda não possui nenhum pedido ou transação registrada nesta conta.
                </p>
                <a href="{{ route('shop') }}" class="btn-primary" style="display: inline-block; padding: 8px 16px; font-size: 12px; text-decoration: none;">
                    Conhecer a Loja de Pacotes
                </a>
            </div>
        @endif
    </div>
</section>
@endsection