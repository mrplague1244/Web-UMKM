<?php

namespace App\Http\Controllers;

use App\Models\CashFlow;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

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
}
