<?php

use App\Http\Controllers\AuthController;
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

// Beranda utama: langsung arahkan ke Kasir POS (akan dialihkan ke Login jika belum masuk)
Route::get('/', function () {
    return redirect()->route('seller.pos');
});

// ==========================================
// AUTENTIKASI PENGELOLA (LOGIN, REGISTER, LOGOUT)
// ==========================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ==========================================
// PORTAL PENJUAL (KASIR, SCM, ERP, CRM) - DILINDUNGI AUTH
// ==========================================
Route::prefix('seller')->middleware('auth')->group(function () {
    // 1. Kasir POS Minuman
    Route::get('/pos', [PosController::class, 'index'])->name('seller.pos');
    Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('seller.pos.checkout');
    Route::get('/pos/receipt/{id}', [PosController::class, 'getReceipt'])->name('seller.pos.receipt');

    // 2. SCM (Stok Bahan Baku, Resep, & Supplier)
    Route::get('/scm', [ScmController::class, 'index'])->name('seller.scm');
    Route::post('/scm/material', [ScmController::class, 'storeMaterial'])->name('seller.scm.material');
    Route::delete('/scm/material/{id}', [ScmController::class, 'destroyMaterial'])->name('seller.scm.material.destroy');
    Route::post('/scm/restock', [ScmController::class, 'restock'])->name('seller.scm.restock');
    Route::post('/scm/product', [ScmController::class, 'storeProduct'])->name('seller.scm.product');
    Route::delete('/scm/product/{id}', [ScmController::class, 'destroyProduct'])->name('seller.scm.product.destroy');
    Route::post('/scm/supplier', [ScmController::class, 'storeSupplier'])->name('seller.scm.supplier');
    Route::delete('/scm/supplier/{id}', [ScmController::class, 'destroySupplier'])->name('seller.scm.supplier.destroy');

    // 3. ERP (Keuangan, Laba Rugi, & Arus Kas)
    Route::get('/erp', [ErpController::class, 'index'])->name('seller.erp');
    Route::get('/erp/export', [ErpController::class, 'exportCsv'])->name('seller.erp.export');
    Route::get('/erp/print', [ErpController::class, 'printReport'])->name('seller.erp.print');
    Route::post('/erp/cashflow', [ErpController::class, 'storeCashFlow'])->name('seller.erp.cashflow');
    Route::put('/erp/cashflow/{id}', [ErpController::class, 'updateCashFlow'])->name('seller.erp.cashflow.update');
    Route::delete('/erp/cashflow/{id}', [ErpController::class, 'destroyCashFlow'])->name('seller.erp.cashflow.destroy');
    Route::delete('/erp/transaction/{id}', [ErpController::class, 'destroyTransaction'])->name('seller.erp.transaction.destroy');

    // 4. CRM (Pelanggan, Poin Loyalitas, & WhatsApp Promo)
    Route::get('/crm', [CrmController::class, 'index'])->name('seller.crm');
    Route::post('/crm/customer', [CrmController::class, 'storeCustomer'])->name('seller.crm.customer');
    Route::put('/crm/customer/{id}', [CrmController::class, 'updateCustomer'])->name('seller.crm.customer.update');
    Route::delete('/crm/customer/{id}', [CrmController::class, 'destroyCustomer'])->name('seller.crm.customer.destroy');
    Route::get('/crm/{id}', [CrmController::class, 'show'])->name('seller.crm.detail');
    Route::post('/crm/customer/{id}/points', [CrmController::class, 'adjustPoints'])->name('seller.crm.customer.points');
    Route::delete('/crm/loyalty-log/{id}', [CrmController::class, 'destroyLoyaltyLog'])->name('seller.crm.loyalty.destroy');
});

// ==========================================
// PORTAL PELANGGAN (KLAIM POIN & LOYALITAS)
// ==========================================
Route::prefix('customer')->group(function () {
    Route::get('/claim', [CustomerPortalController::class, 'claimView'])->name('customer.claim');
    Route::post('/claim', [CustomerPortalController::class, 'processClaim'])->name('customer.claim.process');
    Route::get('/loyalty/{phone}', [CustomerPortalController::class, 'loyaltyView'])->name('customer.loyalty');
});
