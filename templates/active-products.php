<?php if (!defined('ABSPATH')) { exit; } ?>
<div class="okj-wrap">
    <?php if (isset($_GET['deleted'])): ?>
        <div class="notice notice-success is-dismissible okj-mb-2" style="margin: 0 0 20px 0; padding: 12px 16px; border-left-color: #10b981; background: #ecfdf5; color: #065f46; border-radius: 8px; border-left-width: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
            <p style="margin: 0; font-weight: 600;">Data pembelian produk berhasil dihapus.</p>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['renewal_success'])): ?>
        <div class="notice notice-success is-dismissible okj-mb-2" style="margin: 0 0 20px 0; padding: 12px 16px; border-left-color: #10b981; background: #ecfdf5; color: #065f46; border-radius: 8px; border-left-width: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
            <p style="margin: 0; font-weight: 600;">Layanan berhasil diperpanjang (renewed)! Masa aktif dan reminder telah diperbarui.</p>
        </div>
    <?php endif; ?>

    <?php if ($action === 'add' || $action === 'edit'): ?>
        <!-- Add / Edit Page -->
        <div class="okj-header">
            <div>
                <h1><?php echo $action === 'edit' ? 'Edit Pembelian & Layanan Aktif' : 'Tambah Pembelian & Layanan Baru'; ?></h1>
                <p class="okj-subtitle">Catat pembelian produk customer dan aktifkan pemantauan masa aktif layanan.</p>
            </div>
            <div class="okj-actions">
                <a class="okj-btn okj-btn-secondary" href="<?php echo admin_url('admin.php?page=okj-active-products'); ?>">
                    <span class="dashicons dashicons-arrow-left-alt" style="margin-top: 3px;"></span> Kembali
                </a>
            </div>
        </div>

        <div class="okj-card okj-mt-2">
            <div class="okj-card-body">
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" enctype="multipart/form-data">
                    <?php wp_nonce_field('okj_save_active_product'); ?>
                    <input type="hidden" name="action" value="okj_save_active_product" />
                    <?php if ($row): ?>
                        <input type="hidden" name="id" value="<?php echo esc_attr($row['id']); ?>" />
                    <?php endif; ?>

                    <div class="okj-form-grid">
                        <div class="okj-form-group">
                            <label class="okj-label">Pilih Produk <span class="okj-required">*</span></label>
                            <select name="product_id" class="okj-select okj-select2" style="width: 100%;">
                                <option value="">-- Pilih dari Daftar Harga Produk --</option>
                                <?php 
                                global $wpdb;
                                $price_list = $wpdb->get_results("SELECT id, name, duration_days, sale_price FROM " . OKJ_DB::get_table('product_prices') . " ORDER BY name ASC", ARRAY_A);
                                foreach ($price_list as $pl): 
                                    $is_sel = ($row && !empty($row['product_id']) && $row['product_id'] === $pl['id']);
                                ?>
                                    <option value="<?php echo esc_attr($pl['id']); ?>" <?php selected($is_sel); ?>>
                                        <?php echo esc_html($pl['name'] . ' (' . $pl['duration_days'] . ' Hari) - Rp ' . number_format_i18n($pl['sale_price'], 0)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="okj-form-group">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                <label class="okj-label" style="margin-bottom: 0;">Pilih Customer <span class="okj-required">*</span></label>
                                <a href="#" class="okj-quick-add-customer-btn" style="text-decoration: none; font-size: 12px; color: #4f46e5; font-weight: 600; display: inline-flex; align-items: center;">
                                    <span class="dashicons dashicons-plus-alt2" style="font-size: 14px; width: 14px; height: 14px; margin-right: 2px;"></span> Tambah Customer Baru
                                </a>
                            </div>
                            <select name="customer_id" class="okj-select okj-select2" style="width: 100%;" required>
                                <option value="">-- Pilih Customer --</option>
                                <?php foreach ($customers as $c): ?>
                                    <option value="<?php echo esc_attr($c['id']); ?>" <?php echo $row && $row['customer_id'] === $c['id'] ? 'selected' : ''; ?>>
                                        <?php echo esc_html($c['name'] . ($c['whatsapp'] ? ' (' . $c['whatsapp'] . ')' : '')); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="okj-form-group">
                            <label class="okj-label">Tanggal Pembelian / Mulai <span class="okj-required">*</span></label>
                            <input type="date" name="start_date" class="okj-input" value="<?php echo $row ? esc_attr($row['start_date']) : wp_date('Y-m-d'); ?>" required />
                        </div>

                        <div class="okj-form-group">
                            <label class="okj-label">Qty (Jumlah Pembelian)</label>
                            <input type="number" name="qty" class="okj-input" value="<?php echo $row && !empty($row['qty']) ? esc_attr($row['qty']) : '1'; ?>" min="1" required />
                        </div>

                        <div class="okj-form-group">
                            <label class="okj-label">Total Harga (IDR) <span class="okj-required">*</span></label>
                            <input type="number" name="price" class="okj-input" value="<?php echo $row ? esc_attr($row['price']) : '0'; ?>" min="0" required />
                        </div>

                        <div class="okj-form-group">
                            <label class="okj-label">Status Layanan</label>
                            <?php $curr_status = $row && !empty($row['status']) ? $row['status'] : 'active'; ?>
                            <select name="status" class="okj-select" style="width: 100%;">
                                <option value="active" <?php selected($curr_status, 'active'); ?>>🟢 Aktif</option>
                                <option value="pending" <?php selected($curr_status, 'pending'); ?>>🟡 Pending</option>
                                <option value="completed" <?php selected($curr_status, 'completed'); ?>>✅ Selesai</option>
                                <option value="expired" <?php selected($curr_status, 'expired'); ?>>🔴 Kedaluwarsa (Expired)</option>
                                <option value="cancelled" <?php selected($curr_status, 'cancelled'); ?>>⚪ Dibatalkan</option>
                            </select>
                        </div>

                        <div class="okj-form-group">
                            <label class="okj-label">Status Pembayaran</label>
                            <?php $curr_pay_status = $row && !empty($row['payment_status']) ? $row['payment_status'] : 'paid'; ?>
                            <select name="payment_status" class="okj-select" style="width: 100%;">
                                <option value="paid" <?php selected($curr_pay_status, 'paid'); ?>>Lunas (Paid)</option>
                                <option value="pending" <?php selected($curr_pay_status, 'pending'); ?>>Belum Bayar (Pending)</option>
                            </select>
                        </div>

                        <div class="okj-form-group">
                            <label class="okj-label">Nomor Transaksi (Opsional)</label>
                            <input type="text" name="transaction_no" class="okj-input" value="<?php echo $row && !empty($row['transaction_no']) ? esc_attr($row['transaction_no']) : ''; ?>" placeholder="Contoh: WC-10825 atau POS-2026..." />
                            <small style="color: #64748b; font-size: 11px;">Nomor referensi order WooCommerce atau transaksi kasir POS.</small>
                        </div>

                        <div class="okj-form-group">
                            <label class="okj-label">Bukti Pembayaran (Opsional)</label>
                            <input type="file" name="payment_attachments" accept="image/*" class="okj-input" style="padding: 6px;" />
                            <?php if ($row && !empty($row['payment_attachments'])): ?>
                                <div class="okj-mt-1">
                                    <img src="<?php echo esc_url($row['payment_attachments']); ?>" style="max-width: 120px; border-radius: 6px; border: 1px solid #e2e8f0;" />
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="okj-form-group" style="grid-column: span 2;">
                            <label class="okj-label">Keterangan Tambahan / Detail Akun</label>
                            <textarea name="notes" class="okj-input" rows="3" placeholder="Informasi akun yang diserahkan ke pelanggan (Email:Password/Profile/PIN)..."><?php echo $row ? esc_textarea($row['notes']) : ''; ?></textarea>
                        </div>
                    </div>

                    <div class="okj-form-actions okj-mt-2" style="border-top: 1px solid #f1f5f9; padding-top: 16px;">
                        <button type="submit" class="okj-btn okj-btn-primary">
                            <span class="dashicons dashicons-saved" style="margin-top: 3px;"></span> Simpan Pembelian & Layanan
                        </button>
                    </div>
                </form>
            </div>
        </div>

    <?php else: ?>
        <!-- List Page: Pembelian Produk & Produk Aktif -->
        <div class="okj-header">
            <div>
                <h1>Pembelian Produk &amp; Produk Aktif</h1>
                <p class="okj-subtitle">Rekap data pembelian customer, status keaktifan layanan, dan perpanjangan masa aktif.</p>
            </div>
            <div class="okj-actions">
                <a class="okj-btn okj-btn-primary" href="<?php echo admin_url('admin.php?page=okj-active-products&action=add'); ?>">
                    <span class="dashicons dashicons-plus"></span> Catat Pembelian
                </a>
            </div>
        </div>

        <div class="okj-card okj-mt-2">
            <div class="okj-card-body">
                <?php
                $current_status_filter = !empty($_GET['status_filter']) ? sanitize_text_field($_GET['status_filter']) : 'active';
                ?>
                <div class="okj-tabs-wrapper" style="display: flex; gap: 8px; margin-bottom: 16px; border-bottom: 1px solid #e2e8f0; padding-bottom: 16px; align-items: center; justify-content: space-between; flex-wrap: wrap;">
                    <div class="okj-status-tabs" style="display: flex; gap: 4px; background: #f1f5f9; padding: 4px; border-radius: 8px;">
                        <a href="<?php echo admin_url('admin.php?page=okj-active-products&status_filter=active'); ?>" 
                           class="okj-tab-item <?php echo $current_status_filter === 'active' ? 'okj-tab-active' : ''; ?>"
                           style="text-decoration: none; padding: 6px 16px; border-radius: 6px; font-weight: 600; font-size: 13px; transition: all 0.2s; color: <?php echo $current_status_filter === 'active' ? '#ffffff' : '#64748b'; ?>; background: <?php echo $current_status_filter === 'active' ? '#4f46e5' : 'transparent'; ?>;">
                            <span class="dashicons dashicons-yes-alt" style="font-size: 16px; width: 16px; height: 16px; margin-top: 1px; margin-right: 4px;"></span>
                            Aktif
                        </a>
                        <a href="<?php echo admin_url('admin.php?page=okj-active-products&status_filter=expired'); ?>" 
                           class="okj-tab-item <?php echo $current_status_filter === 'expired' ? 'okj-tab-active' : ''; ?>"
                           style="text-decoration: none; padding: 6px 16px; border-radius: 6px; font-weight: 600; font-size: 13px; transition: all 0.2s; color: <?php echo $current_status_filter === 'expired' ? '#ffffff' : '#64748b'; ?>; background: <?php echo $current_status_filter === 'expired' ? '#4f46e5' : 'transparent'; ?>;">
                            <span class="dashicons dashicons-no-alt" style="font-size: 16px; width: 16px; height: 16px; margin-top: 1px; margin-right: 4px;"></span>
                            Expired
                        </a>
                        <a href="<?php echo admin_url('admin.php?page=okj-active-products&status_filter=all'); ?>" 
                           class="okj-tab-item <?php echo $current_status_filter === 'all' ? 'okj-tab-active' : ''; ?>"
                           style="text-decoration: none; padding: 6px 16px; border-radius: 6px; font-weight: 600; font-size: 13px; transition: all 0.2s; color: <?php echo $current_status_filter === 'all' ? '#ffffff' : '#64748b'; ?>; background: <?php echo $current_status_filter === 'all' ? '#4f46e5' : 'transparent'; ?>;">
                            <span class="dashicons dashicons-category" style="font-size: 16px; width: 16px; height: 16px; margin-top: 1px; margin-right: 4px;"></span>
                            Semua Status
                        </a>
                    </div>
                    <?php if (!empty($rows)): ?>
                        <div class="okj-search-container" style="display: flex; gap: 8px; align-items: center;">
                            <input type="text" class="okj-input okj-table-search" placeholder="Cari pelanggan, produk..." style="max-width: 250px; width: 100%; margin-bottom: 0;" />
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (empty($rows)): ?>
                    <div class="okj-empty-state" style="padding: 48px 24px; text-align: center;">
                        <span class="dashicons dashicons-cart" style="font-size: 36px; width: 36px; height: 36px; color: #94a3b8; margin-bottom: 12px; display: inline-block;"></span>
                        <p style="margin: 0; font-size: 15px; color: #64748b; font-weight: 500;">Tidak ada data pembelian produk dengan status ini.</p>
                    </div>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table class="okj-table">
                            <thead>
                                <tr>
                                    <th>ID Pembelian</th>
                                    <th>No. Transaksi</th>
                                    <th>Customer</th>
                                    <th>Produk &amp; Masa Aktif</th>
                                    <th>Tgl Pembelian</th>
                                    <th>Qty</th>
                                    <th>Total Harga</th>
                                    <th>Status Layanan</th>
                                    <th>Status Pembayaran</th>
                                    <th>Keterangan</th>
                                    <th>Perpanjang Masa Aktif &amp; Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rows as $r): ?>
                                    <?php 
                                    $qty_val = !empty($r['qty']) ? (int)$r['qty'] : 1;
                                    $p_status = !empty($r['status']) ? $r['status'] : 'active';
                                    $pay_status = !empty($r['payment_status']) ? strtolower($r['payment_status']) : 'paid';

                                    // Extract transaction number & WooCommerce link if available
                                    $tx_no = !empty($r['transaction_no']) ? trim($r['transaction_no']) : '';
                                    if (empty($tx_no) && !empty($r['notes'])) {
                                        if (preg_match('/Pembelian via (WC[ -]#?\d+|POS-[A-Za-z0-9-]+|INV-[A-Za-z0-9-]+)/i', $r['notes'], $m)) {
                                            $tx_no = $m[1];
                                        }
                                    }
                                    $wc_id = 0;
                                    if (!empty($tx_no)) {
                                        if (preg_match('/(?:WC[ -]#?|#)(\d+)/i', $tx_no, $wm)) {
                                            $wc_id = (int)$wm[1];
                                        } elseif (is_numeric($tx_no)) {
                                            $wc_id = (int)$tx_no;
                                        }
                                    }
                                    $wc_url = '';
                                    if ($wc_id > 0) {
                                        $wc_url = admin_url('post.php?post=' . $wc_id . '&action=edit');
                                        if (class_exists('Automattic\WooCommerce\Utilities\OrderUtil') && Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled()) {
                                            $wc_url = admin_url('admin.php?page=wc-orders&action=edit&id=' . $wc_id);
                                        }
                                    }
                                    ?>
                                    <tr>
                                        <td><code><?php echo esc_html(substr($r['id'], 0, 8)); ?></code></td>
                                        <td>
                                            <?php if ($wc_id > 0): ?>
                                                <a href="<?php echo esc_url($wc_url); ?>" target="_blank" title="Buka Pesanan WooCommerce #<?php echo $wc_id; ?> di Tab Baru" style="font-family: monospace; font-size: 12px; font-weight: 700; color: #4f46e5; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; background: #eef2ff; padding: 3px 8px; border-radius: 5px; border: 1px solid #c7d2fe;">
                                                    <span class="dashicons dashicons-cart" style="font-size: 13px; width: 13px; height: 13px;"></span>
                                                    WC #<?php echo $wc_id; ?>
                                                </a>
                                            <?php elseif (!empty($tx_no)): ?>
                                                <a href="<?php echo esc_url(admin_url('admin.php?page=okj-transactions&s=' . urlencode($tx_no))); ?>" target="_blank" title="Cari di List Transaksi" style="font-family: monospace; font-size: 12px; font-weight: 600; color: #1e293b; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; background: #f8fafc; padding: 3px 8px; border-radius: 5px; border: 1px solid #e2e8f0;">
                                                    <span class="dashicons dashicons-media-text" style="font-size: 13px; width: 13px; height: 13px; color: #64748b;"></span>
                                                    <?php echo esc_html($tx_no); ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="okj-text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($r['customer_name'])): ?>
                                                <a href="#" class="okj-view-customer-detail" 
                                                    data-name="<?php echo esc_attr($r['customer_name']); ?>"
                                                    data-email="<?php echo esc_attr($r['customer_email'] ?: '-'); ?>"
                                                    data-phone="<?php echo esc_attr($r['customer_phone'] ?: '-'); ?>"
                                                    data-telegram="<?php echo esc_attr($r['customer_telegram'] ?: '-'); ?>"
                                                    data-whatsapp="<?php echo esc_attr($r['customer_whatsapp'] ?: '-'); ?>"
                                                    style="text-decoration: none; color: #4f46e5; font-weight: 600;"
                                                    title="Lihat Detail Customer">
                                                    <?php echo esc_html($r['customer_name']); ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="okj-text-muted">-</span>
                                            <?php endif; ?>
                                            <div style="margin-top: 2px; font-size: 11px; color: #64748b;"><?php echo esc_html($r['customer_contact']); ?></div>
                                        </td>
                                        <td>
                                            <strong><?php echo esc_html($r['product_label']); ?></strong>
                                            <div style="margin-top: 2px; display: flex; gap: 4px; align-items: center;">
                                                <?php if (!empty($r['duration_days'])): ?>
                                                    <span class="okj-badge" style="background: #f1f5f9; color: #475569; font-size: 10px; padding: 1px 5px;">
                                                        <?php echo esc_html($r['duration_days']); ?> Hari
                                                    </span>
                                                <?php endif; ?>
                                                <span style="font-size: 11px; color: #64748b;">Exp: <?php echo esc_html($r['expires_at']); ?></span>
                                            </div>
                                        </td>
                                        <td><?php echo esc_html($r['start_date']); ?></td>
                                        <td><strong><?php echo $qty_val; ?></strong></td>
                                        <td><strong style="color: #0f172a;">Rp <?php echo number_format_i18n((float)$r['price'], 0); ?></strong></td>
                                        <!-- Status Layanan -->
                                        <td>
                                            <?php if ($p_status === 'active' || $p_status === 'completed'): ?>
                                                <span class="okj-badge okj-badge-success" style="padding: 4px 10px; font-size: 11.5px; font-weight: 700;">🟢 Aktif</span>
                                            <?php elseif ($p_status === 'pending'): ?>
                                                <span class="okj-badge okj-badge-warning" style="padding: 4px 10px; font-size: 11.5px; font-weight: 700;">🟡 Pending</span>
                                            <?php elseif ($p_status === 'cancelled'): ?>
                                                <span class="okj-badge" style="background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; padding: 4px 10px; font-size: 11.5px; font-weight: 700;">⚪ Dibatalkan</span>
                                            <?php else: ?>
                                                <span class="okj-badge okj-badge-danger" style="padding: 4px 10px; font-size: 11.5px; font-weight: 700;">🔴 Expired</span>
                                            <?php endif; ?>
                                        </td>
                                        <!-- Status Pembayaran -->
                                        <td>
                                            <?php if ($pay_status === 'paid'): ?>
                                                <span class="okj-badge" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; padding: 4px 10px; font-size: 11.5px; font-weight: 700; display: inline-flex; align-items: center; gap: 3px;">
                                                    ✓ Lunas
                                                </span>
                                            <?php elseif ($pay_status === 'cancelled' || $pay_status === 'failed'): ?>
                                                <span class="okj-badge" style="background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; padding: 4px 10px; font-size: 11.5px; font-weight: 700; display: inline-flex; align-items: center; gap: 3px;">
                                                    ✕ Gagal
                                                </span>
                                            <?php else: ?>
                                                <span class="okj-badge" style="background: #fffbeb; color: #b45309; border: 1px solid #fde68a; padding: 4px 10px; font-size: 11.5px; font-weight: 700; display: inline-flex; align-items: center; gap: 3px;">
                                                    ⏳ Belum Bayar
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($r['notes'])): ?>
                                                <a href="#" class="okj-view-active-notes" 
                                                   data-name="<?php echo esc_attr($r['product_label']); ?>" 
                                                   data-notes="<?php echo esc_attr(wp_strip_all_tags($r['notes'])); ?>" 
                                                   style="text-decoration: none; color: #4338ca; display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; background: #e0e7ff; border-radius: 6px;"
                                                   title="Lihat Keterangan">
                                                    <span class="dashicons dashicons-visibility" style="font-size: 16px; width: 16px; height: 16px;"></span>
                                                </a>
                                            <?php else: ?>
                                                <span class="okj-text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div style="display: flex; flex-direction: column; gap: 4px;">
                                                <div style="display: flex; gap: 6px; align-items: center;">
                                                    <a class="okj-btn okj-renew-product-btn" href="#" data-id="<?php echo esc_attr($r['id']); ?>" data-name="<?php echo esc_attr($r['product_label']); ?>" data-expiry="<?php echo esc_attr($r['expires_at']); ?>" data-price="<?php echo esc_attr($r['price']); ?>" style="padding: 4px 8px; font-size: 11px; background: #4f46e5; color: #ffffff; border-radius: 4px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 3px;">
                                                        <span class="dashicons dashicons-update" style="font-size: 13px; width: 13px; height: 13px;"></span> Perpanjang
                                                    </a>
                                                    <a class="okj-renewal-history-btn" href="#" data-id="<?php echo esc_attr($r['id']); ?>" style="color: #059669; font-size: 11px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 2px;" title="Riwayat Renewal">
                                                        <span class="dashicons dashicons-backup" style="font-size: 14px; width: 14px; height: 14px;"></span> Riwayat
                                                    </a>
                                                </div>
                                                <div style="display: flex; gap: 6px; align-items: center; margin-top: 2px;">
                                                    <a class="okj-btn-link" href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=okj_invoice_pdf&id=' . $r['id']), 'okj_invoice_pdf_' . $r['id']); ?>" target="_blank" style="font-size: 11px; color: #0284c7;">
                                                        <span class="dashicons dashicons-pdf" style="font-size: 13px; width: 13px; height: 13px;"></span> Invoice
                                                    </a>
                                                    <a class="okj-btn-link" href="<?php echo admin_url('admin.php?page=okj-active-products&action=edit&id=' . $r['id']); ?>" style="font-size: 11px;">
                                                        <span class="dashicons dashicons-edit" style="font-size: 13px; width: 13px; height: 13px;"></span> Edit
                                                    </a>
                                                    <a class="okj-btn-link okj-btn-link-danger" href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=okj_delete_active_product&id=' . $r['id']), 'okj_delete_active_product_' . $r['id']); ?>" onclick="return confirm('Hapus data pembelian ini?');" style="font-size: 11px;">
                                                        <span class="dashicons dashicons-trash" style="font-size: 13px; width: 13px; height: 13px;"></span>
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if (isset($total_pages) && $total_pages > 1): 
                        $current_offset = ($paged - 1) * $per_page;
                    ?>
                        <div class="okj-pagination okj-mt-2">
                            <div class="okj-pagination-info">
                                Menampilkan <?php echo ($current_offset + 1); ?> - <?php echo min($total_rows, $current_offset + $per_page); ?> dari <?php echo $total_rows; ?> pembelian
                            </div>
                            <div class="okj-pagination-links">
                                <?php
                                echo paginate_links([
                                    'base' => add_query_arg('paged', '%#%'),
                                    'format' => '',
                                    'prev_text' => '&laquo; Prev',
                                    'next_text' => 'Next &raquo;',
                                    'total' => $total_pages,
                                    'current' => $paged,
                                    'type' => 'plain'
                                ]);
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Detail Customer -->
<div id="wrpmCustomerModal" class="okj-modal" style="display: none; position: fixed; z-index: 999999; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); align-items: center; justify-content: center;">
    <div class="okj-modal-content" style="background-color: #ffffff; border-radius: 12px; max-width: 500px; width: 90%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); border: 1px solid #e2e8f0;">
        <div class="okj-modal-header" style="padding: 16px 24px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: #0f172a; display: flex; align-items: center;">
                <span class="dashicons dashicons-admin-users" style="margin-right: 8px; color: #4f46e5;"></span>
                Detail Customer
            </h3>
            <span class="okj-customer-modal-close" style="color: #94a3b8; font-size: 28px; font-weight: bold; cursor: pointer; line-height: 1;">&times;</span>
        </div>
        <div class="okj-modal-body" style="padding: 24px; color: #334155; font-size: 0.95rem; line-height: 1.6;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 10px 0; font-weight: 600; color: #64748b; width: 35%;">Nama Customer</td>
                    <td id="wrpmCustomerName" style="padding: 10px 0; color: #0f172a; font-weight: 600;">-</td>
                </tr>
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 10px 0; font-weight: 600; color: #64748b;">Email</td>
                    <td id="wrpmCustomerEmail" style="padding: 10px 0; color: #0f172a;">-</td>
                </tr>
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 10px 0; font-weight: 600; color: #64748b;">Telepon</td>
                    <td id="wrpmCustomerPhone" style="padding: 10px 0; color: #0f172a;">-</td>
                </tr>
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 10px 0; font-weight: 600; color: #64748b;">Telegram Chat ID</td>
                    <td id="wrpmCustomerTelegram" style="padding: 10px 0; color: #0f172a;">-</td>
                </tr>
                <tr>
                    <td style="padding: 10px 0; font-weight: 600; color: #64748b;">WhatsApp</td>
                    <td id="wrpmCustomerWhatsapp" style="padding: 10px 0; color: #0f172a;">-</td>
                </tr>
            </table>
        </div>
        <div class="okj-modal-footer" style="padding: 12px 24px; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end;">
            <button class="okj-btn okj-btn-secondary okj-customer-modal-close-btn">Tutup</button>
        </div>
    </div>
</div>

<!-- Modal Quick Add Customer -->
<div id="wrpmQuickAddCustomerModal" class="okj-modal" style="display: none; position: fixed; z-index: 999999; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); align-items: center; justify-content: center;">
    <div class="okj-modal-content" style="background-color: #ffffff; border-radius: 12px; max-width: 450px; width: 90%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); border: 1px solid #e2e8f0; margin: auto;">
        <div class="okj-modal-header" style="padding: 16px 24px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: #0f172a; display: flex; align-items: center;">
                <span class="dashicons dashicons-admin-users" style="margin-right: 8px; color: #4f46e5;"></span>
                Tambah Customer Baru
            </h3>
            <span class="okj-quick-customer-close" style="color: #94a3b8; font-size: 24px; font-weight: bold; cursor: pointer; line-height: 1;">&times;</span>
        </div>
        <div class="okj-modal-body" style="padding: 20px 24px;">
            <div class="okj-form-group" style="margin-bottom: 12px;">
                <label class="okj-label">Nama Customer <span class="okj-required">*</span></label>
                <input type="text" id="wrpmQuickCustomerName" class="okj-input" placeholder="Masukkan nama customer..." required />
            </div>
            <div class="okj-form-group" style="margin-bottom: 12px;">
                <label class="okj-label">No. WhatsApp</label>
                <input type="text" id="wrpmQuickCustomerWhatsapp" class="okj-input" placeholder="628123456789..." />
            </div>
            <div class="okj-form-group" style="margin-bottom: 0;">
                <label class="okj-label">Email</label>
                <input type="email" id="wrpmQuickCustomerEmail" class="okj-input" placeholder="customer@email.com..." />
            </div>
        </div>
        <div class="okj-modal-footer" style="padding: 12px 24px; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end; gap: 8px;">
            <button type="button" class="okj-btn okj-btn-secondary okj-quick-customer-close-btn">Batal</button>
            <button type="button" id="wrpmQuickCustomerSubmitBtn" class="okj-btn okj-btn-primary" style="display: inline-flex; align-items: center;">
                Simpan Customer
            </button>
        </div>
    </div>
</div>

<!-- Modal Notes -->
<div id="wrpmActiveNotesModal" class="okj-modal" style="display: none; position: fixed; z-index: 999999; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); align-items: center; justify-content: center;">
    <div class="okj-modal-content" style="background-color: #ffffff; border-radius: 12px; max-width: 450px; width: 90%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); border: 1px solid #e2e8f0; margin: auto;">
        <div class="okj-modal-header" style="padding: 16px 24px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: #0f172a; display: flex; align-items: center;">
                <span class="dashicons dashicons-testimonial" style="margin-right: 8px; color: #4f46e5;"></span>
                Keterangan Layanan
            </h3>
            <span class="okj-active-notes-close" style="color: #94a3b8; font-size: 24px; font-weight: bold; cursor: pointer; line-height: 1;">&times;</span>
        </div>
        <div class="okj-modal-body" style="padding: 20px 24px;">
            <h4 id="wrpmActiveNotesTitle" style="margin: 0 0 12px 0; font-size: 1rem; font-weight: 600; color: #334155;"></h4>
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; font-size: 14px; color: #475569; line-height: 1.6; min-height: 100px; white-space: pre-wrap;" id="wrpmActiveNotesContent"></div>
        </div>
        <div class="okj-modal-footer" style="padding: 12px 24px; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end;">
            <button class="okj-btn okj-btn-secondary okj-active-notes-close-btn">Tutup</button>
        </div>
    </div>
</div>

<!-- Modal Perpanjang Layanan (Renewal) -->
<div id="okjRenewProductModal" class="okj-modal" style="display: none; position: fixed; z-index: 999999; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); align-items: center; justify-content: center;">
    <div class="okj-modal-content" style="background-color: #ffffff; border-radius: 12px; max-width: 500px; width: 90%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); border: 1px solid #e2e8f0; margin: auto;">
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" enctype="multipart/form-data">
            <?php wp_nonce_field('okj_renew_active_product'); ?>
            <input type="hidden" name="action" value="okj_renew_active_product" />
            <input type="hidden" name="active_product_id" id="okj_renew_ap_id" value="" />

            <div class="okj-modal-header" style="padding: 16px 24px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; font-size: 1.2rem; font-weight: 700; color: #0f172a; display: flex; align-items: center;">
                    <span class="dashicons dashicons-update" style="margin-right: 8px; color: #4f46e5;"></span>
                    Perpanjang Masa Aktif Layanan
                </h3>
                <span class="okj-renew-modal-close" style="color: #94a3b8; font-size: 24px; font-weight: bold; cursor: pointer; line-height: 1;">&times;</span>
            </div>
            <div class="okj-modal-body" style="padding: 20px 24px;">
                <div style="margin-bottom: 16px; background: #eef2ff; border: 1px solid #c7d2fe; border-radius: 8px; padding: 12px;">
                    <p style="margin: 0; font-size: 13px; color: #3730a3;">
                        Layanan: <strong id="okj_renew_product_name">-</strong><br>
                        Tgl Kedaluwarsa Saat Ini: <strong id="okj_renew_current_expiry">-</strong>
                    </p>
                </div>

                <div class="okj-form-group" style="margin-bottom: 14px;">
                    <label class="okj-label">Hitung Mulai Dari</label>
                    <select name="start_from" class="okj-select" style="width: 100%;">
                        <option value="old_expiry">Lanjutan Tanggal Expired Lama (Rekomendasi)</option>
                        <option value="today">Hari Ini (Reset Mulai Sekarang)</option>
                    </select>
                </div>

                <div class="okj-form-group" style="margin-bottom: 14px;">
                    <label class="okj-label">Durasi Tambahan (Hari) <span class="okj-required">*</span></label>
                    <input type="number" name="duration_days" class="okj-input" value="30" min="1" required />
                </div>

                <div class="okj-form-group" style="margin-bottom: 14px;">
                    <label class="okj-label">Biaya Perpanjangan (Rp)</label>
                    <input type="number" name="price" id="okj_renew_price" class="okj-input" value="0" min="0" />
                </div>

                <div class="okj-form-group" style="margin-bottom: 14px;">
                    <label class="okj-label">Status Pembayaran</label>
                    <select name="payment_status" class="okj-select" style="width: 100%;">
                        <option value="paid">Lunas (Paid)</option>
                        <option value="pending">Belum Bayar (Pending)</option>
                    </select>
                </div>

                <div class="okj-form-group" style="margin-bottom: 0;">
                    <label class="okj-label">Catatan Perpanjangan</label>
                    <textarea name="notes" class="okj-input" rows="2" placeholder="Catatan opsional..."></textarea>
                </div>
            </div>
            <div class="okj-modal-footer" style="padding: 12px 24px; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end; gap: 8px;">
                <button type="button" class="okj-btn okj-btn-secondary okj-renew-modal-close-btn">Batal</button>
                <button type="submit" class="okj-btn okj-btn-primary">Konfirmasi Perpanjangan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Riwayat Perpanjangan (Renewal History) -->
<div id="okjRenewalHistoryModal" class="okj-modal" style="display: none; position: fixed; z-index: 999999; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); align-items: center; justify-content: center;">
    <div class="okj-modal-content" style="background-color: #ffffff; border-radius: 12px; max-width: 650px; width: 90%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); border: 1px solid #e2e8f0; margin: auto;">
        <div class="okj-modal-header" style="padding: 16px 24px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 1.2rem; font-weight: 700; color: #0f172a; display: flex; align-items: center;">
                <span class="dashicons dashicons-backup" style="margin-right: 8px; color: #059669;"></span>
                Riwayat Perpanjangan Layanan
            </h3>
            <span class="okj-renewal-history-close" style="color: #94a3b8; font-size: 24px; font-weight: bold; cursor: pointer; line-height: 1;">&times;</span>
        </div>
        <div class="okj-modal-body" style="padding: 20px 24px;" id="okjRenewalHistoryBody">
            <div style="text-align: center; padding: 24px;">
                <span class="dashicons dashicons-update okj-spin" style="font-size: 24px; width: 24px; height: 24px; color: #4f46e5;"></span>
                <p style="margin-top: 8px; color: #64748b;">Memuat riwayat...</p>
            </div>
        </div>
        <div class="okj-modal-footer" style="padding: 12px 24px; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end;">
            <button type="button" class="okj-btn okj-btn-secondary okj-renewal-history-close-btn">Tutup</button>
        </div>
    </div>
</div>
