# OKJualan Project Rules & Agent Guidelines

Selamat datang di repository **OKJualan** (WordPress All-in-One POS, Subscription/Service Management, Payment Gateway & Notifier).
Semua developer dan AI agent yang bekerja pada repository ini **wajib** mengikuti pedoman arsitektur dan standar desain UI/UX di bawah ini.

---

## 1. Standar Desain UI & UX (Wajib Dipatuhi)

Detail lengkap spesifikasi desain tercantum di [`.agents/rules/ui-ux-design-system.md`](file:///.agents/rules/ui-ux-design-system.md). Berikut adalah ringkasan aturan kritis:

### 1.1 Larangan Keras Emoticon & Ikon Berwarna
- **DILARANG** menggunakan emoji berwarna (`🟢`, `🟡`, `🔴`, `🔵`, `⚪`, `💵`, `📱`, `⚡`, `🏦`, `💳`, `🌐`, `🧾`, `📦`, `💡`, `🔒`, `🚀`, `🧪`, dsb.) pada:
  - Status badge tabel (contoh: gunakan `Lunas`, BUKAN `Lunas 🟢`).
  - Opsi dropdown `<option>` (contoh: gunakan `Cash / Tunai`, BUKAN `💵 Cash`).
  - Judul menu, card header, atau label input.
  - Tombol aksi dan modal dialog.
  - Halaman kosong / empty state (gunakan Dashicons monokrom netral, bukan emoji).
- **Gunakan WordPress Dashicons resmi** (`<span class="dashicons dashicons-..."></span>`) dengan warna netral (`#64748b`, `#475569`, `#94a3b8`) jika membutuhkan ikon.

### 1.2 Tipografi
- **Font Utama**: `'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;`
- **Ukuran & Bobot**:
  - Judul Menu (`h1`): `24px` (`1.5rem`), font-weight `700`, warna `#1e1b4b` / `#0f172a`.
  - Judul Card / Modal (`h2`, `h3`): `15px-16px`, font-weight `700`, warna `#1e293b`.
  - Header Kolom Tabel (`th`): `11px-12px`, font-weight `700`, uppercase, letter-spacing `0.05em`, warna `#475569`.
  - Data Tabel & Body (`td`): `13px-14px`, font-weight `400-500`, warna `#334155`.
  - Badges Status: `11px-11.5px`, font-weight `700`, padding `4px 10px`, border-radius `9999px` (pill) atau `4px`.
  - Teks Bantuan (`small`, helper): `11px-12px`, warna `#64748b`.

### 1.3 Palet Warna (Modern SaaS Neutral Palette)
- **Background Utama**: `#f8fafc` (Slate 50).
- **Background Card/Tabel/Modal**: `#ffffff`.
- **Border Default**: `#e2e8f0` (Slate 200).
- **Aksen Primer**: Indigo gradient `linear-gradient(135deg, #6366f1 0%, #4f46e5 100%)`.
- **Status Badges (Pastel Halus + Teks Gelap Kontras)**:
  - *Lunas / Aktif / Selesai*: Background `#ecfdf5`, Teks `#047857`, Border `#a7f3d0`.
  - *Pending / Menunggu*: Background `#fffbeb`, Teks `#b45309`, Border `#fde68a`.
  - *Dalam Proses (Perlu Akun)*: Background `#fef3c7`, Teks `#92400e`, Border `#fcd34d`.
  - *Gagal / Expired*: Background `#fef2f2`, Teks `#b91c1c`, Border `#fecaca`.
  - *Dibatalkan / Nonaktif*: Background `#f1f5f9`, Teks `#64748b`, Border `#e2e8f0`.

### 1.4 Penyelarasan Kolom Tabel (Alignment)
- **Rata Kiri (`text-align: left;`)**: Nama Pelanggan, Nama Produk, No. Transaksi, Tanggal.
- **Rata Tengah (`text-align: center;`)**: Qty (Kuantitas), Durasi Hari, Badge Status Layanan, Badge Status Bayar.
- **Rata Kanan (`text-align: right;`)**: Nilai Uang (Harga, Total Bayar), Kolom Aksi.
- **Pemisahan Kolom**: Jangan pernah menggabungkan item produk dan kuantitas dalam satu kolom string. Buat kolom terpisah untuk **Item Produk** dan **Qty**.

---

## 2. Integritas Kode & Verifikasi Sebelum Commit

1. **Keseimbangan Tag PHP**: Pastikan setiap file PHP memiliki pembuka `<?php` dan penutup `?>` yang seimbang jika mencampur HTML dengan PHP.
2. **Keseimbangan Kurung**: Periksa kurung kurawal `{ }` dan tanda kurung `( )`.
3. **Versi Plugin**: Selalu naikkan versi di `okjualan.php` (docblock dan konstanta `VERSION`) pada setiap patch atau perbaikan.
4. **Git Workflow**: Commit perubahan dengan pesan deskriptif, buat tag git versi baru (misal `v0.2.11`), dan lakukan push ke `origin main --tags`.
