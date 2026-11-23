<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\LoyaltyLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CrmController extends Controller
{
    /**
     * Dashboard CRM: Database Pelanggan Minuman & Program Loyalitas
     */
    public function index()
    {
        $customers = Customer::withCount('transactions')
            ->orderByDesc('total_points')
            ->get();

        $store = config('beverage.store', []);
        $tiers = config('beverage.loyalty.tiers', []);
        $waTemplates = config('beverage.whatsapp', []);

        // Hitung ringkasan member
        $totalCustomers = $customers->count();
        $totalPointsIssued = $customers->sum('total_points');
        $vipCount = $customers->where('tier', 'VIP')->count();
        $goldCount = $customers->where('tier', 'Gold')->count();

        return view('seller.crm', compact(
            'customers',
            'store',
            'tiers',
            'waTemplates',
            'totalCustomers',
            'totalPointsIssued',
            'vipCount',
            'goldCount'
        ));
    }

    /**
     * Rincian Data Pelanggan & Riwayat Poin
     */
    public function show($id)
    {
        $customer = Customer::with(['transactions.items', 'loyaltyLogs' => function ($q) {
            $q->latest();
        }])->findOrFail($id);

        $store = config('beverage.store', []);
        $stampTarget = config('beverage.loyalty.stamp_target_for_free_drink', 10);

        return view('seller.crm_detail', compact('customer', 'store', 'stampTarget'));
    }

    /**
     * Tambah Pelanggan Baru Manual dari Kasir / Toko
     */
    public function storeCustomer(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:customers,phone',
            'email' => 'nullable|email',
        ]);

        $customer = Customer::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'total_points' => 0,
            'tier' => 'Bronze',
        ]);

        return redirect()->route('seller.crm')->with('success', "Pelanggan {$customer->name} berhasil didaftarkan!");
    }

    /**
     * Perbarui Data Pelanggan / Member
     */
    public function updateCustomer(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => ['required', 'string', Rule::unique('customers', 'phone')->ignore($customer->id)],
            'email' => 'nullable|email',
        ]);

        $customer->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
        ]);

        return back()->with('success', "Data member {$customer->name} berhasil diperbarui!");
    }

    /**
     * Hapus Data Pelanggan
     */
    public function destroyCustomer($id)
    {
        $customer = Customer::findOrFail($id);
        $name = $customer->name;
        $customer->delete();

        return redirect()->route('seller.crm')->with('success', "Pelanggan {$name} berhasil dihapus!");
    }

    /**
     * Tambah / Sesuaikan Poin Loyalitas Pelanggan (Bonus atau Penukaran Reward)
     */
    public function adjustPoints(Request $request, $id)
    {
        $request->validate([
            'points' => 'required|integer|min:1',
            'type' => 'required|in:earn,redeem',
            'description' => 'required|string|max:255',
        ]);

        $customer = Customer::findOrFail($id);
        $points = (int) $request->points;

        if ($request->type === 'redeem') {
            if ($customer->total_points < $points) {
                return back()->with('error', 'Poin pelanggan tidak mencukupi untuk penukaran ini!');
            }
            $customer->decrement('total_points', $points);
            $logPoints = -$points;
        } else {
            $customer->increment('total_points', $points);
            $logPoints = $points;
        }

        $customer->updateTier();

        LoyaltyLog::create([
            'customer_id' => $customer->id,
            'points' => $logPoints,
            'type' => $request->type,
            'description' => $request->description,
        ]);

        return back()->with('success', "Poin pelanggan {$customer->name} berhasil disesuaikan ({$logPoints} poin)!");
    }

    /**
     * Hapus Riwayat Mutasi Poin & Kembalikan Saldo Poin
     */
    public function destroyLoyaltyLog($id)
    {
        $log = LoyaltyLog::findOrFail($id);
        $customer = $log->customer;

        if ($customer) {
            if ($log->type === 'earn') {
                $customer->decrement('total_points', min($customer->total_points, $log->points));
            } elseif ($log->type === 'redeem') {
                $customer->increment('total_points', abs($log->points));
            }
            $customer->updateTier();
        }

        $log->delete();

        return back()->with('success', 'Catatan mutasi poin berhasil dihapus dan saldo poin disesuaikan!');
    }
}
