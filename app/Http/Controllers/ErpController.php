<?php

namespace App\Http\Controllers;

use App\Models\CashFlow;
use App\Models\Customer;
use App\Models\LoyaltyLog;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ErpController extends Controller
{
    /**
     * Dashboard ERP: Finansial, Laba Rugi, dan Buku Kas Masuk/Keluar
     */
    public function index(Request $request)
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        // 1. Perhitungan Saldo Kas Riil
        $totalCashIn = CashFlow::where('type', 'in')->sum('amount');
        $totalCashOut = CashFlow::where('type', 'out')->sum('amount');
        $activeCashBalance = $totalCashIn - $totalCashOut;

        // 2. Metrik Omzet & Penjualan
        $todaySales = CashFlow::where('type', 'in')
            ->where('category', 'sales')
            ->whereDate('transaction_date', $today)
            ->sum('amount');

        $monthlySales = CashFlow::where('type', 'in')
            ->where('category', 'sales')
            ->whereDate('transaction_date', '>=', $thisMonth)
            ->sum('amount');

        // 3. Metrik Pengeluaran
        $monthlyRestock = CashFlow::where('type', 'out')
            ->where('category', 'restock')
            ->whereDate('transaction_date', '>=', $thisMonth)
            ->sum('amount');

        $monthlyOperational = CashFlow::where('type', 'out')
            ->whereIn('category', ['operational', 'electricity', 'other'])
            ->whereDate('transaction_date', '>=', $thisMonth)
            ->sum('amount');

        // 4. Estimasi Laba Bersih Bulan Ini
        // Formula: Omzet Penjualan - Biaya Kulakan Bahan - Biaya Operasional
        $estimatedNetProfit = $monthlySales - $monthlyRestock - $monthlyOperational;

        // 5. Riwayat Arus Kas Buku Kas
        $cashFlows = CashFlow::latest('transaction_date')
            ->latest('id')
            ->take(50)
            ->get();

        // 6. Transaksi Penjualan Terakhir
        $recentTransactions = Transaction::with(['items', 'customer'])
            ->latest()
            ->take(10)
            ->get();

        $store = config('beverage.store', []);

        return view('seller.erp', compact(
            'totalCashIn',
            'totalCashOut',
            'activeCashBalance',
            'todaySales',
            'monthlySales',
            'monthlyRestock',
            'monthlyOperational',
            'estimatedNetProfit',
            'cashFlows',
            'recentTransactions',
            'store'
        ));
    }

    /**
     * Catat Pengeluaran Operasional / Kas Masuk Manual
     */
    public function storeCashFlow(Request $request)
    {
        $request->validate([
            'type' => 'required|in:in,out',
            'category' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string|max:255',
            'transaction_date' => 'required|date',
        ]);

        CashFlow::create([
            'type' => $request->type,
            'category' => $request->category,
            'amount' => $request->amount,
            'description' => $request->description,
            'transaction_date' => $request->transaction_date,
        ]);

        $message = $request->type === 'in' ? 'Pemasukan kas berhasil dicatat!' : 'Pengeluaran kas berhasil dicatat!';
        return redirect()->route('seller.erp')->with('success', $message);
    }

    /**
     * Perbarui Catatan Arus Kas
     */
    public function updateCashFlow(Request $request, $id)
    {
        $request->validate([
            'type' => 'required|in:in,out',
            'category' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string|max:255',
            'transaction_date' => 'required|date',
        ]);

        $cashFlow = CashFlow::findOrFail($id);
        $cashFlow->update([
            'type' => $request->type,
            'category' => $request->category,
            'amount' => $request->amount,
            'description' => $request->description,
            'transaction_date' => $request->transaction_date,
        ]);

        return redirect()->route('seller.erp')->with('success', 'Catatan arus kas berhasil diperbarui!');
    }

    /**
     * Hapus Catatan Arus Kas
     */
    public function destroyCashFlow($id)
    {
        $cashFlow = CashFlow::findOrFail($id);
        $cashFlow->delete();

        return redirect()->route('seller.erp')->with('success', 'Catatan arus kas berhasil dihapus!');
    }

    /**
     * Batalkan & Hapus Transaksi Kasir POS (Void)
     * Mengembalikan stok bahan baku/produk dan membatalkan pencatatan arus kas serta poin
     */
    public function destroyTransaction($id)
    {
        return DB::transaction(function () use ($id) {
            $transaction = Transaction::with('items')->findOrFail($id);
            $invoice = $transaction->invoice_code;

            // 1. Kembalikan stok bahan baku dari resep & kuantitas produk
            foreach ($transaction->items as $item) {
                $product = Product::with('recipes.material')->find($item->product_id);
                if ($product) {
                    $product->increment('stock', $item->quantity);
                    if ($product->recipes->isNotEmpty()) {
                        foreach ($product->recipes as $recipe) {
                            if ($recipe->material) {
                                $recipe->material->increment('stock', $recipe->quantity * $item->quantity);
                            }
                        }
                    }
                }
            }

            // 2. Kembalikan poin loyalitas pelanggan jika sudah diklaim
            if ($transaction->claim_status === 'claimed' && $transaction->customer_id) {
                $customer = Customer::find($transaction->customer_id);
                if ($customer && $transaction->potential_points > 0) {
                    $customer->decrement('total_points', min($customer->total_points, $transaction->potential_points));
                    $customer->updateTier();
                }
            }

            // 3. Hapus catatan buku kas terkait transaksi ini
            CashFlow::where('transaction_id', $transaction->id)->delete();

            // 4. Hapus log loyalitas terkait transaksi ini
            LoyaltyLog::where('transaction_id', $transaction->id)->delete();

            // 5. Hapus transaksi (item transaksi otomatis cascade delete)
            $transaction->delete();

            return redirect()->route('seller.erp')->with('success', "Transaksi {$invoice} berhasil dibatalkan dan stok dikembalikan!");
        });
    }
}
