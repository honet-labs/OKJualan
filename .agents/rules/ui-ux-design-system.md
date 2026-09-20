# OKJualan UI/UX Design System & Styling Rules

Dokumen ini adalah standar resmi desain antarmuka (UI) dan pengalaman pengguna (UX) untuk seluruh modul, menu admin, template, dan komponen plugin **OKJualan**. Semua developer dan AI assistant **wajib** mematuhi aturan ini tanpa pengecualian.

---

## 1. Larangan Emoticon & Ikon Berwarna (Strict Ban on Colored Emojis)

### 1.1 Aturan Utama
1. **Dilarang keras** menyematkan emoji / emotikon berwarna dalam bentuk apapun ke dalam antarmuka sistem, termasuk namun tidak terbatas pada:
   - Status badge / pills (`🟢 Lunas`, `🟡 Pending`, `🔴 Gagal`, `🔵 Diproses`, `⚪ Batal`, `✅ Selesai`, `⏳ Menunggu`).
   - Dropdown options `<option>` (`💵 Cash`, `📱 QRIS`, `⚡ QRIS Otomatis`, `🏦 Transfer Bank`, `💳 Midtrans`, `🌐 Tripay`).
   - Tombol aksi (`✓ Konfirmasi`, `🗑 Hapus`, `✏️ Edit`, `👁 Detail`).
   - Judul halaman, card header, atau label formulir (`📦 Produk`, `👥 Pelanggan`, `📊 Laporan`, `⚙️ Pengaturan`).
   - Tampilan kosong / empty state (`🧾 Belum Ada Transaksi`, `📦 Tidak Ada Produk`).
   - Teks penjelasan, catatan teknis, atau tooltip (`💡 Tips`, `🔒 Keamanan`, `🚀 Mode Live`, `🧪 Sandbox`).
2. **Format Teks Bersih**: Semua label, status, dan opsi dropdown harus menggunakan teks murni yang jelas dan profesional (contoh: `Lunas`, `Pending`, `Sedang Diproses`, `Gagal`, `Kadaluwarsa`, `Dibatalkan`).

### 1.2 Standar Penggunaan Ikon
- **Gunakan WordPress Dashicons** atau SVG monokrom standar:
  ```html
  <!-- Benar: Menggunakan Dashicons resmi dengan warna netral -->
  <span class="dashicons dashicons-media-document" style="font-size: 16px; color: #64748b;"></span>
  <span class="dashicons dashicons-phone" style="font-size: 13px; color: #64748b;"></span>
  <span class="dashicons dashicons-email" style="font-size: 13px; color: #64748b;"></span>
  <span class="dashicons dashicons-shield" style="font-size: 14px; color: #15803d;"></span>
  ```
- **Warna Ikon**: Ikon harus bernada netral (`#64748b`, `#94a3b8`, `#475569`) atau mewarisi warna elemen induknya (`currentColor`). Ikon tidak boleh memiliki warna-warni pelangi yang mencolok.

---

## 2. Tipografi (Typography Hierarchy)

### 2.1 Font Family
- **Satu Font Utama**: Gunakan font `'Plus Jakarta Sans'` untuk semua elemen di dalam wrapper `.okj-wrap` dan modal `.okj-modal`:
  ```css
  font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
  ```
- **Pengecualian Dashicons**: Pastikan font dashicons tidak tertimpa:
  ```css
  .dashicons, .dashicons-before:before, span.dashicons {
      font-family: dashicons !important;
  }
  ```

### 2.2 Hierarki Ukuran & Bobot (Font Scale & Weights)

| Elemen UI | Ukuran Font | Font Weight | Line Height | Warna Teks | Keterangan |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Page Header (`h1`)** | `1.5rem` (24px) | `700` (Bold) | `1.2` | `#1e1b4b` / `#0f172a` | Judul utama setiap layar menu |
| **Section / Card Header (`h2`, `h3`)** | `0.95rem` - `1rem` (15px-16px) | `700` (Bold) | `1.3` | `#1e293b` | Judul tabel, card summary, modal title |
| **Subtitle / Deskripsi Menu** | `0.875rem` (14px) | `400` (Regular) | `1.4` | `#64748b` | Keterangan di bawah judul utama |
| **Table Header (`th`)** | `0.75rem` (12px) | `700` (Bold) | `1.2` | `#475569` | Huruf kapital (`uppercase`), letter-spacing: `0.05em` |
| **Table Body Cell (`td`)** | `0.85rem` - `0.875rem` (13px-14px) | `400` / `500` | `1.5` | `#334155` | Data baris tabel |
| **Table Data Primer (Nama, ID)** | `0.875rem` (14px) | `600` / `700` | `1.3` | `#0f172a` | Nama produk, nama pembeli, nomor faktur |
| **Table Data Sekunder (Tanggal, Sub)**| `0.75rem` (12px) | `400` (Regular) | `1.3` | `#64748b` | Jam transaksi, kategori, email |
| **Form Label** | `0.8rem` (13px) | `600` (Semi-bold) | `1.2` | `#374151` / `#475569` | Label di atas input form |
| **Input / Select / Textarea** | `0.85rem` (13px-14px) | `400` / `500` | `1.4` | `#1e293b` | Teks nilai input |
| **Badges / Status Pills** | `0.7rem` - `0.75rem` (11px-12px) | `700` (Bold) | `1.0` | Sesuai status | Huruf kapital ringkas, padding `4px 10px` |
| **Helper Text / Help Block** | `0.75rem` (12px) | `400` (Regular) | `1.5` | `#64748b` / `#94a3b8` | Petunjuk pengisian field |

---

## 3. Palet Warna & Latar Belakang (Color Palette & Backgrounds)

Desain mengadopsi standar **SaaS Modern (Stripe / Linear style)** dengan warna netral slate yang bersih dan aksen indigo yang elegan.

### 3.1 Warna Netral (Grayscale & Surfaces)
- **Background Halaman**: `#f8fafc` (Slate 50)
- **Background Kontainer / Card / Modal**: `#ffffff` (Pure White)
- **Background Hover Baris**: `#fafcfc` / `#f8fafc`
- **Border Default**: `#e2e8f0` (Slate 200)
- **Border Input / Form**: `#cbd5e1` (Slate 300), saat fokus `#6366f1` (Indigo 500)
- **Border Divider Halus**: `#f1f5f9` (Slate 100)

### 3.2 Warna Aksen Brand (Indigo Accent)
- **Primary Action (Tombol Utama)**: `linear-gradient(135deg, #6366f1 0%, #4f46e5 100%)`
- **Primary Hover**: `linear-gradient(135deg, #4f46e5 0%, #4338ca 100%)`
- **Primary Text Link**: `#4f46e5` (hover: `#3730a3`)

### 3.3 Sistem Status Badge (Pastel Background + Deep Foreground)
Status badge **tidak boleh** menggunakan warna neon silau atau emoji. Gunakan kombinasi pastel berikut:

| Status | Background | Warna Teks | Border | Penggunaan |
| :--- | :--- | :--- | :--- | :--- |
| **Success** | `#ecfdf5` (Emerald 50) | `#047857` (Emerald 700) | `#a7f3d0` (Emerald 200) | Lunas, Selesai, Aktif |
| **Warning** | `#fffbeb` (Amber 50) | `#b45309` (Amber 700) | `#fde68a` (Amber 200) | Pending, Menunggu Pembayaran, Stok Menipis |
| **Processing** | `#fef3c7` (Yellow 100) | `#92400e` (Yellow 800) | `#fcd34d` (Yellow 300) | Dalam Proses (Perlu Akun Manual) |
| **Info / Tech**| `#e0e7ff` (Indigo 100) | `#4338ca` (Indigo 700) | `#c7d2fe` (Indigo 200) | Sync WooCommerce, Hook, Tag |
| **Danger** | `#fef2f2` (Rose 50) | `#b91c1c` (Rose 700) | `#fecaca` (Rose 200) | Gagal, Expired, Habis |
| **Neutral** | `#f1f5f9` (Slate 100) | `#64748b` (Slate 500) | `#e2e8f0` (Slate 200) | Dibatalkan, Nonaktif, Draft |

---

## 4. Tata Letak Tabel & Penyelarasan Kolom (Table Alignment & Layout)

### 4.1 Aturan Penyelarasan Kolom (Strict Alignment)
1. **Rata Kiri (`text-align: left;`)**:
   - Kolom identitas teks: No. Transaksi, Tanggal/Waktu, Pelanggan, Nama Produk/Item, Kategori.
2. **Rata Tengah (`text-align: center;`)**:
   - Kolom kuantitas / Qty (`<th>Qty</th>`, `<td>2</td>`).
   - Kolom durasi hari (`<td>30 Hari</td>`).
   - Kolom status layanan dan status pembayaran (`.okj-badge`).
3. **Rata Kanan (`text-align: right;`)**:
   - Semua nilai nominal uang (Harga Satuan, Subtotal, Diskon, Total Bayar).
   - Kolom Aksi (`<th>Aksi</th>`, tombol Detail, Cetak, Hapus).

### 4.2 Larangan Menggabungkan Kolom yang Berbeda Konsep (Separation of Concerns)
- **Item Produk & Qty WAJIB DIPISAH**: Dilarang menggabungkan nama produk dengan jumlah qty dalam satu kolom string (misal `"Netflix Premium (1)"` atau `"Canva x 2"`). 
  - Kolom **Item Produk**: berisi nama produk dan variannya.
  - Kolom **Qty**: berada di kolom tersendiri dengan `text-align: center;` dan format badge/angka yang jelas.

---

## 5. Tombol & Elemen Interaktif (Buttons & Interactive Elements)

### 5.1 Tombol Utama (`.okj-btn-primary`)
- Padding: `8px 16px` (Normal) atau `5px 10px` (Small).
- Border-radius: `6px` atau `8px`.
- Font-weight: `600`.
- Efek hover halus (`transition: all 0.2s ease`).

### 5.2 Tombol Sekunder (`.okj-btn-secondary`)
- Background: `#ffffff`.
- Border: `1px solid #e2e8f0`.
- Text color: `#475569`.
- Hover: Background `#f8fafc`, border `#cbd5e1`.

### 5.3 Tombol Tautan Aksi Tabel (`.okj-btn-link`)
- Teks ringkas tanpa ikon berwarna berlebih.
- Warna: `#4f46e5` (normal), `#ef4444` (danger/hapus).
- Tidak menggunakan garis bawah (`text-decoration: none`), munculkan underline hanya saat hover jika diperlukan.

---

## 6. Formulir & Kontrol Input (Forms & Inputs)

1. **Struktur Grid**: Gunakan `.okj-form-grid` (2 kolom) atau `.okj-form-row` yang seimbang.
2. **Konsistensi Field**:
   - Input teks & Select: `padding: 8px 12px; height: 38px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px;`
   - Label: `font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 5px; display: block;`
   - Select option: Tidak boleh mengandung emoji awalan (`💵 Cash` ❌ -> `Cash` ✅).

---

## 7. Checklist Pra-Rilis UI (UI Verification Checklist)

Sebelum melakukan commit kode yang memodifikasi antarmuka:
- [ ] Apakah ada emoji berwarna (`🟢`, `🟡`, `🔴`, `💵`, `📱`, `⚡`, `🧾`, `📦`, dsb.) di dalam teks template? **Wajib 0 emoji di UI.**
- [ ] Apakah font `'Plus Jakarta Sans'` diterapkan dengan benar?
- [ ] Apakah tabel memisahkan kolom **Item Produk** dan **Qty** secara terpisah?
- [ ] Apakah alignment kolom tabel sudah benar (Teks di kiri, Qty/Status di tengah, Uang/Aksi di kanan)?
- [ ] Apakah status badge menggunakan warna pastel yang tenang dengan teks yang kontras?
- [ ] Apakah seluruh tag PHP (`<?php ... ?>`) dan kurung kurawal seimbang?
