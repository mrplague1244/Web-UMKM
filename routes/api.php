<?php

use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| REST API Routes: Ekstensi Mobile / PWA
|--------------------------------------------------------------------------
*/

// Katalog Menu Minuman
Route::get('/products', function () {
    return response()->json([
        'success' => true,
        'data' => Product::where('is_active', true)->with('recipes.material')->get(),
    ]);
});

// Info Profil Member
Route::get('/customer/{phone}', function ($phone) {
    $customer = Customer::where('phone', $phone)->with('loyaltyLogs')->first();
    if (!$customer) {
        return response()->json(['success' => false, 'message' => 'Pelanggan tidak ditemukan'], 404);
    }
    return response()->json(['success' => true, 'data' => $customer]);
});
