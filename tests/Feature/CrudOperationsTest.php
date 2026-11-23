<?php

namespace Tests\Feature;

use App\Models\CashFlow;
use App\Models\Customer;
use App\Models\LoyaltyLog;
use App\Models\Material;
use App\Models\Product;
use App\Models\ProductRecipe;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrudOperationsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Uji Hapus Bahan Baku (Material) di SCM
     */
    public function test_can_delete_material(): void
    {
        $material = Material::create([
            'name' => 'Biji Kopi Robusta',
            'category' => 'Bahan Baku',
            'unit' => 'gram',
            'stock' => 500,
            'min_stock' => 50,
            'cost_per_unit' => 150,
        ]);

        $response = $this->delete(route('seller.scm.material.destroy', $material->id));

        $response->assertRedirect(route('seller.scm'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('materials', ['id' => $material->id]);
    }

    /**
     * Uji Tambah & Hapus Menu Minuman (Product) beserta Resep di SCM
     */
    public function test_can_create_and_delete_product_with_recipe(): void
    {
        $material = Material::create([
            'name' => 'Bubuk Cokelat Murni',
            'category' => 'Bahan Baku',
            'unit' => 'gram',
            'stock' => 1000,
            'min_stock' => 100,
            'cost_per_unit' => 100,
        ]);

        // 1. Tambah Menu Baru dengan Resep
        $responseCreate = $this->post(route('seller.scm.product'), [
            'name' => 'Es Cokelat Nusantara',
            'category' => 'Non-Coffee',
            'selling_price' => 15000,
            'cost_price' => 5000,
            'description' => 'Minuman cokelat kental segar',
            'recipes' => [
                [
                    'material_id' => $material->id,
                    'quantity' => 30,
                ],
            ],
        ]);

        $responseCreate->assertRedirect(route('seller.scm'));
        $responseCreate->assertSessionHas('success');

        $product = Product::where('name', 'Es Cokelat Nusantara')->first();
        $this->assertNotNull($product);
        $this->assertCount(1, $product->recipes);

        // 2. Hapus Menu Minuman
        $responseDelete = $this->delete(route('seller.scm.product.destroy', $product->id));

        $responseDelete->assertRedirect(route('seller.scm'));
        $responseDelete->assertSessionHas('success');
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        $this->assertDatabaseMissing('product_recipes', ['product_id' => $product->id]);
    }

    /**
     * Uji Tambah & Hapus Pemasok (Supplier) di SCM
     */
    public function test_can_create_and_delete_supplier(): void
    {
        $responseCreate = $this->post(route('seller.scm.supplier'), [
            'name' => 'CV Sumber Kemasan Indah',
            'phone' => '089911223344',
            'category' => 'Kemasan Cup',
            'address' => 'Semarang Tengah',
            'notes' => 'Pemasok cup dan sedotan',
        ]);

        $responseCreate->assertRedirect(route('seller.scm'));
        $supplier = Supplier::where('name', 'CV Sumber Kemasan Indah')->first();
        $this->assertNotNull($supplier);

        // Hapus pemasok
        $responseDelete = $this->delete(route('seller.scm.supplier.destroy', $supplier->id));
        $responseDelete->assertRedirect(route('seller.scm'));
        $responseDelete->assertSessionHas('success');
        $this->assertDatabaseMissing('suppliers', ['id' => $supplier->id]);
    }

    /**
     * Uji Hapus Catatan Arus Kas (Cash Flow) di ERP
     */
    public function test_can_delete_cash_flow(): void
    {
        $cashFlow = CashFlow::create([
            'type' => 'out',
            'category' => 'operational',
            'amount' => 50000,
            'description' => 'Beli sabun cuci dan spons',
            'transaction_date' => now()->toDateString(),
        ]);

        $response = $this->delete(route('seller.erp.cashflow.destroy', $cashFlow->id));

        $response->assertRedirect(route('seller.erp'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('cash_flows', ['id' => $cashFlow->id]);
    }

    /**
     * Uji Pembatalan Transaksi Kasir POS (Void Transaction)
     * Mengembalikan stok bahan baku dan menghapus pencatatan kas keluar/masuk
     */
    public function test_can_void_transaction_and_restore_stock(): void
    {
        $material = Material::create([
            'name' => 'Boba Tapioka Brown Sugar',
            'category' => 'Topping',
            'unit' => 'gram',
            'stock' => 1000,
            'min_stock' => 100,
            'cost_per_unit' => 50,
        ]);

        $product = Product::create([
            'name' => 'Brown Sugar Milk Tea',
            'category' => 'Tea Series',
            'selling_price' => 20000,
            'cost_price' => 8000,
            'stock' => 50,
            'min_stock' => 5,
        ]);

        ProductRecipe::create([
            'product_id' => $product->id,
            'material_id' => $material->id,
            'quantity' => 50, // 50 gram per cup
        ]);

        // Simulasikan checkout kasir: stok bahan berkurang dari 1000 ke 900 (2 cup)
        $material->decrement('stock', 100);
        $product->decrement('stock', 2);

        $customer = Customer::create([
            'name' => 'Budi Santoso',
            'phone' => '081233445566',
            'total_points' => 4,
            'tier' => 'Bronze',
        ]);

        $trx = Transaction::create([
            'invoice_code' => 'INV-VOID-TEST',
            'customer_id' => $customer->id,
            'total_amount' => 40000,
            'total_cost' => 16000,
            'gross_profit' => 24000,
            'payment_method' => 'cash',
            'claim_token' => 'VOID12',
            'claim_status' => 'claimed',
            'potential_points' => 4,
        ]);

        TransactionItem::create([
            'transaction_id' => $trx->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 2,
            'unit_price' => 20000,
            'unit_cost' => 8000,
            'subtotal' => 40000,
            'subtotal_cost' => 16000,
        ]);

        CashFlow::create([
            'type' => 'in',
            'category' => 'sales',
            'amount' => 40000,
            'description' => 'Penjualan kasir #INV-VOID-TEST',
            'transaction_id' => $trx->id,
            'transaction_date' => now()->toDateString(),
        ]);

        LoyaltyLog::create([
            'customer_id' => $customer->id,
            'transaction_id' => $trx->id,
            'points' => 4,
            'type' => 'earn',
            'description' => 'Klaim poin transaksi INV-VOID-TEST',
        ]);

        // Eksekusi Batalkan Transaksi
        $response = $this->delete(route('seller.erp.transaction.destroy', $trx->id));

        $response->assertRedirect(route('seller.erp'));
        $response->assertSessionHas('success');

        // Verifikasi transaksi terhapus
        $this->assertDatabaseMissing('transactions', ['id' => $trx->id]);
        $this->assertDatabaseMissing('transaction_items', ['transaction_id' => $trx->id]);
        $this->assertDatabaseMissing('cash_flows', ['transaction_id' => $trx->id]);
        $this->assertDatabaseMissing('loyalty_logs', ['transaction_id' => $trx->id]);

        // Verifikasi stok bahan baku dan produk dikembalikan
        $this->assertEquals(1000, $material->fresh()->stock);
        $this->assertEquals(50, $product->fresh()->stock);

        // Verifikasi poin pelanggan dibatalkan
        $this->assertEquals(0, $customer->fresh()->total_points);
    }

    /**
     * Uji Hapus Member Pelanggan di CRM
     */
    public function test_can_delete_customer(): void
    {
        $customer = Customer::create([
            'name' => 'Dewi Sartika',
            'phone' => '081277889900',
            'total_points' => 12,
            'tier' => 'Gold',
        ]);

        $response = $this->delete(route('seller.crm.customer.destroy', $customer->id));

        $response->assertRedirect(route('seller.crm'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }

    /**
     * Uji Penyesuaian Poin Manual & Hapus Catatan Mutasi Poin di CRM
     */
    public function test_can_adjust_points_and_delete_loyalty_log(): void
    {
        $customer = Customer::create([
            'name' => 'Agus Priyono',
            'phone' => '085600112233',
            'total_points' => 5,
            'tier' => 'Silver',
        ]);

        // 1. Tambah Poin Manual (Bonus Promo)
        $responseEarn = $this->post(route('seller.crm.customer.points', $customer->id), [
            'type' => 'earn',
            'points' => 15,
            'description' => 'Bonus member baru & hari ultah',
        ]);

        $responseEarn->assertSessionHas('success');
        $this->assertEquals(20, $customer->fresh()->total_points);
        $this->assertEquals('Gold', $customer->fresh()->tier);

        // 2. Tukar Reward (Kurangi Poin)
        $responseRedeem = $this->post(route('seller.crm.customer.points', $customer->id), [
            'type' => 'redeem',
            'points' => 10,
            'description' => 'Penukaran 1 Free Cup Es Kopi Susu',
        ]);

        $responseRedeem->assertSessionHas('success');
        $this->assertEquals(10, $customer->fresh()->total_points);

        // 3. Hapus Catatan Log dan Pulihkan Saldo
        $log = LoyaltyLog::where('customer_id', $customer->id)->where('type', 'redeem')->first();
        $this->assertNotNull($log);

        $responseDeleteLog = $this->delete(route('seller.crm.loyalty.destroy', $log->id));
        $responseDeleteLog->assertSessionHas('success');

        // Saldo pulih dari 10 kembali ke 20
        $this->assertEquals(20, $customer->fresh()->total_points);
    }
}
