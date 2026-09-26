@extends('layouts.main')
@section('content')

<div class="section-heading">
    <h2>Administração</h2>
    <span>Painel de controle de operações</span>
</div>

{{-- NAVEGAÇÃO EM ABAS DO PAINEL ADMIN --}}
<div class="admin-tabs-nav" id="admin-tabs-nav">
    <button type="button" class="admin-tab-btn active" data-tab="tab-noticias">
        <i class="fas fa-newspaper"></i> Notícias & Comunicados
    </button>
    <button type="button" class="admin-tab-btn" data-tab="tab-loja">
        <i class="fas fa-shopping-cart"></i> Loja & Pacotes
    </button>
    <button type="button" class="admin-tab-btn" data-tab="tab-geral">
        <i class="fas fa-sliders-h"></i> Configurações & Arquivos
    </button>
    <button type="button" class="admin-tab-btn" data-tab="tab-jogadores">
        <i class="fas fa-users-cog"></i> Gestão de Jogadores
    </button>
</div>

<!-- ABA: CONFIGURAÇÕES GERAIS E ARQUIVOS -->
<div id="tab-geral" class="admin-tab-pane">
    <section class="admin-card">
        <h2>Upload de Arquivos</h2>
        <p class="admin-card-desc">Suba arquivos de atualização ou referencie links externos.</p>
        <form action="{{ route('upload')}}" method="POST" enctype="multipart/form-data">
            @csrf
            <div id='download-div' style="display: flex; flex-direction: column; gap: 15px;">
                <label style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" name="is_external" id="is_external" style="margin: 0; transform: none;"> Link Externo
                </label>
                
                <div id="local-upload-fields" style="width: 100%;">
                    <input type="file" name="file" id="file" required>
                </div>

                <div id="external-upload-fields" style="width: 100%; display: none; flex-direction: column; gap: 10px;">
                    <input type="text" name="ext_name" placeholder="Nome do Arquivo (Ex: Cliente Completo Google Drive)" style="width:100%;">
                    <input type="url" name="ext_url" placeholder="URL: https://..." style="width:100%;">
                    <input type="text" name="ext_size" placeholder="Tamanho (Ex: 500 MB)" style="width:100%;">
                </div>
            </div>
            <br>
            <button type="submit" class="btn-primary" style="left: auto;">Salvar Download</button>
        </form>
    </section>

    <section class="admin-card">
        <h2>Configurações Globais</h2>
    <form action="{{ route('admin.settings.update') }}" method="POST">
        @csrf
        @php
            $settingsPath = storage_path('app/settings.json');
            $sData = file_exists($settingsPath) ? json_decode(file_get_contents($settingsPath), true) : [];
            $discordUrl = $sData['discord_url'] ?? '';
            $whatsappUrl = $sData['whatsapp_url'] ?? '';
            $eulaText = $sData['eula_text'] ?? '';
        @endphp
        
        <div class="form-group">
            <label>Link Discord (deixe vazio para ocultar)</label>
            <input type="url" name="discord_url" value="{{ $discordUrl }}">
        </div>
        
        <div class="form-group">
            <label>Link WhatsApp (deixe vazio para ocultar)</label>
            <input type="url" name="whatsapp_url" value="{{ $whatsappUrl }}">
        </div>
        
        <div class="form-group">
            <label>Termos de Conduta (HTML/Texto)</label>
            <textarea name="eula_text" rows="8" style="resize: vertical;">{{ $eulaText }}</textarea>
        </div>
        
        <button type="submit" class="btn-primary">Salvar Configurações</button>
    </form>
</section>
</div>

<!-- ABA: LOJA, PACOTES & BÔNUS DE CADASTRO -->
<div id="tab-loja" class="admin-tab-pane">
<section class="admin-card">
    <h2>Loja & Pagamentos: Nomenclaturas e Mercado Pago</h2>
    <p class="admin-card-desc">Defina as nomenclaturas visíveis das moedas, tiers de VIP e credenciais do Checkout Transparente.</p>
    <form action="{{ route('admin.shop.settings') }}" method="POST">
        @csrf
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div>
                <h3 style="color: var(--gold); font-family: var(--font-display); font-size: 14px; margin-bottom: 12px; border-bottom: 1px solid rgba(201,166,90,0.3); padding-bottom: 4px;">Nomenclatura das Moedas</h3>
                <div class="form-group">
                    <label>Moeda 1 (Padrão: WCoinC)</label>
                    <input type="text" name="coin1_name" value="{{ $shopSettings['coins']['coin1_name'] ?? 'WCoinC (WC)' }}" required>
                </div>
                <div class="form-group">
                    <label>Moeda 2 (Padrão: WCoinP)</label>
                    <input type="text" name="coin2_name" value="{{ $shopSettings['coins']['coin2_name'] ?? 'WCoinP (WP)' }}" required>
                </div>
                <div class="form-group">
                    <label>Moeda 3 (Padrão: Goblin Point)</label>
                    <input type="text" name="coin3_name" value="{{ $shopSettings['coins']['coin3_name'] ?? 'Goblin Point (GP)' }}" required>
                </div>
            </div>

            <div>
                <h3 style="color: var(--gold); font-family: var(--font-display); font-size: 14px; margin-bottom: 12px; border-bottom: 1px solid rgba(201,166,90,0.3); padding-bottom: 4px;">Nomenclatura dos Níveis VIP</h3>
                <div class="form-group">
                    <label>VIP 0 (Nível Padrão Gratuito)</label>
                    <input type="text" name="vip_0_name" value="{{ $shopSettings['vip_tiers']['0'] ?? 'Free' }}" required>
                </div>
                <div class="form-group">
                    <label>VIP 1 (Tier 1)</label>
                    <input type="text" name="vip_1_name" value="{{ $shopSettings['vip_tiers']['1'] ?? 'Bronze' }}" required>
                </div>
                <div class="form-group">
                    <label>VIP 2 (Tier 2)</label>
                    <input type="text" name="vip_2_name" value="{{ $shopSettings['vip_tiers']['2'] ?? 'Prata' }}" required>
                </div>
                <div class="form-group">
                    <label>VIP 3 (Tier 3)</label>
                    <input type="text" name="vip_3_name" value="{{ $shopSettings['vip_tiers']['3'] ?? 'Ouro' }}" required>
                </div>
            </div>
        </div>

        <div style="margin-top: 15px; border-top: 1px solid rgba(201,166,90,0.3); padding-top: 15px;">
            <h3 style="color: var(--gold); font-family: var(--font-display); font-size: 14px; margin-bottom: 4px;">API Mercado Pago (API de Orders / Pedidos)</h3>
            <p style="color: var(--steel); font-size: 12px; margin: 0 0 12px 0;">Utilizamos a moderna <strong>API de Orders</strong> do Mercado Pago para criação de pagamentos e Webhooks seguros.</p>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Public Key (Chave Pública)</label>
                    <input type="text" name="mp_public_key" value="{{ $shopSettings['mp_public_key'] ?? '' }}" placeholder="APP_USR-...">
                </div>
                <div class="form-group">
                    <label>Access Token (Produção)</label>
                    <input type="password" name="mp_access_token" value="{{ $shopSettings['mp_access_token'] ?? '' }}" placeholder="APP_USR-...">
                </div>
                <div class="form-group">
                    <label>Webhook Secret (Assinatura)</label>
                    <input type="password" name="mp_webhook_secret" value="{{ $shopSettings['mp_webhook_secret'] ?? '' }}" placeholder="Assinatura secreta (opcional)">
                </div>
            </div>
            <div style="background: rgba(10, 10, 12, 0.7); border: 1px solid rgba(201,166,90,0.2); padding: 8px 12px; border-radius: 4px; font-size: 12px; color: var(--steel);">
                <strong>URL do Webhook para cadastrar no Mercado Pago:</strong> 
                <code style="color: var(--gold-bright);">{{ url('/api/webhooks/mercadopago') }}</code> (Selecionar eventos de <strong>Pedidos / Orders</strong>)
            </div>
        </div>

        <button type="submit" class="btn-primary" style="margin-top: 15px;">Salvar Configurações da Loja</button>
    </form>
</section>

<!-- MÓDULO DE BÔNUS NO CADASTRO -->
<section class="admin-card">
    <h2>Bônus de Boas-Vindas no Cadastro</h2>
    <p class="admin-card-desc">Configure benefícios automáticos (VIP e Moedas) concedidos imediatamente quando um novo jogador se cadastra pelo site.</p>
    <form action="{{ route('admin.registration.bonus') }}" method="POST">
        @csrf
        <div class="form-group" style="margin-bottom: 20px;">
            <label style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
                <input type="checkbox" name="bonus_enabled" {{ !empty($registrationBonus['enabled']) ? 'checked' : '' }}> 
                <strong>Habilitar Bônus de Cadastro</strong>
            </label>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px;">
            <div class="form-group">
                <label>Tipo de VIP Inicial</label>
                <select name="bonus_vip_type">
                    <option value="0" {{ ($registrationBonus['vip_type'] ?? 0) == 0 ? 'selected' : '' }}>Nenhum (Free)</option>
                    <option value="1" {{ ($registrationBonus['vip_type'] ?? 0) == 1 ? 'selected' : '' }}>{{ $shopSettings['vip_tiers']['1'] ?? 'VIP 1 (Bronze)' }}</option>
                    <option value="2" {{ ($registrationBonus['vip_type'] ?? 0) == 2 ? 'selected' : '' }}>{{ $shopSettings['vip_tiers']['2'] ?? 'VIP 2 (Prata)' }}</option>
                    <option value="3" {{ ($registrationBonus['vip_type'] ?? 0) == 3 ? 'selected' : '' }}>{{ $shopSettings['vip_tiers']['3'] ?? 'VIP 3 (Ouro)' }}</option>
                </select>
            </div>
            <div class="form-group">
                <label>Dias de Duração do VIP</label>
                <input type="number" name="bonus_vip_days" value="{{ $registrationBonus['vip_days'] ?? 3 }}" min="0">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 15px;">
            <div class="form-group">
                <label>Bônus {{ $shopSettings['coins']['coin1_name'] ?? 'Coin 1' }}</label>
                <input type="number" name="bonus_coin1" value="{{ $registrationBonus['coin1'] ?? 0 }}" min="0">
            </div>
            <div class="form-group">
                <label>Bônus {{ $shopSettings['coins']['coin2_name'] ?? 'Coin 2' }}</label>
                <input type="number" name="bonus_coin2" value="{{ $registrationBonus['coin2'] ?? 0 }}" min="0">
            </div>
            <div class="form-group">
                <label>Bônus {{ $shopSettings['coins']['coin3_name'] ?? 'Coin 3' }}</label>
                <input type="number" name="bonus_coin3" value="{{ $registrationBonus['coin3'] ?? 0 }}" min="0">
            </div>
        </div>

        <button type="submit" class="btn-primary" style="margin-top: 5px;">Salvar Bônus de Cadastro</button>
    </form>
</section>

<!-- MÓDULO DA LOJA: GESTÃO DE PACOTES -->
<section class="admin-card">
    <h2>Gestão de Pacotes da Loja</h2>
    <p class="admin-card-desc">Cadastre e gerencie os pacotes que os jogadores podem adquirir na Loja.</p>

    <!-- Formulário de Criação de Pacote -->
    <div style="background: rgba(10, 10, 12, 0.7); border: 1px solid var(--border-default); padding: 20px; border-radius: 4px; margin-bottom: 25px;">
        <h3 style="color: var(--gold-bright); font-family: var(--font-display); font-size: 15px; margin-top: 0; margin-bottom: 15px; text-transform: uppercase;">Adicionar Novo Pacote</h3>
        <form action="{{ route('admin.shop.package.store') }}" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Nome do Pacote</label>
                    <input type="text" name="name" placeholder="Ex: Pacote Guerreiro" required>
                </div>
                <div class="form-group">
                    <label>Preço (R$)</label>
                    <input type="number" step="0.01" name="price" placeholder="Ex: 29.90" required>
                </div>
                <div class="form-group">
                    <label>Em Destaque?</label>
                    <label style="margin-top: 10px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                        <input type="checkbox" name="highlight"> Destacar na Loja
                    </label>
                </div>
                <div class="form-group">
                    <label>Visibilidade Inicial</label>
                    <label style="margin-top: 10px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                        <input type="checkbox" name="active" checked> Pacote Ativo
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label>Descrição do Pacote</label>
                <input type="text" name="description" placeholder="Breve descrição dos benefícios para o jogador">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Tipo de VIP Concedido (Apenas um)</label>
                    <select name="vip_type" required>
                        <option value="0">Nenhum (Apenas Coins)</option>
                        <option value="1">{{ $shopSettings['vip_tiers']['1'] ?? 'VIP 1 (Bronze)' }}</option>
                        <option value="2">{{ $shopSettings['vip_tiers']['2'] ?? 'VIP 2 (Prata)' }}</option>
                        <option value="3">{{ $shopSettings['vip_tiers']['3'] ?? 'VIP 3 (Ouro)' }}</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Dias de VIP</label>
                    <input type="number" name="vip_days" value="30" min="0">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>{{ $shopSettings['coins']['coin1_name'] ?? 'Coin 1' }}</label>
                    <input type="number" name="coin1" value="0" min="0">
                </div>
                <div class="form-group">
                    <label>{{ $shopSettings['coins']['coin2_name'] ?? 'Coin 2' }}</label>
                    <input type="number" name="coin2" value="0" min="0">
                </div>
                <div class="form-group">
                    <label>{{ $shopSettings['coins']['coin3_name'] ?? 'Coin 3' }}</label>
                    <input type="number" name="coin3" value="0" min="0">
                </div>
            </div>

            <button type="submit" class="btn-primary" style="margin-top: 10px;">Criar Pacote</button>
        </form>
    </div>

    <!-- Tabela de Pacotes Atuais -->
    <h3 style="color: var(--gold-bright); font-family: var(--font-display); font-size: 15px; margin-bottom: 12px; text-transform: uppercase;">Pacotes Cadastrados</h3>
    <table class="downloads-table">
        <thead>
            <tr>
                <th style="width: 50px;">ID</th>
                <th>Nome / Descrição</th>
                <th>Preço</th>
                <th>VIP</th>
                <th>Moedas Concedidas</th>
                <th style="text-align: center;">Status</th>
                <th style="text-align: right;">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($shopPackages as $pkg)
                @php
                    $isPkgActive = !isset($pkg['active']) || (bool)$pkg['active'] === true;
                @endphp
                <tr>
                    <td style="color: var(--steel); font-family: monospace;">#{{ $pkg['id'] }}</td>
                    <td>
                        <strong style="color: var(--gold-bright); font-family: var(--font-display);">{{ $pkg['name'] }}</strong>
                        @if(!empty($pkg['highlight']))
                            <span style="background: rgba(209, 42, 36, 0.4); border: 1px solid var(--blood-bright); font-size: 10px; padding: 2px 6px; border-radius: 2px; color: #fff; margin-left: 5px;">DESTAQUE</span>
                        @endif
                        <br>
                        <span style="font-size: 12px; color: var(--steel);">{{ $pkg['description'] ?? 'Sem descrição' }}</span>
                    </td>
                    <td style="color: #4ade80; font-weight: bold; font-family: monospace; font-size: 15px;">
                        R$ {{ number_format($pkg['price'], 2, ',', '.') }}
                    </td>
                    <td>
                        @if($pkg['vip_type'] > 0)
                            <span style="color: var(--gold-bright); font-weight: bold;">
                                {{ $shopSettings['vip_tiers'][$pkg['vip_type']] ?? 'VIP '.$pkg['vip_type'] }}
                            </span>
                            <span style="color: var(--steel); font-size: 12px;">({{ $pkg['vip_days'] }} dias)</span>
                        @else
                            <span style="color: var(--steel);">-</span>
                        @endif
                    </td>
                    <td style="font-size: 12px; line-height: 1.4;">
                        @if($pkg['coin1'] > 0) <div><strong style="color: var(--gold);">{{ $shopSettings['coins']['coin1_name'] ?? 'Coin 1' }}:</strong> {{ number_format($pkg['coin1']) }}</div> @endif
                        @if($pkg['coin2'] > 0) <div><strong style="color: var(--gold);">{{ $shopSettings['coins']['coin2_name'] ?? 'Coin 2' }}:</strong> {{ number_format($pkg['coin2']) }}</div> @endif
                        @if($pkg['coin3'] > 0) <div><strong style="color: var(--gold);">{{ $shopSettings['coins']['coin3_name'] ?? 'Coin 3' }}:</strong> {{ number_format($pkg['coin3']) }}</div> @endif
                        @if($pkg['coin1'] == 0 && $pkg['coin2'] == 0 && $pkg['coin3'] == 0) <span style="color: var(--steel);">-</span> @endif
                    </td>
                    <td style="text-align: center;">
                        <form action="{{ route('admin.shop.package.toggle', $pkg['id']) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" style="background: none; border: none; cursor: pointer; padding: 0;" title="Clique para alternar ativação do pacote">
                                @if($isPkgActive)
                                    <span style="background: rgba(20, 83, 45, 0.6); border: 1px solid #22c55e; color: #86efac; padding: 3px 8px; border-radius: 3px; font-size: 11px; font-weight: bold; text-transform: uppercase;">
                                        Ativo
                                    </span>
                                @else
                                    <span style="background: rgba(127, 29, 29, 0.6); border: 1px solid #ef4444; color: #fca5a5; padding: 3px 8px; border-radius: 3px; font-size: 11px; font-weight: bold; text-transform: uppercase;">
                                        Inativo
                                    </span>
                                @endif
                            </button>
                        </form>
                    </td>
                    <td style="text-align: right;">
                        <div style="display: inline-flex; gap: 8px; align-items: center;">
                            <button type="button" class="btn" style="padding: 5px 12px; font-size: 11px; min-height: unset;" 
                                onclick="openEditPackageModal({{ json_encode($pkg) }})">
                                Editar
                            </button>
                            <form action="{{ route('admin.shop.package.delete', $pkg['id']) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir o pacote {{ $pkg['name'] }}?');" style="margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-danger" style="padding: 5px 12px; font-size: 11px; min-height: unset;">Excluir</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: var(--steel); padding: 20px;">Nenhum pacote cadastrado no momento.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- MODAL DE EDIÇÃO DE PACOTE -->
    <div id="edit-package-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 3000; align-items: center; justify-content: center; padding: 20px;">
        <div style="background: linear-gradient(145deg, #1c1d20, #0c0c0e); border: 1px solid var(--gold); border-top: 2px solid var(--gold-bright); max-width: 600px; width: 100%; padding: 25px; border-radius: 4px; box-shadow: 0 10px 30px rgba(0,0,0,0.9);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 1px solid rgba(201,166,90,0.3); padding-bottom: 8px;">
                <h3 style="color: var(--gold-bright); font-family: var(--font-display); margin: 0; font-size: 1.2em;">Editar Pacote da Loja</h3>
                <button type="button" onclick="closeEditPackageModal()" style="background: none; border: none; color: var(--steel); font-size: 20px; cursor: pointer;">&times;</button>
            </div>
            
            <form id="edit-package-form" method="POST" action="">
                @csrf
                @method('PUT')
                
                <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label>Nome do Pacote</label>
                        <input type="text" id="edit-name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Preço (R$)</label>
                        <input type="number" step="0.01" id="edit-price" name="price" required>
                    </div>
                    <div class="form-group">
                        <label>Destaque</label>
                        <label style="margin-top: 10px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                            <input type="checkbox" id="edit-highlight" name="highlight"> Destacar
                        </label>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <input type="hidden" name="active_status_present" value="1">
                        <label style="margin-top: 10px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                            <input type="checkbox" id="edit-active" name="active"> Ativo
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label>Descrição</label>
                    <input type="text" id="edit-description" name="description">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label>Tipo de VIP</label>
                        <select id="edit-vip-type" name="vip_type" required>
                            <option value="0">Nenhum (Apenas Coins)</option>
                            <option value="1">{{ $shopSettings['vip_tiers']['1'] ?? 'VIP 1 (Bronze)' }}</option>
                            <option value="2">{{ $shopSettings['vip_tiers']['2'] ?? 'VIP 2 (Prata)' }}</option>
                            <option value="3">{{ $shopSettings['vip_tiers']['3'] ?? 'VIP 3 (Ouro)' }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Dias de VIP</label>
                        <input type="number" id="edit-vip-days" name="vip_days" min="0">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label>{{ $shopSettings['coins']['coin1_name'] ?? 'Coin 1' }}</label>
                        <input type="number" id="edit-coin1" name="coin1" min="0">
                    </div>
                    <div class="form-group">
                        <label>{{ $shopSettings['coins']['coin2_name'] ?? 'Coin 2' }}</label>
                        <input type="number" id="edit-coin2" name="coin2" min="0">
                    </div>
                    <div class="form-group">
                        <label>{{ $shopSettings['coins']['coin3_name'] ?? 'Coin 3' }}</label>
                        <input type="number" id="edit-coin3" name="coin3" min="0">
                    </div>
                </div>

                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 15px;">
                    <button type="button" onclick="closeEditPackageModal()" class="btn" style="background: rgba(30,30,35,0.8);">Cancelar</button>
                    <button type="submit" class="btn-primary">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</section>
</div>

<!-- ABA: NOTÍCIAS & COMUNICADOS -->
<div id="tab-noticias" class="admin-tab-pane active">
<section class="admin-card" id="news-management">
    <h2>Gestão de Notícias & Comunicados</h2>
    <p class="admin-card-desc">Crie notícias, controle ativação, defina agendamentos de publicação e escolha qual notícia será destacada no modal da página inicial.</p>

    <!-- Formulário de Criação de Notícia -->
    <div style="background: rgba(10, 10, 12, 0.7); border: 1px solid var(--border-default); padding: 20px; border-radius: 4px; margin-bottom: 25px;">
        <h3 style="color: var(--gold-bright); font-family: var(--font-display); font-size: 15px; margin-top: 0; margin-bottom: 15px; text-transform: uppercase;">Publicar Nova Notícia</h3>
        <form action="{{ route('admin.news.store') }}" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Título da Notícia</label>
                    <input type="text" name="title" placeholder="Ex: Inauguração do Servidor e Bônus Iniciais" required>
                </div>
                <div class="form-group">
                    <label>Categoria</label>
                    <select name="category">
                        <option value="Servidor">Servidor</option>
                        <option value="Atualização">Atualização</option>
                        <option value="Evento">Evento</option>
                        <option value="Manutenção">Manutenção</option>
                        <option value="Promoção">Promoção</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Resumo / Chamada (Opcional - Exibido nos cards e no modal)</label>
                <input type="text" name="summary" placeholder="Breve introdução da notícia (até 300 caracteres)">
            </div>

            <div class="form-group">
                <label>Conteúdo Completo (Suporta quebras de linha e HTML: &lt;b&gt;, &lt;i&gt;, &lt;u&gt;, &lt;h2&gt;, &lt;ul&gt;, &lt;li&gt;, &lt;a&gt;, etc.)</label>
                <textarea name="content" rows="7" placeholder="Escreva o texto completo da notícia. Pode usar tags HTML como <b>negrito</b>, <i>itálico</i>, links e listas..." required style="resize: vertical; font-family: inherit;"></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>URL da Imagem de Capa (Opcional)</label>
                    <input type="url" name="image_url" placeholder="https://exemplo.com/imagem.jpg">
                </div>
                <div class="form-group">
                    <label>Agendar Publicação (Opcional - Deixe vazio para publicar imediatamente)</label>
                    <input type="datetime-local" name="published_at">
                </div>
            </div>

            <div style="display: flex; gap: 25px; margin-top: 15px; flex-wrap: wrap;">
                <label style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" name="active" checked> Publicar Imediatamente (Ativo)
                </label>
                <label style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" name="show_on_home_modal"> Exibir no Modal da Página Inicial (Apenas 1 por vez)
                </label>
            </div>

            <button type="submit" class="btn-primary" style="margin-top: 15px;">Cadastrar Notícia</button>
        </form>
    </div>

    <!-- Tabela de Notícias Cadastradas -->
    <h3 style="color: var(--gold-bright); font-family: var(--font-display); font-size: 15px; margin-bottom: 12px; text-transform: uppercase;">Notícias Cadastradas</h3>
    <div style="overflow-x: auto;">
        <table class="downloads-table" style="margin: 0; width: 100%;">
            <thead>
                <tr>
                    <th style="width: 50px;">ID</th>
                    <th>Título</th>
                    <th>Categoria</th>
                    <th>Autor</th>
                    <th>Agendamento / Publicação</th>
                    <th style="text-align: center;">Modal Home</th>
                    <th style="text-align: center;">Status</th>
                    <th style="text-align: right;">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($newsList ?? [] as $n)
                    <tr>
                        <td style="color: var(--text-muted); font-size: 12px;">#{{ $n->id }}</td>
                        <td style="color: var(--gold-bright); font-weight: bold;">
                            <a href="/noticias/{{ $n->slug ?: $n->id }}" target="_blank" style="color: inherit; text-decoration: none;" title="Visualizar notícia">
                                {{ $n->title }} <i class="fas fa-external-link-alt" style="font-size: 10px; margin-left: 4px; color: var(--gold);"></i>
                            </a>
                        </td>
                        <td>
                            <span style="background: rgba(201,166,90,0.1); border: 1px solid rgba(201,166,90,0.3); padding: 2px 7px; border-radius: 3px; font-size: 11px;">
                                {{ $n->category ?? 'Servidor' }}
                            </span>
                        </td>
                        <td style="font-size: 12px; color: var(--steel);">
                            <span style="color: var(--gold);"><i class="far fa-user"></i> {{ $n->author_name ?? $n->author }}</span>
                            @if(!empty($n->author_id) && $n->author_id !== ($n->author_name ?? ''))
                                <br><small style="color: var(--text-muted); font-family: monospace;">({{ $n->author_id }})</small>
                            @endif
                        </td>
                        <td style="font-size: 12px; color: var(--steel);">
                            @if($n->published_at)
                                @if(\Carbon\Carbon::parse($n->published_at)->isFuture())
                                    <span style="color: #93c5fd;" title="Publicação programada">
                                        <i class="far fa-clock"></i> {{ \Carbon\Carbon::parse($n->published_at)->format('d/m/Y H:i') }} (Agendada)
                                    </span>
                                @else
                                    {{ \Carbon\Carbon::parse($n->published_at)->format('d/m/Y H:i') }}
                                @endif
                            @else
                                {{ \Carbon\Carbon::parse($n->created_at)->format('d/m/Y H:i') }}
                            @endif
                        </td>
                        <td style="text-align: center;">
                            @if($n->show_on_home_modal)
                                <span style="background: rgba(201, 166, 90, 0.25); border: 1px solid var(--gold); color: var(--gold-bright); padding: 3px 8px; border-radius: 3px; font-size: 10px; font-weight: bold; text-transform: uppercase;">
                                    ★ Sim
                                </span>
                            @else
                                <span style="color: var(--text-muted); font-size: 12px;">Não</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <form action="{{ route('admin.news.toggle', $n->id) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" style="background: none; border: none; cursor: pointer; padding: 0;">
                                    @if($n->active)
                                        <span style="background: rgba(20, 83, 45, 0.6); border: 1px solid #22c55e; color: #86efac; padding: 3px 8px; border-radius: 3px; font-size: 11px; font-weight: bold; text-transform: uppercase;">
                                            Ativo
                                        </span>
                                    @else
                                        <span style="background: rgba(127, 29, 29, 0.6); border: 1px solid #ef4444; color: #fca5a5; padding: 3px 8px; border-radius: 3px; font-size: 11px; font-weight: bold; text-transform: uppercase;">
                                            Inativo
                                        </span>
                                    @endif
                                </button>
                            </form>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 8px;">
                                <button type="button" class="btn-secondary" style="padding: 4px 8px; font-size: 11px;" onclick="openEditNewsModal({{ json_encode($n) }})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('admin.news.destroy', $n->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir esta notícia?')" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger" style="padding: 4px 8px; font-size: 11px;" title="Excluir">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 20px;">Nenhuma notícia cadastrada até o momento.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Modal de Edição de Notícia -->
    <div id="edit-news-modal" class="confirm-modal-overlay" style="display: none; z-index: 1200;">
        <div class="confirm-modal" style="max-width: 680px; width: 95%; max-height: 90vh; overflow-y: auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-default); padding-bottom: 10px; margin-bottom: 15px;">
                <h3 style="color: var(--gold-bright); margin: 0; font-family: var(--font-display);">Editar Notícia</h3>
                <button type="button" onclick="closeEditNewsModal()" style="background: transparent; border: none; color: var(--gold); font-size: 18px; cursor: pointer;">✕</button>
            </div>

            <form id="edit-news-form" method="POST">
                @csrf
                @method('PUT')

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>Título da Notícia</label>
                        <input type="text" id="edit-news-title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label>Categoria</label>
                        <select id="edit-news-category" name="category">
                            <option value="Servidor">Servidor</option>
                            <option value="Atualização">Atualização</option>
                            <option value="Evento">Evento</option>
                            <option value="Manutenção">Manutenção</option>
                            <option value="Promoção">Promoção</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Resumo / Chamada (Opcional)</label>
                    <input type="text" id="edit-news-summary" name="summary">
                </div>

                <div class="form-group">
                    <label>Conteúdo Completo (Suporta HTML: &lt;b&gt;, &lt;i&gt;, &lt;u&gt;, etc.)</label>
                    <textarea id="edit-news-content" name="content" rows="7" required style="resize: vertical; font-family: inherit;"></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>URL da Imagem de Capa (Opcional)</label>
                        <input type="url" id="edit-news-image" name="image_url">
                    </div>
                    <div class="form-group">
                        <label>Agendar Publicação (Opcional)</label>
                        <input type="datetime-local" id="edit-news-published" name="published_at">
                    </div>
                </div>

                <div style="display: flex; gap: 20px; margin-top: 15px; flex-wrap: wrap;">
                    <label style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" id="edit-news-active" name="active"> Ativo
                    </label>
                    <label style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" id="edit-news-modal-check" name="show_on_home_modal"> Exibir no Modal da Página Inicial (Apenas 1)
                    </label>
                </div>

                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                    <button type="button" onclick="closeEditNewsModal()" class="btn-secondary" style="padding: 8px 16px;">Cancelar</button>
                    <button type="submit" class="btn-primary" style="padding: 8px 18px;">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</section>
</div>

<!-- ABA: GESTÃO DE JOGADORES -->
<div id="tab-jogadores" class="admin-tab-pane">
<section class="admin-card admin-card--danger">
    <h2>Desconectar jogador</h2>
    <p class="admin-card-desc">Força a desconexão imediata de uma conta conectada ao servidor.</p>
    <form action="{{ route('admin.players.disconnect') }}" method="POST" onsubmit="return handleDisconnectConfirm(event, this)">
        @csrf
        <div class="form-group">
            <label for="disconnect-username">Conta do jogador</label>
            <input type="text" id="disconnect-username" name="username" placeholder="Digite a conta" maxlength="20" required>
        </div>
        <button type="submit" class="btn-danger">Desconectar</button>
    </form>
</section>

@if (isset($csvData))
<table class="comparison-table">
    <thead>
        <tr>
            @foreach ($columns as $column)
                <th>{{ $column }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach ($sqlData as $sqlRow)
            <tr>
                @php
                    $csvRow = collect($csvData)->firstWhere('Id', $sqlRow->Id);
                @endphp

                @foreach ($columns as $column)
                    <td>
                        @if ($csvRow && isset($csvRow[$column]) && $csvRow[$column] != $sqlRow->{$column})
                            <span style="color: #a8d8a8;">{{ $sqlRow->{$column} }}</span><br>
                            <span style="color: #f0a0a0;">{{ $csvRow[$column] }}</span>
                        @else
                            @if ($sqlRow->{$column} === $sqlRow->Id)
                                <span style="color: #888;">{{ $sqlRow->{$column} }}</span>
                            @else
                                <span style="color: #a8d8a8;">{{ $sqlRow->{$column} }}</span>
                            @endif
                        @endif
                    </td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
@endif
</div>

<script>
    document.getElementById('is_external').addEventListener('change', function() {
        var local = document.getElementById('local-upload-fields');
        var external = document.getElementById('external-upload-fields');
        var fileInput = document.getElementById('file');
        var extUrl = document.querySelector('input[name="ext_url"]');
        var extName = document.querySelector('input[name="ext_name"]');
        
        if (this.checked) {
            local.style.display = 'none';
            external.style.display = 'flex';
            fileInput.required = false;
            extUrl.required = true;
            extName.required = true;
        } else {
            local.style.display = 'block';
            external.style.display = 'none';
            fileInput.required = true;
            extUrl.required = false;
            extName.required = false;
        }
    });

    function handleDisconnectConfirm(e, form) {
        e.preventDefault();
        const username = form.username.value;
        if (!username) return;
        
        if (typeof createConfirmModal === 'function') {
            createConfirmModal({
                title: 'Desconectar Jogador',
                message: 'Tem certeza que deseja forçar a desconexão deste jogador? Ele perderá qualquer progresso não salvo.',
                accountText: username,
                confirmText: 'Sim, Desconectar',
                cancelText: 'Cancelar',
                onConfirm: () => form.submit()
            });
        } else {
            if (confirm('Tem certeza que deseja forçar a desconexão de ' + username + '?')) {
                form.submit();
            }
        }
    }

    function openEditPackageModal(pkg) {
        const modal = document.getElementById('edit-package-modal');
        const form = document.getElementById('edit-package-form');
        
        form.action = '/admin/shop/packages/' + pkg.id;
        document.getElementById('edit-name').value = pkg.name || '';
        document.getElementById('edit-price').value = pkg.price || '';
        document.getElementById('edit-description').value = pkg.description || '';
        document.getElementById('edit-vip-type').value = pkg.vip_type !== undefined ? pkg.vip_type : 0;
        document.getElementById('edit-vip-days').value = pkg.vip_days || 0;
        document.getElementById('edit-coin1').value = pkg.coin1 || 0;
        document.getElementById('edit-coin2').value = pkg.coin2 || 0;
        document.getElementById('edit-coin3').value = pkg.coin3 || 0;
        document.getElementById('edit-highlight').checked = !!pkg.highlight;
        document.getElementById('edit-active').checked = pkg.active === undefined ? true : !!pkg.active;

        modal.style.display = 'flex';
    }

    function closeEditPackageModal() {
        const modal = document.getElementById('edit-package-modal');
        modal.style.display = 'none';
    }

    function openEditNewsModal(news) {
        const modal = document.getElementById('edit-news-modal');
        const form = document.getElementById('edit-news-form');

        form.action = '/admin/news/' + news.id;
        document.getElementById('edit-news-title').value = news.title || '';
        document.getElementById('edit-news-category').value = news.category || 'Servidor';
        document.getElementById('edit-news-summary').value = news.summary || '';
        document.getElementById('edit-news-content').value = news.content || '';
        document.getElementById('edit-news-image').value = news.image_url || '';
        
        if (news.published_at) {
            // Formato datetime-local: YYYY-MM-DDTHH:MM
            const dt = new Date(news.published_at);
            const formatted = dt.toISOString().slice(0, 16);
            document.getElementById('edit-news-published').value = formatted;
        } else {
            document.getElementById('edit-news-published').value = '';
        }

        document.getElementById('edit-news-active').checked = !!news.active;
        document.getElementById('edit-news-modal-check').checked = !!news.show_on_home_modal;

        modal.style.display = 'flex';
    }

    function closeEditNewsModal() {
        const modal = document.getElementById('edit-news-modal');
        modal.style.display = 'none';
    }

    // Gerenciador de Abas do Painel Admin
    function switchAdminTab(targetTabId) {
        const buttons = document.querySelectorAll('.admin-tab-btn');
        const panes = document.querySelectorAll('.admin-tab-pane');

        let matched = false;
        panes.forEach(pane => {
            if (pane.id === targetTabId) {
                pane.classList.add('active');
                matched = true;
            } else {
                pane.classList.remove('active');
            }
        });

        if (matched) {
            buttons.forEach(btn => {
                if (btn.getAttribute('data-tab') === targetTabId) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });
            // Salva na URL hash
            history.replaceState(null, null, '#' + targetTabId);
        }
    }

    document.querySelectorAll('.admin-tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const target = this.getAttribute('data-tab');
            switchAdminTab(target);
        });
    });

    // Detecta hash na URL ao carregar a página (ex: #tab-loja, #news-management)
    window.addEventListener('DOMContentLoaded', () => {
        const hash = window.location.hash.replace('#', '');
        if (hash) {
            if (hash === 'news-management' || hash === 'tab-noticias') {
                switchAdminTab('tab-noticias');
            } else if (hash === 'tab-loja' || hash.includes('shop') || hash.includes('pacote')) {
                switchAdminTab('tab-loja');
            } else if (hash === 'tab-geral' || hash.includes('upload') || hash.includes('config')) {
                switchAdminTab('tab-geral');
            } else if (hash === 'tab-jogadores' || hash.includes('disconnect')) {
                switchAdminTab('tab-jogadores');
            } else if (document.getElementById(hash)) {
                switchAdminTab(hash);
            }
        }
    });
</script>
@endsection