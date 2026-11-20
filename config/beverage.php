<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Identitas & Branding UMKM Minuman
    |--------------------------------------------------------------------------
    | Pengaturan nama kedai, slogan, kontak, dan mata uang yang tampil di
    | seluruh aplikasi (Kasir, SCM, ERP, CRM, dan Struk Digital).
    */
    'store' => [
        'name' => env('STORE_NAME', 'Kopi & Teh Nusantara'),
        'tagline' => env('STORE_TAGLINE', 'Racikan Autentik Minuman Kekinian UMKM'),
        'address' => env('STORE_ADDRESS', 'Jl. Pemuda No. 12, Semarang'),
        'phone' => env('STORE_PHONE', '081234567890'),
        'currency' => 'Rp',
        'currency_code' => 'IDR',
    ],

    /*
    |--------------------------------------------------------------------------
    | Kategori Menu Minuman
    |--------------------------------------------------------------------------
    | Digunakan untuk filter cepat di layar Kasir POS dan pengelompokan SCM.
    */
    'categories' => [
        'Coffee' => 'Kopi Tradisional & Kekinian',
        'Non-Coffee' => 'Susu, Cokelat, & Matcha',
        'Tea Series' => 'Teh Melati, Earl Grey, & Buah',
        'Topping' => 'Topping & Ekstra Tambahan',
    ],

    /*
    |--------------------------------------------------------------------------
    | Opsi Kustomisasi Minuman (Varian)
    |--------------------------------------------------------------------------
    | Opsi penyesuaian pesanan di kasir POS untuk memenuhi selera pelanggan.
    */
    'variants' => [
        'ice_levels' => [
            'Normal Ice' => 'Es Normal',
            'Less Ice' => 'Sedikit Es',
            'No Ice' => 'Tanpa Es',
        ],
        'sugar_levels' => [
            'Normal (100%)' => 'Manis Normal',
            'Less Sugar (50%)' => 'Sedikit Gula (50%)',
            'No Sugar (0%)' => 'Tanpa Gula',
        ],
        'toppings' => [
            'Boba Tapioka' => 3000,
            'Grass Jelly' => 3000,
            'Coffee Jelly' => 4000,
            'Cheese Foam' => 5000,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pengaturan Program Loyalitas CRM
    |--------------------------------------------------------------------------
    | Aturan perhitungan poin reward dan tingkat membership pelanggan.
    */
    'loyalty' => [
        // Kelipatan nominal belanja untuk memperoleh 1 poin loyalty
        'amount_per_point' => 10000,

        // Jumlah stamp yang dibutuhkan untuk mendapatkan 1 minuman gratis
        'stamp_target_for_free_drink' => 10,

        // Batas minimal akumulasi poin untuk masing-masing tier
        'tiers' => [
            'VIP' => 50,
            'Gold' => 20,
            'Silver' => 5,
            'Bronze' => 0,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Template Pesan Promosi WhatsApp (1-Click WA Marketing)
    |--------------------------------------------------------------------------
    */
    'whatsapp' => [
        'thank_you' => "Halo {name}! Terima kasih sudah mampir ke {store}. Transaksi Anda tercatat dan poin loyalty Anda sekarang: {points} poin. Kumpulkan 10 stamp untuk 1 minuman gratis ya! 😊",
        'promo_friday' => "Halo {name}! Spesial Jumat Berkah di {store}, dapatkan Diskon 20% untuk semua menu Series Matcha & Kopi Susu! Tunjukkan pesan ini ke kasir. Ditunggu kunjungannya! 🧋",
    ],
];
