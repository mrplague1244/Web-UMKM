<?php

namespace App\Http\Controllers;

use App\Models\CashFlow;
use App\Models\Transaction;
use App\Services\SumopodService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SumopodWebhookController extends Controller
{
    public function __construct(protected SumopodService $sumopod) {}

    /**
     * Terima notifikasi webhook dari Sumopod.
     * Sumopod akan POST ke: /webhook/sumopod
     *
     * Payload contoh:
     * {
     *   "payment_id": "uuid",
     *   "order_id": "INV-2026-001",
     *   "status": "paid",
     *   "amount": 50000,
     *   "fee": 650,
     *   "net_amount": 49350,
     *   "paid_at": "2026-01-01T10:00:00Z"
     * }
     */
    public function handle(Request $request)
    {
        $rawBody  = $request->getContent();
        $signature = $request->header('X-Webhook-Signature', '');

        // 1. Validasi signature (nonaktifkan di sandbox jika belum ada secret)
        if (config('services.sumopod.webhook_secret')) {
            if (! $this->sumopod->validateWebhookSignature($rawBody, $signature)) {
                Log::warning('[Sumopod Webhook] Invalid signature', ['ip' => $request->ip()]);
                return response()->json(['message' => 'Invalid signature'], 403);
            }
        }

        $payload = $request->json()->all();

        Log::info('[Sumopod Webhook] Payload diterima', $payload);

        $orderId   = $payload['order_id']   ?? null;
        $status    = $payload['status']     ?? null;
        $paymentId = $payload['payment_id'] ?? null;
        $fee       = $payload['fee']        ?? 0;

        if (! $orderId || ! $status) {
            return response()->json(['message' => 'Payload tidak valid'], 422);
        }

        // 2. Cari transaksi berdasarkan invoice_code (order_id Sumopod = invoice_code kita)
        $transaction = Transaction::where('invoice_code', $orderId)->first();

        if (! $transaction) {
            Log::warning('[Sumopod Webhook] Transaksi tidak ditemukan', ['order_id' => $orderId]);
            return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
        }

        // 3. Update status sumopod di transaksi
        $transaction->sumopod_status    = $status;
        $transaction->sumopod_fee       = $fee;
        if ($paymentId) {
            $transaction->sumopod_payment_id = $paymentId;
        }

        // 4. Jika paid → update payment_status transaksi dan catat kas masuk
        if ($status === 'paid' && $transaction->payment_status !== 'paid') {
            $transaction->payment_status = 'paid';
            $transaction->save();

            // Pastikan cash_flow belum dicatat sebelumnya
            if (! $transaction->cashFlow) {
                CashFlow::create([
                    'type'             => 'in',
                    'category'         => 'sales',
                    'amount'           => $transaction->total_amount,
                    'description'      => "Pembayaran QRIS (Sumopod) - {$transaction->invoice_code}",
                    'transaction_id'   => $transaction->id,
                    'transaction_date' => now()->toDateString(),
                ]);
            }

            Log::info('[Sumopod Webhook] Pembayaran BERHASIL', ['invoice' => $orderId]);
        } elseif (in_array($status, ['expired', 'cancelled'])) {
            // Pembayaran gagal/expired → transaksi kembali ke pending
            $transaction->payment_status = 'pending';
            $transaction->save();

            Log::info('[Sumopod Webhook] Pembayaran GAGAL/EXPIRED', ['invoice' => $orderId, 'status' => $status]);
        } else {
            $transaction->save();
        }

        return response()->json(['message' => 'OK'], 200);
    }
}
