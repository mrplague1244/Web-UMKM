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

        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $type = $request->query('type');

        // 1. Perhitungan Saldo Kas Riil (Kumulatif Keseluruhan)
        $totalCashIn = CashFlow::where('type', 'in')->sum('amount');
        $totalCashOut = CashFlow::where('type', 'out')->sum('amount');
        $activeCashBalance = $totalCashIn - $totalCashOut;

        // 2. Metrik Omzet & Penjualan Default (Hari Ini & Bulan Ini)
        $todaySales = CashFlow::where('type', 'in')
            ->where('category', 'sales')
            ->whereDate('transaction_date', $today)
            ->sum('amount');

        $monthlySales = CashFlow::where('type', 'in')
            ->where('category', 'sales')
            ->whereDate('transaction_date', '>=', $thisMonth)
            ->sum('amount');

        // 3. Metrik Pengeluaran Default Bulan Ini
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

        // 5. Query Arus Kas dengan Filter Tanggal & Tipe
        $query = CashFlow::query();
        $isFiltered = false;

        if ($startDate) {
            $query->whereDate('transaction_date', '>=', $startDate);
            $isFiltered = true;
        }
        if ($endDate) {
            $query->whereDate('transaction_date', '<=', $endDate);
            $isFiltered = true;
        }
        if ($type && in_array($type, ['in', 'out'])) {
            $query->where('type', $type);
            $isFiltered = true;
        }

        // Metrik Spesifik Periode Terpilih
        $filteredQuery = clone $query;
        $periodCashIn = (clone $filteredQuery)->where('type', 'in')->sum('amount');
        $periodCashOut = (clone $filteredQuery)->where('type', 'out')->sum('amount');
        $periodSales = (clone $filteredQuery)->where('type', 'in')->where('category', 'sales')->sum('amount');
        $periodRestock = (clone $filteredQuery)->where('type', 'out')->where('category', 'restock')->sum('amount');
        $periodOperational = (clone $filteredQuery)->where('type', 'out')->whereIn('category', ['operational', 'electricity', 'other', 'rent', 'salary'])->sum('amount');
        $periodNetProfit = $periodSales - $periodRestock - $periodOperational;

        if ($isFiltered) {
            $cashFlows = $query->latest('transaction_date')->latest('id')->get();
        } else {
            $cashFlows = $query->latest('transaction_date')->latest('id')->take(50)->get();
        }

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
            'store',
            'startDate',
            'endDate',
            'type',
            'isFiltered',
            'periodCashIn',
            'periodCashOut',
            'periodSales',
            'periodRestock',
            'periodOperational',
            'periodNetProfit'
        ));
    }

    /**
     * Export Laporan Arus Kas & Keuangan ke CSV / Excel
     */
    public function exportCsv(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $type = $request->query('type');

        $query = CashFlow::query();
        if ($startDate) {
            $query->whereDate('transaction_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('transaction_date', '<=', $endDate);
        }
        if ($type && in_array($type, ['in', 'out'])) {
            $query->where('type', $type);
        }

        $cashFlows = $query->orderBy('transaction_date', 'asc')->orderBy('id', 'asc')->get();

        $storeName = config('beverage.store.name', 'Kedai Minuman UMKM');
        $filename = 'Laporan-Keuangan-' . ($startDate ? $startDate : 'Awal') . '-sd-' . ($endDate ? $endDate : Carbon::today()->toDateString()) . '.csv';

        $totalIn = $cashFlows->where('type', 'in')->sum('amount');
        $totalOut = $cashFlows->where('type', 'out')->sum('amount');
        $sales = $cashFlows->where('type', 'in')->where('category', 'sales')->sum('amount');
        $restock = $cashFlows->where('type', 'out')->where('category', 'restock')->sum('amount');
        $ops = $cashFlows->where('type', 'out')->whereIn('category', ['operational', 'electricity', 'rent', 'salary', 'other'])->sum('amount');
        $netProfit = $sales - $restock - $ops;

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($storeName, $startDate, $endDate, $cashFlows, $totalIn, $totalOut, $sales, $restock, $ops, $netProfit) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM agar terbaca sempurna di Microsoft Excel Windows
            fputs($file, "\xEF\xBB\xBF");

            // Header Ringkasan Laporan
            fputcsv($file, ["LAPORAN KEUANGAN & ARUS KAS - {$storeName}"]);
            fputcsv($file, ["Periode", ($startDate ? $startDate : 'Awal Catatan') . " s/d " . ($endDate ? $endDate : date('Y-m-d'))]);
            fputcsv($file, ["Tanggal Unduh", Carbon::now()->format('d/m/Y H:i') . " WIB"]);
            fputcsv($file, []);

            // Ringkasan Finansial
            fputcsv($file, ["RINGKASAN FINANSIAL PERIODE"]);
            fputcsv($file, ["Total Kas Masuk", "Rp " . number_format($totalIn, 0, ',', '.')]);
            fputcsv($file, ["Total Kas Keluar", "Rp " . number_format($totalOut, 0, ',', '.')]);
            fputcsv($file, ["Omzet Penjualan", "Rp " . number_format($sales, 0, ',', '.')]);
            fputcsv($file, ["Beban Kulakan Bahan", "Rp " . number_format($restock, 0, ',', '.')]);
            fputcsv($file, ["Beban Operasional", "Rp " . number_format($ops, 0, ',', '.')]);
            fputcsv($file, ["Estimasi Laba Bersih", "Rp " . number_format($netProfit, 0, ',', '.')]);
            fputcsv($file, []);

            // Tabel Rincian Arus Kas
            fputcsv($file, ["RINCIAN CATATAN ARUS KAS"]);
            fputcsv($file, ["No", "Tanggal", "Tipe", "Kategori", "Keterangan", "Kas Masuk (Rp)", "Kas Keluar (Rp)"]);

            $no = 1;
            foreach ($cashFlows as $cf) {
                fputcsv($file, [
                    $no++,
                    $cf->transaction_date ? $cf->transaction_date->format('d/m/Y') : '-',
                    $cf->type === 'in' ? 'Masuk' : 'Keluar',
                    ucfirst($cf->category),
                    $cf->description,
                    $cf->type === 'in' ? $cf->amount : 0,
                    $cf->type === 'out' ? $cf->amount : 0,
                ]);
            }

            // Baris Total
            fputcsv($file, []);
            fputcsv($file, ["TOTAL", "", "", "", "", $totalIn, $totalOut]);
            fputcsv($file, ["SELISIH KAS (BERSIH)", "", "", "", "", $totalIn - $totalOut, ""]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Tampilan Cetak / Print Laporan Keuangan (Siap PDF)
     */
    public function printReport(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $type = $request->query('type');

        $query = CashFlow::query();
        if ($startDate) {
            $query->whereDate('transaction_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('transaction_date', '<=', $endDate);
        }
        if ($type && in_array($type, ['in', 'out'])) {
            $query->where('type', $type);
        }

        $cashFlows = $query->orderBy('transaction_date', 'asc')->orderBy('id', 'asc')->get();

        $totalIn = $cashFlows->where('type', 'in')->sum('amount');
        $totalOut = $cashFlows->where('type', 'out')->sum('amount');
        $sales = $cashFlows->where('type', 'in')->where('category', 'sales')->sum('amount');
        $restock = $cashFlows->where('type', 'out')->where('category', 'restock')->sum('amount');
        $operational = $cashFlows->where('type', 'out')->whereIn('category', ['operational', 'electricity', 'rent', 'salary', 'other'])->sum('amount');
        $netProfit = $sales - $restock - $operational;

        $store = config('beverage.store', []);

        return view('seller.erp_print', compact(
            'cashFlows',
            'startDate',
            'endDate',
            'type',
            'totalIn',
            'totalOut',
            'sales',
            'restock',
            'operational',
            'netProfit',
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
