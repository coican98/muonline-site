@extends('layouts.main')

@section('content')
<div class="section-heading">
    <h2>Loja de Pacotes & VIP</h2>
    <span>Adquira vantagens exclusivas, dias de VIP e moedas para evoluir no MuRootz</span>
</div>

<div class="shop-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; margin-top: 25px;">
    @forelse($packages as $pkg)
        <div class="shop-card {{ !empty($pkg['highlight']) ? 'shop-card--highlight' : '' }}" style="background: linear-gradient(145deg, rgba(26, 26, 30, 0.95), rgba(12, 12, 14, 0.98)); border: 1px solid {{ !empty($pkg['highlight']) ? 'var(--gold-bright)' : 'rgba(201, 166, 90, 0.35)' }}; border-radius: var(--radius-sm); padding: 24px; display: flex; flex-direction: column; position: relative; box-shadow: {{ !empty($pkg['highlight']) ? '0 0 20px rgba(201, 166, 90, 0.25), 0 6px 15px rgba(0,0,0,0.8)' : '0 4px 12px rgba(0,0,0,0.6)' }}; transition: transform var(--transition-fast), border-color var(--transition-fast);">
            
            @if(!empty($pkg['highlight']))
                <div style="position: absolute; top: -12px; right: 16px; background: linear-gradient(180deg, var(--blood-bright), var(--blood)); color: #fff; font-family: var(--font-display); font-size: 11px; font-weight: 700; letter-spacing: 1.5px; padding: 3px 12px; border-radius: 2px; text-transform: uppercase; box-shadow: 0 2px 6px rgba(0,0,0,0.6); border: 1px solid rgba(240, 210, 138, 0.6);">
                    Mais Popular
                </div>
            @endif

            <h3 style="color: var(--gold-bright); font-family: var(--font-display); font-size: 1.4em; margin: 0 0 6px 0; text-transform: uppercase; letter-spacing: 1.5px; text-shadow: 1px 1px 2px #000;">
                {{ $pkg['name'] }}
            </h3>

            <p style="color: var(--steel); font-size: 13px; line-height: 1.4; margin: 0 0 16px 0; min-height: 38px;">
                {{ $pkg['description'] ?? 'Pacote de benefícios para sua conta.' }}
            </p>

            <div style="background: rgba(10, 10, 12, 0.8); border: 1px solid rgba(201, 166, 90, 0.2); border-radius: var(--radius-sm); padding: 14px; margin-bottom: 20px;">
                <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: var(--steel); margin-bottom: 2px;">Valor</div>
                <div style="font-size: 28px; font-weight: 800; color: #4ade80; font-family: monospace;">
                    R$ {{ number_format($pkg['price'], 2, ',', '.') }}
                </div>
            </div>

            <!-- VANTAGENS E BENEFÍCIOS -->
            <div style="flex: 1; margin-bottom: 24px;">
                <div style="font-family: var(--font-display); font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: var(--gold); margin-bottom: 12px; border-bottom: 1px solid rgba(201, 166, 90, 0.2); padding-bottom: 4px;">
                    Itens Inclusos
                </div>

                <ul style="list-style: none; padding: 0; margin: 0;">
                    @if($pkg['vip_type'] > 0)
                        <li style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px; font-size: 14px; color: var(--gold-bright);">
                            <span style="color: #4ade80; font-weight: bold;">✓</span>
                            <span>
                                <strong>{{ $shopSettings['vip_tiers'][$pkg['vip_type']] ?? 'VIP '.$pkg['vip_type'] }}</strong> por <strong>{{ $pkg['vip_days'] }} dias</strong>
                            </span>
                        </li>
                    @endif

                    @if($pkg['coin1'] > 0)
                        <li style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px; font-size: 14px; color: var(--text-primary);">
                            <span style="color: #4ade80; font-weight: bold;">✓</span>
                            <span><strong>{{ number_format($pkg['coin1']) }}</strong> {{ $shopSettings['coins']['coin1_name'] ?? 'Coin 1' }}</span>
                        </li>
                    @endif

                    @if($pkg['coin2'] > 0)
                        <li style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px; font-size: 14px; color: var(--text-primary);">
                            <span style="color: #4ade80; font-weight: bold;">✓</span>
                            <span><strong>{{ number_format($pkg['coin2']) }}</strong> {{ $shopSettings['coins']['coin2_name'] ?? 'Coin 2' }}</span>
                        </li>
                    @endif

                    @if($pkg['coin3'] > 0)
                        <li style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px; font-size: 14px; color: var(--text-primary);">
                            <span style="color: #4ade80; font-weight: bold;">✓</span>
                            <span><strong>{{ number_format($pkg['coin3']) }}</strong> {{ $shopSettings['coins']['coin3_name'] ?? 'Coin 3' }}</span>
                        </li>
                    @endif
                </ul>
            </div>

            <!-- AÇÃO DE COMPRA -->
            <form action="{{ route('shop.checkout', ['package_id' => $pkg['id']]) }}" method="POST">
                @csrf
                <input type="hidden" name="package_id" value="{{ $pkg['id'] }}">

                <button type="submit" class="btn-primary" style="width: 100%; box-sizing: border-box; padding: 12px; font-size: 14px;">
                    Comprar Agora
                </button>
            </form>
        </div>
    @empty
        <div style="grid-column: 1 / -1; text-align: center; padding: 40px; background: rgba(20, 20, 24, 0.6); border: 1px solid var(--border-default);">
            <h3 style="color: var(--gold-bright); font-family: var(--font-display);">Nenhum pacote disponível no momento</h3>
            <p style="color: var(--steel);">A administração está preparando novas ofertas e pacotes. Volte em breve!</p>
        </div>
    @endforelse
</div>

<div class="admin-card" style="margin-top: 40px; border-left: 3px solid var(--gold);">
    <h3 style="color: var(--gold-bright); font-family: var(--font-display); margin-top: 0; margin-bottom: 8px; font-size: 16px;">
        <i class="fas fa-shield-alt" style="margin-right: 8px;"></i> Pagamento 100% Seguro via Mercado Pago
    </h3>
    <p style="color: var(--steel); font-size: 13px; line-height: 1.5; margin: 0;">
        Seus pagamentos são processados com segurança de ponta a ponta. <strong>PIX</strong> com ativação automática instantânea logo após a confirmação bancária. <strong>Cartão de crédito</strong> processado sem retenção de dados confidenciais pelo servidor. Em caso de dúvidas ou suporte, entre em contato via nosso Discord ou WhatsApp oficial.
    </p>
</div>
@endsection
