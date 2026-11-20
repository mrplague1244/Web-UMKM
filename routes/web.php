<?php

use App\Http\Controllers\CrmController;
use App\Http\Controllers\CustomerPortalController;
use App\Http\Controllers\ErpController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ScmController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes: UMKM Penjual Minuman (ERP - SCM - CRM)
|--------------------------------------------------------------------------
*/

// Beranda utama: langsung arahkan ke Kasir POS
Route::get('/', function () {
    return redirect()->route('seller.pos');
});

// ==========================================
// PORTAL PENJUAL (KASIR, SCM, ERP, CRM)
// ==========================================
Route::prefix('seller')->group(function () {
    // 1. Kasir POS Minuman
    Route::get('/pos', [PosController::class, 'index'])->name('seller.pos');
    Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('seller.pos.checkout');
    Route::get('/pos/receipt/{id}', [PosController::class, 'getReceipt'])->name('seller.pos.receipt');

    // 2. SCM (Stok Bahan Baku, Resep, & Supplier)
    Route::get('/scm', [ScmController::class, 'index'])->name('seller.scm');
    Route::post('/scm/material', [ScmController::class, 'storeMaterial'])->name('seller.scm.material');
    Route::post('/scm/restock', [ScmController::class, 'restock'])->name('seller.scm.restock');
    Route::post('/scm/product', [ScmController::class, 'storeProduct'])->name('seller.scm.product');
    Route::post('/scm/supplier', [ScmController::class, 'storeSupplier'])->name('seller.scm.supplier');

    // 3. ERP (Keuangan, Laba Rugi, & Arus Kas)
    Route::get('/erp', [ErpController::class, 'index'])->name('seller.erp');
    Route::post('/erp/cashflow', [ErpController::class, 'storeCashFlow'])->name('seller.erp.cashflow');

    // 4. CRM (Pelanggan, Poin Loyalitas, & WhatsApp Promo)
    Route::get('/crm', [CrmController::class, 'index'])->name('seller.crm');
    Route::post('/crm/customer', [CrmController::class, 'storeCustomer'])->name('seller.crm.customer');
    Route::get('/crm/{id}', [CrmController::class, 'show'])->name('seller.crm.detail');
});

// ==========================================
// PORTAL PELANGGAN (KLAIM POIN & LOYALITAS)
// ==========================================
Route::prefix('customer')->group(function () {
    Route::get('/claim', [CustomerPortalController::class, 'claimView'])->name('customer.claim');
    Route::post('/claim', [CustomerPortalController::class, 'processClaim'])->name('customer.claim.process');
    Route::get('/loyalty/{phone}', [CustomerPortalController::class, 'loyaltyView'])->name('customer.loyalty');
});
