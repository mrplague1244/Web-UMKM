<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\LoyaltyLog;
use Illuminate\Http\Request;

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
}
