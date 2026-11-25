<?php

namespace App\Http\Controllers;

use App\Models\CashFlow;
use App\Models\Material;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Dashboard Analisis Bisnis & Visualisasi Data
     */
    public function index(Request $request)
    {
        $range = $request->query('range', '7d');
        $customStart = $request->query('start_date');
        $customEnd = $request->query('end_date');

        // 1. Tentukan Rentang Waktu (Timeframe)
        if ($range === '30d') {
            $startDate = Carbon::today()->subDays(29)->startOfDay();
            $endDate = Carbon::today()->endOfDay();
            $rangeLabel = '30 Hari Terakhir';
        } elseif ($range === 'this_month') {
            $startDate = Carbon::now()->startOfMonth()->startOfDay();
            $endDate = Carbon::now()->endOfMonth()->endOfDay();
            $rangeLabel = 'Bulan Ini (' . Carbon::now()->translatedFormat('F Y') . ')';
        } elseif ($range === 'custom' && $customStart && $customEnd) {
            $startDate = Carbon::parse($customStart)->startOfDay();
            $endDate = Carbon::parse($customEnd)->endOfDay();
            $rangeLabel = $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y');
        } else {
            $range = '7d';
            $startDate = Carbon::today()->subDays(6)->startOfDay();
            $endDate = Carbon::today()->endOfDay();
            $rangeLabel = '7 Hari Terakhir';
        }

        // 2. Ambil Transaksi & Arus Kas dalam Periode
        $transactions = Transaction::whereBetween('created_at', [$startDate, $endDate])
            ->where('payment_status', 'paid')
            ->get();

        $cashFlows = CashFlow::whereBetween('transaction_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get();

        // 3. KPI Indikator Utama
        $totalSales = $transactions->sum('total_amount');
        $totalTransactions = $transactions->count();
        $averageOrderValue = $totalTransactions > 0 ? (int) round($totalSales / $totalTransactions) : 0;
        
        $totalCupsSold = TransactionItem::whereHas('transaction', function ($q) use ($startDate, $endDate) {
            $q->whereBetween('created_at', [$startDate, $endDate])->where('payment_status', 'paid');
        })->sum('quantity');

        $totalGrossProfit = $transactions->sum('gross_profit');
        $profitMargin = $totalSales > 0 ? round(($totalGrossProfit / $totalSales) * 100, 1) : 0;

        // 4. A. Grafik Tren Penjualan & Beban Harian (Line Chart)
        $dailyLabels = [];
        $dailySales = [];
        $dailyExpenses = [];
        $dailyProfits = [];

        $period = CarbonPeriod::create($startDate, $endDate);
        foreach ($period as $date) {
            $dateStr = $date->toDateString();
            $label = $date->format('d M');
            $dailyLabels[] = $label;

            $sales = $transactions->filter(function ($t) use ($dateStr) {
                return $t->created_at->toDateString() === $dateStr;
            })->sum('total_amount');

            $expenses = $cashFlows->filter(function ($cf) use ($dateStr) {
                return $cf->type === 'out' && $cf->transaction_date->toDateString() === $dateStr;
            })->sum('amount');

            $dailySales[] = (float) $sales;
            $dailyExpenses[] = (float) $expenses;
            $dailyProfits[] = (float) ($sales - $expenses);
        }

        // 5. B. Analisis Jam Sibuk Penjualan (Hourly Peak Hours Bar Chart)
        // Jam operasional: 08:00 - 22:00
        $hourlyLabels = [];
        $hourlyTransactionCounts = [];
        $hourlyRevenue = [];
        $busiestHour = null;
        $maxHourlyTx = 0;

        for ($hour = 8; $hour <= 22; $hour++) {
            $formattedHour = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00';
            $hourlyLabels[] = $formattedHour;

            $txInHour = $transactions->filter(function ($t) use ($hour) {
                return (int) $t->created_at->format('H') === $hour;
            });

            $count = $txInHour->count();
            $rev = $txInHour->sum('total_amount');

            $hourlyTransactionCounts[] = $count;
            $hourlyRevenue[] = (float) $rev;

            if ($count > $maxHourlyTx) {
                $maxHourlyTx = $count;
                $busiestHour = $formattedHour . ' - ' . str_pad($hour + 1, 2, '0', STR_PAD_LEFT) . ':00';
            }
        }

        // 6. C. Analisis Menu Terlaris (Top 5 Best Sellers) & Menu Lambat Terjual
        $itemAggregates = TransactionItem::whereHas('transaction', function ($q) use ($startDate, $endDate) {
            $q->whereBetween('created_at', [$startDate, $endDate])->where('payment_status', 'paid');
        })
        ->select('product_name', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_revenue'))
        ->groupBy('product_name')
        ->orderByDesc('total_qty')
        ->get();

        $topProducts = $itemAggregates->take(5);
        $topProductNames = $topProducts->pluck('product_name')->toArray();
        $topProductQuantities = $topProducts->pluck('total_qty')->map(fn($q) => (int)$q)->toArray();
        $topProductRevenues = $topProducts->pluck('total_revenue')->map(fn($r) => (float)$r)->toArray();

        // Cari Menu yang Kurang Laku (Slow Moving)
        $soldProductNames = $itemAggregates->pluck('product_name')->toArray();
        $allActiveProducts = Product::where('is_active', true)->pluck('name')->toArray();
        $unsoldProducts = array_diff($allActiveProducts, $soldProductNames);
        
        $slowMovingProducts = $itemAggregates->reverse()->take(5);

        // 7. D. Analisis Metode Pembayaran (Payment Method Distribution)
        $paymentMethods = [
            'cash' => [
                'label' => 'Tunai (Cash)',
                'count' => $transactions->where('payment_method', 'cash')->count(),
                'amount' => $transactions->where('payment_method', 'cash')->sum('total_amount'),
            ],
            'qris' => [
                'label' => 'QRIS (Nontunai)',
                'count' => $transactions->where('payment_method', 'qris')->count(),
                'amount' => $transactions->where('payment_method', 'qris')->sum('total_amount'),
            ],
            'transfer' => [
                'label' => 'Transfer Bank',
                'count' => $transactions->where('payment_method', 'transfer')->count(),
                'amount' => $transactions->where('payment_method', 'transfer')->sum('total_amount'),
            ],
        ];

        $cashlessTx = $paymentMethods['qris']['count'] + $paymentMethods['transfer']['count'];
        $cashlessPercentage = $totalTransactions > 0 ? round(($cashlessTx / $totalTransactions) * 100, 1) : 0;

        // 8. E. Data Bahan Baku & Riwayat Bahan Terbuang (Waste / Spoilage)
        $materials = Material::where('is_active', true)->orderBy('name')->get();
        $wasteLogs = StockMovement::where('type', 'waste')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with('material')
            ->latest()
            ->take(20)
            ->get();
        $totalWasteCost = $wasteLogs->sum(function ($w) {
            return $w->quantity * $w->unit_cost;
        });

        $store = config('beverage.store', []);

        return view('seller.analytics', compact(
            'range',
            'rangeLabel',
            'startDate',
            'endDate',
            'totalSales',
            'totalTransactions',
            'averageOrderValue',
            'totalCupsSold',
            'totalGrossProfit',
            'profitMargin',
            'dailyLabels',
            'dailySales',
            'dailyExpenses',
            'dailyProfits',
            'hourlyLabels',
            'hourlyTransactionCounts',
            'hourlyRevenue',
            'busiestHour',
            'maxHourlyTx',
            'topProducts',
            'topProductNames',
            'topProductQuantities',
            'topProductRevenues',
            'unsoldProducts',
            'slowMovingProducts',
            'paymentMethods',
            'cashlessPercentage',
            'materials',
            'wasteLogs',
            'totalWasteCost',
            'store'
        ));
    }

    /**
     * Catat Bahan Baku Terbuang / Rusak (Waste & Spoilage)
     */
    public function storeWaste(Request $request)
    {
        $request->validate([
            'material_id' => 'required|exists:materials,id',
            'quantity' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:100',
            'notes' => 'nullable|string|max:255',
        ]);

        return DB::transaction(function () use ($request) {
            $material = Material::findOrFail($request->material_id);
            $lossAmount = $request->quantity * $material->cost_per_unit;

            // 1. Kurangi stok bahan baku
            $material->decrement('stock', $request->quantity);

            // 2. Catat audit trail mutasi stok (type: waste)
            StockMovement::create([
                'material_id' => $material->id,
                'type' => 'waste',
                'quantity' => $request->quantity,
                'unit_cost' => $material->cost_per_unit,
                'notes' => "[Bahan Terbuang] {$request->reason}: " . ($request->notes ?: '-'),
            ]);

            // 3. Catat kerugian ke buku kas keluar ERP (Kategori: operasional)
            if ($lossAmount > 0) {
                CashFlow::create([
                    'type' => 'out',
                    'category' => 'operational',
                    'amount' => $lossAmount,
                    'description' => "Kerugian Bahan Rusak: {$material->name} ({$request->quantity} {$material->unit}) - {$request->reason}",
                    'transaction_date' => Carbon::today(),
                ]);
            }

            return redirect()->route('seller.analytics')->with('success', "Kerusakan bahan {$material->name} sebanyak {$request->quantity} {$material->unit} berhasil dicatat!");
        });
    }

    /**
     * Batalkan Catatan Bahan Rusak (Kembalikan Stok)
     */
    public function destroyWaste($id)
    {
        return DB::transaction(function () use ($id) {
            $movement = StockMovement::where('type', 'waste')->findOrFail($id);
            
            // Kembalikan stok bahan
            if ($movement->material) {
                $movement->material->increment('stock', $movement->quantity);
            }

            $movement->delete();

            return redirect()->route('seller.analytics')->with('success', 'Catatan bahan rusak berhasil dihapus dan stok dikembalikan.');
        });
    }
}
