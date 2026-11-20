<?php

namespace Tests\Feature;

use App\Models\CashFlow;
use App\Models\Customer;
use App\Models\Material;
use App\Models\Product;
use App\Models\ProductRecipe;
use App\Models\Supplier;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Uji Alur Kasir POS: Checkout Minuman -> Resep SCM Memotong Bahan Baku -> ERP Catat Kas Masuk
     */
    public function test_pos_checkout_beverage_reduces_recipe_materials_and_creates_cashflow(): void
    {
        // 1. Siapkan Bahan Baku (SCM)
        $matCup = Material::create([
            'name' => 'Cup Plastik 16oz',
            'category' => 'Packaging',
            'unit' => 'pcs',
            'stock' => 100,
            'min_stock' => 10,
            'cost_per_unit' => 500,
        ]);

        $matSusu = Material::create([
            'name' => 'Susu UHT Fresh',
            'category' => 'Bahan Baku',
            'unit' => 'ml',
            'stock' => 5000,
            'min_stock' => 500,
            'cost_per_unit' => 20,
        ]);

        $matKopi = Material::create([
            'name' => 'Biji Kopi Espresso',
            'category' => 'Bahan Baku',
            'unit' => 'gram',
            'stock' => 1000,
            'min_stock' => 100,
            'cost_per_unit' => 200,
        ]);

        // 2. Siapkan Menu Minuman & Resep (BOM)
        $product = Product::create([
            'name' => 'Es Kopi Susu Gula Aren',
            'category' => 'Coffee',
            'selling_price' => 18000,
            'cost_price' => 7500,
            'stock' => 50,
            'min_stock' => 5,
        ]);

        ProductRecipe::create(['product_id' => $product->id, 'material_id' => $matCup->id, 'quantity' => 1]);
        ProductRecipe::create(['product_id' => $product->id, 'material_id' => $matSusu->id, 'quantity' => 120]);
        ProductRecipe::create(['product_id' => $product->id, 'material_id' => $matKopi->id, 'quantity' => 15]);

        // 3. Eksekusi Checkout Kasir POS (Pesan 2 Cup)
        $response = $this->post(route('seller.pos.checkout'), [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'ice_level' => 'Normal Ice',
                    'sugar_level' => 'Less Sugar (50%)',
                    'topping' => null,
                    'notes' => 'Gula sedikit',
                ]
            ],
            'payment_method' => 'cash',
            'cashier_name' => 'Kasir Uji',
        ]);

        $response->assertSessionHas('success');

        // 4. Verifikasi SCM: Stok Bahan Baku Terpotong Sesuai Resep
        // 2 cup x 1 pcs = 2 cup terpakai (100 - 2 = 98)
        $this->assertEquals(98, $matCup->fresh()->stock);
        // 2 cup x 120 ml = 240 ml terpakai (5000 - 240 = 4760)
        $this->assertEquals(4760, $matSusu->fresh()->stock);
        // 2 cup x 15 gr = 30 gr terpakai (1000 - 30 = 970)
        $this->assertEquals(970, $matKopi->fresh()->stock);

        // 5. Verifikasi ERP: Transaksi & Kas Masuk Tercatat
        $trx = Transaction::first();
        $this->assertNotNull($trx);
        $this->assertEquals(36000, $trx->total_amount); // 2 x 18.000 = 36.000
        $this->assertEquals(15000, $trx->total_cost);   // 2 x 7.500 = 15.000
        $this->assertEquals(21000, $trx->gross_profit); // 36.000 - 15.000 = 21.000
        $this->assertEquals(3, $trx->potential_points); // 36.000 / 10.000 = 3 poin

        $cashFlow = CashFlow::where('category', 'sales')->first();
        $this->assertNotNull($cashFlow);
        $this->assertEquals('in', $cashFlow->type);
        $this->assertEquals(36000, $cashFlow->amount);
    }

    /**
     * Uji SCM Kulakan: Pembelian Bahan -> Stok Bertambah -> ERP Catat Kas Keluar
     */
    public function test_scm_restock_increases_material_stock_and_records_cash_outflow(): void
    {
        $supplier = Supplier::create([
            'name' => 'Distributor Susu Segar',
            'phone' => '08123456789',
        ]);

        $material = Material::create([
            'name' => 'Susu UHT Full Cream',
            'category' => 'Bahan Baku',
            'unit' => 'ml',
            'stock' => 1000,
            'min_stock' => 200,
            'cost_per_unit' => 20,
        ]);

        // Eksekusi Restock: beli 5.000 ml dengan harga Rp 20/ml = Rp 100.000
        $response = $this->post(route('seller.scm.restock'), [
            'material_id' => $material->id,
            'supplier_id' => $supplier->id,
            'quantity' => 5000,
            'unit_cost' => 20,
            'reference_no' => 'PO-TEST-001',
            'notes' => 'Restock mingguan',
        ]);

        $response->assertSessionHas('success');

        // Verifikasi SCM: stok bertambah
        $this->assertEquals(6000, $material->fresh()->stock);

        // Verifikasi ERP: buku kas mencatat kas keluar Rp 100.000
        $cashOut = CashFlow::where('category', 'restock')->first();
        $this->assertNotNull($cashOut);
        $this->assertEquals('out', $cashOut->type);
        $this->assertEquals(100000, $cashOut->amount);
    }

    /**
     * Uji CRM Loyalitas: Pelanggan Klaim Token Struk -> Poin Bertambah -> Cegah Double Claim
     */
    public function test_customer_can_claim_points_from_receipt_token_and_tier_updates(): void
    {
        // 1. Buat transaksi dengan token klaim
        $trx = Transaction::create([
            'invoice_code' => 'INV-TEST-CLAIM',
            'total_amount' => 60000,
            'total_cost' => 25000,
            'gross_profit' => 35000,
            'payment_method' => 'qris',
            'claim_token' => 'TOKEN123',
            'claim_status' => 'unclaimed',
            'potential_points' => 6,
        ]);

        // 2. Pelanggan melakukan klaim mandiri di portal
        $response = $this->post(route('customer.claim.process'), [
            'claim_token' => 'TOKEN123',
            'phone' => '081299998888',
            'name' => 'Dewi Lestari',
        ]);

        $response->assertSessionHas('success');

        // 3. Verifikasi Customer & Poin
        $customer = Customer::where('phone', '081299998888')->first();
        $this->assertNotNull($customer);
        $this->assertEquals(6, $customer->total_points);
        $this->assertEquals('Silver', $customer->tier); // >= 5 poin = Silver

        // 4. Verifikasi status transaksi berubah menjadi claimed
        $this->assertEquals('claimed', $trx->fresh()->claim_status);
        $this->assertEquals($customer->id, $trx->fresh()->customer_id);

        // 5. Uji Cegah Double Claim: Klaim ulang token yang sama harus ditolak
        $duplicateResponse = $this->post(route('customer.claim.process'), [
            'claim_token' => 'TOKEN123',
            'phone' => '081299998888',
            'name' => 'Dewi Lestari',
        ]);

        $duplicateResponse->assertSessionHas('error');
    }

    /**
     * Uji ERP Pencatatan Pengeluaran Operasional Manual
     */
    public function test_erp_can_record_operational_expense(): void
    {
        $response = $this->post(route('seller.erp.cashflow'), [
            'type' => 'out',
            'category' => 'operational',
            'amount' => 35000,
            'description' => 'Beli 2 balok es batu kristal',
            'transaction_date' => now()->toDateString(),
        ]);

        $response->assertSessionHas('success');

        $expense = CashFlow::where('description', 'Beli 2 balok es batu kristal')->first();
        $this->assertNotNull($expense);
        $this->assertEquals('out', $expense->type);
        $this->assertEquals(35000, $expense->amount);
    }
}
