# OKJualan 🚀
> **Platform All-in-One Penjualan Produk, POS Kasir, Pelacakan Layanan & Pembelian, Payment Gateway (SumoPod QRIS), Notifikasi Multi-Channel, dan Laporan Penjualan Berkala untuk WordPress.**

[![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-blue.svg)](https://wordpress.org)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%20%7C%208.0%20%7C%208.1%20%7C%208.2-indigo.svg)](https://php.net)
[![Version](https://img.shields.io/badge/Version-0.2.17-green.svg)](https://github.com/honet-labs/OKJualan)
[![License](https://img.shields.io/badge/License-GPLv2-orange.svg)](LICENSE)
[![Gateway](https://img.shields.io/badge/Payment-SumoPod%20QRIS-purple.svg)](https://sumopod.com)

---

## 📋 Tentang OKJualan

**OKJualan** (rebranding dari *OKJualin*) adalah plugin WordPress modular yang dirancang untuk kebutuhan bisnis modern, UMKM, toko fisik, dan digital services. OKJualan mengintegrasikan manajemen master produk, supplier, customer, kasir POS *real-time*, halaman pemesanan *self-service*, otomatisasi notifikasi WhatsApp & Telegram, serta laporan penjualan komprehensif dalam satu dasbor terpadu.

---

## ✨ 10 Fitur Unggulan

### 1. 🏷️ Daftar Harga Produk (11 Kolom Lengkap)
Manajemen katalog produk yang fleksibel untuk produk fisik, virtual, maupun langganan/layanan:
- **11 Kolom Terstruktur:** `ID Product` (`PRD-XXXX`), `Nama Product`, `Harga Product`, `Stok Product`, `Durasi Product`, `Kategori Product`, `Deskripsi Product`, `Gambar Product`, `Seller/Supplier/Provider`, `Keterangan Tambahan`, dan `Status Product` (*Aktif/Nonaktif*).
- **Stok Fleksibel:** Mendukung `-1` untuk produk *Unlimited* (tanpa batas) atau angka stok riil dengan badge peringatan stok otomatis.
- **Media Library Integration:** Upload gambar langsung dari WordPress Media Library dan live preview thumbnail.
- **Provider Selector:** Relasi terintegrasi ke data supplier/seller.

### 2. 🏢 List Seller / Supplier / Provider (7 Kolom)
Pengelolaan mitra vendor, supplier stok, atau provider layanan:
- **7 Kolom Lengkap:** `ID Seller` (`SLR-XXXX`), `Nama`, `Alamat`, `Nomor Telepon`, `Email`, `Keterangan Tambahan`, dan `Status` (*Aktif/Nonaktif*).
- **Tautan Cepat WhatsApp:** Hubungi supplier langsung dari tabel dengan 1 klik.
- **Quick-Add AJAX Modal:** Tambah vendor baru langsung dari form input produk tanpa berpindah halaman.

### 3. 👥 List Customer (7 Kolom)
Database pelanggan terpusat dengan riwayat interaksi:
- **7 Kolom Lengkap:** `ID Customer` (`CST-XXXX`), `Nama Customer`, `Alamat`, `Nomor Telepon`, `Email`, `Keterangan Tambahan`, dan `Status Customer` (*Aktif/Nonaktif*).
- **Riwayat Pembelian Modal:** Pantau transaksi sebelumnya yang pernah dilakukan oleh pelanggan.
- **Chat WhatsApp Instan:** Tombol langsung menuju chat WhatsApp customer.

### 4. 📦 Pembelian Produk & Monitoring Produk Aktif
Pusat kontrol pemantauan layanan langganan, durasi aktif, dan rekapitulasi pembelian:
- **9 Kolom Utama:** `ID Pembelian`, `Customer`, `Produk`, `Tanggal Pembelian`, `Qty`, `Total Harga`, `Status` (*Active, Expired, Pending, Cancelled*), `Keterangan`, dan aksi `Perpanjang Masa Aktif`.
- **Perpanjang Masa Aktif Modal:** Ekstensi durasi hari/bulan langsung dengan kalkulasi harga baru.
- **Audit Log Riwayat Perpanjangan:** Jejak rekam visual perpanjangan layanan di tabel `wp_okj_active_product_renewals`.

### 5. 🛒 Sistem Point of Sale (POS) Cepat & Efisien
Sistem kasir modern ala aplikasi retail premium untuk toko offline dan penjualan langsung:
- **Katalog Kasir Cepat:** Filter kategori dengan tombol pills, pencarian instan, dan live stock indicator (*Unlimited, Sisa X, Habis*).
- **Riwayat Transaksi (7 Kolom):** `ID Transaksi` (`POS-YYYYMMDD-XXXX`), `Customer`, `Produk & Detail Item`, `Jumlah Terjual (Qty)`, `Total Harga`, `Tanggal`, dan `Keterangan`.
- **Pemotongan Stok Otomatis:** Stok otomatis berkurang saat transaksi POS selesai.
- **Peringatan Stok Menipis:** Notifikasi otomatis ke seller jika stok tersisa $\le 3$.
- **Thermal Receipt & WhatsApp:** Cetak struk kasir ukuran printer thermal dan opsi kirim rincian struk via WhatsApp customer.

### 6. 💳 Payment Gateway Terintegrasi (SumoPod QRIS & Multi-Payment)
Solusi penerimaan pembayaran otomatis dan manual:
- **SumoPod Gateway:**
  - Dukungan Sandbox & Production API.
  - Generate otomatis QRIS dinamis via API SumoPod saat customer checkout di halaman pemesanan mandiri.
  - Webhook listener `/?okj_webhook=payment` dengan verifikasi signature Svix standar keamanan tinggi (`whsec_...` HMAC-SHA256) serta fallback token webhook (`whtok_...`).
  - Auto-lunas (*Mark Order Paid*) dan pemotongan stok otomatis saat notifikasi pelunasan diterima.
- **Metode Pembayaran Lainnya:** Midtrans, Tripay, Transfer Bank Manual (BCA, Mandiri, BRI, BNI), dan Scan QRIS Statis Toko.

### 7. 📊 Laporan Penjualan Berkala (Bulanan & Rentang Tanggal)
Rekapitulasi performa bisnis untuk pengambilan keputusan strategis:
- **Filter Fleksibel:** Filter rekap bulanan atau rentang tanggal spesifik (*Start Date* s/d *End Date*).
- **KPI Metrik Omset:** Kartu statistik ringkasan Total Omset (Rp), Total Transaksi Selesai, dan Total Produk Terjual.
- **Unduh Laporan PDF:** Cetak laporan penjualan formal berlogo toko lengkap dengan tabel rincian transaksi dan kolom tanda tangan penanggung jawab.
- **Export CSV:** Unduh berkas CSV dengan UTF-8 BOM agar angka dan simbol Rupiah tampil sempurna saat dibuka di Microsoft Excel.

### 8. 🔔 Notifikasi & Pengingat Multi-Channel
Automasi pesan pengingat dan konfirmasi transaksi:
- **Channel Tersedia:** WhatsApp (WAHA API), Telegram Bot API, dan Email SMTP kustom.
- **Notifikasi Pelanggan:**
  - Konfirmasi saat pesanan baru dibuat.
  - Notifikasi pembayaran lunas & rincian layanan.
  - Pengingat masa aktif layanan mendekati jatuh tempo (H-7, H-3, dan H-1).
- **Notifikasi Seller/Supplier:**
  - Notifikasi saat produk terjual di kasir POS atau pemesanan publik.
  - Peringatan stok menipis (*Low Stock Alert*) otomatis jika stok $\le 3$ unit.

### 9. 🛡️ Sistem Keamanan & Privasi
- **Enkripsi Kredensial Sensitif:** Kelas `OKJ_Security` mengamankan API Key, Secret Token, Bot Token, dan Password SMTP dengan enkripsi `AES-256-CBC` berbasis salt unik WordPress.
- **Rate-Limiting Anti-Spam:** Proteksi brute-force dan spam submission pada endpoint publik menggunakan IP sliding window transient.
- **Hak Akses & Role Khusus:** Role baru `OKJualan Manager` (`okj_manager`) dengan kapabilitas granular (`okj_manage`, `okj_view_reports`, `okj_manage_settings`).
- **Sanitasi & Proteksi Nonce:** Seluruh request divalidasi dengan `check_admin_referer` / `wp_verify_nonce` dan sanitasi data ketat.

### 10. 💬 Dukungan Pelanggan (Customer Support)
- **Menu Dukungan Pelanggan:** Halaman khusus di dasbor admin untuk kontak CS WhatsApp, email helpdesk, dan panduan.
- **FAQ Accordion:** Daftar pertanyaan yang sering diajukan yang dapat dikonfigurasi melalui menu Settings.
- **Widget Floating WhatsApp:** Tombol mengambang hijau WhatsApp di halaman self-service order (`/?okj_order=1`) dengan pesan pembuka otomatis.

---

## 🗄️ Struktur Database

Plugin menggunakan tabel mandiri berkinerja tinggi dengan prefix `wp_okj_*`:
| Nama Tabel | Deskripsi |
| :--- | :--- |
| `wp_okj_product_prices` | Master katalog harga produk, stok, provider, kategori, dan gambar. |
| `wp_okj_sellers` | Data vendor, supplier, dan provider layanan. |
| `wp_okj_customers` | Database pelanggan dan kontak WhatsApp/email. |
| `wp_okj_active_products` | Rekap pembelian customer dan monitoring masa aktif langganan. |
| `wp_okj_active_product_renewals` | Riwayat audit log perpanjangan durasi produk aktif. |
| `wp_okj_pos_transactions` | Header transaksi penjualan kasir POS. |
| `wp_okj_pos_transaction_items` | Rincian item produk yang terjual per transaksi POS. |
| `wp_okj_active_reminders` | Antrean jadwal pengiriman reminder otomatis (H-7, H-3, H-1). |
| `wp_okj_shortlinks` | Sistem pemendek tautan referral affiliate & tracking klik. |
| `wp_okj_logs` | Audit trail aktivitas sistem dan log sinkronisasi. |

---

## 🚀 Panduan Instalasi & Penggunaan

### 1. Instalasi Plugin
1. Unduh repositori ini atau clone ke direktori plugin WordPress:
   ```bash
   cd wp-content/plugins/
   git clone https://github.com/honet-labs/OKJualan.git okjualan
   ```
2. Buka Dasbor WordPress &rarr; **Plugins** &rarr; cari **OKJualan** &rarr; klik **Activate**.
3. Sistem secara otomatis menjalankan migrasi skema tabel database dan menginisialisasi role `OKJualan Manager`.

### 2. Konfigurasi Awal
1. Buka menu **OKJualan** &rarr; **Settings**:
   - **Tab Umum:** Nama toko, alamat, logo, dan nomor WhatsApp admin.
   - **Tab Payment Gateway:** Masukkan kredensial SumoPod (API Key, Secret Key, Webhook Token/Secret), atau aktifkan metode Manual / QRIS Statis.
   - **Tab Notifikasi:** Konfigurasikan WAHA endpoint/session, Telegram Bot Token & Chat ID, atau SMTP Email.
   - **Tab Dukungan Pelanggan:** Atur nomor WhatsApp CS dan email helpdesk.

### 3. Setup Webhook SumoPod Payment
1. Pada dashboard SumoPod Anda, tambahkan Webhook URL:
   ```
   https://domain-anda.com/?okj_webhook=payment
   ```
2. Masukkan Webhook Secret (`whsec_...`) atau Webhook Token ke pengaturan OKJualan.
3. Transaksi pembayaran QRIS dari customer akan otomatis terverifikasi secara *real-time*.

### 4. Halaman Pemesanan Publik (Self-Service Order)
Pelanggan dapat melakukan pemesanan langsung melalui tautan:
```
https://domain-anda.com/?okj_order=1
```
Halaman ini responsif untuk perangkat mobile, menampilkan katalog produk, filter kategori, keranjang belanja, checkout instan, pelacakan status pesanan real-time, dan tombol bantuan CS WhatsApp.

---

## 🔄 Changelog

### v0.1.3 (Rilis Terbaru)
- **Rebranding:** Perubahan identitas dari *OKJualin* menjadi **OKJualan**.
- **Fitur Baru:** Penambahan modul SumoPod Payment Gateway dengan verifikasi signature Svix HMAC-SHA256.
- **Pembaruan Fitur:** Master produk diperluas menjadi 11 kolom dengan sistem stok unlimited (`-1`), upload gambar, dan status aktif.
- **Pembaruan Fitur:** Master Seller dan Customer diperluas menjadi 7 kolom lengkap.
- **Pembaruan Fitur:** Modul Kasir POS dengan riwayat 7 kolom, pemotongan stok otomatis, dan notifikasi stok menipis ($\le 3$).
- **Pembaruan Fitur:** Laporan penjualan berkala (bulanan/custom date range) dengan download PDF berlogo dan CSV UTF-8 BOM.
- **Pembaruan Fitur:** Multi-channel notifications untuk notifikasi customer dan seller (produk terjual & low stock).
- **Keamanan:** Enkripsi AES-256-CBC untuk kredensial sensitif dan IP sliding-window rate limiting.
- **Dukungan Pelanggan:** Halaman Dukungan Pelanggan, FAQ interaktif, dan widget floating WhatsApp di halaman pemesanan mandiri.

---

## 📄 Lisensi & Kontributor

- **Pengembang:** [HONET](https://github.com/honet-labs)
- **Lisensi:** GNU General Public License v2.0 or later (GPLv2).
