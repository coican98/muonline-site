<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\ShopOrderService;
use App\Services\MercadoPagoOrderService;

class MercadoPagoWebhookController extends Controller
{
    /**
     * Recebe notificações assíncronas do Mercado Pago
     */
    public function handle(Request $request)
    {
        ShopOrderService::ensureTableExists();

        // Obtém o tópico ou tipo de notificação
        $type = $request->input('type') ?? $request->input('topic');
        $id = $request->input('data.id') ?? $request->input('id');

        Log::info("MercadoPago Webhook recebido: type={$type}, id={$id}", $request->all());

        if (empty($id)) {
            return response()->json(['status' => 'ignored', 'message' => 'No ID provided'], 200);
        }

        try {
            $mp = new MercadoPagoOrderService();
            $mpData = $mp->getOrderStatus((string) $id);

            if (!$mpData) {
                return response()->json(['status' => 'not_found'], 200);
            }

            // Busca na base o pedido correspondente por mp_order_id ou external_reference
            $externalRef = $mpData['external_reference'] ?? null;
            $order = null;

            if ($externalRef) {
                $order = DB::table('mw_shop_orders')->where('external_reference', $externalRef)->first();
            }

            if (!$order) {
                $order = DB::table('mw_shop_orders')->where('mp_order_id', (string) $id)->first();
            }

            if (!$order) {
                Log::warning("MercadoPago Webhook: pedido não localizado localmente para order/payment ID {$id}");
                return response()->json(['status' => 'order_not_tracked'], 200);
            }

            // Verifica se está aprovado/pago
            $status = $mpData['status'] ?? ($mpData['order_status'] ?? null);
            if (in_array($status, ['paid', 'approved', 'closed'])) {
                ShopOrderService::deliverOrder($order);
                Log::info("MercadoPago Webhook: pedido {$order->external_reference} entregue com sucesso!");
            } elseif (in_array($status, ['rejected', 'cancelled'])) {
                DB::table('mw_shop_orders')->where('id', $order->id)->update([
                    'status' => 'rejected',
                    'updated_at' => now(),
                ]);
            }

            return response()->json(['status' => 'success'], 200);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 200);
        }
    }
}
