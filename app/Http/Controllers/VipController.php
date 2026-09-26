<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Services\ShopOrderService;
use App\Services\MercadoPagoOrderService;

class VipController extends Controller
{
    /**
     * Tela inicial da loja com a listagem de pacotes
     */
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/')->with('error', 'Esta página está restrita a usuários que estão logados!');
        }

        ShopOrderService::ensureTableExists();

        $shopSettings = ShopOrderService::getShopSettings();
        $packages = ShopOrderService::getPackages(true);

        return view('shop', [
            'shopSettings' => $shopSettings,
            'packages' => $packages,
            'title' => 'Mu Rootz - Loja de Pacotes'
        ]);
    }

    /**
     * Tela de checkout para o pacote selecionado (escolha entre PIX e Cartão)
     */
    public function checkout(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/')->with('error', 'Você precisa estar logado para adquirir um pacote!');
        }

        ShopOrderService::ensureTableExists();

        $packageId = (int) $request->input('package_id');
        $packages = ShopOrderService::getPackages(true);
        $selectedPackage = null;
        foreach ($packages as $pkg) {
            if ($pkg['id'] == $packageId) {
                $selectedPackage = $pkg;
                break;
            }
        }

        if (!$selectedPackage) {
            return redirect()->route('shop')->with('error', 'Pacote não encontrado ou temporariamente indisponível.');
        }

        $user = Auth::user();
        $membInfo = DB::table('MEMB_INFO')->where('memb___id', $user->username)->first();
        $currentVipType = (int) ($membInfo->AccountLevel ?? 0);
        $currentExpire = !empty($membInfo->AccountExpireDate) ? Carbon::parse($membInfo->AccountExpireDate) : null;
        $isVipActive = $currentExpire && $currentExpire->isAfter(Carbon::now()) && $currentVipType > 0;

        $targetVipType = (int) ($selectedPackage['vip_type'] ?? 0);

        // REGRA DE VIP: Se o novo pacote solicitado for menor que o VIP ativo atual,
        // impede ANTES de criar a requisição no Mercado Pago para evitar problemas com estorno
        if ($isVipActive && $targetVipType > 0 && $targetVipType < $currentVipType) {
            $shopSettings = ShopOrderService::getShopSettings();
            $currentVipName = $shopSettings['vip_tiers'][$currentVipType] ?? "VIP {$currentVipType}";
            $targetVipName = $shopSettings['vip_tiers'][$targetVipType] ?? "VIP {$targetVipType}";
            $formattedExpire = $currentExpire->format('d/m/Y H:i');

            return redirect()->route('shop')->with('error', 
                "Você já possui o plano {$currentVipName} ativo até {$formattedExpire}. Não é permitido adquirir um plano inferior ({$targetVipName}) enquanto seu VIP atual estiver ativo."
            );
        }

        $shopSettings = ShopOrderService::getShopSettings();
        $mp = new MercadoPagoOrderService();

        return view('shop-checkout', [
            'package' => $selectedPackage,
            'shopSettings' => $shopSettings,
            'publicKey' => $mp->getPublicKey(),
            'currentVipType' => $currentVipType,
            'currentExpire' => $currentExpire,
            'isVipActive' => $isVipActive,
            'title' => 'Mu Rootz - Finalizar Pedido'
        ]);
    }

    /**
     * Processa a criação da requisição no Mercado Pago (PIX ou Cartão) com trava Anti-Spam
     */
    public function processPayment(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/')->with('error', 'Você precisa estar logado para continuar!');
        }

        ShopOrderService::ensureTableExists();

        $packageId = (int) $request->input('package_id');
        $paymentMethod = $request->input('payment_method', 'pix'); // pix | card
        $packages = ShopOrderService::getPackages(true);

        $selectedPackage = null;
        foreach ($packages as $pkg) {
            if ($pkg['id'] == $packageId) {
                $selectedPackage = $pkg;
                break;
            }
        }

        if (!$selectedPackage) {
            return redirect()->route('shop')->with('error', 'Pacote não encontrado ou temporariamente indisponível.');
        }

        $user = Auth::user();
        $accountId = $user->username;

        // 1. Verificação de VIP (Prevenção de Downgrade)
        $membInfo = DB::table('MEMB_INFO')->where('memb___id', $accountId)->first();
        $currentVipType = (int) ($membInfo->AccountLevel ?? 0);
        $currentExpire = !empty($membInfo->AccountExpireDate) ? Carbon::parse($membInfo->AccountExpireDate) : null;
        $isVipActive = $currentExpire && $currentExpire->isAfter(Carbon::now()) && $currentVipType > 0;
        $targetVipType = (int) ($selectedPackage['vip_type'] ?? 0);

        if ($isVipActive && $targetVipType > 0 && $targetVipType < $currentVipType) {
            return redirect()->route('shop')->with('error', 'Ação bloqueada: você não pode rebaixar seu nível de VIP atual enquanto ele estiver ativo.');
        }

        // 2. REGRA ANTI-SPAM:
        // Não cancela o pedido anterior; impede sequer enviar a requisição para o Mercado Pago
        // e exibe retorno visual informando que o pagamento já está em processamento.
        $recentPendingOrder = DB::table('mw_shop_orders')
            ->where('account_id', $accountId)
            ->where('status', 'pending')
            ->where('created_at', '>=', Carbon::now()->subMinutes(5))
            ->orderBy('id', 'desc')
            ->first();

        if ($recentPendingOrder) {
            $minutesLeft = 5 - Carbon::parse($recentPendingOrder->created_at)->diffInMinutes(Carbon::now());
            $minutesText = $minutesLeft > 1 ? "{$minutesLeft} minutos" : "1 minuto";
            
            return redirect()->route('shop.order', ['externalReference' => $recentPendingOrder->external_reference])
                ->with('info', "Seu pagamento já está em processamento! Aguarde alguns instantes ({$minutesText}) antes de tentar gerar uma nova fatura.");
        }

        // 3. Comunicação com Mercado Pago via Orders API
        $mp = new MercadoPagoOrderService();
        if (!$mp->isConfigured()) {
            return redirect()->back()->with('error', 'O sistema de pagamentos automáticos está em configuração pela administração.');
        }

        $externalReference = 'ROOTZ-' . strtoupper(Str::random(12));
        $amount = (float) $selectedPackage['price'];
        $title = "Mu Rootz - " . $selectedPackage['name'];

        if ($paymentMethod === 'pix') {
            $result = $mp->createPixOrder(
                $externalReference,
                $amount,
                $title,
                $user->mail_addr ?? 'jogador@murootz.com.br',
                $user->name ?? $user->username
            );

            if (!$result['success']) {
                return redirect()->back()->with('error', 'Não foi possível gerar a chave PIX: ' . ($result['error'] ?? 'Tente novamente.'));
            }

            // Grava o pedido pendente
            DB::table('mw_shop_orders')->insert([
                'account_id' => $accountId,
                'package_id' => $selectedPackage['id'],
                'package_name' => $selectedPackage['name'],
                'amount' => $amount,
                'payment_method' => 'pix',
                'status' => 'pending',
                'mp_order_id' => $result['order_id'],
                'external_reference' => $externalReference,
                'qr_code' => $result['qr_code'],
                'qr_code_base64' => $result['qr_code_base64'],
                'ticket_url' => $result['ticket_url'],
                'package_snapshot' => json_encode($selectedPackage),
                'expires_at' => Carbon::now()->addMinutes(30),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            return redirect()->route('shop.order', ['externalReference' => $externalReference])
                ->with('success', 'Chave PIX gerada com sucesso! Conclua o pagamento para ativação imediata.');
        }

        if ($paymentMethod === 'card') {
            $token = $request->input('card_token');
            $installments = (int) $request->input('installments', 1);
            $paymentMethodId = $request->input('payment_method_id') ?: null;
            $issuerId = $request->input('issuer_id') ?: null;

            if (empty($token)) {
                return redirect()->back()->with('error', 'Token do cartão não gerado. Verifique os dados digitados.');
            }

            $result = $mp->createCardPayment(
                $externalReference,
                $amount,
                $title,
                $user->mail_addr ?? 'jogador@murootz.com.br',
                $token,
                $installments,
                $paymentMethodId,
                $issuerId
            );

            if (!$result['success']) {
                return redirect()->back()->with('error', 'Transação no cartão não autorizada: ' . ($result['error'] ?? 'Consulte a emissora.'));
            }

            $status = $result['status'] === 'approved' ? 'approved' : ($result['status'] === 'in_process' ? 'pending' : 'rejected');

            $orderId = DB::table('mw_shop_orders')->insertGetId([
                'account_id' => $accountId,
                'package_id' => $selectedPackage['id'],
                'package_name' => $selectedPackage['name'],
                'amount' => $amount,
                'payment_method' => 'card',
                'status' => $status,
                'mp_order_id' => $result['order_id'],
                'external_reference' => $externalReference,
                'package_snapshot' => json_encode($selectedPackage),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            if ($status === 'approved') {
                $order = DB::table('mw_shop_orders')->where('id', $orderId)->first();
                ShopOrderService::deliverOrder($order);

                return redirect()->route('account.settings')
                    ->with('success', 'Pagamento aprovado com sucesso! Os benefícios do pacote já foram creditados na sua conta.');
            }

            return redirect()->route('shop.order', ['externalReference' => $externalReference])
                ->with('info', 'Pagamento com cartão enviado para análise da operadora.');
        }

        return redirect()->back()->with('error', 'Método de pagamento inválido.');
    }

    /**
     * Tela com detalhes do pedido (QR Code do PIX, status e botão de checagem)
     */
    public function showOrder(Request $request, $externalReference)
    {
        if (!Auth::check()) {
            return redirect('/')->with('error', 'Faça login para visualizar seus pedidos.');
        }

        ShopOrderService::ensureTableExists();

        $order = DB::table('mw_shop_orders')
            ->where('external_reference', $externalReference)
            ->where('account_id', Auth::user()->username)
            ->first();

        if (!$order) {
            return redirect()->route('shop')->with('error', 'Pedido não encontrado.');
        }

        $shopSettings = ShopOrderService::getShopSettings();

        return view('shop-order', [
            'order' => $order,
            'shopSettings' => $shopSettings,
            'title' => 'Mu Rootz - Pagamento do Pedido'
        ]);
    }

    /**
     * Endpoint JSON para polling de status pelo frontend (verifica se foi pago)
     */
    public function checkOrderStatus(Request $request, $externalReference)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        ShopOrderService::ensureTableExists();

        $order = DB::table('mw_shop_orders')
            ->where('external_reference', $externalReference)
            ->where('account_id', Auth::user()->username)
            ->first();

        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        // Se ainda estiver pendente, consulta o Mercado Pago em tempo real
        if ($order->status === 'pending' && !empty($order->mp_order_id)) {
            $mp = new MercadoPagoOrderService();
            $mpData = $mp->getOrderStatus($order->mp_order_id);

            if ($mpData) {
                // Checa status na API de Orders ou Payments
                $orderStatus = $mpData['status'] ?? ($mpData['order_status'] ?? null);
                if (in_array($orderStatus, ['paid', 'approved', 'closed'])) {
                    ShopOrderService::deliverOrder($order);
                    $order = DB::table('mw_shop_orders')->where('id', $order->id)->first();
                }
            }
        }

        return response()->json([
            'status' => $order->status,
            'is_paid' => $order->status === 'approved',
            'delivered_at' => $order->delivered_at,
        ]);
    }

    /**
     * Cancelamento manual do pedido pelo próprio usuário
     */
    public function cancelOrder(Request $request, $externalReference)
    {
        if (!Auth::check()) {
            return redirect('/')->with('error', 'Faça login para continuar.');
        }

        ShopOrderService::ensureTableExists();

        $order = DB::table('mw_shop_orders')
            ->where('external_reference', $externalReference)
            ->where('account_id', Auth::user()->username)
            ->first();

        if (!$order) {
            return redirect()->back()->with('error', 'Pedido não encontrado.');
        }

        if ($order->status !== 'pending') {
            return redirect()->back()->with('error', 'Apenas pedidos pendentes podem ser cancelados.');
        }

        DB::table('mw_shop_orders')
            ->where('id', $order->id)
            ->update([
                'status' => 'cancelled',
                'updated_at' => Carbon::now(),
            ]);

        return redirect()->route('account.settings')
            ->with('success', "O pedido {$order->package_name} ({$order->external_reference}) foi cancelado com sucesso!");
    }
}
