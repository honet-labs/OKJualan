<?php if (!defined('ABSPATH')) { exit; } ?>
<div class="okj-wrap">
    <div class="okj-header">
        <div>
            <h1>Laporan Keuangan & Penjualan</h1>
            <p class="okj-subtitle">Unduh rekapitulasi data penjualan bulanan untuk pelaporan pajak dan analisis performa keuangan.</p>
        </div>
    </div>

    <div class="okj-grid okj-grid-3 okj-mt-2">
        <!-- Quick stats card -->
        <div class="okj-card">
            <div class="okj-card-header">
                <h2>Total Volume Penjualan</h2>
            </div>
            <div class="okj-card-body">
                <?php
                $total_items = count($sales);
                $total_amount = 0;
                foreach ($sales as $s) {
                    $total_amount += (float)$s['price'];
                }
                ?>
                <h3 class="okj-text-indigo" style="font-size: 2.2rem; margin: 10px 0;"><?php echo $total_items; ?> Layanan</h3>
                <p class="okj-text-muted">Total transaksi layanan dengan status lunas (Paid).</p>
            </div>
        </div>

        <div class="okj-card">
            <div class="okj-card-header">
                <h2>Total Omset Bersih</h2>
            </div>
            <div class="okj-card-body">
                <h3 class="okj-text-success" style="font-size: 2.2rem; margin: 10px 0;">Rp <?php echo number_format_i18n($total_amount, 0); ?></h3>
                <p class="okj-text-muted">Omset bersih terakumulasi dari seluruh penjualan customer.</p>
            </div>
        </div>

        <!-- Export Monthly Report Widget -->
        <div class="okj-card">
            <div class="okj-card-header">
                <h2>Unduh Rekap Laporan Bulanan (PDF)</h2>
            </div>
            <div class="okj-card-body">
                <form method="get" action="<?php echo admin_url('admin-post.php'); ?>" target="_blank">
                    <input type="hidden" name="action" value="okj_monthly_report_pdf" />
                    <div class="okj-form-group">
                        <label class="okj-label">Pilih Bulan</label>
                        <select name="ym" class="okj-select" required>
                            <?php
                            $months = [];
                            foreach ($sales as $s) {
                                if ($s['start_date']) {
                                    $m = date('Y-m', strtotime($s['start_date']));
                                    $months[$m] = date('F Y', strtotime($s['start_date']));
                                }
                            }
                            krsort($months);
                            if (empty($months)) {
                                $curr = date('Y-m');
                                $months[$curr] = date('F Y');
                            }
                            foreach ($months as $k => $v):
                            ?>
                                <option value="<?php echo esc_attr($k); ?>"><?php echo esc_html($v); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="okj-form-actions okj-mt-1">
                        <button type="submit" class="okj-btn okj-btn-primary" style="width: 100%;">
                            <span class="dashicons dashicons-pdf"></span> Unduh Laporan PDF
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
