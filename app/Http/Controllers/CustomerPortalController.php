<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\LoyaltyLog;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerPortalController extends Controller
{
    /**
     * Halaman Publik Klaim Poin Mandiri oleh Pelanggan
     */
    public function claimView(Request $request)
    {
        $store = config('beverage.store', []);
        $tokenFromUrl = $request->get('token', '');

        return view('customer.claim', compact('store', 'tokenFromUrl'));
    }

    /**
     * Proses Validasi & Penukaran Kode Struk Menjadi Poin Loyalitas
     */
    public function processClaim(Request $request)
    {
        $request->validate([
            'claim_token' => 'required|string',
            'phone' => 'required|string|min:9',
            'name' => 'required|string|max:255',
        ]);

        $token = strtoupper(trim($request->claim_token));

        return DB::transaction(function () use ($request, $token) {
            $transaction = Transaction::where('claim_token', $token)->first();

            if (!$transaction) {
                return back()->withInput()->with('error', 'Kode struk / token tidak ditemukan! Pastikan kode diketik dengan benar.');
            }

            if ($transaction->claim_status === 'claimed') {
                return back()->withInput()->with('error', 'Kode struk ini sudah pernah diklaim sebelumnya pada ' . $transaction->claimed_at?->format('d M Y H:i'));
            }

            // Temukan atau buat pelanggan baru
            $customer = Customer::firstOrCreate(
                ['phone' => $request->phone],
                ['name' => $request->name]
            );

            // Jika pelanggan sudah ada tapi nama belum lengkap, perbarui
            if ($customer->name === 'Pelanggan ' . substr($customer->phone, -4) && $request->filled('name')) {
                $customer->update(['name' => $request->name]);
            }

            $points = $transaction->potential_points;

            // Beri poin jika transaksi bernilai poin
            if ($points > 0) {
                $customer->increment('total_points', $points);
                $customer->updateTier();

                LoyaltyLog::create([
                    'customer_id' => $customer->id,
                    'transaction_id' => $transaction->id,
                    'points' => $points,
                    'type' => 'earn',
                    'description' => "Klaim poin dari transaksi {$transaction->invoice_code}",
                ]);
            }

            // Tandai transaksi sebagai telah diklaim
            $transaction->update([
                'claim_status' => 'claimed',
                'customer_id' => $customer->id,
                'claimed_at' => now(),
            ]);

            return redirect()->route('customer.loyalty', ['phone' => $customer->phone])
                ->with('success', "Selamat! Anda berhasil mengklaim {$points} poin loyalitas.");
        });
    }

    /**
     * Kartu Loyalitas Digital & Riwayat Belanja Pelanggan
     */
    public function loyaltyView($phone)
    {
        $customer = Customer::where('phone', $phone)
            ->with(['transactions.items', 'loyaltyLogs' => fn($q) => $q->latest()])
            ->firstOrFail();

        $store = config('beverage.store', []);
        $stampTarget = config('beverage.loyalty.stamp_target_for_free_drink', 10);

        // Hitung stamp aktif untuk penukaran minuman gratis
        // Contoh: total 14 poin = 1 reward gratis sudah tercapai, sisa 4 stamp untuk target berikutnya
        $currentStamps = $customer->total_points % $stampTarget;
        $freeDrinksEarned = (int) floor($customer->total_points / $stampTarget);

        return view('customer.loyalty', compact('customer', 'store', 'stampTarget', 'currentStamps', 'freeDrinksEarned'));
    }

    /**
     * Halaman Menu Digital Publik untuk Pelanggan (QR Meja / Dine-in)
     */
    public function menuView(Request $request)
    {
        $tableNo = $request->query('table');
        $categoryFilter = $request->query('category', 'Semua');
        $search = $request->query('q');

        $query = Product::where('is_active', true)->with('recipes.material');

        if ($categoryFilter && $categoryFilter !== 'Semua') {
            $query->where('category', $categoryFilter);
        }

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $products = $query->orderBy('category')->orderBy('name')->get();
        $allCategories = ['Semua', ...Product::where('is_active', true)->pluck('category')->unique()->values()->all()];

        $store = config('beverage.store', []);
        $variants = config('beverage.variants', []);

        return view('customer.menu', compact(
            'products',
            'allCategories',
            'categoryFilter',
            'search',
            'tableNo',
            'store',
            'variants'
        ));
    }

    /**
     * Halaman Cetak Kartu QR Meja (Table Standee) untuk Pengelola Kedai
     */
    public function tableQrView(Request $request)
    {
        $tableCount = (int) $request->query('tables', 10);
        if ($tableCount < 1) $tableCount = 1;
        if ($tableCount > 30) $tableCount = 30;

        $store = config('beverage.store', []);
        $baseUrl = url('/menu');

        return view('seller.table_qr', compact('tableCount', 'store', 'baseUrl'));
    }
}
