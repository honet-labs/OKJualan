<?php if (!defined('ABSPATH')) { exit; } ?>
<div class="okj-wrap">
    <div class="okj-header">
        <div>
            <h1>Laporan Penjualan</h1>
            <p class="okj-subtitle">Rekapitulasi transaksi penjualan berkala (bulanan / mingguan) dengan fitur unduh PDF dan CSV.</p>
        </div>
        <div class="okj-actions" style="display: flex; gap: 8px; align-items: center;">
            <?php 
            $pdf_nonce = wp_create_nonce('okj_monthly_report_pdf');
            $csv_nonce = wp_create_nonce('okj_export_sales_csv');
            $pdf_url = admin_url('admin-post.php?action=okj_monthly_report_pdf&month=' . urlencode($selected_month) . '&_wpnonce=' . $pdf_nonce);
            $csv_url = admin_url('admin-post.php?action=okj_export_sales_csv&month=' . urlencode($selected_month) . '&_wpnonce=' . $csv_nonce);
            ?>
            <a class="okj-btn okj-btn-secondary" href="<?php echo esc_url($csv_url); ?>" style="display: inline-flex; align-items: center; gap: 4px;">
                <span class="dashicons dashicons-media-spreadsheet" style="color: #16a34a;"></span> Unduh CSV
            </a>
            <a class="okj-btn okj-btn-primary" href="<?php echo esc_url($pdf_url); ?>" target="_blank" style="display: inline-flex; align-items: center; gap: 4px;">
                <span class="dashicons dashicons-pdf"></span> Unduh PDF
            </a>
        </div>
    </div>

    <!-- Filter Bar: Periode Bulanan / Mingguan -->
    <div class="okj-card okj-mt-2">
        <div class="okj-card-body" style="padding: 16px 20px;">
            <form method="get" action="<?php echo admin_url('admin.php'); ?>" style="display: flex; gap: 16px; align-items: flex-end; flex-wrap: wrap;">
                <input type="hidden" name="page" value="okj-reports" />

                <div class="okj-form-group" style="margin-bottom: 0;">
                    <label class="okj-label" style="font-size: 12px; margin-bottom: 4px;">Pilih Jenis Periode</label>
                    <select name="period_type" id="report_period_type" class="okj-select" style="min-width: 160px;" onchange="this.form.submit()">
                        <option value="monthly" <?php selected($period_type, 'monthly'); ?>>📅 Laporan Bulanan</option>
                        <option value="range" <?php selected($period_type, 'range'); ?>>📆 Rentang Tanggal / Mingguan</option>
                    </select>
                </div>

                <?php if ($period_type === 'range'): ?>
                    <div class="okj-form-group" style="margin-bottom: 0;">
                        <label class="okj-label" style="font-size: 12px; margin-bottom: 4px;">Dari Tanggal</label>
                        <input type="date" name="start_date" class="okj-input" value="<?php echo esc_attr($start_date); ?>" />
                    </div>
                    <div class="okj-form-group" style="margin-bottom: 0;">
                        <label class="okj-label" style="font-size: 12px; margin-bottom: 4px;">Sampai Tanggal</label>
                        <input type="date" name="end_date" class="okj-input" value="<?php echo esc_attr($end_date); ?>" />
                    </div>
                <?php else: ?>
                    <div class="okj-form-group" style="margin-bottom: 0;">
                        <label class="okj-label" style="font-size: 12px; margin-bottom: 4px;">Pilih Bulan</label>
                        <input type="month" name="month" class="okj-input" value="<?php echo esc_attr($selected_month); ?>" onchange="this.form.submit()" />
                    </div>
                <?php endif; ?>

                <div class="okj-form-group" style="margin-bottom: 0;">
                    <button type="submit" class="okj-btn okj-btn-primary" style="height: 38px;">
                        <span class="dashicons dashicons-filter" style="font-size: 16px; width: 16px; height: 16px; margin-top: 2px;"></span> Terapkan Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistics KPI Grid -->
    <div class="okj-grid okj-grid-3 okj-mt-2">
        <div class="okj-card">
            <div class="okj-card-header" style="padding: 12px 16px;">
                <h3 style="margin: 0; font-size: 13px; text-transform: uppercase; color: #64748b; font-weight: 700;">Total Transaksi</h3>
            </div>
            <div class="okj-card-body" style="padding: 16px;">
                <div style="font-size: 2rem; font-weight: 800; color: #4f46e5;"><?php echo number_format_i18n($total_orders); ?></div>
                <p class="okj-text-muted" style="margin: 4px 0 0 0; font-size: 12px;">Transaksi pada periode: <strong><?php echo esc_html($period_label); ?></strong></p>
            </div>
        </div>

        <div class="okj-card">
            <div class="okj-card-header" style="padding: 12px 16px;">
                <h3 style="margin: 0; font-size: 13px; text-transform: uppercase; color: #64748b; font-weight: 700;">Item Produk Terjual</h3>
            </div>
            <div class="okj-card-body" style="padding: 16px;">
                <div style="font-size: 2rem; font-weight: 800; color: #0891b2;"><?php echo number_format_i18n($total_items_sold); ?> pcs</div>
                <p class="okj-text-muted" style="margin: 4px 0 0 0; font-size: 12px;">Akumulasi jumlah unit produk/layanan laku terjual.</p>
            </div>
        </div>

        <div class="okj-card">
            <div class="okj-card-header" style="padding: 12px 16px;">
                <h3 style="margin: 0; font-size: 13px; text-transform: uppercase; color: #64748b; font-weight: 700;">Total Omset Penjualan</h3>
            </div>
            <div class="okj-card-body" style="padding: 16px;">
                <div style="font-size: 2rem; font-weight: 800; color: #16a34a;">Rp <?php echo number_format_i18n($total_omset, 0); ?></div>
                <p class="okj-text-muted" style="margin: 4px 0 0 0; font-size: 12px;">Penerimaan omset kotor dari seluruh transaksi terdata.</p>
            </div>
        </div>
    </div>

    <!-- Sales Table -->
    <div class="okj-card okj-mt-2">
        <div class="okj-card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h2 style="margin: 0;">Rincian Data Penjualan (<?php echo esc_html($period_label); ?>)</h2>
            <div style="font-size: 13px; color: #64748b;">
                Menampilkan <strong><?php echo count($rows); ?></strong> transaksi
            </div>
        </div>
        <div class="okj-card-body">
            <?php if (empty($rows)): ?>
                <div class="okj-empty-state" style="padding: 40px 20px; text-align: center;">
                    <span class="dashicons dashicons-chart-line" style="font-size: 36px; width: 36px; height: 36px; color: #94a3b8; margin-bottom: 12px;"></span>
                    <p style="margin: 0; color: #64748b;">Tidak ada transaksi penjualan yang tercatat pada periode ini.</p>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table class="okj-table">
                        <thead>
                            <tr>
                                <th style="width: 40px;">No.</th>
                                <th>No. Transaksi</th>
                                <th>Tanggal</th>
                                <th>Pelanggan</th>
                                <th>Produk Terjual</th>
                                <th>Qty</th>
                                <th>Total Bayar</th>
                                <th>Metode &amp; Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $idx = 1;
                            foreach ($rows as $r): 
                                $status = !empty($r['payment_status']) ? $r['payment_status'] : 'paid';
                            ?>
                                <tr>
                                    <td><?php echo $idx++; ?>.</td>
                                    <td>
                                        <strong><?php echo esc_html($r['transaction_no']); ?></strong>
                                        <div style="font-size: 10px; color: #94a3b8;"><code><?php echo esc_html(substr($r['id'], 0, 8)); ?></code></div>
                                    </td>
                                    <td><?php echo esc_html(wp_date('d/m/Y H:i', strtotime($r['created_at']))); ?></td>
                                    <td><strong><?php echo esc_html($r['customer_name']); ?></strong></td>
                                    <td>
                                        <div style="max-width: 250px; font-size: 12px; color: #334155;">
                                            <?php echo esc_html($r['products_summary'] ?: 'Pesanan POS'); ?>
                                        </div>
                                    </td>
                                    <td><strong><?php echo (int)$r['total_qty']; ?></strong></td>
                                    <td><strong style="color: #0f172a;">Rp <?php echo number_format_i18n((float)$r['total'], 0); ?></strong></td>
                                    <td>
                                        <span class="okj-badge" style="background: #f1f5f9; color: #334155; font-size: 10px;">
                                            <?php echo esc_html(strtoupper($r['payment_method'])); ?>
                                        </span>
                                        <?php if ($status === 'paid' || $status === 'completed'): ?>
                                            <span class="okj-badge okj-badge-success" style="font-size: 10px; margin-left: 2px;">Lunas</span>
                                        <?php else: ?>
                                            <span class="okj-badge okj-badge-warning" style="font-size: 10px; margin-left: 2px;">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr style="background: #f8fafc; font-weight: 700; border-top: 2px solid #e2e8f0;">
                                <td colspan="5" style="text-align: right; padding: 12px 16px;">TOTAL OMSET PENJUALAN:</td>
                                <td style="padding: 12px 10px; color: #4f46e5;"><?php echo number_format_i18n($total_items_sold); ?> pcs</td>
                                <td colspan="2" style="padding: 12px 10px; color: #16a34a; font-size: 15px;">
                                    Rp <?php echo number_format_i18n($total_omset, 0); ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
