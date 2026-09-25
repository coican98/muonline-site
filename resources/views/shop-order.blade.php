@extends('layouts.main')

@section('content')
<div class="section-heading">
    <h2>Pagamento do Pedido</h2>
    <span>Referência: <code style="color: var(--gold-bright);">{{ $order->external_reference }}</code></span>
</div>

<div style="max-width: 650px; margin: 20px auto 40px auto; background: linear-gradient(145deg, rgba(26, 26, 30, 0.98), rgba(12, 12, 14, 0.98)); border: 1px solid var(--border-default); border-radius: var(--radius-sm); padding: 25px; box-shadow: 0 8px 25px rgba(0,0,0,0.8); text-align: center;">

    @if($order->status === 'approved')
        <div style="background: rgba(28, 58, 28, 0.94); border: 1px solid #3a8a3a; padding: 20px; border-radius: var(--radius-sm); margin-bottom: 20px;">
            <i class="fas fa-check-circle" style="font-size: 45px; color: #4ade80; margin-bottom: 10px;"></i>
            <h3 style="color: #a8d8a8; font-family: var(--font-display); margin: 0 0 6px 0; font-size: 20px;">
                Pagamento Aprovado com Sucesso!
            </h3>
            <p style="color: #d6d6d6; font-size: 14px; margin: 0;">
                Seus benefícios foram entregues automaticamente na conta. Bom jogo!
            </p>
        </div>
        <a href="{{ route('account.settings') }}" class="btn-primary" style="display: inline-block; padding: 12px 24px; text-decoration: none;">
            Ver Minha Conta e Histórico
        </a>
    @elseif($order->status === 'pending')
        <div id="payment-status-badge" style="display: inline-block; background: rgba(58, 44, 8, 0.94); border: 1px solid #c8960a; color: #f0d060; padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 20px;">
            <i class="fas fa-clock" style="margin-right: 6px;"></i> Aguardando Pagamento
        </div>

        <h3 style="color: var(--gold-bright); font-family: var(--font-display); font-size: 22px; margin: 0 0 10px 0;">
            {{ $order->package_name }}
        </h3>
        <div style="font-size: 26px; font-weight: 800; color: #4ade80; font-family: monospace; margin-bottom: 20px;">
            R$ {{ number_format($order->amount, 2, ',', '.') }}
        </div>

        @if($order->payment_method === 'pix')
            @if(!empty($order->qr_code_base64))
                <div style="background: #fff; padding: 16px; display: inline-block; border-radius: 6px; box-shadow: 0 4px 15px rgba(0,0,0,0.5); margin-bottom: 20px;">
                    <img src="data:image/png;base64,{{ $order->qr_code_base64 }}" alt="QR Code PIX" style="display: block; width: 220px; height: 220px;">
                </div>
            @endif

            @if(!empty($order->qr_code))
                <div style="margin-bottom: 25px; text-align: left;">
                    <label style="display: block; font-family: var(--font-display); font-size: 12px; color: var(--gold); margin-bottom: 6px; text-transform: uppercase;">
                        Código Copia e Cola (PIX)
                    </label>
                    <div style="display: flex; gap: 8px;">
                        <input type="text" id="pixCodeInput" value="{{ $order->qr_code }}" readonly style="font-family: monospace; font-size: 12px; background: rgba(10, 10, 12, 0.95); color: #fff;">
                        <button type="button" class="btn-primary" onclick="copyPixCode()" style="white-space: nowrap; padding: 0 18px;">
                            <i class="fas fa-copy"></i> Copiar
                        </button>
                    </div>
                    <span id="copySuccessMsg" style="display: none; color: #4ade80; font-size: 12px; margin-top: 4px; font-weight: bold;">
                        ✓ Chave copiada para a área de transferência!
                    </span>
                </div>
            @endif

            <p style="color: var(--steel); font-size: 13px; line-height: 1.5; margin: 0 0 20px 0;">
                Abra o app do seu banco, escolha <strong>Pagar via PIX com QR Code ou Copia e Cola</strong> e conclua a transação. O sistema detectará o pagamento automaticamente sem necessidade de recarregar.
            </p>

            <div style="display: flex; justify-content: center; align-items: center; gap: 10px; color: var(--steel); font-size: 12px; margin-bottom: 25px;">
                <div class="loader-spinner" style="width: 14px; height: 14px; border: 2px solid rgba(201, 166, 90, 0.3); border-top-color: var(--gold-bright); border-radius: 50%; animation: spin 1s linear infinite;"></div>
                <span>Checando status da transação em tempo real...</span>
            </div>

            <form action="{{ route('shop.order.cancel', ['externalReference' => $order->external_reference]) }}" method="POST" onsubmit="return confirm('Deseja cancelar esta fatura e liberar a criação de um novo pedido?');">
                @csrf
                <button type="submit" class="btn-danger" style="padding: 8px 18px; font-size: 12px; cursor: pointer;">
                    <i class="fas fa-times" style="margin-right: 6px;"></i> Cancelar Pedido
                </button>
            </form>
        @else
            <p style="color: var(--steel); font-size: 14px; margin-bottom: 20px;">
                Sua transação no cartão está em processamento pela operadora.
            </p>
        @endif

    @else
        <div style="background: rgba(68, 14, 14, 0.94); border: 1px solid var(--blood-bright); padding: 20px; border-radius: var(--radius-sm); margin-bottom: 20px;">
            <i class="fas fa-times-circle" style="font-size: 45px; color: #f0a0a0; margin-bottom: 10px;"></i>
            <h3 style="color: #f0a0a0; font-family: var(--font-display); margin: 0 0 6px 0; font-size: 20px;">
                Pedido Cancelado ou Rejeitado
            </h3>
            <p style="color: #d6d6d6; font-size: 14px; margin: 0;">
                Esta fatura expirou ou não foi autorizada pela instituição bancária.
            </p>
        </div>
        <a href="{{ route('shop') }}" class="btn-primary" style="display: inline-block; padding: 12px 24px; text-decoration: none;">
            Voltar para a Loja
        </a>
    @endif
</div>

<style>
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<script>
function copyPixCode() {
    const copyText = document.getElementById("pixCodeInput");
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(copyText.value).then(() => {
        const msg = document.getElementById("copySuccessMsg");
        msg.style.display = "block";
        setTimeout(() => { msg.style.display = "none"; }, 4000);
    });
}

@if($order->status === 'pending')
// Polling a cada 4 segundos para verificar aprovação instantânea
const pollInterval = setInterval(async () => {
    try {
        const res = await fetch("{{ route('shop.order.check', ['externalReference' => $order->external_reference]) }}");
        if (res.ok) {
            const data = await res.json();
            if (data.is_paid || data.status === 'approved') {
                clearInterval(pollInterval);
                window.location.reload();
            }
        }
    } catch (e) {
        console.warn("Polling error", e);
    }
}, 4000);
@endif
</script>
@endsection
