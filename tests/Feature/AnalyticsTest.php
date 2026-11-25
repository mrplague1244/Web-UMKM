<?php

namespace Tests\Feature;

use App\Models\CashFlow;
use App\Models\Material;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'role' => 'owner',
        ]);
        $this->actingAs($this->user);
    }

    /**
     * Uji Halaman Dashboard Analisis Terbuka Normal
     */
    public function test_analytics_dashboard_renders_successfully(): void
    {
        $response = $this->get(route('seller.analytics'));

        $response->assertStatus(200);
        $response->assertViewIs('seller.analytics');
        $response->assertSee('Analisis Bisnis & Visualisasi Data');
        $response->assertSee('Omzet Penjualan');
        $response->assertSee('Volume Penjualan');
        $response->assertSee('Jam Tersibuk Kedai');
        $response->assertSee('Tren Omzet & Beban Harian');
        $response->assertSee('Analisis Jam Ramai (Peak Hours)');
        $response->assertSee('5 Menu Paling Laris');
        $response->assertSee('Metode Pembayaran');
    }

    /**
     * Uji Agregasi Data KPI dan Grafik pada Analisis
     */
    public function test_analytics_aggregates_sales_and_products(): void
    {
        // 1. Buat Produk
        $product = Product::create([
            'name' => 'Kopi Susu Aren Gula Jawa',
            'category' => 'Coffee',
            'price' => 18000,
            'cost_price' => 6000,
            'is_active' => true,
        ]);

        // 2. Buat Transaksi Penjualan
        $transaction = Transaction::create([
            'invoice_code' => 'INV-20261125-001',
            'total_amount' => 36000,
            'total_cost' => 12000,
            'gross_profit' => 24000,
            'payment_method' => 'qris',
            'payment_status' => 'paid',
            'claim_token' => 'TOKEN1',
            'claim_status' => 'unclaimed',
            'potential_points' => 3,
            'created_at' => Carbon::today()->setHour(14), // jam 14:00
        ]);

        TransactionItem::create([
            'transaction_id' => $transaction->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 2,
            'unit_price' => 18000,
            'unit_cost' => 6000,
            'subtotal' => 36000,
            'subtotal_cost' => 12000,
        ]);

        $response = $this->get(route('seller.analytics'));

        $response->assertStatus(200);
        $response->assertSee('Rp 36.000'); // Total Omzet
        $response->assertSee('2 Cup'); // Volume Penjualan
        $response->assertSee('Kopi Susu Aren Gula Jawa'); // Best Seller
        $response->assertSee('14:00 - 15:00'); // Jam Tersibuk
    }

    /**
     * Uji Pilihan Rentang Waktu (Timeframe Filter)
     */
    public function test_analytics_timeframe_filters(): void
    {
        // 1. Uji range 30 hari
        $response30d = $this->get(route('seller.analytics', ['range' => '30d']));
        $response30d->assertStatus(200);
        $response30d->assertSee('30 Hari Terakhir');

        // 2. Uji range bulan ini
        $responseMonth = $this->get(route('seller.analytics', ['range' => 'this_month']));
        $responseMonth->assertStatus(200);
        $responseMonth->assertSee('Bulan Ini');
    }

    /**
     * Uji Pencatatan Bahan Terbuang / Rusak (Waste & Spoilage)
     */
    public function test_analytics_can_record_waste(): void
    {
        $material = Material::create([
            'name' => 'Susu UHT Fresh Milk',
            'category' => 'Bahan Baku',
            'unit' => 'liter',
            'stock' => 20,
            'min_stock' => 5,
            'cost_per_unit' => 18000,
            'is_active' => true,
        ]);

        $response = $this->post(route('seller.analytics.waste'), [
            'material_id' => $material->id,
            'quantity' => 2,
            'reason' => 'Basi / Kedaluwarsa',
            'notes' => 'Kulkas pendingin sempat mati semalaman',
        ]);

        $response->assertRedirect(route('seller.analytics'));
        $response->assertSessionHas('success');

        // Pastikan stok terpotong dari 20 jadi 18
        $this->assertEquals(18, $material->fresh()->stock);

        // Pastikan tercatat di audit trail stock_movements
        $this->assertDatabaseHas('stock_movements', [
            'material_id' => $material->id,
            'type' => 'waste',
            'quantity' => 2,
        ]);

        // Pastikan kerugian tercatat di cash_flows keluar
        $this->assertDatabaseHas('cash_flows', [
            'type' => 'out',
            'category' => 'operational',
            'amount' => 36000, // 2 x 18.000
        ]);
    }

    /**
     * Uji Pembatalan Catatan Bahan Rusak (Kembalikan Stok)
     */
    public function test_analytics_can_delete_waste_and_restore_stock(): void
    {
        $material = Material::create([
            'name' => 'Sirup Gula Aren',
            'category' => 'Bahan Baku',
            'unit' => 'ml',
            'stock' => 1000,
            'min_stock' => 100,
            'cost_per_unit' => 50,
            'is_active' => true,
        ]);

        $movement = StockMovement::create([
            'material_id' => $material->id,
            'type' => 'waste',
            'quantity' => 200,
            'unit_cost' => 50,
            'notes' => 'Tumpah saat racik',
        ]);

        $material->decrement('stock', 200);
        $this->assertEquals(800, $material->fresh()->stock);

        $response = $this->delete(route('seller.analytics.waste.destroy', $movement->id));

        $response->assertRedirect(route('seller.analytics'));
        $response->assertSessionHas('success');

        // Pastikan stok kembali jadi 1000
        $this->assertEquals(1000, $material->fresh()->stock);
        $this->assertDatabaseMissing('stock_movements', ['id' => $movement->id]);
    }
}
