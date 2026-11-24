<?php

namespace Tests\Feature;

use App\Models\CashFlow;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErpFeaturesTest extends TestCase
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
     * Uji Halaman ERP Terbuka Normal Tanpa Filter
     */
    public function test_erp_dashboard_renders_without_filter(): void
    {
        CashFlow::create([
            'type' => 'in',
            'category' => 'sales',
            'amount' => 50000,
            'description' => 'Penjualan Kopi Susu',
            'transaction_date' => Carbon::today(),
        ]);

        $response = $this->get(route('seller.erp'));

        $response->assertStatus(200);
        $response->assertViewIs('seller.erp');
        $response->assertViewHas('isFiltered', false);
        $response->assertSee('Saldo Kas Riil');
        $response->assertSee('Penjualan Kopi Susu');
        $response->assertSee('Export CSV');
        $response->assertSee('Cetak / PDF');
    }

    /**
     * Uji Filter Rentang Tanggal di Halaman ERP
     */
    public function test_erp_filters_cash_flows_by_date_range(): void
    {
        // 1. Data di luar rentang (sebelumnya)
        CashFlow::create([
            'type' => 'in',
            'category' => 'sales',
            'amount' => 20000,
            'description' => 'Transaksi Lawas 1 November',
            'transaction_date' => Carbon::parse('2026-11-01'),
        ]);

        // 2. Data di dalam rentang
        CashFlow::create([
            'type' => 'in',
            'category' => 'sales',
            'amount' => 35000,
            'description' => 'Transaksi Target 15 November',
            'transaction_date' => Carbon::parse('2026-11-15'),
        ]);

        // 3. Data di luar rentang (setelahnya)
        CashFlow::create([
            'type' => 'out',
            'category' => 'operational',
            'amount' => 15000,
            'description' => 'Pengeluaran Masa Depan 25 November',
            'transaction_date' => Carbon::parse('2026-11-25'),
        ]);

        // Panggil route dengan rentang tanggal 10 Nov - 20 Nov
        $response = $this->get(route('seller.erp', [
            'start_date' => '2026-11-10',
            'end_date' => '2026-11-20',
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('isFiltered', true);
        $response->assertSee('Transaksi Target 15 November');
        $response->assertDontSee('Transaksi Lawas 1 November');
        $response->assertDontSee('Pengeluaran Masa Depan 25 November');
        $response->assertSee('Periode Aktif:');
    }

    /**
     * Uji Filter Berdasarkan Tipe Kas (Masuk / Keluar)
     */
    public function test_erp_filters_by_type(): void
    {
        CashFlow::create([
            'type' => 'in',
            'category' => 'sales',
            'amount' => 40000,
            'description' => 'Kas Masuk Penjualan Boba',
            'transaction_date' => Carbon::today(),
        ]);

        CashFlow::create([
            'type' => 'out',
            'category' => 'operational',
            'amount' => 12000,
            'description' => 'Kas Keluar Beli Sedotan',
            'transaction_date' => Carbon::today(),
        ]);

        $response = $this->get(route('seller.erp', ['type' => 'in']));

        $response->assertStatus(200);
        $response->assertSee('Kas Masuk Penjualan Boba');
        $response->assertDontSee('Kas Keluar Beli Sedotan');
    }

    /**
     * Uji Export Laporan Keuangan ke CSV
     */
    public function test_erp_can_export_csv(): void
    {
        CashFlow::create([
            'type' => 'in',
            'category' => 'sales',
            'amount' => 45000,
            'description' => 'Penjualan Kopi Pandan',
            'transaction_date' => Carbon::parse('2026-11-12'),
        ]);

        CashFlow::create([
            'type' => 'out',
            'category' => 'operational',
            'amount' => 10000,
            'description' => 'Beli Es Batu Kristal',
            'transaction_date' => Carbon::parse('2026-11-12'),
        ]);

        $response = $this->get(route('seller.erp.export', [
            'start_date' => '2026-11-10',
            'end_date' => '2026-11-20',
        ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('attachment; filename=', $response->headers->get('Content-Disposition'));

        // Cek isi stream CSV
        $content = $response->streamedContent();
        $this->assertStringContainsString('LAPORAN KEUANGAN & ARUS KAS', $content);
        $this->assertStringContainsString('Penjualan Kopi Pandan', $content);
        $this->assertStringContainsString('Beli Es Batu Kristal', $content);
        $this->assertStringContainsString('45000', $content);
        $this->assertStringContainsString('10000', $content);
    }

    /**
     * Uji Halaman Cetak / Print Laporan Keuangan
     */
    public function test_erp_can_render_print_report(): void
    {
        CashFlow::create([
            'type' => 'in',
            'category' => 'sales',
            'amount' => 60000,
            'description' => 'Penjualan 4 Cup Thai Tea',
            'transaction_date' => Carbon::today(),
        ]);

        $response = $this->get(route('seller.erp.print', [
            'start_date' => Carbon::today()->startOfMonth()->toDateString(),
            'end_date' => Carbon::today()->toDateString(),
        ]));

        $response->assertStatus(200);
        $response->assertViewIs('seller.erp_print');
        $response->assertSee('Laporan Arus Kas');
        $response->assertSee('Penjualan 4 Cup Thai Tea');
        $response->assertSee('Total Kas Masuk');
        $response->assertSee('Petugas Kasir / Admin');
    }
}
