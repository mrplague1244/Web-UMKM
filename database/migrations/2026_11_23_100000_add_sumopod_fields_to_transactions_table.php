<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom Sumopod ke tabel transactions.
     * - sumopod_payment_id  : UUID payment dari Sumopod
     * - sumopod_status      : pending | paid | expired | cancelled
     * - sumopod_payment_url : link pembayaran QRIS / VA
     * - sumopod_expires_at  : waktu kadaluarsa link pembayaran
     * - sumopod_fee         : biaya gateway (dari response Sumopod)
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('sumopod_payment_id')->nullable()->after('payment_method');
            $table->string('sumopod_status')->nullable()->after('sumopod_payment_id');      // pending, paid, expired, cancelled
            $table->text('sumopod_payment_url')->nullable()->after('sumopod_status');
            $table->timestamp('sumopod_expires_at')->nullable()->after('sumopod_payment_url');
            $table->decimal('sumopod_fee', 14, 2)->default(0)->after('sumopod_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'sumopod_payment_id',
                'sumopod_status',
                'sumopod_payment_url',
                'sumopod_expires_at',
                'sumopod_fee',
            ]);
        });
    }
};
