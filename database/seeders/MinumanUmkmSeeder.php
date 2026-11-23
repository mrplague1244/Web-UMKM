<?php

namespace Database\Seeders;

use App\Models\CashFlow;
use App\Models\Customer;
use App\Models\Material;
use App\Models\Product;
use App\Models\ProductRecipe;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MinumanUmkmSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Akun Pengelola Kedai (Demo Users)
        User::updateOrCreate(
            ['email' => 'owner@umkm.com'],
            [
                'name' => 'Owner Kedai Nusantara',
                'password' => Hash::make('password'),
                'role' => 'owner',
            ]
        );

        User::updateOrCreate(
            ['email' => 'kasir@umkm.com'],
            [
                'name' => 'Kasir Toko',
                'password' => Hash::make('password'),
                'role' => 'cashier',
            ]
        );

        // 1. Data Supplier Bahan Minuman
        $supPackaging = Supplier::create([
            'name' => 'CV Sumber Kemasan Sejahtera',
            'phone' => '081122334455',
            'category' => 'Packaging & Cup',
            'address' => 'Kawasan Industri Candi Blok A, Semarang',
            'notes' => 'Pemasok cup sablon, sedotan boba, dan lid sealer',
        ]);

        $supKopi = Supplier::create([
            'name' => 'Koperasi Petani Kopi Mandiri',
            'phone' => '082233445566',
            'category' => 'Biji Kopi',
            'address' => 'Lereng Gunung Ungaran, Jawa Tengah',
            'notes' => 'Espresso blend Arabika-Robusta 70:30 roast profile medium-dark',
        ]);

        $supDairy = Supplier::create([
            'name' => 'Distributor Dairy & Sirup Nusantara',
            'phone' => '083344556677',
            'category' => 'Susu & Sirup',
            'address' => 'Jl. Majapahit No. 88, Semarang',
            'notes' => 'Penyedia susu UHT fresh, kental manis, sirup gula aren murni',
        ]);

        // 2. Data Bahan Baku (SCM Materials)
        $matCup = Material::create([
            'name' => 'Cup Plastik 16oz Sablon',
            'sku' => 'MAT-CUP-16',
            'category' => 'Packaging',
            'unit' => 'pcs',
            'stock' => 250,
            'min_stock' => 50,
            'cost_per_unit' => 450,
        ]);

        $matStraw = Material::create([
            'name' => 'Sedotan Steril Higienis',
            'sku' => 'MAT-STR-01',
            'category' => 'Packaging',
            'unit' => 'pcs',
            'stock' => 300,
            'min_stock' => 50,
            'cost_per_unit' => 100,
        ]);

        $matKopi = Material::create([
            'name' => 'Biji Kopi Espresso Blend',
            'sku' => 'MAT-KOP-01',
            'category' => 'Bahan Baku',
            'unit' => 'gram',
            'stock' => 2500,
            'min_stock' => 500,
            'cost_per_unit' => 200,
        ]);

        $matSusu = Material::create([
            'name' => 'Susu UHT Fresh Full Cream',
            'sku' => 'MAT-SUS-01',
            'category' => 'Bahan Baku',
            'unit' => 'ml',
            'stock' => 20000,
            'min_stock' => 3000,
            'cost_per_unit' => 20,
        ]);

        $matGulaAren = Material::create([
            'name' => 'Sirup Gula Aren Organik',
            'sku' => 'MAT-ARE-01',
            'category' => 'Sirup & Pemanis',
            'unit' => 'ml',
            'stock' => 5000,
            'min_stock' => 1000,
            'cost_per_unit' => 35,
        ]);

        $matMatcha = Material::create([
            'name' => 'Bubuk Matcha Uji Premium',
            'sku' => 'MAT-MAT-01',
            'category' => 'Bahan Baku',
            'unit' => 'gram',
            'stock' => 1200,
            'min_stock' => 200,
            'cost_per_unit' => 350,
        ]);

        $matTeh = Material::create([
            'name' => 'Daun Teh Melati Wangi',
            'sku' => 'MAT-TEH-01',
            'category' => 'Bahan Baku',
            'unit' => 'gram',
            'stock' => 1500,
            'min_stock' => 300,
            'cost_per_unit' => 80,
        ]);

        $matBoba = Material::create([
            'name' => 'Boba Tapioka Pearls',
            'sku' => 'MAT-BOB-01',
            'category' => 'Topping',
            'unit' => 'gram',
            'stock' => 3000,
            'min_stock' => 500,
            'cost_per_unit' => 60,
        ]);

        // 3. Menu Minuman (Products)
        $p1 = Product::create([
            'name' => 'Es Kopi Susu Gula Aren',
            'sku' => 'DRK-KOP-01',
            'category' => 'Coffee',
            'selling_price' => 18000,
            'cost_price' => 7500,
            'stock' => 250,
            'min_stock' => 15,
            'description' => 'Espresso double shot berpadu dengan susu UHT creamy dan gula aren murni.',
        ]);

        $p2 = Product::create([
            'name' => 'Matcha Latte Creamy',
            'sku' => 'DRK-MAT-01',
            'category' => 'Non-Coffee',
            'selling_price' => 22000,
            'cost_price' => 9000,
            'stock' => 100,
            'min_stock' => 10,
            'description' => 'Matcha asli Jepang dengan busa susu lembut dan rasa gurih manis seimbang.',
        ]);

        $p3 = Product::create([
            'name' => 'Brown Sugar Boba Fresh Milk',
            'sku' => 'DRK-BOB-01',
            'category' => 'Non-Coffee',
            'selling_price' => 20000,
            'cost_price' => 8500,
            'stock' => 80,
            'min_stock' => 10,
            'description' => 'Boba tapioka kenyal berbalur karamel gula aren dengan susu segar dingin.',
        ]);

        $p4 = Product::create([
            'name' => 'Es Lemon Jasmine Tea',
            'sku' => 'DRK-TEH-01',
            'category' => 'Tea Series',
            'selling_price' => 14000,
            'cost_price' => 4500,
            'stock' => 150,
            'min_stock' => 15,
            'description' => 'Teh melati wangi berpadu sari lemon segar, pelepas dahaga sempurna.',
        ]);

        $p5 = Product::create([
            'name' => 'Americano Dingin',
            'sku' => 'DRK-KOP-02',
            'category' => 'Coffee',
            'selling_price' => 15000,
            'cost_price' => 4000,
            'stock' => 200,
            'min_stock' => 10,
            'description' => 'Double shot espresso murni dengan es batu segar tanpa pemanis tambahan.',
        ]);

        $p6 = Product::create([
            'name' => 'Topping Ekstra Boba',
            'sku' => 'TOP-BOB-01',
            'category' => 'Topping',
            'selling_price' => 3000,
            'cost_price' => 1200,
            'stock' => 100,
            'min_stock' => 10,
            'description' => 'Tambahan 1 scoop boba kenyal legit.',
        ]);

        // 4. Resep Minuman (Product Recipes - BOM)
        // Resep Es Kopi Susu
        ProductRecipe::create(['product_id' => $p1->id, 'material_id' => $matCup->id, 'quantity' => 1]);
        ProductRecipe::create(['product_id' => $p1->id, 'material_id' => $matStraw->id, 'quantity' => 1]);
        ProductRecipe::create(['product_id' => $p1->id, 'material_id' => $matKopi->id, 'quantity' => 15]); // 15gr kopi
        ProductRecipe::create(['product_id' => $p1->id, 'material_id' => $matSusu->id, 'quantity' => 120]); // 120ml susu
        ProductRecipe::create(['product_id' => $p1->id, 'material_id' => $matGulaAren->id, 'quantity' => 25]); // 25ml aren

        // Resep Matcha Latte
        ProductRecipe::create(['product_id' => $p2->id, 'material_id' => $matCup->id, 'quantity' => 1]);
        ProductRecipe::create(['product_id' => $p2->id, 'material_id' => $matStraw->id, 'quantity' => 1]);
        ProductRecipe::create(['product_id' => $p2->id, 'material_id' => $matMatcha->id, 'quantity' => 15]); // 15gr matcha
        ProductRecipe::create(['product_id' => $p2->id, 'material_id' => $matSusu->id, 'quantity' => 150]); // 150ml susu

        // Resep Boba Milk
        ProductRecipe::create(['product_id' => $p3->id, 'material_id' => $matCup->id, 'quantity' => 1]);
        ProductRecipe::create(['product_id' => $p3->id, 'material_id' => $matStraw->id, 'quantity' => 1]);
        ProductRecipe::create(['product_id' => $p3->id, 'material_id' => $matBoba->id, 'quantity' => 50]); // 50gr boba
        ProductRecipe::create(['product_id' => $p3->id, 'material_id' => $matSusu->id, 'quantity' => 150]); // 150ml susu
        ProductRecipe::create(['product_id' => $p3->id, 'material_id' => $matGulaAren->id, 'quantity' => 30]); // 30ml aren

        // Resep Lemon Jasmine Tea
        ProductRecipe::create(['product_id' => $p4->id, 'material_id' => $matCup->id, 'quantity' => 1]);
        ProductRecipe::create(['product_id' => $p4->id, 'material_id' => $matStraw->id, 'quantity' => 1]);
        ProductRecipe::create(['product_id' => $p4->id, 'material_id' => $matTeh->id, 'quantity' => 10]); // 10gr teh

        // 5. Data Pelanggan (CRM)
        $c1 = Customer::create([
            'name' => 'Rina Amelia',
            'phone' => '081298765432',
            'email' => 'rina.amelia@gmail.com',
            'total_points' => 12,
            'tier' => 'Gold',
        ]);

        $c2 = Customer::create([
            'name' => 'Budi Santoso',
            'phone' => '085612345678',
            'email' => 'budi.santoso@gmail.com',
            'total_points' => 6,
            'tier' => 'Silver',
        ]);

        $c3 = Customer::create([
            'name' => 'Dewi Lestari',
            'phone' => '087788990011',
            'email' => 'dewi.lestari@gmail.com',
            'total_points' => 2,
            'tier' => 'Bronze',
        ]);

        // 6. Modal Awal & Kulakan Bahan (ERP Cash Flow)
        CashFlow::create([
            'type' => 'in',
            'category' => 'other',
            'amount' => 3000000,
            'description' => 'Modal Kas Awal Operasional Kedai Minuman',
            'transaction_date' => now()->subDays(3)->toDateString(),
        ]);

        CashFlow::create([
            'type' => 'out',
            'category' => 'restock',
            'amount' => 450000,
            'description' => 'Pembelian Biji Kopi Espresso 2.5kg dari Koperasi Petani Kopi',
            'transaction_date' => now()->subDays(2)->toDateString(),
        ]);

        CashFlow::create([
            'type' => 'out',
            'category' => 'operational',
            'amount' => 35000,
            'description' => 'Beli 2 Balok Es Batu Kristal Higienis',
            'transaction_date' => now()->subDay()->toDateString(),
        ]);

        // 7. Transaksi Contoh (POS & ERP Sales)
        $trx = Transaction::create([
            'invoice_code' => 'INV-' . date('Ymd') . '-0001',
            'customer_id' => $c1->id,
            'total_amount' => 38000,
            'total_cost' => 16000,
            'gross_profit' => 22000,
            'payment_method' => 'qris',
            'payment_status' => 'paid',
            'claim_token' => strtoupper(Str::random(10)),
            'claim_status' => 'claimed',
            'claimed_at' => now()->subHours(2),
            'potential_points' => 3,
            'cashier_name' => 'Kasir Utama',
            'created_at' => now()->subHours(2),
        ]);

        TransactionItem::create([
            'transaction_id' => $trx->id,
            'product_id' => $p1->id,
            'product_name' => $p1->name,
            'quantity' => 1,
            'unit_price' => 18000,
            'unit_cost' => 7500,
            'subtotal' => 18000,
            'subtotal_cost' => 7500,
            'ice_level' => 'Normal Ice',
            'sugar_level' => 'Less Sugar (50%)',
            'topping' => null,
            'notes' => 'Gula aren sedikit',
        ]);

        TransactionItem::create([
            'transaction_id' => $trx->id,
            'product_id' => $p3->id,
            'product_name' => $p3->name,
            'quantity' => 1,
            'unit_price' => 20000,
            'unit_cost' => 8500,
            'subtotal' => 20000,
            'subtotal_cost' => 8500,
            'ice_level' => 'Normal Ice',
            'sugar_level' => 'Normal (100%)',
            'topping' => 'Boba Tapioka',
            'notes' => null,
        ]);

        CashFlow::create([
            'type' => 'in',
            'category' => 'sales',
            'amount' => 38000,
            'description' => 'Penjualan Kasir POS (' . $trx->invoice_code . ')',
            'transaction_id' => $trx->id,
            'transaction_date' => now()->toDateString(),
        ]);
    }
}
