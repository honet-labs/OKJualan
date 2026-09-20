<?php if (!defined('ABSPATH')) { exit; } ?>
<div class="okj-wrap">
    <div class="okj-header">
        <div>
            <h1>Daftar Transaksi Penjualan</h1>
            <p class="okj-subtitle">Kelola dan pantau seluruh transaksi kasir POS, pesanan mandiri online, status pembayaran, dan riwayat belanja.</p>
        </div>
        <div class="okj-header-actions" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <a href="<?php echo admin_url('admin-post.php?action=okj_export_transactions_csv' . (!empty($_SERVER['QUERY_STRING']) ? '&' . sanitize_text_field($_SERVER['QUERY_STRING']) : '')); ?>" class="okj-btn okj-btn-secondary" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 600;">
                <span class="dashicons dashicons-download" style="font-size: 17px; width: 17px; height: 17px;"></span> Export CSV
            </a>
            <a href="<?php echo admin_url('admin.php?page=okj-pos'); ?>" class="okj-btn okj-btn-primary" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 700; background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%); box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.25);">
                <span class="dashicons dashicons-calculator" style="font-size: 17px; width: 17px; height: 17px;"></span> Buka Kasir POS
            </a>
        </div>
    </div>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="notice notice-success is-dismissible okj-mb-2" style="margin: 0 0 20px 0; padding: 12px 16px; border-left-color: #10b981; background: #ecfdf5; color: #065f46; border-radius: 8px; border-left-width: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
            <p style="margin: 0; font-weight: 600;">Transaksi berhasil dihapus dari sistem.</p>
        </div>
    <?php endif; ?>

    <!-- KPI Summary Grid -->
    <div class="okj-grid okj-grid-4 okj-mb-2">
        <div class="okj-card okj-kpi-card" style="border-left: 4px solid #10b981;">
            <div class="okj-kpi-icon" style="background: #ecfdf5; color: #059669;"><span class="dashicons dashicons-chart-area"></span></div>
            <div>
                <span class="okj-kpi-label">Total Omset Lunas</span>
                <strong class="okj-kpi-value" style="color: #047857;">Rp <?php echo number_format_i18n($kpi_paid_amount, 0); ?></strong>
            </div>
        </div>
        <div class="okj-card okj-kpi-card" style="border-left: 4px solid #4f46e5;">
            <div class="okj-kpi-icon" style="background: #eef2ff; color: #4f46e5;"><span class="dashicons dashicons-yes-alt"></span></div>
            <div>
                <span class="okj-kpi-label">Transaksi Berhasil</span>
                <strong class="okj-kpi-value" style="color: #3730a3;"><?php echo number_format_i18n($kpi_paid_count); ?> <small style="font-size: 13px; font-weight: 500; color: #64748b;">Pesanan</small></strong>
            </div>
        </div>
        <div class="okj-card okj-kpi-card" style="border-left: 4px solid #f59e0b;">
            <div class="okj-kpi-icon" style="background: #fffbeb; color: #d97706;"><span class="dashicons dashicons-clock"></span></div>
            <div>
                <span class="okj-kpi-label">Menunggu Bayar (Pending)</span>
                <strong class="okj-kpi-value" style="color: #b45309;"><?php echo number_format_i18n($kpi_pending_count); ?> <small style="font-size: 13px; font-weight: 500; color: #64748b;">Pesanan</small></strong>
            </div>
        </div>
        <div class="okj-card okj-kpi-card" style="border-left: 4px solid #8b5cf6;">
            <div class="okj-kpi-icon" style="background: #f5f3ff; color: #7c3aed;"><span class="dashicons dashicons-products"></span></div>
            <div>
                <span class="okj-kpi-label">Total Produk Terjual</span>
                <strong class="okj-kpi-value" style="color: #6d28d9;"><?php echo number_format_i18n($kpi_total_items_sold); ?> <small style="font-size: 13px; font-weight: 500; color: #64748b;">Unit</small></strong>
            </div>
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div class="okj-card okj-mb-2">
        <div class="okj-card-body" style="padding: 16px 20px;">
            <form method="get" action="<?php echo admin_url('admin.php'); ?>" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
                <input type="hidden" name="page" value="okj-transactions" />

                <!-- Search Input -->
                <div style="flex: 1; min-width: 220px;">
                    <label class="okj-label" style="font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 4px;">Pencarian</label>
                    <div style="position: relative;">
                        <input type="text" name="s" class="okj-input" placeholder="No. Transaksi, Customer, Catatan..." value="<?php echo esc_attr($search); ?>" style="padding-left: 32px;" />
                        <span class="dashicons dashicons-search" style="position: absolute; left: 8px; top: 8px; color: #94a3b8; font-size: 18px;"></span>
                    </div>
                </div>

                <!-- Status Filter -->
                <div style="min-width: 160px;">
                    <label class="okj-label" style="font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 4px;">Status Pembayaran</label>
                    <select name="status" class="okj-select" style="width: 100%;">
                        <option value="">Semua Status</option>
                        <option value="paid" <?php selected($status, 'paid'); ?>>🟢 Lunas / Selesai</option>
                        <option value="pending" <?php selected($status, 'pending'); ?>>🟡 Pending (Menunggu)</option>
                        <option value="processing" <?php selected($status, 'processing'); ?>>🔵 Sedang Diproses</option>
                        <option value="failed" <?php selected($status, 'failed'); ?>>🔴 Gagal</option>
                        <option value="expired" <?php selected($status, 'expired'); ?>>⏰ Kadaluwarsa (Expired)</option>
                        <option value="cancelled" <?php selected($status, 'cancelled'); ?>>⚪ Dibatalkan</option>
                    </select>
                </div>

                <!-- Payment Method Filter -->
                <div style="min-width: 160px;">
                    <label class="okj-label" style="font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 4px;">Metode Pembayaran</label>
                    <select name="payment_method" class="okj-select" style="width: 100%;">
                        <option value="">Semua Metode</option>
                        <option value="cash" <?php selected($payment_method, 'cash'); ?>>💵 Cash / Tunai</option>
                        <option value="qris" <?php selected($payment_method, 'qris'); ?>>📱 QRIS</option>
                        <option value="sumopod" <?php selected($payment_method, 'sumopod'); ?>>⚡ QRIS (Otomatis)</option>
                        <option value="transfer" <?php selected($payment_method, 'transfer'); ?>>🏦 Transfer Bank</option>
                        <option value="midtrans" <?php selected($payment_method, 'midtrans'); ?>>💳 Midtrans</option>
                        <option value="tripay" <?php selected($payment_method, 'tripay'); ?>>🌐 Tripay</option>
                    </select>
                </div>

                <!-- Date Range Filters -->
                <div style="min-width: 130px;">
                    <label class="okj-label" style="font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 4px;">Dari Tgl</label>
                    <input type="date" name="start_date" class="okj-input" value="<?php echo esc_attr($start_date); ?>" />
                </div>
                <div style="min-width: 130px;">
                    <label class="okj-label" style="font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 4px;">Sampai Tgl</label>
                    <input type="date" name="end_date" class="okj-input" value="<?php echo esc_attr($end_date); ?>" />
                </div>

                <!-- Action Buttons -->
                <div style="display: flex; gap: 8px;">
                    <button type="submit" class="okj-btn okj-btn-primary" style="height: 38px; display: inline-flex; align-items: center; gap: 4px;">
                        <span class="dashicons dashicons-filter" style="font-size: 16px; width: 16px; height: 16px;"></span> Filter
                    </button>
                    <?php if (!empty($search) || !empty($status) || !empty($payment_method) || !empty($start_date) || !empty($end_date)): ?>
                        <a href="<?php echo admin_url('admin.php?page=okj-transactions'); ?>" class="okj-btn okj-btn-secondary" style="height: 38px; display: inline-flex; align-items: center; gap: 4px; text-decoration: none;">
                            <span class="dashicons dashicons-dismiss" style="font-size: 16px; width: 16px; height: 16px;"></span> Reset
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="okj-card">
        <div class="okj-card-body" style="padding: 0;">
            <div style="overflow-x: auto;">
                <table class="okj-table" style="width: 100%; border-collapse: collapse; margin: 0;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; text-align: left;">
                            <th style="padding: 14px 16px; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">No. Transaksi</th>
                            <th style="padding: 14px 16px; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Referensi Order ID</th>
                            <th style="padding: 14px 16px; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Waktu</th>
                            <th style="padding: 14px 16px; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Customer</th>
                            <th style="padding: 14px 16px; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Item Produk</th>
                            <th style="padding: 14px 16px; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Metode Bayar</th>
                            <th style="padding: 14px 16px; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Total</th>
                            <th style="padding: 14px 16px; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Status</th>
                            <th style="padding: 14px 16px; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($transactions)): ?>
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 50px 20px; color: #64748b;">
                                    <div style="font-size: 42px; margin-bottom: 10px;">🧾</div>
                                    <h3 style="margin: 0 0 6px 0; color: #1e293b; font-size: 16px; font-weight: 700;">Belum Ada Transaksi Ditemukan</h3>
                                    <p style="margin: 0; font-size: 13px; color: #94a3b8;">Transaksi yang masuk melalui Kasir POS atau Checkout Online akan otomatis tercatat di sini.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($transactions as $tx): 
                                $status = strtolower($tx['payment_status']);
                                $contact = !empty($tx['cust_whatsapp']) ? $tx['cust_whatsapp'] : (!empty($tx['cust_phone']) ? $tx['cust_phone'] : '');
                                $clean_wa = preg_replace('/[^0-9]/', '', $contact);
                                if (substr($clean_wa, 0, 1) === '0') $clean_wa = '62' . substr($clean_wa, 1);

                                $raw_method = strtolower($tx['payment_method']);
                                $method_label = class_exists('OKJ_App') ? OKJ_App::format_payment_method($tx['payment_method']) : strtoupper($tx['payment_method']);
                                $method_badge_bg = '#f1f5f9';
                                $method_badge_color = '#475569';
                                $method_icon = 'dashicons-money';

                                if (in_array($raw_method, ['cash', 'cod', 'tunai'])) {
                                    $method_badge_bg = '#ecfdf5';
                                    $method_badge_color = '#047857';
                                    $method_icon = 'dashicons-money';
                                } elseif (strpos($raw_method, 'sumopod') !== false || strpos($raw_method, 'qris') !== false) {
                                    $method_badge_bg = '#eff6ff';
                                    $method_badge_color = '#1d4ed8';
                                    $method_icon = 'dashicons-smartphone';
                                } elseif (in_array($raw_method, ['transfer', 'bacs', 'bank_transfer']) || strpos($raw_method, 'transfer') !== false || strpos($raw_method, 'bank') !== false) {
                                    $method_badge_bg = '#faf5ff';
                                    $method_badge_color = '#7e22ce';
                                    $method_icon = 'dashicons-bank';
                                }
                            ?>
                            <tr id="tx-row-<?php echo esc_attr($tx['id']); ?>" style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                                <!-- No Transaksi -->
                                <td style="padding: 14px 16px;">
                                    <?php
                                    $wc_order_id = 0;
                                    if (preg_match('/^WC-(\d+)/i', $tx['transaction_no'], $m)) {
                                        $wc_order_id = (int)$m[1];
                                    } elseif (!empty($tx['notes']) && preg_match('/WooCommerce Order #(\d+)/i', $tx['notes'], $m)) {
                                        $wc_order_id = (int)$m[1];
                                    }
                                    $wc_edit_url = '';
                                    if ($wc_order_id > 0) {
                                        $wc_edit_url = admin_url('post.php?post=' . $wc_order_id . '&action=edit');
                                        if (class_exists('Automattic\WooCommerce\Utilities\OrderUtil') && Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled()) {
                                            $wc_edit_url = admin_url('admin.php?page=wc-orders&action=edit&id=' . $wc_order_id);
                                        }
                                    }
                                    ?>
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <?php if ($wc_order_id > 0): ?>
                                            <a href="<?php echo esc_url($wc_edit_url); ?>" target="_blank" title="Buka Pesanan WooCommerce #<?php echo $wc_order_id; ?> di Tab Baru" style="font-family: monospace; font-size: 13px; font-weight: 700; color: #4f46e5; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                                <span class="dashicons dashicons-cart" style="font-size: 14px; width: 14px; height: 14px;"></span>
                                                WC #<?php echo $wc_order_id; ?>
                                            </a>
                                        <?php else: ?>
                                            <strong style="font-family: monospace; font-size: 13px; color: #1e293b;"><?php echo esc_html($tx['transaction_no']); ?></strong>
                                        <?php endif; ?>
                                        <button type="button" class="okj-copy-btn" data-clipboard="<?php echo esc_attr($tx['transaction_no']); ?>" title="Salin No Transaksi" style="background: none; border: none; padding: 2px; cursor: pointer; color: #94a3b8;">
                                            <span class="dashicons dashicons-clipboard" style="font-size: 14px; width: 14px; height: 14px;"></span>
                                        </button>
                                    </div>
                                    <?php if (!empty($tx['notes'])): ?>
                                        <small style="display: block; font-size: 11px; color: #64748b; margin-top: 2px; font-style: italic;"><?php echo esc_html(wp_trim_words($tx['notes'], 6)); ?></small>
                                    <?php endif; ?>
                                </td>

                                <!-- Referensi Order ID (SumoPod / Gateway) -->
                                <td style="padding: 14px 16px; white-space: nowrap;">
                                    <?php 
                                    $ref_id = !empty($tx['reference_no']) ? $tx['reference_no'] : (strpos($tx['transaction_no'], 'INV-') === 0 ? $tx['transaction_no'] : '');
                                    if ($ref_id): 
                                    ?>
                                        <div style="display: flex; align-items: center; gap: 5px;">
                                            <span style="font-family: monospace; font-size: 11.5px; background: #eff6ff; color: #1e40af; padding: 3px 8px; border-radius: 5px; border: 1px solid #bfdbfe; font-weight: 600;" title="<?php echo esc_attr($ref_id); ?>">
                                                <?php echo esc_html($ref_id); ?>
                                            </span>
                                            <button type="button" class="okj-copy-btn" data-clipboard="<?php echo esc_attr($ref_id); ?>" title="Salin Order ID SumoPod" style="background: none; border: none; padding: 2px; cursor: pointer; color: #3b82f6;">
                                                <span class="dashicons dashicons-clipboard" style="font-size: 14px; width: 14px; height: 14px;"></span>
                                            </button>
                                            <a href="https://sumopod.com/dashboard/managed-payment/payments" target="_blank" title="Buka di Dashboard Pembayaran SumoPod" style="color: #6366f1; display: inline-flex; align-items: center; text-decoration: none;">
                                                <span class="dashicons dashicons-external" style="font-size: 14px; width: 14px; height: 14px;"></span>
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: #94a3b8; font-size: 12px;">-</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Tanggal & Waktu -->
                                <td style="padding: 14px 16px; white-space: nowrap;">
                                    <div style="font-weight: 600; font-size: 13px; color: #1e293b;"><?php echo esc_html(class_exists('OKJ_App') ? OKJ_App::format_datetime($tx['created_at'], 'd M Y') : date('d M Y', strtotime($tx['created_at']))); ?></div>
                                    <small style="color: #64748b; font-size: 11px;"><?php echo esc_html(class_exists('OKJ_App') ? OKJ_App::format_datetime($tx['created_at'], 'H:i') : date('H:i', strtotime($tx['created_at']))); ?> WIB</small>
                                </td>

                                <!-- Customer -->
                                <td style="padding: 14px 16px;">
                                    <div style="font-weight: 700; color: #0f172a; font-size: 13.5px;"><?php echo esc_html($tx['customer_name'] ?: 'Pelanggan Umum'); ?></div>
                                    <?php if ($clean_wa): ?>
                                        <a href="https://wa.me/<?php echo esc_attr($clean_wa); ?>" target="_blank" style="display: inline-flex; align-items: center; gap: 3px; color: #16a34a; font-size: 11.5px; text-decoration: none; margin-top: 2px; font-weight: 600;">
                                            <span class="dashicons dashicons-whatsapp" style="font-size: 13px; width: 13px; height: 13px;"></span> <?php echo esc_html($contact); ?>
                                        </a>
                                    <?php elseif (!empty($tx['cust_email'])): ?>
                                        <small style="color: #64748b; font-size: 11px;"><?php echo esc_html($tx['cust_email']); ?></small>
                                    <?php endif; ?>
                                </td>

                                <!-- Item Produk -->
                                <td style="padding: 14px 16px; max-width: 260px;">
                                    <?php if (!empty($tx['items'])): ?>
                                        <div style="display: flex; flex-direction: column; gap: 4px;">
                                            <?php foreach (array_slice($tx['items'], 0, 2) as $it): ?>
                                                <div style="font-size: 12px; color: #334155; display: flex; justify-content: space-between; gap: 8px;">
                                                    <span style="font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 180px;" title="<?php echo esc_attr($it['product_name']); ?>">
                                                        • <?php echo esc_html($it['product_name']); ?>
                                                    </span>
                                                    <span style="color: #64748b; font-size: 11px; font-weight: 600; white-space: nowrap;">x<?php echo (int)$it['qty']; ?></span>
                                                </div>
                                            <?php endforeach; ?>
                                            <?php if (count($tx['items']) > 2): ?>
                                                <small style="color: #4f46e5; font-size: 11px; font-weight: 600; cursor: pointer;" onclick="okjOpenTxDetail('<?php echo esc_js($tx['id']); ?>')">
                                                    +<?php echo count($tx['items']) - 2; ?> item lainnya...
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: #94a3b8; font-size: 12px;">-</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Metode Bayar -->
                                <td style="padding: 14px 16px; white-space: nowrap;">
                                    <span style="display: inline-flex; align-items: center; gap: 5px; background: <?php echo $method_badge_bg; ?>; color: <?php echo $method_badge_color; ?>; padding: 4px 10px; border-radius: 6px; font-size: 11.5px; font-weight: 700;">
                                        <span class="dashicons <?php echo $method_icon; ?>" style="font-size: 14px; width: 14px; height: 14px;"></span>
                                        <?php echo esc_html($method_label); ?>
                                    </span>
                                </td>

                                <!-- Total Pembayaran -->
                                <td style="padding: 14px 16px; white-space: nowrap;">
                                    <strong style="color: #0f172a; font-size: 14px;">Rp <?php echo number_format_i18n((float)$tx['total'], 0); ?></strong>
                                    <?php if ($tx['discount'] > 0): ?>
                                        <small style="display: block; font-size: 10.5px; color: #ef4444; font-weight: 600;">Diskon -Rp <?php echo number_format_i18n((float)$tx['discount'], 0); ?></small>
                                    <?php endif; ?>
                                </td>

                                <!-- Status -->
                                <td style="padding: 14px 16px; white-space: nowrap;">
                                    <div class="status-cell-<?php echo esc_attr($tx['id']); ?>">
                                        <?php if ($status === 'paid' || $status === 'completed'): ?>
                                            <span class="okj-badge okj-badge-success" style="padding: 4px 10px; font-size: 11.5px; font-weight: 700;">Lunas 🟢</span>
                                        <?php elseif ($status === 'pending'): ?>
                                            <div style="display: flex; flex-direction: column; gap: 4px; align-items: flex-start;">
                                                <span class="okj-badge okj-badge-warning" style="padding: 4px 10px; font-size: 11.5px; font-weight: 700;">Pending 🟡</span>
                                                <button type="button" class="okj-btn-link" onclick="okjQuickMarkPaid('<?php echo esc_js($tx['id']); ?>')" style="font-size: 11px; color: #16a34a; font-weight: 700; text-decoration: none; border: 1px solid #bbf7d0; background: #f0fdf4; padding: 2px 6px; border-radius: 4px;" title="Tandai pesanan lunas jika sudah menerima transfer">
                                                    ✓ Tandai Lunas
                                                </button>
                                            </div>
                                        <?php elseif ($status === 'processing'): ?>
                                            <span class="okj-badge" style="background: #e0e7ff; color: #4338ca; padding: 4px 10px; font-size: 11.5px; font-weight: 700;">Diproses 🔵</span>
                                        <?php elseif ($status === 'failed'): ?>
                                            <span class="okj-badge okj-badge-danger" style="padding: 4px 10px; font-size: 11.5px; font-weight: 700;">Gagal 🔴</span>
                                        <?php elseif ($status === 'expired'): ?>
                                            <span class="okj-badge" style="background: #fee2e2; color: #991b1b; padding: 4px 10px; font-size: 11.5px; font-weight: 700;">Expired ⏰</span>
                                        <?php else: ?>
                                            <span class="okj-badge" style="background: #f1f5f9; color: #64748b; padding: 4px 10px; font-size: 11.5px; font-weight: 700;">Batal ⚪</span>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <!-- Aksi -->
                                <td style="padding: 14px 16px; text-align: right; white-space: nowrap;">
                                    <div style="display: inline-flex; gap: 6px; align-items: center;">
                                        <!-- Detail Button -->
                                        <button type="button" class="okj-btn okj-btn-secondary okj-btn-small" onclick="okjOpenTxDetail('<?php echo esc_js($tx['id']); ?>')" title="Lihat Detail Transaksi" style="padding: 5px 8px; font-size: 12px; font-weight: 600;">
                                            <span class="dashicons dashicons-visibility" style="font-size: 15px; width: 15px; height: 15px; vertical-align: middle;"></span> Detail
                                        </button>

                                        <!-- Cetak Struk Button -->
                                        <button type="button" class="okj-btn okj-btn-secondary okj-btn-small" onclick="okjPrintReceipt('<?php echo esc_js($tx['id']); ?>')" title="Cetak Struk Pembelian" style="padding: 5px 8px; font-size: 12px; font-weight: 600; color: #0284c7; border-color: #bae6fd; background: #f0f9ff;">
                                            <span class="dashicons dashicons-printer" style="font-size: 15px; width: 15px; height: 15px; vertical-align: middle;"></span> Struk
                                        </button>

                                        <!-- Kirim WhatsApp Button -->
                                        <?php if ($clean_wa): ?>
                                            <button type="button" class="okj-btn okj-btn-small" onclick="okjSendWaReceipt('<?php echo esc_js($tx['id']); ?>', '<?php echo esc_js($contact); ?>')" title="Kirim Nota via WA Customer" style="padding: 5px 8px; font-size: 12px; font-weight: 600; color: #16a34a; border: 1px solid #bbf7d0; background: #f0fdf4;">
                                                <span class="dashicons dashicons-whatsapp" style="font-size: 15px; width: 15px; height: 15px; vertical-align: middle;"></span>
                                            </button>
                                        <?php endif; ?>

                                        <!-- Hapus Button -->
                                        <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=okj_delete_pos_transaction&id=' . $tx['id']), 'okj_delete_pos_transaction_' . $tx['id']); ?>" class="okj-btn-link okj-text-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus transaksi ini beserta rincian itemnya? Tindakan ini tidak dapat dibatalkan.');" title="Hapus Transaksi" style="padding: 5px; color: #ef4444;">
                                            <span class="dashicons dashicons-trash" style="font-size: 16px; width: 16px; height: 16px; vertical-align: middle;"></span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            <?php if ($total_pages > 1): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px 20px; border-top: 1px solid #e2e8f0; flex-wrap: wrap; gap: 10px;">
                    <div style="font-size: 13px; color: #64748b;">
                        Menampilkan <strong><?php echo number_format_i18n(min($total_items, ($current_page - 1) * $per_page + 1)); ?></strong> - <strong><?php echo number_format_i18n(min($total_items, $current_page * $per_page)); ?></strong> dari <strong><?php echo number_format_i18n($total_items); ?></strong> transaksi
                    </div>
                    <div style="display: flex; gap: 6px;">
                        <?php if ($current_page > 1): ?>
                            <a href="<?php echo add_query_arg('paged', $current_page - 1); ?>" class="okj-btn okj-btn-secondary okj-btn-small" style="font-weight: 600;">&laquo; Sebelumnya</a>
                        <?php endif; ?>

                        <?php for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++): ?>
                            <a href="<?php echo add_query_arg('paged', $i); ?>" class="okj-btn okj-btn-small <?php echo $i === $current_page ? 'okj-btn-primary' : 'okj-btn-secondary'; ?>" style="font-weight: 700; min-width: 32px; text-align: center;">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($current_page < $total_pages): ?>
                            <a href="<?php echo add_query_arg('paged', $current_page + 1); ?>" class="okj-btn okj-btn-secondary okj-btn-small" style="font-weight: 600;">Selanjutnya &raquo;</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ======================================================================= -->
<!-- MODAL: DETAIL TRANSAKSI                                                 -->
<!-- ======================================================================= -->
<div id="okjTxDetailModal" class="okj-modal" style="display: none; position: fixed; z-index: 99999; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); align-items: center; justify-content: center;">
    <div class="okj-modal-content" style="background-color: #fff; margin: auto; border-radius: 12px; max-width: 650px; width: 92%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); border: 1px solid #e2e8f0; overflow: hidden;">
        <div style="background: #f8fafc; padding: 18px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h3 style="margin: 0; font-size: 17px; color: #1e293b; font-weight: 700;" id="okj-modal-tx-no">Detail Transaksi</h3>
                <small style="color: #64748b; font-size: 12px;" id="okj-modal-tx-date">-</small>
            </div>
            <span onclick="document.getElementById('okjTxDetailModal').style.display='none'" style="color: #64748b; font-size: 26px; font-weight: bold; cursor: pointer; line-height: 1;">&times;</span>
        </div>

        <div style="padding: 24px; max-height: 70vh; overflow-y: auto;" id="okj-modal-tx-body">
            <!-- Dynamic Content loaded via AJAX -->
            <div style="text-align: center; padding: 40px 0; color: #64748b;">
                <span class="dashicons dashicons-update" style="font-size: 28px; width: 28px; height: 28px; animation: okjSpin 1s linear infinite;"></span>
                <p style="margin-top: 10px; font-weight: 600;">Memuat rincian transaksi...</p>
            </div>
        </div>

        <div style="background: #f8fafc; padding: 14px 24px; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div id="okj-modal-tx-status-actions"></div>
            <div style="display: flex; gap: 8px;">
                <button type="button" class="okj-btn okj-btn-secondary" id="okj-modal-tx-print" style="display: inline-flex; align-items: center; gap: 4px; font-weight: 600;">
                    <span class="dashicons dashicons-printer"></span> Cetak Struk
                </button>
                <button type="button" class="okj-btn okj-btn-secondary" onclick="document.getElementById('okjTxDetailModal').style.display='none'" style="font-weight: 600;">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================================= -->
<!-- HIDDEN PRINT AREA FOR RECEIPT                                          -->
<!-- ======================================================================= -->
<div id="okj-receipt-print-area" style="display: none;"></div>

<style>
@keyframes okjSpin { 100% { transform: rotate(360deg); } }
@media print {
    body * { visibility: hidden; }
    #okj-receipt-print-area, #okj-receipt-print-area * { visibility: visible; }
    #okj-receipt-print-area { position: absolute; left: 0; top: 0; width: 100%; display: block !important; }
}
</style>

<script>
jQuery(document).ready(function($) {
    // 1-Click Copy Transaction Number
    $('.okj-copy-btn').on('click', function(e) {
        e.preventDefault();
        var code = $(this).data('clipboard');
        var $btn = $(this);
        navigator.clipboard.writeText(code).then(function() {
            $btn.css('color', '#10b981');
            setTimeout(function() { $btn.css('color', '#94a3b8'); }, 1200);
        });
    });

    // Close Modals on Backdrop Click
    $(window).on('click', function(e) {
        if ($(e.target).is('#okjTxDetailModal')) {
            $('#okjTxDetailModal').hide();
        }
    });
});

// Helper to format payment method name in JS
function okjFormatPaymentMethod(method) {
    if (!method) return '-';
    var m = String(method).toLowerCase();
    if (m.indexOf('sumopod') !== -1 || m.indexOf('qris') !== -1) return 'QRIS';
    if (m === 'cash' || m === 'cod' || m === 'tunai') return 'Cash / Tunai';
    if (m === 'transfer' || m === 'bacs' || m.indexOf('transfer') !== -1 || m.indexOf('bank') !== -1) return 'Transfer Bank';
    if (m.indexOf('midtrans') !== -1) return 'Midtrans';
    if (m.indexOf('tripay') !== -1) return 'Tripay';
    if (m.indexOf('xendit') !== -1) return 'Xendit';
    var clean = m.replace(/^(okj_|wc_)/i, '').replace(/[_-]+/g, ' ');
    return clean.replace(/\b\w/g, function(l) { return l.toUpperCase(); });
}

// Open Transaction Detail Modal via AJAX
function okjOpenTxDetail(txId) {
    var $ = jQuery;
    $('#okjTxDetailModal').css('display', 'flex');
    $('#okj-modal-tx-no').text('Memuat Transaksi...');
    $('#okj-modal-tx-date').text('-');
    $('#okj-modal-tx-status-actions').empty();

    $.get(ajaxurl, {
        action: 'okj_get_transaction_detail',
        id: txId
    }, function(res) {
        if (!res.success) {
            alert(res.data.message || 'Gagal memuat detail transaksi.');
            $('#okjTxDetailModal').hide();
            return;
        }

        var tx = res.data;
        var headerTitle = 'Faktur: ' + tx.transaction_no;
        if (tx.reference_no) {
            headerTitle += ' (' + tx.reference_no + ')';
        }
        $('#okj-modal-tx-no').text(headerTitle);
        $('#okj-modal-tx-date').text(tx.formatted_date + ' WIB');

        var itemsHtml = '';
        if (tx.items && tx.items.length > 0) {
            tx.items.forEach(function(it) {
                var priceStr = 'Rp ' + Number(it.price).toLocaleString('id-ID');
                var subStr = 'Rp ' + Number(it.subtotal).toLocaleString('id-ID');
                itemsHtml += `
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 10px 0; font-weight: 600; color: #1e293b;">
                            ${it.product_name}
                            ${it.duration_days > 0 ? `<small style="display:block; color:#64748b; font-weight:normal;">Masa Aktif: ${it.duration_days} Hari</small>` : ''}
                        </td>
                        <td style="padding: 10px 0; text-align: center; color: #475569;">${it.qty}</td>
                        <td style="padding: 10px 0; text-align: right; color: #475569;">${priceStr}</td>
                        <td style="padding: 10px 0; text-align: right; font-weight: 700; color: #0f172a;">${subStr}</td>
                    </tr>
                `;
            });
        }

        var statusBadge = `<span class="okj-badge okj-badge-success">Lunas</span>`;
        if (tx.payment_status === 'pending') statusBadge = `<span class="okj-badge okj-badge-warning">Pending</span>`;
        if (tx.payment_status === 'processing') statusBadge = `<span class="okj-badge" style="background:#e0e7ff; color:#4338ca;">Diproses</span>`;
        if (tx.payment_status === 'failed') statusBadge = `<span class="okj-badge okj-badge-danger">Gagal</span>`;

        var pendingQrHtml = '';
        if (tx.payment_status === 'pending' && tx.qr_image_url) {
            var rawCustPhone = (tx.cust_whatsapp || tx.cust_phone || '').replace(/[^0-9]/g, '');
            if (rawCustPhone.startsWith('0')) rawCustPhone = '62' + rawCustPhone.slice(1);

            var waPendingText = encodeURIComponent(
                'Halo Kak ' + (tx.customer_name || '') + ', pesanan Anda ' + tx.transaction_no + 
                ' sebesar ' + tx.formatted_total + ' masih menunggu pembayaran.\n\n' +
                (tx.payment_url ? 'Silakan selesaikan pembayaran melalui tautan berikut:\n' + tx.payment_url + '\n\n' : '') +
                'Atau scan QRIS yang kami lampirkan untuk proses otomatis. Terima kasih!'
            );

            pendingQrHtml = `
                <div style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border: 1.5px solid #fcd34d; border-radius: 12px; padding: 16px 18px; margin-bottom: 20px; box-shadow: 0 4px 6px -1px rgba(245, 158, 11, 0.08);">
                    <div style="display: flex; gap: 18px; align-items: center; flex-wrap: wrap;">
                        <div style="background: #ffffff; padding: 8px; border-radius: 10px; border: 1px solid #fde68a; box-shadow: 0 2px 4px rgba(0,0,0,0.05); text-align: center; flex-shrink: 0; margin: 0 auto;">
                            <img src="${tx.qr_image_url}" 
                                 onerror="this.src='https://quickchart.io/qr?size=220&text=' + encodeURIComponent('${encodeURIComponent(tx.payment_url || tx.reference_no || tx.transaction_no)}')"
                                 style="width: 155px; height: 155px; display: block; border-radius: 6px;" 
                                 alt="QRIS Pembayaran" />
                            <div style="margin-top: 6px; font-size: 11px; font-weight: 800; color: #92400e; letter-spacing: 0.5px; display: flex; align-items: center; justify-content: center; gap: 4px;">
                                <span class="dashicons dashicons-camera" style="font-size: 13px; width: 13px; height: 13px;"></span>
                                SCAN UNTUK BAYAR
                            </div>
                        </div>
                        <div style="flex: 1; min-width: 240px;">
                            <div style="display: inline-flex; align-items: center; gap: 6px; background: #fef3c7; color: #b45309; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; border: 1px solid #fde68a; margin-bottom: 6px;">
                                <span class="dashicons dashicons-clock" style="font-size: 13px; width: 13px; height: 13px;"></span>
                                Menunggu Pembayaran Pelanggan
                            </div>
                            <h4 style="margin: 0 0 4px 0; font-size: 14px; font-weight: 800; color: #78350f;">
                                Selesaikan Pembayaran via QRIS
                            </h4>
                            <p style="margin: 0 0 10px 0; font-size: 11.5px; color: #92400e; line-height: 1.4;">
                                Jika halaman pembayaran pembeli sebelumnya tertutup, berikan QR Code ini untuk di-scan atau bagikan link pembayaran di bawah ke pembeli.
                            </p>
                            <div style="background: #ffffff; border: 1px solid #fde68a; border-radius: 6px; padding: 6px 12px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 11.5px; color: #64748b;">Total Tagihan:</span>
                                <strong style="font-size: 15px; color: #b45309;">${tx.formatted_total}</strong>
                            </div>
                            <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                                ${tx.payment_url ? `
                                    <button type="button" class="okj-btn" onclick="navigator.clipboard.writeText('${tx.payment_url}'); alert('Link pembayaran berhasil disalin!');" style="background: #ffffff; color: #92400e; border: 1px solid #fcd34d; font-size: 11.5px; font-weight: 700; padding: 5px 10px; display: inline-flex; align-items: center; gap: 4px; cursor: pointer;">
                                        <span class="dashicons dashicons-admin-links" style="font-size: 13px; width: 13px; height: 13px;"></span>
                                        Salin Link
                                    </button>
                                    <a href="${tx.payment_url}" target="_blank" class="okj-btn" style="background: #f59e0b; color: #ffffff; border: none; font-size: 11.5px; font-weight: 700; padding: 5px 10px; display: inline-flex; align-items: center; gap: 4px; text-decoration: none;">
                                        <span class="dashicons dashicons-external" style="font-size: 13px; width: 13px; height: 13px;"></span>
                                        Buka Pembayaran ↗
                                    </a>
                                ` : ''}
                                ${rawCustPhone ? `
                                    <a href="https://wa.me/${rawCustPhone}?text=${waPendingText}" target="_blank" class="okj-btn" style="background: #25d366; color: #ffffff; border: none; font-size: 11.5px; font-weight: 700; padding: 5px 10px; display: inline-flex; align-items: center; gap: 4px; text-decoration: none;">
                                        <span class="dashicons dashicons-whatsapp" style="font-size: 13px; width: 13px; height: 13px;"></span>
                                        Kirim ke WA
                                    </a>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        var html = `
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; background: #f8fafc; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #e2e8f0; font-size: 12.5px;">
                <div>
                    <span style="color: #64748b; display: block; margin-bottom: 2px;">Data Pelanggan:</span>
                    <strong style="color: #0f172a; font-size: 14px; display: block;">${tx.customer_name || 'Pelanggan Umum'}</strong>
                    ${tx.cust_phone || tx.cust_whatsapp ? `<span style="color: #475569;">📱 ${tx.cust_whatsapp || tx.cust_phone}</span><br>` : ''}
                    ${tx.cust_email ? `<span style="color: #475569;">✉️ ${tx.cust_email}</span>` : ''}
                    ${tx.wc_order_id ? `<div style="margin-top: 8px;"><a href="${tx.wc_edit_url}" target="_blank" style="color: #4f46e5; text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; background: #eef2ff; padding: 3px 8px; border-radius: 4px; font-size: 11.5px; border: 1px solid #c7d2fe;">🛒 Buka Order WooCommerce #${tx.wc_order_id} ↗</a></div>` : ''}
                </div>
                <div>
                    <span style="color: #64748b; display: block; margin-bottom: 2px;">Metode Pembayaran:</span>
                    <strong style="color: #0f172a; font-size: 13px;">${okjFormatPaymentMethod(tx.formatted_payment_method || tx.payment_method)}</strong>
                    <div style="margin-top: 6px;">
                        <span style="color: #64748b; font-size: 11px;">Status: </span>
                        ${statusBadge}
                    </div>
                    ${tx.reference_no ? `
                    <div style="margin-top: 8px; font-size: 11.5px;">
                        <span style="color: #64748b; display: block; margin-bottom: 2px;">Ref Order ID:</span>
                        <div style="display: flex; align-items: center; gap: 4px;">
                            <code style="background: #eff6ff; color: #1e40af; padding: 2px 7px; border-radius: 4px; font-weight: 600; border: 1px solid #bfdbfe; font-size: 11.5px;">${tx.reference_no}</code>
                            <a href="https://sumopod.com/dashboard/managed-payment/payments" target="_blank" title="Buka Dashboard SumoPod" style="color: #4f46e5; text-decoration: none; font-weight: 700; font-size: 12px; margin-left: 2px;">↗</a>
                        </div>
                    </div>` : ''}
                </div>
            </div>

            ${pendingQrHtml}

            <h4 style="margin: 0 0 10px 0; font-size: 14px; font-weight: 700; color: #334155;">Rincian Item Belanja</h4>
            <table style="width: 100%; border-collapse: collapse; font-size: 13px; margin-bottom: 16px;">
                <thead>
                    <tr style="border-bottom: 1.5px solid #cbd5e1; text-align: left; font-size: 11px; text-transform: uppercase; color: #64748b;">
                        <th style="padding-bottom: 8px;">Produk</th>
                        <th style="padding-bottom: 8px; text-align: center;">Qty</th>
                        <th style="padding-bottom: 8px; text-align: right;">Harga</th>
                        <th style="padding-bottom: 8px; text-align: right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>${itemsHtml}</tbody>
            </table>

            <div style="background: #f8fafc; padding: 12px 16px; border-radius: 8px; font-size: 13px; margin-top: 15px; border: 1px dashed #cbd5e1;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 6px; color: #475569;">
                    <span>Subtotal</span>
                    <strong>${tx.formatted_subtotal}</strong>
                </div>
                ${tx.discount > 0 ? `
                <div style="display: flex; justify-content: space-between; margin-bottom: 6px; color: #ef4444;">
                    <span>Diskon</span>
                    <strong>-${tx.formatted_discount}</strong>
                </div>` : ''}
                <div style="display: flex; justify-content: space-between; font-size: 15px; font-weight: 800; color: #0f172a; border-top: 1px solid #e2e8f0; padding-top: 8px; margin-top: 4px;">
                    <span>Total Pembayaran</span>
                    <span style="color: #10b981;">${tx.formatted_total}</span>
                </div>
            </div>

            ${tx.notes ? `
            <div style="margin-top: 15px; font-size: 12px; color: #64748b; background: #fffbeb; padding: 10px 14px; border-radius: 6px; border-left: 3px solid #f59e0b;">
                <strong>Catatan:</strong> ${tx.notes}
            </div>` : ''}
        `;

        $('#okj-modal-tx-body').html(html);

        // Status update actions
        if (tx.payment_status === 'pending') {
            $('#okj-modal-tx-status-actions').html(`
                <button type="button" class="okj-btn okj-btn-primary okj-btn-small" onclick="okjQuickMarkPaid('${tx.id}'); document.getElementById('okjTxDetailModal').style.display='none';" style="background:#10b981; font-weight:700;">
                    ✓ Konfirmasi Lunas Sekarang
                </button>
            `);
        }

        $('#okj-modal-tx-print').off('click').on('click', function() {
            okjPrintReceipt(tx.id);
        });
    });
}

// Quick Mark Paid (e.g. for manual bank transfer or cash)
function okjQuickMarkPaid(txId) {
    if (!confirm('Apakah Anda yakin ingin menandai transaksi ini sebagai LUNAS? Notifikasi konfirmasi akan otomatis diteruskan jika nomor WhatsApp tersedia.')) {
        return;
    }
    var $ = jQuery;
    $.post(ajaxurl, {
        action: 'okj_pos_update_status',
        transaction_id: txId,
        status: 'paid'
    }, function(res) {
        if (res.success) {
            $('.status-cell-' + txId).html('<span class="okj-badge okj-badge-success" style="padding: 4px 10px; font-size: 11.5px; font-weight: 700;">Lunas 🟢</span>');
        } else {
            alert(res.data.message || 'Gagal memperbarui status.');
        }
    });
}

// Print Thermal Receipt
function okjPrintReceipt(txId) {
    var $ = jQuery;
    $.get(ajaxurl, {
        action: 'okj_get_transaction_detail',
        id: txId
    }, function(res) {
        if (!res.success) {
            alert('Gagal mengambil data nota.');
            return;
        }
        var tx = res.data;
        var itemsReceipt = '';
        if (tx.items) {
            tx.items.forEach(function(it) {
                itemsReceipt += `
                    <div style="display:flex; justify-content:space-between; margin-bottom: 3px; font-size: 12px;">
                        <span>${it.product_name} x${it.qty}</span>
                        <span>${Number(it.subtotal).toLocaleString('id-ID')}</span>
                    </div>
                `;
            });
        }

        var html = `
            <div style="width: 280px; margin: 0 auto; font-family: monospace; padding: 10px; font-size: 12px; line-height: 1.4; color: #000;">
                <div style="text-align: center; margin-bottom: 8px;">
                    <h3 style="margin: 0; font-size: 16px; font-weight: bold; text-transform: uppercase;"><?php echo esc_js(get_bloginfo('name')); ?></h3>
                    <p style="margin: 2px 0 0 0; font-size: 11px;">NOTA PEMBELIAN</p>
                </div>
                <div style="border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 6px 0; margin-bottom: 8px; font-size: 11px;">
                    <div>No: ${tx.transaction_no}</div>
                    ${tx.reference_no ? `<div>Ref: ${tx.reference_no}</div>` : ''}
                    <div>Tgl: ${tx.formatted_date}</div>
                    <div>Cust: ${tx.customer_name || 'Umum'}</div>
                </div>
                <div style="margin-bottom: 8px;">
                    ${itemsReceipt}
                </div>
                <div style="border-top: 1px dashed #000; padding-top: 6px; font-size: 12px;">
                    <div style="display:flex; justify-content:space-between;">
                        <span>Subtotal:</span>
                        <span>${tx.formatted_subtotal}</span>
                    </div>
                    ${tx.discount > 0 ? `
                    <div style="display:flex; justify-content:space-between; color: #000;">
                        <span>Diskon:</span>
                        <span>-${tx.formatted_discount}</span>
                    </div>` : ''}
                    <div style="display:flex; justify-content:space-between; font-weight: bold; font-size: 13px; margin-top: 4px;">
                        <span>TOTAL:</span>
                        <span>${tx.formatted_total}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size: 11px; margin-top: 4px;">
                        <span>Metode:</span>
                        <span>${okjFormatPaymentMethod(tx.formatted_payment_method || tx.payment_method)}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size: 11px;">
                        <span>Status:</span>
                        <span style="font-weight: bold;">
                            ${tx.payment_status === 'paid' ? 'LUNAS' : (tx.payment_status === 'pending' ? 'PENDING (MENUNGGU PEMBAYARAN)' : tx.payment_status.toUpperCase())}
                        </span>
                    </div>
                </div>

                ${tx.payment_status === 'pending' && tx.qr_image_url ? `
                    <div style="text-align: center; margin-top: 10px; border-top: 1px dashed #000; padding-top: 8px;">
                        <p style="margin: 0 0 4px 0; font-size: 11px; font-weight: bold;">SCAN QRIS UNTUK MEMBAYAR</p>
                        <img src="${tx.qr_image_url}" style="width: 140px; height: 140px; margin: 0 auto 4px auto; display: block;" alt="QRIS Pembayaran" />
                        ${tx.payment_url ? `<p style="margin: 2px 0 0 0; font-size: 9px; word-break: break-all; color: #333;">${tx.payment_url}</p>` : ''}
                        <p style="margin: 4px 0 0 0; font-size: 10px; color: #444;">Scan dengan GoPay, OVO, DANA, BCA, atau Mobile Banking.</p>
                    </div>
                ` : ''}

                <div style="text-align: center; margin-top: 15px; font-size: 11px; border-top: 1px dashed #000; padding-top: 8px;">
                    <p style="margin: 0;">Terima kasih atas pembelian Anda!</p>
                </div>
            </div>
        `;

        $('#okj-receipt-print-area').html(html);
        window.print();
    });
}

// Send Receipt via WhatsApp
function okjSendWaReceipt(txId, phone) {
    var $ = jQuery;
    if (!confirm('Kirim nota transaksi ke WhatsApp ' + phone + '?')) return;
    $.post(ajaxurl, {
        action: 'okj_pos_send_wa_struk',
        transaction_id: txId,
        whatsapp_no: phone
    }, function(res) {
        if (res.success) {
            alert('Nota belanja berhasil dikirim ke WhatsApp ' + phone);
        } else {
            alert(res.data.message || 'Gagal mengirim nota WA.');
        }
    });
}

// Auto open modal on page load if open_detail / tx_id is present in URL
jQuery(document).ready(function($) {
    var urlParams = new URLSearchParams(window.location.search);
    var autoOpenTx = urlParams.get('open_detail') || urlParams.get('tx_id') || urlParams.get('detail');
    if (autoOpenTx) {
        setTimeout(function() {
            okjOpenTxDetail(autoOpenTx);
        }, 150);
    }
});
</script>
