<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerExperienceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Uji Halaman Menu Digital Publik Terbuka Tanpa Perlu Login
     */
    public function test_public_menu_renders_without_auth(): void
    {
        Product::create([
            'name' => 'Kopi Susu Senja Aren',
            'category' => 'Coffee',
            'price' => 18000,
            'cost_price' => 6000,
            'is_active' => true,
        ]);

        $response = $this->get(route('menu'));

        $response->assertStatus(200);
        $response->assertViewIs('customer.menu');
        $response->assertSee('Kopi Susu Senja Aren');
        $response->assertSee('Rp 18.000');
        $response->assertSee('Klaim Poin');
    }

    /**
     * Uji Menu Digital dengan Parameter Nomor Meja (Dine-in QR)
     */
    public function test_menu_with_table_parameter(): void
    {
        $response = $this->get(route('menu', ['table' => 5]));

        $response->assertStatus(200);
        $response->assertSee('Meja 5');
    }

    /**
     * Uji Filter Kategori dan Pencarian pada Menu Digital
     */
    public function test_menu_filters_by_category_and_search(): void
    {
        Product::create([
            'name' => 'Espresso Double Shot',
            'category' => 'Coffee',
            'price' => 15000,
            'cost_price' => 5000,
            'is_active' => true,
        ]);

        Product::create([
            'name' => 'Matcha Latte Ice',
            'category' => 'Tea Series',
            'price' => 20000,
            'cost_price' => 7000,
            'is_active' => true,
        ]);

        // Filter Kategori Coffee
        $responseCoffee = $this->get(route('menu', ['category' => 'Coffee']));
        $responseCoffee->assertStatus(200);
        $responseCoffee->assertSee('Espresso Double Shot');
        $responseCoffee->assertDontSee('Matcha Latte Ice');

        // Pencarian Kata Kunci
        $responseSearch = $this->get(route('menu', ['q' => 'Matcha']));
        $responseSearch->assertStatus(200);
        $responseSearch->assertSee('Matcha Latte Ice');
        $responseSearch->assertDontSee('Espresso Double Shot');
    }

    /**
     * Uji Halaman Cetak Kartu QR Meja Pengelola
     */
    public function test_seller_can_render_table_qr_standee(): void
    {
        $user = User::factory()->create(['role' => 'owner']);
        $this->actingAs($user);

        $response = $this->get(route('seller.table_qr', ['tables' => 5]));

        $response->assertStatus(200);
        $response->assertViewIs('seller.table_qr');
        $response->assertSee('MEJA 01');
        $response->assertSee('MEJA 05');
        $response->assertSee('SCAN UNTUK LIHAT MENU');
    }

    /**
     * Uji Berkas PWA (Manifest & Service Worker)
     */
    public function test_pwa_files_accessible(): void
    {
        $this->assertFileExists(public_path('manifest.json'));
        $this->assertFileExists(public_path('sw.js'));
        $this->assertFileExists(public_path('icons/icon-192.svg'));

        $manifestContent = json_decode(file_get_contents(public_path('manifest.json')), true);
        $this->assertEquals('standalone', $manifestContent['display']);
        $this->assertEquals('#d97706', $manifestContent['theme_color']);
    }
}
