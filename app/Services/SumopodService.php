<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SumopodService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.sumopod.base_url', 'https://api-pay-sandbox.sumopod.com/api/v1');
        $this->apiKey  = config('services.sumopod.api_key', '');
    }

    /**
     * Buat payment baru di Sumopod.
     *
     * @param  array  $data
     * @return array  ['success' => bool, 'data' => array|null, 'error' => string|null]
     */
    public function createPayment(array $data): array
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-Api-Key'    => $this->apiKey,
            ])->post("{$this->baseUrl}/payments", $data);

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }

            Log::error('[Sumopod] createPayment gagal', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return [
                'success' => false,
                'data'    => null,
                'error'   => $response->json('message', 'Terjadi kesalahan pada payment gateway.'),
            ];
        } catch (\Throwable $e) {
            Log::error('[Sumopod] Exception createPayment', ['message' => $e->getMessage()]);

            return ['success' => false, 'data' => null, 'error' => $e->getMessage()];
        }
    }

    /**
     * Cek status payment berdasarkan payment_id.
     *
     * @param  string  $paymentId  UUID dari Sumopod
     * @return array
     */
    public function getPaymentStatus(string $paymentId): array
    {
        try {
            $response = Http::withHeaders([
                'X-Api-Key' => $this->apiKey,
            ])->get("{$this->baseUrl}/payments/{$paymentId}");

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }

            Log::error('[Sumopod] getPaymentStatus gagal', [
                'payment_id' => $paymentId,
                'status'     => $response->status(),
                'body'       => $response->body(),
            ]);

            return [
                'success' => false,
                'data'    => null,
                'error'   => $response->json('message', 'Gagal mengambil status payment.'),
            ];
        } catch (\Throwable $e) {
            Log::error('[Sumopod] Exception getPaymentStatus', ['message' => $e->getMessage()]);

            return ['success' => false, 'data' => null, 'error' => $e->getMessage()];
        }
    }

    /**
     * Validasi webhook signature dari Sumopod.
     * Sumopod mengirim header X-Webhook-Signature berisi HMAC-SHA256 dari raw body.
     *
     * @param  string  $rawBody
     * @param  string  $signature  Nilai header X-Webhook-Signature
     * @return bool
     */
    public function validateWebhookSignature(string $rawBody, string $signature): bool
    {
        $secret   = config('services.sumopod.webhook_secret', '');
        $expected = hash_hmac('sha256', $rawBody, $secret);

        return hash_equals($expected, $signature);
    }
}
