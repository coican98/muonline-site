<?php

namespace App\Services;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ShopOrderService
{
    /**
     * Garante que a tabela de pedidos exista no SQL Server sem necessidade de rodar migration pelo console.
     */
    public static function ensureTableExists(): void
    {
        try {
            if (!Schema::hasTable('mw_shop_orders')) {
                Schema::create('mw_shop_orders', function (Blueprint $table) {
                    $table->id();
                    $table->string('account_id', 30);
                    $table->integer('package_id');
                    $table->string('package_name', 100);
                    $table->decimal('amount', 10, 2);
                    $table->string('payment_method', 20)->default('pix'); // pix | card
                    $table->string('status', 30)->default('pending'); // pending | approved | rejected | cancelled
                    $table->string('mp_order_id', 100)->nullable();
                    $table->string('external_reference', 64)->unique();
                    $table->text('qr_code')->nullable();
                    $table->text('qr_code_base64')->nullable();
                    $table->text('ticket_url')->nullable();
                    $table->json('package_snapshot')->nullable();
                    $table->dateTime('delivered_at')->nullable();
                    $table->dateTime('expires_at')->nullable();
                    $table->timestamps();

                    $table->index(['account_id', 'status']);
                    $table->index('mp_order_id');
                });
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Obtém as configurações da loja do settings.json
     */
    public static function getShopSettings(): array
    {
        $settingsPath = storage_path('app/settings.json');
        $settings = file_exists($settingsPath) ? json_decode(file_get_contents($settingsPath), true) : [];
        return $settings['shop_settings'] ?? [];
    }

    /**
     * Obtém os pacotes da loja
     */
    public static function getPackages(): array
    {
        $settingsPath = storage_path('app/settings.json');
        $settings = file_exists($settingsPath) ? json_decode(file_get_contents($settingsPath), true) : [];
        return $settings['shop_packages'] ?? [];
    }

    /**
     * Entrega os benefícios do pacote ao jogador (atualiza MEMB_INFO e CashShopData)
     */
    public static function deliverOrder(object $order): bool
    {
        if ($order->status === 'approved' && !empty($order->delivered_at)) {
            return true; // Já entregue anteriormente
        }

        $snapshot = is_string($order->package_snapshot) ? json_decode($order->package_snapshot, true) : (array) $order->package_snapshot;
        $accountId = $order->account_id;

        DB::beginTransaction();
        try {
            // 1. Atualizar ou somar VIP no MEMB_INFO
            $targetVipType = (int) ($snapshot['vip_type'] ?? 0);
            $vipDays = (int) ($snapshot['vip_days'] ?? 0);

            if ($targetVipType > 0 && $vipDays > 0) {
                $user = DB::table('MEMB_INFO')->where('memb___id', $accountId)->first();
                if ($user) {
                    $currentVipType = (int) ($user->AccountLevel ?? 0);
                    $currentExpire = !empty($user->AccountExpireDate) ? Carbon::parse($user->AccountExpireDate) : null;
                    $now = Carbon::now();

                    // Se o VIP atual estiver ativo (data no futuro)
                    $isVipActive = $currentExpire && $currentExpire->isAfter($now) && $currentVipType > 0;

                    if ($isVipActive) {
                        if ($currentVipType === $targetVipType) {
                            // Mesmo tipo: apenas SOMA os dias na data de expiração existente
                            $newExpire = $currentExpire->addDays($vipDays);
                            $newVipType = $currentVipType;
                        } elseif ($targetVipType > $currentVipType) {
                            // Upgrade: tipo superior substitui o tipo e define a partir de agora
                            $newExpire = $now->copy()->addDays($vipDays);
                            $newVipType = $targetVipType;
                        } else {
                            // Se for menor, mantém o maior até expirar, ou ajusta
                            $newExpire = $currentExpire;
                            $newVipType = $currentVipType;
                        }
                    } else {
                        // Não tinha VIP ou já expirou: começa a contar a partir de hoje
                        $newExpire = $now->copy()->addDays($vipDays);
                        $newVipType = $targetVipType;
                    }

                    DB::table('MEMB_INFO')->where('memb___id', $accountId)->update([
                        'AccountLevel' => $newVipType,
                        'AccountExpireDate' => $newExpire,
                    ]);
                }
            }

            // 2. Incrementar moedas no CashShopData
            $coin1 = (int) ($snapshot['coin1'] ?? 0);
            $coin2 = (int) ($snapshot['coin2'] ?? 0);
            $coin3 = (int) ($snapshot['coin3'] ?? 0);

            if ($coin1 > 0 || $coin2 > 0 || $coin3 > 0) {
                try {
                    $cashData = DB::table('CashShopData')->where('AccountID', $accountId)->first();
                    if ($cashData) {
                        DB::table('CashShopData')->where('AccountID', $accountId)->update([
                            'WCoinC' => DB::raw("WCoinC + {$coin1}"),
                            'WCoinP' => DB::raw("WCoinP + {$coin2}"),
                            'GoblinPoint' => DB::raw("GoblinPoint + {$coin3}"),
                        ]);
                    } else {
                        DB::table('CashShopData')->insert([
                            'AccountID' => $accountId,
                            'WCoinC' => $coin1,
                            'WCoinP' => $coin2,
                            'GoblinPoint' => $coin3,
                        ]);
                    }
                } catch (\Throwable $e) {
                    report($e);
                }
            }

            // 3. Marcar pedido como entregue
            DB::table('mw_shop_orders')->where('id', $order->id)->update([
                'status' => 'approved',
                'delivered_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            DB::commit();
            return true;
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return false;
        }
    }
}
