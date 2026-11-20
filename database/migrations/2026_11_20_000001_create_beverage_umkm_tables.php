<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. CRM: Customers
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->unique();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->integer('total_points')->default(0);
            $table->string('tier')->default('Silver'); // Bronze, Silver, Gold, VIP
            $table->rememberToken();
            $table->timestamps();
        });

        // 2. SCM: Suppliers
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('category')->default('Bahan Minuman'); // Bahan Baku, Packaging, Susu & Sirup
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 3. SCM: Raw Materials & Packaging (Bahan Baku & Kemasan)
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique()->nullable();
            $table->string('category')->default('Bahan Baku'); // Bahan Baku, Packaging, Sirup & Pemanis, Topping
            $table->string('unit')->default('pcs'); // pcs, ml, gram, pack
            $table->decimal('stock', 12, 2)->default(0);
            $table->decimal('min_stock', 12, 2)->default(10);
            $table->decimal('cost_per_unit', 14, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 4. Products: Menu Minuman & Topping
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique()->nullable();
            $table->string('category')->default('Coffee'); // Coffee, Non-Coffee, Tea Series, Topping
            $table->decimal('cost_price', 14, 2)->default(0); // Estimasi HPP
            $table->decimal('selling_price', 14, 2)->default(0); // Harga Jual
            $table->integer('stock')->default(999); // Ketersediaan porsi
            $table->integer('min_stock')->default(10);
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 5. Product Recipes (Resep / Bill of Materials)
        Schema::create('product_recipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('material_id')->constrained('materials')->cascadeOnDelete();
            $table->decimal('quantity', 12, 2); // Jumlah pemakaian per cup
            $table->timestamps();
        });

        // 6. SCM: Stock Movements (Audit Trail Stok Bahan & Produk)
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->nullable()->constrained('materials')->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('type'); // in (restock), out (penjualan/pemakaian), adjustment
            $table->decimal('quantity', 12, 2);
            $table->decimal('unit_cost', 14, 2)->default(0);
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->string('reference_no')->nullable(); // PO-xxxx, INV-xxxx
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 7. POS & ERP: Transactions
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_code')->unique();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->decimal('total_amount', 14, 2);
            $table->decimal('total_cost', 14, 2)->default(0);
            $table->decimal('gross_profit', 14, 2)->default(0);
            $table->string('payment_method')->default('cash'); // cash, qris, transfer
            $table->string('payment_status')->default('paid'); // paid, pending
            $table->string('claim_token', 32)->unique();
            $table->string('claim_status')->default('unclaimed'); // unclaimed, claimed
            $table->timestamp('claimed_at')->nullable();
            $table->integer('potential_points')->default(0);
            $table->string('cashier_name')->default('Kasir Toko');
            $table->timestamps();
        });

        // 8. Transaction Items
        Schema::create('transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('product_name');
            $table->integer('quantity');
            $table->decimal('unit_price', 14, 2);
            $table->decimal('unit_cost', 14, 2)->default(0);
            $table->decimal('subtotal', 14, 2);
            $table->decimal('subtotal_cost', 14, 2)->default(0);
            $table->string('ice_level')->nullable(); // Normal, Less, No Ice
            $table->string('sugar_level')->nullable(); // Normal, Less, No Sugar
            $table->string('topping')->nullable(); // Boba, Grass Jelly, etc.
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 9. ERP: Cash Flows
        Schema::create('cash_flows', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // in, out
            $table->string('category'); // sales, restock, operational, electricity, other
            $table->decimal('amount', 14, 2);
            $table->text('description')->nullable();
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->date('transaction_date');
            $table->timestamps();
        });

        // 10. CRM: Loyalty Logs
        Schema::create('loyalty_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->integer('points');
            $table->string('type')->default('earn'); // earn, redeem
            $table->string('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_logs');
        Schema::dropIfExists('cash_flows');
        Schema::dropIfExists('transaction_items');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('product_recipes');
        Schema::dropIfExists('products');
        Schema::dropIfExists('materials');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('customers');
    }
};
