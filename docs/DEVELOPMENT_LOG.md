# 📘 DOKUMENTASI LOG PENGEMBANGAN (DEVELOPMENT LOG)
## Web App MVP UMKM Minuman (ERP - SCM - CRM)

---

### 📋 INFORMASI PROYEK
* **Nama Proyek**: Web-UMKM Minuman (ERP, SCM, CRM, POS)
* **Repositori GitHub**: [mrplague1244/Web-UMKM](https://github.com/mrplague1244/Web-UMKM)
* **Teknologi Utama**: Laravel 13, SQLite, Tailwind CSS, Blade Templates
* **Target Pengguna**: UMKM Penjual Minuman (Kedai Kopi, Teh, Boba, Jus)
* **Status**: Tahap Produksi / Preview Live (Railway.app)

---

## 📐 BAGIAN 1: STANDAR TEMPLATE LOG PENGEMBANGAN
*(Gunakan format template di bawah ini untuk mencatat setiap fitur baru, perbaikan, atau rilis berikutnya agar terstruktur dan seragam).*

---

### [TEMPLATE ENTRI LOG BARU]
```markdown
### 🏷️ LOG #[NOMOR]: [JUDUL TUGAS / FITUR]
* **Tanggal & Waktu**: [Hari, DD Bulan YYYY - HH:MM WIB]
* **Tipe Perubahan**: [Feature / Bugfix / Refactor / UI-UX / Deployment]
* **Branch Git**: `[nama-branch]`
* **Pull Request**: [Tautan PR atau ID PR]
* **Status**: [Draft / In Review / Merged / Deployed]

#### 1. Latar Belakang & Tujuan
* Jelaskan masalah yang ingin diselesaikan atau kebutuhan bisnis yang mendasari perubahan ini.

#### 2. Rincian Perubahan Teknis
* **Komponen Backend (Controller / Model / Route)**:
  - `NamaController.php`: [Penjelasan fungsi baru/perubahan]
  - `ModelName.php`: [Relasi baru atau casts]
* **Basis Data & Migrasi**:
  - `xxxx_create_table.php`: [Tabel baru / kolom yang diubah]
* **Antarmuka Pengguna (UI / Blade)**:
  - `view_name.blade.php`: [Perubahan elemen tampilan / interaksi]

#### 3. Hasil Pengujian & Verifikasi
* **Automated Tests**:
  - Perintah: `php artisan test`
  - Hasil: [X passed, Y assertions]
* **Manual Verification**:
  - [Langkah pengujian 1]
  - [Langkah pengujian 2]

#### 4. Dampak & Rencana Tindak Lanjut (*Next Steps*)
* [Hal yang perlu diperhatikan pengguna atau langkah pengembangan selanjutnya]
```

---

## 📜 BAGIAN 2: RIWAYAT LOG PENGEMBANGAN (HISTORY)

---

### 🏷️ LOG #01: INISIALISASI ARSITEKTUR MVP ERP-SCM-CRM UMKM MINUMAN
* **Tanggal & Waktu**: Jumat, 20 November 2026 - 16:30 WIB
* **Tipe Perubahan**: `Feature` & `Initial Architecture`
* **Branch Git**: `main`
* **Commit Hash**: `9a22f0a`
* **Status**: Merged & Live

#### 1. Latar Belakang & Tujuan
* Membangun fondasi sistem aplikasi web siap pakai (*minimum viable product*) berbasis Laravel 13 untuk UMKM penjual minuman yang mengintegrasikan tiga fungsi esensial: Kasir POS & Keuangan (ERP), Stok Bahan Baku & Resep (SCM), dan Loyalitas Pelanggan (CRM).
* Memastikan struktur direktori berada langsung di *root repository* agar template sangat mudah dikloning dan dijalankan (*zero-configuration* menggunakan SQLite).

#### 2. Rincian Perubahan Teknis
* **Arsitektur & Konfigurasi**:
  - `config/beverage.php`: Berkas konfigurasi terpusat untuk nama kedai, mata uang, daftar ukuran cup, level es/gula, harga topping, dan aturan poin stamp.
* **Basis Data (10 Tabel Terpadu)**:
  - `materials`: Master bahan baku (kopi, susu, sirup, cup, sedotan) dengan satuan, stok riil, dan batas minimum.
  - `products`: Katalog menu minuman jadi dengan harga jual dan estimasi HPP.
  - `product_recipes`: Komposisi resep takaran bahan per cup (*Bill of Materials*).
  - `suppliers`: Data pemasok bahan dan kemasan.
  - `stock_movements`: Audit trail mutasi stok bahan (masuk/keluar).
  - `transactions` & `transaction_items`: Transaksi penjualan kasir dengan atribut varian minuman.
  - `cash_flows`: Buku kas masuk (penjualan) dan kas keluar (kulakan bahan & operasional).
  - `customers` & `loyalty_logs`: Member kedai, saldo poin, dan tingkatan member (*tier*).
* **Controller & Logika Bisnis**:
  - `PosController.php`: Kasir POS, pengurangan bahan otomatis via resep, dan cetak struk termal.
  - `ScmController.php`: Manajemen bahan baku, resep, pemasok, dan kulakan yang terhubung ke kas keluar ERP.
  - `ErpController.php`: Perhitungan saldo kas riil, omzet harian/bulanan, beban, dan laba bersih.
  - `CrmController.php`: Database pelanggan dan pesan otomatis promosi WhatsApp (`wa.me`).
  - `CustomerPortalController.php`: Halaman publik mandiri klaim token struk dan kartu stamp virtual.
* **Seeder Awal**:
  - `MinumanUmkmSeeder.php`: Data awal lengkap berisi 15+ menu minuman, 8 bahan baku, 3 supplier, data transaksi, dan member contoh.

#### 3. Hasil Pengujian & Verifikasi
* **Automated Tests**:
  - Perintah: `php artisan test`
  - Hasil: **6 tests passed (31 assertions)** dalam 0.17 detik.
  - Skenario teruji: Kasir POS memotong bahan sesuai resep, kulakan bahan menambah stok dan mencatat kas keluar, serta klaim poin pelanggan mencegah *double claim*.

---

### 🏷️ LOG #02: PENINGKATAN ANTARMUKA RAMAH MOBILE (MOBILE-FIRST UI/UX)
* **Tanggal & Waktu**: Jumat, 20 November 2026 - 16:47 WIB
* **Tipe Perubahan**: `UI/UX` & `Feature Enhancement`
* **Branch Git**: `feature/mobile-friendly-ui` → `main`
* **Pull Request**: [#3 feat(ui): Tampilan Ramah Mobile (Mobile-First UI/UX & Bottom Navigation)](https://github.com/mrplague1244/Web-UMKM/pull/3)
* **Status**: Merged

#### 1. Latar Belakang & Tujuan
* Meningkatkan kemudahan penggunaan (*usability*) pada perangkat smartphone dan tablet agar kasir atau pemilik kedai dapat mengoperasikan seluruh fitur kasir, cek stok, dan kirim pesan WhatsApp langsung dari HP tanpa repot *zooming*.

#### 2. Rincian Perubahan Teknis
* **Tata Letak Global (`layouts/seller.blade.php`)**:
  - Menambahkan **Fixed Mobile Bottom Navigation Bar** di smartphone (< 640px) dengan 5 tombol ikon: 🛒 Kasir, 📦 SCM, 💰 ERP, 👥 CRM, dan 📱 Klaim.
  - Menyesuaikan *padding* bawah kontainer utama (`pb-24 sm:pb-8`) agar konten tidak tertutup bilah navigasi bawah.
* **Kasir POS (`seller/pos.blade.php`)**:
  - Mengubah grid menu minuman menjadi 2 kolom di HP dengan tombol sentuh `+ Pesan` yang empuk (*thumb-friendly*).
  - Menambahkan **Floating Cart Bottom Bar** yang melayang di atas bilah navigasi ketika keranjang terisi (`🥤 X Cup • Rp X.XXX`).
  - Menambahkan **Mobile Cart Drawer (Bottom Sheet)** untuk meninjau keranjang, memilih metode bayar (Tunai/QRIS), kalkulator uang kembalian, dan *checkout* instan tanpa *scrolling*.
  - Menjadikan modal pemilihan gula, es, dan topping sebagai *bottom sheet* dari bawah layar.
* **SCM Bahan & Resep (`seller/scm.blade.php`)**:
  - Navigasi tab SCM dapat digeser secara horizontal (*swipeable strip*).
  - Menambahkan *Mobile Card View* ringkas untuk memantau status bahan yang **Menipis** atau **Habis**.
* **ERP Keuangan (`seller/erp.blade.php`)**:
  - Menyusun 4 kartu metrik utama (Saldo Kas, Omzet, Beban, Laba Bersih) dalam **Grid 2x2 di Mobile**.
* **CRM Pelanggan (`seller/crm.blade.php`)**:
  - Mengubah tabel pelanggan di HP menjadi format kartu dengan tombol pesan WhatsApp berukuran lebar (*WA Sapa* dan *WA Promo*).
* **Struk Digital (`seller/pos_receipt.blade.php`)**:
  - Menambahkan tombol **💬 Kirim ke WA** untuk membagikan nota pesanan dan tautan klaim poin ke nomor WhatsApp pelanggan dalam 1 klik.

#### 3. Hasil Pengujian & Verifikasi
* **Automated Tests**:
  - Perintah: `php artisan test`
  - Hasil: **6 tests passed (31 assertions)** tanpa regresi logika.
* **Manual Verification**:
  - Pengujian responsivitas pada resolusi viewport 375px (iPhone SE) dan 390px (iPhone 12/14) berjalan lancar.

---

### 🏷️ LOG #03: DEPLOYMENT CLOUD & ONLINE PREVIEW (RAILWAY.APP)
* **Tanggal & Waktu**: Jumat, 20 November 2026 - 17:30 WIB
* **Tipe Perubahan**: `Deployment` & `DevOps`
* **Platform**: Railway.app
* **Status**: Deployed & Online

#### 1. Latar Belakang & Tujuan
* Menyediakan tautan publik HTTPS yang aktif 24/7 agar aplikasi web dapat diakses secara langsung (*live*) oleh tim pengembang, klien, maupun penguji (*reviewers*).

#### 2. Rincian Konfigurasi Teknis
* **Environment Variables (Railway Variables)**:
  - `APP_NAME="Kedai Kopi & Teh Nusantara"`
  - `APP_ENV=production`
  - `APP_KEY=base64:+WgQT4znrckMtsy6wgbzKlBrHODy4vHobogXxvQB7y8=`
  - `APP_DEBUG=true`
  - `DB_CONNECTION=sqlite`
  - `SESSION_DRIVER=file`
  - `CACHE_STORE=file`
* **Inisialisasi Database SQLite di Container**:
  - Perintah: `touch database/database.sqlite && php artisan migrate --force --seed`
* **Networking**:
  - Mengaktifkan *Public Networking* melalui fitur *Generate Domain* Railway (`.up.railway.app`).

#### 3. Hasil Pengujian & Verifikasi
* Web app berhasil diakses secara daring dengan respons cepat.
* Fitur kasir POS, mutasi bahan SCM, buku kas ERP, dan portal klaim pelanggan berjalan normal di lingkungan *cloud container*.

---

### 📌 RENCANA PENGEMBANGAN BERIKUTNYA (*BACKLOG / ROADMAP*)
- [ ] Integrasi Payment Gateway otomatis (Midtrans / Xendit QRIS Dinamis).
- [ ] Export Laporan Keuangan ke format PDF & Excel (.xlsx).
- [ ] Pengingat otomatis via WhatsApp jika pelanggan sudah lama tidak berkunjung.
