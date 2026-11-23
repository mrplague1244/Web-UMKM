<?php

namespace App\Http\Controllers;

use App\Models\CashFlow;
use App\Models\Customer;
use App\Models\Material;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Services\SumopodService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PosController extends Controller
{
    /**
     * Tampilan Layar Kasir POS Minuman
     */
    public function index(Request $request)
    {
        $selectedCategory = $request->get('category', 'all');

        $query = Product::where('is_active', true);
        if ($selectedCategory !== 'all') {
            $query->where('category', $selectedCategory);
        }
        $products = $query->with('recipes.material')->orderBy('name')->get();

        $categories = config('beverage.categories', []);
        $variants = config('beverage.variants', []);
        $store = config('beverage.store', []);

        // Ambil data bahan yang menipis untuk notifikasi di header
        $lowStockMaterials = Material::where('is_active', true)
            ->whereRaw('stock <= min_stock')
            ->get();

        return view('seller.pos', compact('products', 'categories', 'variants', 'store', 'selectedCategory', 'lowStockMaterials'));
    }

    /**
     * Proses Checkout Pesanan Kasir POS
     * Mengintegrasikan:
     * - ERP: Pembuatan transaksi, perhitungan HPP, laba kotor, dan pencatatan kas masuk otomatis
     * - SCM: Pengurangan stok bahan baku otomatis berdasarkan resep (BOM)
     * - CRM: Pembuatan claim_token untuk struk / QR Code pelanggan
     * - Payment: Sumopod QRIS / Transfer (payment_status = pending, dikonfirmasi via webhook)
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.ice_level' => 'nullable|string',
            'items.*.sugar_level' => 'nullable|string',
            'items.*.topping' => 'nullable|string',
            'items.*.notes' => 'nullable|string',
            'payment_method' => 'required|in:cash,qris,transfer',
            'cashier_name' => 'nullable|string',
            'customer_phone' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request) {
            $totalAmount = 0;
            $totalCost = 0;
            $itemsData = [];

            // 1. Hitung total belanja dan HPP
            foreach ($request->items as $itemInput) {
                $product = Product::with('recipes.material')->findOrFail($itemInput['product_id']);
                $qty = (int) $itemInput['quantity'];

                $unitPrice = $product->selling_price;
                $unitCost = $product->cost_price;

                // Ekstra biaya topping jika dipilih
                $topping = $itemInput['topping'] ?? null;
                if ($topping && isset(config('beverage.variants.toppings')[$topping])) {
                    $unitPrice += config('beverage.variants.toppings')[$topping];
                }

                $subtotal = $unitPrice * $qty;
                $subtotalCost = $unitCost * $qty;

                $totalAmount += $subtotal;
                $totalCost += $subtotalCost;

                $itemsData[] = [
                    'product' => $product,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'unit_cost' => $unitCost,
                    'subtotal' => $subtotal,
                    'subtotal_cost' => $subtotalCost,
                    'ice_level' => $itemInput['ice_level'] ?? 'Normal Ice',
                    'sugar_level' => $itemInput['sugar_level'] ?? 'Normal (100%)',
                    'topping' => $topping,
                    'notes' => $itemInput['notes'] ?? null,
                ];

                // SCM Integration: Kurangi stok bahan baku berdasarkan resep minuman
                if ($product->recipes->isNotEmpty()) {
                    foreach ($product->recipes as $recipe) {
                        $material = $recipe->material;
                        if ($material) {
                            $usedQty = $recipe->quantity * $qty;
                            $material->decrement('stock', $usedQty);

                            StockMovement::create([
                                'material_id' => $material->id,
                                'type' => 'out',
                                'quantity' => $usedQty,
                                'unit_cost' => $material->cost_per_unit,
                                'reference_no' => 'SALE-' . date('Ymd'),
                                'notes' => "Pemakaian resep {$qty}x {$product->name}",
                            ]);
                        }
                    }
                }

                // Kurangi stok ketersediaan porsi produk
                $product->decrement('stock', $qty);
            }

            // 2. Hubungkan customer jika ada nomor telepon di kasir
            $customerId = null;
            if ($request->filled('customer_phone')) {
                $customer = Customer::firstOrCreate(
                    ['phone' => $request->customer_phone],
                    ['name' => 'Pelanggan ' . substr($request->customer_phone, -4)]
                );
                $customerId = $customer->id;
            }

            $grossProfit = $totalAmount - $totalCost;
            $pointsPerAmount = config('beverage.loyalty.amount_per_point', 10000);
            $potentialPoints = (int) floor($totalAmount / $pointsPerAmount);

            // Kode Struk & Claim Token Unik
            $invoiceCode = 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(5));
            $claimToken = strtoupper(Str::random(6));

            // ----------------------------------------------------------------
            // Tentukan payment_status awal berdasarkan metode pembayaran:
            // - cash     : langsung 'paid' (uang tunai diterima saat itu)
            // - qris / transfer : 'pending' → menunggu konfirmasi Sumopod webhook
            // ----------------------------------------------------------------
            $isSumopodPayment = in_array($request->payment_method, ['qris', 'transfer']);
            $initialStatus    = $isSumopodPayment ? 'pending' : 'paid';

            // 3. Simpan Transaksi Penjualan
            $transaction = Transaction::create([
                'invoice_code'  => $invoiceCode,
                'customer_id'   => $customerId,
                'total_amount'  => $totalAmount,
                'total_cost'    => $totalCost,
                'gross_profit'  => $grossProfit,
                'payment_method' => $request->payment_method,
                'payment_status' => $initialStatus,
                'claim_token'   => $claimToken,
                'claim_status'  => $customerId ? 'claimed' : 'unclaimed',
                'claimed_at'    => $customerId ? now() : null,
                'potential_points' => $potentialPoints,
                'cashier_name'  => $request->cashier_name ?: 'Kasir Toko',
            ]);

            // 4. Simpan Detail Item Transaksi
            foreach ($itemsData as $row) {
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id'     => $row['product']->id,
                    'product_name'   => $row['product']->name,
                    'quantity'       => $row['quantity'],
                    'unit_price'     => $row['unit_price'],
                    'unit_cost'      => $row['unit_cost'],
                    'subtotal'       => $row['subtotal'],
                    'subtotal_cost'  => $row['subtotal_cost'],
                    'ice_level'      => $row['ice_level'],
                    'sugar_level'    => $row['sugar_level'],
                    'topping'        => $row['topping'],
                    'notes'          => $row['notes'],
                ]);
            }

            // 5a. Pembayaran CASH → langsung catat kas masuk
            if (! $isSumopodPayment) {
                CashFlow::create([
                    'type'             => 'in',
                    'category'         => 'sales',
                    'amount'           => $totalAmount,
                    'description'      => "Penjualan POS Minuman ({$invoiceCode})",
                    'transaction_id'   => $transaction->id,
                    'transaction_date' => now()->toDateString(),
                ]);
            }

            // 5b. QRIS / Transfer → buat payment link Sumopod
            $paymentUrl = null;
            if ($isSumopodPayment) {
                /** @var SumopodService $sumopod */
                $sumopod = app(SumopodService::class);

                $methodCode = $request->payment_method === 'qris' ? 'QRIS' : 'BANK_TRANSFER';

                $result = $sumopod->createPayment([
                    'order_id'                => $invoiceCode,
                    'amount'                  => (int) round($totalAmount),
                    'currency'                => 'IDR',
                    'expires_in_hours'        => 2,
                    'success_return_url'      => route('seller.pos.receipt', $transaction->id),
                    'cancel_return_url'       => route('seller.pos'),
                    'payment_method_type_code' => $methodCode,
                ]);

                if ($result['success']) {
                    $data = $result['data'];
                    $transaction->update([
                        'sumopod_payment_id'  => $data['payment_id'] ?? null,
                        'sumopod_status'      => $data['status']     ?? 'pending',
                        'sumopod_payment_url' => $data['payment_link_url'] ?? null,
                        'sumopod_expires_at'  => isset($data['expires_at'])
                            ? \Carbon\Carbon::parse($data['expires_at'])
                            : null,
                        'sumopod_fee'         => $data['fee']        ?? 0,
                    ]);
                    $paymentUrl = $data['payment_link_url'] ?? null;
                }
                // Jika Sumopod gagal dipanggil, transaksi tetap tersimpan dengan status pending
                // kasir bisa retry melalui halaman receipt
            }

            // 6. Poin loyalitas — hanya diberikan setelah payment confirmed (cash = langsung)
            if (! $isSumopodPayment && $customerId && $potentialPoints > 0) {
                $customer->increment('total_points', $potentialPoints);
                $customer->updateTier();
            }

            // 7. Redirect ke struk; jika ada payment URL → tampilkan ke kasir
            $redirectTarget = route('seller.pos.receipt', $transaction->id);
            if ($paymentUrl) {
                return redirect($paymentUrl);
            }

            return redirect($redirectTarget)
                ->with('success', 'Pesanan berhasil diselesaikan!');
        });
    }

    /**
     * Tampilan Struk Kasir Termal & Kode QR Klaim Poin
     */
    public function getReceipt($id)
    {
        $transaction = Transaction::with(['items.product', 'customer'])->findOrFail($id);
        $store = config('beverage.store', []);

        return view('seller.pos_receipt', compact('transaction', 'store'));
    }

    /**
     * Polling status pembayaran Sumopod (dipanggil via AJAX dari halaman struk)
     * GET /seller/pos/payment-status/{id}
     */
    public function getPaymentStatus($id)
    {
        $transaction = Transaction::findOrFail($id);

        // Jika sudah paid di DB, langsung kembalikan
        if ($transaction->payment_status === 'paid') {
            return response()->json(['status' => 'paid']);
        }

        // Belum paid & ada payment_id → tanya ke Sumopod
        if ($transaction->sumopod_payment_id) {
            /** @var SumopodService $sumopod */
            $sumopod = app(SumopodService::class);
            $result  = $sumopod->getPaymentStatus($transaction->sumopod_payment_id);

            if ($result['success']) {
                $status = $result['data']['status'] ?? $transaction->sumopod_status;
                $transaction->sumopod_status = $status;

                if ($status === 'paid' && $transaction->payment_status !== 'paid') {
                    $transaction->payment_status = 'paid';
                    $transaction->save();

                    // Catat kas masuk jika belum ada
                    if (! $transaction->cashFlow) {
                        \App\Models\CashFlow::create([
                            'type'             => 'in',
                            'category'         => 'sales',
                            'amount'           => $transaction->total_amount,
                            'description'      => "Pembayaran QRIS/Transfer (Sumopod) - {$transaction->invoice_code}",
                            'transaction_id'   => $transaction->id,
                            'transaction_date' => now()->toDateString(),
                        ]);
                    }
                } else {
                    $transaction->save();
                }

                return response()->json(['status' => $status]);
            }
        }

        return response()->json(['status' => $transaction->sumopod_status ?? 'pending']);
    }
}
