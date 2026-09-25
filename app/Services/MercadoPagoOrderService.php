<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MercadoPagoOrderService
{
    protected string $accessToken;
    protected string $publicKey;

    public function __construct()
    {
        $settings = ShopOrderService::getShopSettings();
        $this->accessToken = (string) ($settings['mp_access_token'] ?? env('MERCADOPAGO_ACCESS_TOKEN', ''));
        $this->publicKey = (string) ($settings['mp_public_key'] ?? env('MERCADOPAGO_PUBLIC_KEY', ''));
    }

    public function isConfigured(): bool
    {
        return !empty($this->accessToken);
    }

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    /**
     * Cria um Pedido/Order com método PIX usando a moderna API do Mercado Pago
     *
     * @param string $externalReference
     * @param float $amount
     * @param string $title
     * @param string $email
     * @param string $firstName
     * @return array [success => bool, data => array|null, error => string|null]
     */
    public function createPixOrder(
        string $externalReference,
        float $amount,
        string $title,
        string $email,
        string $firstName
    ): array {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'data' => null,
                'error' => 'Mercado Pago não configurado. Verifique as credenciais no painel administrativo.',
            ];
        }

        $payload = [
            'type' => 'online',
            'external_reference' => $externalReference,
            'total_amount' => number_format($amount, 2, '.', ''),
            'items' => [
                [
                    'title' => substr($title, 0, 100),
                    'unit_price' => number_format($amount, 2, '.', ''),
                    'quantity' => 1,
                    'currency_id' => 'BRL',
                ]
            ],
            'payer' => [
                'email' => filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : 'jogador@murootz.com.br',
                'first_name' => substr($firstName, 0, 50),
            ],
            'payment_methods' => [
                'allowed_payment_types' => [
                    ['id' => 'bank_transfer']
                ]
            ],
            'transactions' => [
                'payments' => [
                    [
                        'payment_method' => [
                            'id' => 'pix'
                        ],
                        'amount' => number_format($amount, 2, '.', ''),
                    ]
                ]
            ]
        ];

        try {
            // Chamada à API moderna de Orders (/v1/orders)
            $response = Http::withoutVerifying()->withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
                'X-Idempotency-Key' => $externalReference,
            ])->timeout(20)->post('https://api.mercadopago.com/v1/orders', $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                // Extrai o QR Code do payload de retorno da Order
                $qrCode = null;
                $qrCodeBase64 = null;
                $ticketUrl = null;

                // Na API de Orders, os detalhes de pagamento vêm em transactions.payments ou payment_method
                $payments = $data['transactions']['payments'] ?? ($data['payments'] ?? []);
                if (!empty($payments[0])) {
                    $p = $payments[0];
                    $qrCode = $p['point_of_interaction']['transaction_data']['qr_code'] ?? null;
                    $qrCodeBase64 = $p['point_of_interaction']['transaction_data']['qr_code_base64'] ?? null;
                    $ticketUrl = $p['point_of_interaction']['transaction_data']['ticket_url'] ?? null;
                }

                // Fallback de chave externa se não aninhado
                if (!$qrCode && isset($data['point_of_interaction']['transaction_data']['qr_code'])) {
                    $qrCode = $data['point_of_interaction']['transaction_data']['qr_code'];
                    $qrCodeBase64 = $data['point_of_interaction']['transaction_data']['qr_code_base64'] ?? null;
                    $ticketUrl = $data['point_of_interaction']['transaction_data']['ticket_url'] ?? null;
                }

                return [
                    'success' => true,
                    'order_id' => $data['id'] ?? null,
                    'qr_code' => $qrCode,
                    'qr_code_base64' => $qrCodeBase64,
                    'ticket_url' => $ticketUrl,
                    'raw' => $data,
                ];
            }

            // Se a API de Orders retornar erro com regras estritas de payload, tentar fallback de Payment v1 com idempotência
            Log::warning('MercadoPago Orders API error, tentando payload compatível: ' . $response->body());
            
            $fallbackPayload = [
                'transaction_amount' => (float) $amount,
                'description' => substr($title, 0, 100),
                'payment_method_id' => 'pix',
                'external_reference' => $externalReference,
                'payer' => [
                    'email' => filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : 'jogador@murootz.com.br',
                    'first_name' => substr($firstName, 0, 50),
                ]
            ];

            $fallbackResp = Http::withoutVerifying()->withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
                'X-Idempotency-Key' => $externalReference,
            ])->timeout(20)->post('https://api.mercadopago.com/v1/payments', $fallbackPayload);

            if ($fallbackResp->successful()) {
                $fData = $fallbackResp->json();
                return [
                    'success' => true,
                    'order_id' => (string) ($fData['id'] ?? ''),
                    'qr_code' => $fData['point_of_interaction']['transaction_data']['qr_code'] ?? null,
                    'qr_code_base64' => $fData['point_of_interaction']['transaction_data']['qr_code_base64'] ?? null,
                    'ticket_url' => $fData['point_of_interaction']['transaction_data']['ticket_url'] ?? null,
                    'raw' => $fData,
                ];
            }

            return [
                'success' => false,
                'data' => null,
                'error' => $fallbackResp->json()['message'] ?? $response->json()['message'] ?? 'Falha ao comunicar com Mercado Pago.',
            ];

        } catch (\Throwable $e) {
            report($e);
            return [
                'success' => false,
                'data' => null,
                'error' => 'Exceção ao gerar pagamento: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Consulta status de uma Order no Mercado Pago
     */
    public function getOrderStatus(string $orderId): ?array
    {
        if (!$this->isConfigured() || empty($orderId)) {
            return null;
        }

        try {
            // Tenta consultar em /v1/orders/{id}
            $response = Http::withoutVerifying()->withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
            ])->timeout(15)->get("https://api.mercadopago.com/v1/orders/{$orderId}");

            if ($response->successful()) {
                return $response->json();
            }

            // Tenta consulta de pagamento direta caso tenha sido gerado com fallback
            $fallback = Http::withoutVerifying()->withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
            ])->timeout(15)->get("https://api.mercadopago.com/v1/payments/{$orderId}");

            if ($fallback->successful()) {
                return $fallback->json();
            }

            return null;
        } catch (\Throwable $e) {
            report($e);
            return null;
        }
    }

    /**
     * Cria um pagamento com Cartão de Crédito via Token
     */
    public function createCardPayment(
        string $externalReference,
        float $amount,
        string $title,
        string $email,
        string $token,
        int $installments,
        ?string $paymentMethodId = null,
        ?string $issuerId = null
    ): array {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'Mercado Pago não configurado.',
            ];
        }

        $payload = [
            'transaction_amount' => (float) $amount,
            'token' => $token,
            'description' => substr($title, 0, 100),
            'installments' => $installments > 0 ? $installments : 1,
            'external_reference' => $externalReference,
            'payer' => [
                'email' => filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : 'jogador@murootz.com.br',
            ]
        ];

        if (!empty($paymentMethodId)) {
            $payload['payment_method_id'] = $paymentMethodId;
        }

        if (!empty($issuerId)) {
            $payload['issuer_id'] = $issuerId;
        }

        try {
            $response = Http::withoutVerifying()->withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
                'X-Idempotency-Key' => $externalReference,
            ])->timeout(25)->post('https://api.mercadopago.com/v1/payments', $payload);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'status' => $data['status'] ?? 'pending',
                    'status_detail' => $data['status_detail'] ?? '',
                    'order_id' => (string) ($data['id'] ?? ''),
                    'raw' => $data,
                ];
            }

            $err = $response->json();
            return [
                'success' => false,
                'error' => $err['message'] ?? 'Erro ao processar transação no cartão.',
                'raw' => $err,
            ];
        } catch (\Throwable $e) {
            report($e);
            return [
                'success' => false,
                'error' => 'Exceção ao comunicar com a operadora de cartão: ' . $e->getMessage(),
            ];
        }
    }
}
