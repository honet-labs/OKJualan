<?php if (!defined('ABSPATH')) { exit; } 

$settings = get_option('okj_settings_v1', []);
$company_name = !empty($settings['pdf_company_name']) ? $settings['pdf_company_name'] : 'OKJualan';
$cs_phone = !empty($settings['support_whatsapp']) ? $settings['support_whatsapp'] : (!empty($settings['pdf_company_phone']) ? $settings['pdf_company_phone'] : '');
$cs_email = !empty($settings['support_email']) ? $settings['support_email'] : get_option('admin_email');
$cs_hours = !empty($settings['support_hours']) ? $settings['support_hours'] : 'Senin - Sabtu (08:00 - 21:00 WIB)';

// Format wa link
$clean_phone = preg_replace('/[^0-9]/', '', $cs_phone);
if (strpos($clean_phone, '0') === 0) {
    $clean_phone = '62' . substr($clean_phone, 1);
}
$wa_url = $clean_phone ? 'https://wa.me/' . $clean_phone . '?text=' . rawurlencode("Halo CS {$company_name}, saya membutuhkan bantuan terkait sistem OKJualan.") : '#';
?>

<div class="okj-wrap">
    <div class="okj-header">
        <div>
            <h1>Pusat Bantuan & Dukungan Pelanggan</h1>
            <p class="okj-subtitle">Layanan bantuan resmi untuk customer, seller, dan pengelola platform <?php echo esc_html($company_name); ?>.</p>
        </div>
    </div>

    <!-- Quick Contact Cards Grid -->
    <div class="okj-grid okj-grid-3 okj-mt-2">
        <!-- Card 1: WhatsApp Live Support -->
        <div class="okj-card" style="border-top: 4px solid #10b981;">
            <div class="okj-card-body" style="text-align: center; padding: 24px 20px;">
                <div style="width: 56px; height: 56px; background: #dcfce7; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                    <span class="dashicons dashicons-whatsapp" style="font-size: 32px; width: 32px; height: 32px; color: #10b981;"></span>
                </div>
                <h3 style="font-size: 17px; margin-bottom: 6px; color: #0f172a;">Live Chat WhatsApp</h3>
                <p class="okj-text-muted" style="font-size: 13px; margin-bottom: 18px;">Hubungi tim Customer Service kami secara instan untuk bantuan kendala transaksi & teknis.</p>
                <?php if ($clean_phone): ?>
                    <a href="<?php echo esc_url($wa_url); ?>" target="_blank" class="okj-btn okj-btn-primary" style="background: #10b981; border-color: #10b981; width: 100%; display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
                        <span class="dashicons dashicons-phone"></span> Chat WhatsApp CS
                    </a>
                <?php else: ?>
                    <a href="<?php echo admin_url('admin.php?page=okj-settings'); ?>" class="okj-btn okj-btn-secondary" style="width: 100%;">
                        Atur No. WhatsApp CS
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Card 2: Email Helpdesk -->
        <div class="okj-card" style="border-top: 4px solid #6366f1;">
            <div class="okj-card-body" style="text-align: center; padding: 24px 20px;">
                <div style="width: 56px; height: 56px; background: #e0e7ff; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                    <span class="dashicons dashicons-email-alt" style="font-size: 32px; width: 32px; height: 32px; color: #6366f1;"></span>
                </div>
                <h3 style="font-size: 17px; margin-bottom: 6px; color: #0f172a;">Email Support</h3>
                <p class="okj-text-muted" style="font-size: 13px; margin-bottom: 18px;">Kirim pesan tiket pertanyaan resmi atau konfirmasi data rekonsiliasi pembayaran.</p>
                <a href="mailto:<?php echo esc_attr($cs_email); ?>?subject=Bantuan%20OKJualan" class="okj-btn okj-btn-secondary" style="width: 100%; display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
                    <span class="dashicons dashicons-email"></span> Kirim Email Bantuan
                </a>
            </div>
        </div>

        <!-- Card 3: Operational Hours -->
        <div class="okj-card" style="border-top: 4px solid #f59e0b;">
            <div class="okj-card-body" style="text-align: center; padding: 24px 20px;">
                <div style="width: 56px; height: 56px; background: #fef3c7; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                    <span class="dashicons dashicons-clock" style="font-size: 32px; width: 32px; height: 32px; color: #f59e0b;"></span>
                </div>
                <h3 style="font-size: 17px; margin-bottom: 6px; color: #0f172a;">Jam Operasional</h3>
                <p class="okj-text-muted" style="font-size: 13px; margin-bottom: 18px;">Waktu aktif respon layanan pelanggan & verifikasi pembayaran manual.</p>
                <div style="background: #f8fafc; padding: 8px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; color: #475569; border: 1px solid #e2e8f0;">
                    <?php echo esc_html($cs_hours); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- FAQ Accordion Section -->
    <div class="okj-card okj-mt-2">
        <div class="okj-card-header">
            <h2>Frequently Asked Questions (FAQ) & Panduan Sistem</h2>
        </div>
        <div class="okj-card-body" style="padding: 24px;">
            <div class="okj-faq-list" style="display: flex; flex-direction: column; gap: 12px;">
                
                <!-- FAQ 1 -->
                <div class="okj-faq-item" style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                    <button type="button" class="okj-faq-question" style="width: 100%; text-align: left; padding: 14px 18px; background: #f8fafc; border: none; font-size: 14px; font-weight: 700; color: #1e293b; cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                        <span>1. Bagaimana cara menambahkan produk baru di Daftar Harga Produk?</span>
                        <span class="dashicons dashicons-arrow-down-alt2 okj-faq-arrow"></span>
                    </button>
                    <div class="okj-faq-answer" style="display: none; padding: 16px 18px; font-size: 13px; color: #475569; line-height: 1.6; border-top: 1px solid #e2e8f0; background: #ffffff;">
                        Buka menu <strong>Daftar Harga Produk</strong> &raquo; klik tombol <strong>Tambah Daftar Harga</strong>. Anda dapat mengisi nama produk, harga jual, stok (bisa diisi angka atau biarkan -1 untuk stok tidak terbatas), durasi masa aktif (jika produk digital/langganan), URL gambar, seller/supplier terkait, serta status aktif/nonaktif produk.
                    </div>
                </div>

                <!-- FAQ 2 -->
                <div class="okj-faq-item" style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                    <button type="button" class="okj-faq-question" style="width: 100%; text-align: left; padding: 14px 18px; background: #f8fafc; border: none; font-size: 14px; font-weight: 700; color: #1e293b; cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                        <span>2. Bagaimana cara mengoperasikan Mesin Kasir POS (Point of Sale)?</span>
                        <span class="dashicons dashicons-arrow-down-alt2 okj-faq-arrow"></span>
                    </button>
                    <div class="okj-faq-answer" style="display: none; padding: 16px 18px; font-size: 13px; color: #475569; line-height: 1.6; border-top: 1px solid #e2e8f0; background: #ffffff;">
                        Buka menu <strong>OKJualan POS</strong>. Pilih produk yang ingin dibeli pelanggan dari panel kiri untuk dimasukkan ke keranjang belanja. Di panel kanan, pilih data customer atau tambahkan customer baru, berikan potongan diskon jika ada, pilih metode pembayaran (Tunai, Transfer Bank, atau QRIS), lalu klik <strong>Checkout Sekarang</strong>. Anda bisa langsung mencetak struk thermal atau mengirimkannya ke WhatsApp customer dengan 1 klik!
                    </div>
                </div>

                <!-- FAQ 3 -->
                <div class="okj-faq-item" style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                    <button type="button" class="okj-faq-question" style="width: 100%; text-align: left; padding: 14px 18px; background: #f8fafc; border: none; font-size: 14px; font-weight: 700; color: #1e293b; cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                        <span>3. Bagaimana cara mengaktifkan Payment Gateway SumoPod untuk menerima pembayaran QRIS otomatis?</span>
                        <span class="dashicons dashicons-arrow-down-alt2 okj-faq-arrow"></span>
                    </button>
                    <div class="okj-faq-answer" style="display: none; padding: 16px 18px; font-size: 13px; color: #475569; line-height: 1.6; border-top: 1px solid #e2e8f0; background: #ffffff;">
                        Buka menu <strong>Settings</strong> &raquo; pilih tab <strong>Payment Gateway</strong> &raquo; aktifkan <strong>SumoPod Payment Gateway</strong>. Masukkan <em>API Key</em> dari dashboard SumoPod Anda, isi <em>Webhook Secret</em> atau <em>Webhook Token</em>, pilih mode (Sandbox untuk uji coba atau Production untuk live), lalu salin URL Webhook yang tertera di halaman tersebut dan tempelkan ke menu Webhook dashboard SumoPod Anda.
                    </div>
                </div>

                <!-- FAQ 4 -->
                <div class="okj-faq-item" style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                    <button type="button" class="okj-faq-question" style="width: 100%; text-align: left; padding: 14px 18px; background: #f8fafc; border: none; font-size: 14px; font-weight: 700; color: #1e293b; cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                        <span>4. Bagaimana cara kerja sistem Notifikasi & Reminder otomatis?</span>
                        <span class="dashicons dashicons-arrow-down-alt2 okj-faq-arrow"></span>
                    </button>
                    <div class="okj-faq-answer" style="display: none; padding: 16px 18px; font-size: 13px; color: #475569; line-height: 1.6; border-top: 1px solid #e2e8f0; background: #ffffff;">
                        Sistem menjalankan Cron terjadwal harian secara otomatis. Ketika sebuah produk aktif mendekati tanggal expired (misal H-7, H-3, H-1), sistem akan mengirimkan pesan pengingat ke nomor WhatsApp customer (via WAHA API), Telegram Bot, atau Email SMTP. Anda juga dapat mengirimkan reminder manual kapan saja melalui menu <strong>Reminder</strong>.
                    </div>
                </div>

                <!-- FAQ 5 -->
                <div class="okj-faq-item" style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                    <button type="button" class="okj-faq-question" style="width: 100%; text-align: left; padding: 14px 18px; background: #f8fafc; border: none; font-size: 14px; font-weight: 700; color: #1e293b; cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                        <span>5. Bagaimana cara memperpanjang (Renew) langganan produk aktif customer?</span>
                        <span class="dashicons dashicons-arrow-down-alt2 okj-faq-arrow"></span>
                    </button>
                    <div class="okj-faq-answer" style="display: none; padding: 16px 18px; font-size: 13px; color: #475569; line-height: 1.6; border-top: 1px solid #e2e8f0; background: #ffffff;">
                        Buka menu <strong>Pembelian & Produk Aktif</strong>, temukan produk customer yang ingin diperpanjang, lalu klik tombol <strong>Perpanjang (Renew)</strong>. Masukkan durasi hari tambahan, harga perpanjangan, serta bukti pembayaran. Masa aktif akan otomatis bertambah dan seluruh jadwal pengingat reminder di-reset kembali ke status pending untuk periode baru.
                    </div>
                </div>

                <!-- FAQ 6 -->
                <div class="okj-faq-item" style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                    <button type="button" class="okj-faq-question" style="width: 100%; text-align: left; padding: 14px 18px; background: #f8fafc; border: none; font-size: 14px; font-weight: 700; color: #1e293b; cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                        <span>6. Bagaimana cara mengekspor Laporan Penjualan?</span>
                        <span class="dashicons dashicons-arrow-down-alt2 okj-faq-arrow"></span>
                    </button>
                    <div class="okj-faq-answer" style="display: none; padding: 16px 18px; font-size: 13px; color: #475569; line-height: 1.6; border-top: 1px solid #e2e8f0; background: #ffffff;">
                        Buka menu <strong>Laporan</strong>. Anda dapat memfilter laporan penjualan berdasarkan rentang tanggal tertentu atau bulan yang dipilih. Tersedia 2 tombol ekspor: <strong>Unduh Laporan PDF</strong> untuk berkas cetak resmi dan <strong>Unduh Laporan CSV</strong> untuk pembukuan di Microsoft Excel / Google Sheets.
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('.okj-faq-question').on('click', function() {
        var $item = $(this).closest('.okj-faq-item');
        var $answer = $item.find('.okj-faq-answer');
        var $arrow = $(this).find('.okj-faq-arrow');

        $answer.slideToggle(200);
        if ($arrow.hasClass('dashicons-arrow-down-alt2')) {
            $arrow.removeClass('dashicons-arrow-down-alt2').addClass('dashicons-arrow-up-alt2');
            $(this).css('background', '#eff6ff');
        } else {
            $arrow.removeClass('dashicons-arrow-up-alt2').addClass('dashicons-arrow-down-alt2');
            $(this).css('background', '#f8fafc');
        }
    });
});
</script>
