# 🧋 Template Web App MVP UMKM Minuman (ERP - SCM - CRM)
### Berbasis Laravel 13 • Arsitektur Bersih & Siap Modifikasi

Aplikasi web terpadu **Minimum Viable Product (MVP)** yang dirancang khusus untuk **UMKM Penjual Minuman** (Kedai Kopi, Teh Kekinian, Boba, Jus, dan Minuman Segar). Template ini mengintegrasikan tiga pilar operasional bisnis: **ERP** (Enterprise Resource Planning), **SCM** (Supply Chain Management), dan **CRM** (Customer Relationship Management) ke dalam satu sistem yang ringan, cepat, dan mudah dimodifikasi.

---

## 🌟 Fitur Utama Sistem

### 1. 🛒 Kasir POS Minuman (`/seller/pos`)
- **Katalog Menu Cepat**: Filter kategori instan (*Coffee, Non-Coffee, Tea Series, Topping*).
- **Kustomisasi Minuman (Varian)**:
  - *Sugar Level* (Normal 100%, Less 50%, No Sugar 0%)
  - *Ice Level* (Normal Ice, Less Ice, No Ice)
  - *Ekstra Topping* (Boba Tapioka, Grass Jelly, Coffee Jelly, Cheese Foam)
- **Kalkulator Kasir Cepat**: Tombol uang pecahan cepat (Uang Pas, 20rb, 50rb, 100rb) dan kalkulasi kembalian otomatis.
- **Metode Pembayaran Fleksibel**: Mendukung Tunai (Cash), QRIS, dan Transfer Bank.
- **Struk Termal & Token Klaim Poin (`/seller/pos/receipt/{id}`)**: Siap cetak printer kasir (58mm/80mm) dilengkapi QR / Kode Klaim Struk 6-karakter untuk pelanggan.

### 2. 📦 SCM & Inventaris Bahan Baku (`/seller/scm`)
- **Manajemen Bahan Baku & Kemasan**: Pemantauan stok Cup 16oz, Sedotan, Susu UHT, Biji Kopi, Sirup Gula Aren, Bubuk Matcha, Boba, dll.
- **Resep Minuman Otomatis (Bill of Materials / BOM)**: Setiap kali kasir menjual 1 cup minuman, sistem **otomatis memotong stok bahan baku** sesuai takaran resep (contoh: 1 cup, 1 sedotan, 15g kopi, 120ml susu).
- **Form Kulakan / Restock Terintegrasi**: Form pembelian bahan baku dari supplier yang otomatis menambah kuantitas stok di SCM dan **mencatat pengeluaran uang di buku kas keluar ERP**.
- **Peringatan Stok Menipis (*Low Stock Alert*)**: Notifikasi otomatis di header saat bahan baku berada di bawah batas minimum stok.
- **Database Pemasok (Suppliers)** & Riwayat Mutasi Stok (*Audit Trail*).

### 3. 💰 ERP & Keuangan Sederhana (`/seller/erp`)
- **Dashboard Keuangan Realtime**:
  - Saldo Kas Riil (Total Kas Masuk - Total Kas Keluar)
  - Omzet Penjualan Hari Ini & Bulan Ini
  - Total Beban Kulakan Bahan & Biaya Operasional
  - Estimasi Laba Bersih (*Net Profit*) dan Margin Keuntungan
- **Buku Kas Terintegrasi (Arus Kas)**:
  - Kas Masuk otomatis dari penjualan POS
  - Kas Keluar otomatis dari kulakan bahan SCM
  - Form pencatatan manual biaya operasional harian (beli es batu kristal, galon air, gas, listrik, dll).

### 4. 👥 CRM & Program Loyalitas Pelanggan (`/seller/crm`)
- **Database Pelanggan**: Menyimpan riwayat belanja, frekuensi kunjungan, dan akumulasi poin.
- **Tingkatan Membership Otomatis**: *Bronze*, *Silver*, *Gold*, dan *VIP* berdasarkan keaktifan belanja.
- **Pemasaran Langsung WhatsApp (1-Click WA Marketing)**: Tombol instan untuk mengirim pesan terima kasih atau broadcast promo Jumat Berkah langsung via WhatsApp (`wa.me`).
- **Kartu Loyalitas Virtual (*Digital Stamp Card*)**:
  - Pelanggan dapat mengumpulkan stamp (misal: tiap Rp 10.000 = 1 Stamp).
  - Target: **10 Stamp = 1 Cup Minuman Gratis**!

### 5. 📱 Portal Mandiri Pelanggan (`/customer/claim`)
- Halaman web ramah smartphone tanpa perlu unduh aplikasi.
- Pelanggan cukup memasukkan 6-karakter kode struk kasir dan nomor WhatsApp.
- Poin otomatis bertambah dan menampilkan **Kartu Member Digital Interaktif** (`/customer/loyalty/{phone}`) dengan animasi stamp lingkaran.

---

## 🚀 Panduan Memulai Cepat (Zero-Config)

Aplikasi ini menggunakan database **SQLite** bawaan, sehingga dapat langsung dijalankan tanpa perlu menginstal atau mengkonfigurasi server MySQL.

### 1. Kloning Repositori
```bash
git clone https://github.com/mrplague1244/Web-UMKM.git
cd Web-UMKM
```

### 2. Instalasi Dependensi PHP
```bash
composer install
```

### 3. Konfigurasi Lingkungan
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Jalankan Migrasi & Data Seeder Contoh
```bash
php artisan migrate:fresh --seed
```
*Seeder ini otomatis menyiapkan 15+ menu minuman populer, data bahan baku, pemasok, transaksi demo, dan pelanggan.*

### 5. Jalankan Web Server
```bash
php artisan serve
```
Buka browser di:
- **Kasir & Manajemen UMKM**: [http://localhost:8000/seller/pos](http://localhost:8000/seller/pos)
- **Portal Klaim Pelanggan**: [http://localhost:8000/customer/claim](http://localhost:8000/customer/claim)

---

## 🛠️ Cara Kustomisasi & Modifikasi Template

Template ini didesain agar sangat mudah disesuaikan dengan merek usaha Anda:

### 1. Mengubah Identitas Kedai & Aturan Bisnis
Buka berkas **`config/beverage.php`**:
```php
'store' => [
    'name' => 'Kopi Senja Nusantara',
    'tagline' => 'Racikan Kopi & Teh Autentik',
    'address' => 'Jl. Malioboro No. 45, Yogyakarta',
    'phone' => '081234567890',
],
'loyalty' => [
    'amount_per_point' => 10000,           // Belanja Rp 10.000 dapat 1 stamp
    'stamp_target_for_free_drink' => 10,   // 10 stamp gratis 1 cup
],
```

### 2. Menambah / Mengubah Varian Minuman (Gula, Es, Topping)
Masih di **`config/beverage.php`**, Anda dapat menambah atau mengurangi pilihan varian kasir:
```php
'variants' => [
    'toppings' => [
        'Boba Tapioka' => 3000,
        'Grass Jelly' => 3000,
        'Cheese Foam' => 5000,
        'Egg Pudding' => 4000, // Tambah topping baru di sini!
    ],
]
```

### 3. Mengubah Teks Pesan Promosi WhatsApp
Ubah template pesan di `config/beverage.php`:
```php
'whatsapp' => [
    'thank_you' => "Halo {name}! Terima kasih sudah belanja di {store}. Sisa stamp Anda: {points} stamp.",
    'promo_friday' => "Halo {name}! Promo Beli 1 Gratis 1 Jumat Berkah spesial untuk Anda di {store}! 🎉",
]
```

---

## 🧪 Pengujian Otomatis (Automated Tests)

Seluruh logika bisnis (POS, pengurangan stok resep SCM, kas masuk/keluar ERP, dan klaim CRM) telah dilengkapi unit & feature test:

```bash
php artisan test
```
*Hasil: 6 pengujian terverifikasi sukses dengan 31 assertions.*

---

## 📄 Lisensi
Template ini dirilis di bawah lisensi [MIT](LICENSE). Bebas digunakan, dimodifikasi, dan dikembangkan untuk keperluan bisnis UMKM maupun pembelajaran.
