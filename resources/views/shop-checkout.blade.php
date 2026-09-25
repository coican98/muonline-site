@extends('layouts.main')

@section('content')
<div class="section-heading">
    <h2>Finalizar Pedido</h2>
    <span>Selecione a forma de pagamento para ativar seus benefícios</span>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-top: 20px;">
    <!-- RESUMO DO PACOTE -->
    <div class="admin-card" style="margin-bottom: 0;">
        <h3 style="color: var(--gold-bright); font-family: var(--font-display); font-size: 18px; margin-top: 0; margin-bottom: 12px; border-bottom: 1px solid rgba(201,166,90,0.3); padding-bottom: 6px;">
            Resumo do Pacote
        </h3>

        <h4 style="color: var(--gold); font-size: 20px; font-family: var(--font-display); margin: 0 0 8px 0;">
            {{ $package['name'] }}
        </h4>
        <p style="color: var(--steel); font-size: 13px; line-height: 1.4; margin: 0 0 16px 0;">
            {{ $package['description'] ?? 'Pacote de benefícios exclusivos para sua conta no MuRootz.' }}
        </p>

        <div style="background: rgba(10, 10, 12, 0.85); border: 1px solid rgba(201, 166, 90, 0.3); padding: 14px; border-radius: var(--radius-sm); margin-bottom: 16px;">
            <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: var(--steel);">Total a Pagar</div>
            <div style="font-size: 28px; font-weight: 800; color: #4ade80; font-family: monospace;">
                R$ {{ number_format($package['price'], 2, ',', '.') }}
            </div>
        </div>

        <div style="margin-bottom: 15px;">
            <div style="font-family: var(--font-display); font-size: 12px; text-transform: uppercase; color: var(--gold); margin-bottom: 8px;">
                Benefícios Incluídos
            </div>
            <ul style="list-style: none; padding: 0; margin: 0; font-size: 13px;">
                @if($package['vip_type'] > 0)
                    <li style="display: flex; gap: 8px; margin-bottom: 8px; color: var(--gold-bright);">
                        <span style="color: #4ade80; font-weight: bold;">✓</span>
                        <span>
                            <strong>{{ $shopSettings['vip_tiers'][$package['vip_type']] ?? 'VIP '.$package['vip_type'] }}</strong> por <strong>{{ $package['vip_days'] }} dias</strong>
                        </span>
                    </li>
                @endif
                @if($package['coin1'] > 0)
                    <li style="display: flex; gap: 8px; margin-bottom: 8px; color: var(--text-primary);">
                        <span style="color: #4ade80; font-weight: bold;">✓</span>
                        <span><strong>{{ number_format($package['coin1']) }}</strong> {{ $shopSettings['coins']['coin1_name'] ?? 'Coin 1' }}</span>
                    </li>
                @endif
                @if($package['coin2'] > 0)
                    <li style="display: flex; gap: 8px; margin-bottom: 8px; color: var(--text-primary);">
                        <span style="color: #4ade80; font-weight: bold;">✓</span>
                        <span><strong>{{ number_format($package['coin2']) }}</strong> {{ $shopSettings['coins']['coin2_name'] ?? 'Coin 2' }}</span>
                    </li>
                @endif
                @if($package['coin3'] > 0)
                    <li style="display: flex; gap: 8px; margin-bottom: 8px; color: var(--text-primary);">
                        <span style="color: #4ade80; font-weight: bold;">✓</span>
                        <span><strong>{{ number_format($package['coin3']) }}</strong> {{ $shopSettings['coins']['coin3_name'] ?? 'Coin 3' }}</span>
                    </li>
                @endif
            </ul>
        </div>

        @if($isVipActive)
            <div style="background: rgba(40, 25, 10, 0.7); border-left: 3px solid var(--gold); padding: 10px 12px; font-size: 12px; color: var(--gold-bright);">
                <strong>Status VIP Atual:</strong> Você possui 
                {{ $shopSettings['vip_tiers'][$currentVipType] ?? 'VIP '.$currentVipType }} ativo até {{ $currentExpire->format('d/m/Y H:i') }}.
                @if($package['vip_type'] == $currentVipType)
                    <span style="display: block; margin-top: 4px; color: #a8d8a8;">Os {{ $package['vip_days'] }} dias deste pacote serão somados ao seu vencimento atual.</span>
                @elseif($package['vip_type'] > $currentVipType)
                    <span style="display: block; margin-top: 4px; color: #f0d060;">Seu plano será aprimorado imediatamente para {{ $shopSettings['vip_tiers'][$package['vip_type']] ?? 'VIP '.$package['vip_type'] }}.</span>
                @endif
            </div>
        @endif
    </div>

    <!-- SELEÇÃO DA FORMA DE PAGAMENTO -->
    <div class="admin-card" style="margin-bottom: 0;">
        <h3 style="color: var(--gold-bright); font-family: var(--font-display); font-size: 18px; margin-top: 0; margin-bottom: 12px; border-bottom: 1px solid rgba(201,166,90,0.3); padding-bottom: 6px;">
            Forma de Pagamento
        </h3>

        <!-- Abas de seleção -->
        <div style="display: flex; gap: 12px; margin-bottom: 20px;">
            <button type="button" id="tab-pix-btn" onclick="selectPaymentMethod('pix')" class="btn-primary" style="flex: 1; padding: 12px; font-size: 13px; display: flex; align-items: center; justify-content: center; gap: 8px;">
                <i class="fas fa-qrcode"></i> PIX (Instantâneo)
            </button>
            <button type="button" id="tab-card-btn" onclick="selectPaymentMethod('card')" class="btn-secondary" style="flex: 1; padding: 12px; font-size: 13px; display: flex; align-items: center; justify-content: center; gap: 8px;">
                <i class="fas fa-credit-card"></i> Cartão de Crédito
            </button>
        </div>

        <!-- FORMULÁRIO PIX -->
        <div id="method-pix-box">
            <p style="color: var(--steel); font-size: 13px; line-height: 1.5; margin-bottom: 20px;">
                Ao clicar no botão abaixo, geramos o QR Code e o código "Copia e Cola" do PIX. O crédito é efetuado de forma 100% automatizada e instantânea assim que seu banco processar a transferência.
            </p>

            <form action="{{ route('shop.payment.process') }}" method="POST">
                @csrf
                <input type="hidden" name="package_id" value="{{ $package['id'] }}">
                <input type="hidden" name="payment_method" value="pix">

                <button type="submit" class="btn-primary" style="width: 100%; padding: 14px; font-size: 15px;">
                    <i class="fas fa-bolt" style="margin-right: 8px;"></i> Gerar Chave PIX
                </button>
            </form>
        </div>

        <!-- FORMULÁRIO CARTÃO DE CRÉDITO -->
        <div id="method-card-box" style="display: none;">
            <p style="color: var(--steel); font-size: 12px; margin-bottom: 15px;">
                Pagamento seguro processado diretamente pelos servidores do Mercado Pago.
            </p>

            <form id="form-checkout" action="{{ route('shop.payment.process') }}" method="POST">
                @csrf
                <input type="hidden" name="package_id" value="{{ $package['id'] }}">
                <input type="hidden" name="payment_method" value="card">
                <input type="hidden" name="card_token" id="card_token">
                <input type="hidden" name="payment_method_id" id="payment_method_id">
                <input type="hidden" name="issuer_id" id="issuer_id">

                <div class="form-group" style="margin-bottom: 12px;">
                    <label style="font-size: 12px;">Número do Cartão</label>
                    <input type="text" id="cardNumber" placeholder="0000 0000 0000 0000" maxlength="19" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="font-size: 12px;">Validade (MM/AA)</label>
                        <input type="text" id="cardExpirationDate" placeholder="MM/AA" maxlength="5" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="font-size: 12px;">Código de Segurança (CVV)</label>
                        <input type="text" id="securityCode" placeholder="123" maxlength="4" required>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 12px;">
                    <label style="font-size: 12px;">Nome no Cartão</label>
                    <input type="text" id="cardholderName" placeholder="Como impresso no cartão" required>
                </div>

                <div class="form-group" style="margin-bottom: 12px;">
                    <label style="font-size: 12px;">Parcelas</label>
                    <select name="installments" id="installments">
                        <option value="1">1x de R$ {{ number_format($package['price'], 2, ',', '.') }} (à vista)</option>
                    </select>
                </div>

                <button type="submit" id="btn-pay-card" class="btn-primary" style="width: 100%; padding: 14px; font-size: 15px; margin-top: 10px;">
                    Pagar com Cartão
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function selectPaymentMethod(method) {
    const pixBox = document.getElementById('method-pix-box');
    const cardBox = document.getElementById('method-card-box');
    const pixBtn = document.getElementById('tab-pix-btn');
    const cardBtn = document.getElementById('tab-card-btn');

    if (method === 'pix') {
        pixBox.style.display = 'block';
        cardBox.style.display = 'none';
        pixBtn.className = 'btn-primary';
        cardBtn.className = 'btn-secondary';
    } else {
        pixBox.style.display = 'none';
        cardBox.style.display = 'block';
        pixBtn.className = 'btn-secondary';
        cardBtn.className = 'btn-primary';
    }
}
</script>

@if(!empty($publicKey))
<script src="https://sdk.mercadopago.com/js/v2"></script>
<script>
const mp = new MercadoPago("{{ $publicKey }}");

document.getElementById('cardNumber').addEventListener('input', async function(e) {
    const cardNumber = e.target.value.replace(/\s+/g, '');
    if (cardNumber.length >= 6) {
        try {
            const bin = cardNumber.substring(0, 6);
            const { results } = await mp.getPaymentMethods({ bin });
            if (results && results.length > 0) {
                document.getElementById('payment_method_id').value = results[0].id;
                if (results[0].processing_modes.includes('aggregator')) {
                    // Obtém parcelas
                    const installments = await mp.getInstallments({
                        amount: "{{ $package['price'] }}",
                        bin: bin,
                        paymentTypeId: 'credit_card'
                    });
                    if (installments && installments.length > 0) {
                        const select = document.getElementById('installments');
                        select.innerHTML = '';
                        installments[0].payer_costs.forEach(cost => {
                            const opt = document.createElement('option');
                            opt.value = cost.installments;
                            opt.innerText = cost.recommended_message;
                            select.appendChild(opt);
                        });
                        if (installments[0].issuer) {
                            document.getElementById('issuer_id').value = installments[0].issuer.id;
                        }
                    }
                }
            }
        } catch (err) {
            console.error('Erro ao buscar método de pagamento', err);
        }
    }
});

document.getElementById('form-checkout').addEventListener('submit', async function(e) {
    const tokenInput = document.getElementById('card_token');
    if (!tokenInput.value) {
        e.preventDefault();
        const payBtn = document.getElementById('btn-pay-card');
        payBtn.disabled = true;
        payBtn.innerText = 'Processando...';

        const exp = document.getElementById('cardExpirationDate').value.split('/');
        const month = exp[0] ? exp[0].trim() : '';
        const year = exp[1] ? (exp[1].trim().length === 2 ? '20' + exp[1].trim() : exp[1].trim()) : '';

        try {
            const cleanCardNumber = document.getElementById('cardNumber').value.replace(/\s+/g, '');
            if (!document.getElementById('payment_method_id').value && cleanCardNumber.length >= 6) {
                const bin = cleanCardNumber.substring(0, 6);
                const { results } = await mp.getPaymentMethods({ bin });
                if (results && results.length > 0) {
                    document.getElementById('payment_method_id').value = results[0].id;
                }
            }

            const cardToken = await mp.createCardToken({
                cardNumber: cleanCardNumber,
                cardholderName: document.getElementById('cardholderName').value,
                cardExpirationMonth: month,
                cardExpirationYear: year,
                securityCode: document.getElementById('securityCode').value,
            });

            if (cardToken && cardToken.id) {
                tokenInput.value = cardToken.id;
                this.submit();
            } else {
                alert('Verifique os dados do cartão inseridos.');
                payBtn.disabled = false;
                payBtn.innerText = 'Pagar com Cartão';
            }
        } catch (err) {
            alert('Erro ao validar cartão: ' + (err.message || 'Verifique os dados digitados'));
            payBtn.disabled = false;
            payBtn.innerText = 'Pagar com Cartão';
        }
    }
});
</script>
@endif
@endsection
