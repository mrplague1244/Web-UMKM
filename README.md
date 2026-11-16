# 🏪 Web-UMKM: RasaNusantara

Landing Page modern, responsif, dan siap pakai untuk showcase produk dan profil usaha **UMKM (Usaha Mikro, Kecil, dan Menengah)** Indonesia.

---

## 📌 Gambaran Proyek

Proyek ini dibuat sebagai fondasi awal (starter/template) untuk tugas kelompok pengembangan website profil UMKM. Menggunakan pendekatan *zero-build setup*, website ini dapat langsung dibuka dan dijalankan di browser manapun tanpa instalasi software tambahan.

### 🌟 Fitur Utama
- **Responsive Design**: Tampilan optimal di smartphone, tablet, maupun layar desktop.
- **Hero Showcase**: Bagian pembuka dengan headline menarik, statistik penjualan, dan gambar produk.
- **Katalog Produk**: Daftar menu/produk lengkap dengan badge harga, deskripsi singkat, dan tombol pemesanan.
- **Tentang Kami**: Narasi kisah usaha, visi, dan misi pemberdayaan lokal.
- **Testimoni Pelanggan**: Kartu ulasan dan rating kepuasan konsumen.
- **Direct WhatsApp Order**: Tombol order langsung mengarahkan pelanggan ke chat WhatsApp dengan teks pesan otomatis.
- **Modern UI**: Menggunakan Tailwind CSS CDN, Lucide Icons, dan font Plus Jakarta Sans.

---

## 🛠️ Teknologi yang Digunakan

| Komponen | Teknologi |
| :--- | :--- |
| **Markup** | HTML5 Semantic |
| **Styling** | [Tailwind CSS](https://tailwindcss.com/) (via CDN) |
| **Icons** | [Lucide Icons](https://lucide.dev/) |
| **Typography** | Google Fonts (Plus Jakarta Sans) |

---

## 🚀 Cara Menjalankan Secara Lokal

Tidak diperlukan `npm` atau `Node.js`. Cukup:

1. **Clone repositori**:
   ```bash
   git clone git@github.com:mrplague1244/Web-UMKM.git
   cd Web-UMKM
   ```
2. **Buka file di browser**:
   - Double-click file `index.html`, **atau**
   - Gunakan extension **Live Server** di VS Code, **atau**
   - Jalankan web server sederhana menggunakan Python:
     ```bash
     python3 -m http.server 3000
     ```
     Lalu buka `http://localhost:3000` di browser.

---

## 👥 Tim Pengembang (Kelompok)

| Nama / Akun | Peran | GitHub |
| :--- | :--- | :--- |
| **Hafid Indra Pramana** | Owner / Lead | [@mrplague1244](https://github.com/mrplague1244) |
| **Wahid Maulana** | Collaborator | [@wahidmaulanaa](https://github.com/wahidmaulanaa) |
| **Nuno Mahawira** | Collaborator | [@NunoMahawira](https://github.com/NunoMahawira) |
| **Andaru** | Collaborator | [@andrujpya](https://github.com/andrujpya) |

---

## 🌿 Panduan Kolaborasi Git untuk Anggota

Agar tidak terjadi *conflict* saat pengerjaan tugas bersama:

1. **Tarik perubahan terbaru sebelum mulai coding**:
   ```bash
   git pull origin main
   ```
2. **Buat branch baru untuk fitur yang dikerjakan**:
   ```bash
   git checkout -b fitur/nama-fitur
   ```
3. **Commit dan push branch**:
   ```bash
   git add .
   git commit -m "feat: menambahkan katalog produk keripik"
   git push origin fitur/nama-fitur
   ```
4. **Buka Pull Request (PR)** di GitHub untuk di-review oleh teman kelompok sebelum di-merge ke `main`.

---

## 📄 Lisensi
Proyek ini dibuat untuk keperluan pembelajaran dan tugas kelompok.
