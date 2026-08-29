<?php if (!defined('ABSPATH')) { exit; } ?>
<div class="okj-wrap">
    <?php if (isset($_GET['deleted'])): ?>
        <div class="notice notice-success is-dismissible okj-mb-2" style="margin: 0 0 20px 0; padding: 12px 16px; border-left-color: #10b981; background: #ecfdf5; color: #065f46; border-radius: 8px; border-left-width: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
            <p style="margin: 0; font-weight: 600;">Data pembelian produk reseller berhasil dihapus.</p>
        </div>
    <?php endif; ?>
    <?php if ($action === 'add' || $action === 'edit'): ?>
        <!-- Add / Edit Page -->
        <div class="okj-header">
            <div>
                <h1><?php echo $action === 'edit' ? 'Edit Pembelian Produk' : 'Tambah Pembelian Produk'; ?></h1>
                <p class="okj-subtitle">Daftarkan transaksi produk yang dibeli dari supplier luar.</p>
            </div>
            <div class="okj-actions">
                <a class="okj-btn okj-btn-secondary" href="<?php echo admin_url('admin.php?page=okj-reseller-products'); ?>">Kembali</a>
            </div>
        </div>

        <div class="okj-card okj-mt-2">
            <div class="okj-card-body">
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" enctype="multipart/form-data">
                    <?php wp_nonce_field('okj_save_reseller_product'); ?>
                    <input type="hidden" name="action" value="okj_save_reseller_product" />
                    <?php if ($row): ?>
                        <input type="hidden" name="id" value="<?php echo esc_attr($row['id']); ?>" />
                    <?php endif; ?>

                    <div class="okj-form-grid">
                        <div class="okj-form-group">
                            <label class="okj-label">Pilih Produk Referensi <span class="okj-required">*</span></label>
                            <select name="price_id" class="okj-select okj-select2" style="width: 100%;" required>
                                <option value="">-- Pilih Master Harga --</option>
                                <?php foreach ($prices as $p): 
                                    $short_id = substr($p['id'], 0, 8);
                                    $seller_info = !empty($p['seller_name']) ? ' - ' . $p['seller_name'] : ' - Tanpa Seller';
                                ?>
                                    <option value="<?php echo esc_attr($p['id']); ?>" 
                                            data-seller-id="<?php echo esc_attr($p['seller_id']); ?>" 
                                            data-duration="<?php echo esc_attr($p['duration_days']); ?>"
                                            <?php echo $row && $row['price_id'] === $p['id'] ? 'selected' : ''; ?>>
                                        <?php echo esc_html($short_id . ' - ' . $p['name'] . ' - ' . $p['duration_days'] . ' Hari' . $seller_info); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="okj-form-group">
                            <label class="okj-label">Kategori</label>
                            <input type="text" name="category" class="okj-input" value="<?php echo $row ? esc_attr($row['category']) : ''; ?>" />
                        </div>

                        <div class="okj-form-group">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                <label class="okj-label" style="margin-bottom: 0;">Seller/Supplier/Provider</label>
                                <a href="#" class="okj-quick-add-seller-btn" style="text-decoration: none; font-size: 12px; color: #4f46e5; font-weight: 600; display: inline-flex; align-items: center;">
                                    <span class="dashicons dashicons-plus-alt2" style="font-size: 14px; width: 14px; height: 14px; margin-right: 2px; margin-top: 1px;"></span> Tambah Seller/Supplier/Provider Baru
                                </a>
                            </div>
                            <select name="seller_id" class="okj-select okj-select2" style="width: 100%;">
                                <option value="">-- Pilih Seller/Supplier/Provider --</option>
                                <?php foreach ($sellers as $s): ?>
                                    <option value="<?php echo esc_attr($s['id']); ?>" <?php echo $row && $row['seller_id'] === $s['id'] ? 'selected' : ''; ?>>
                                        <?php echo esc_html($s['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="okj-form-group">
                            <label class="okj-label">Tanggal Pembelian</label>
                            <input type="date" name="purchase_date" class="okj-input" value="<?php echo $row ? esc_attr($row['purchase_date']) : wp_date('Y-m-d'); ?>" />
                        </div>

                        <div class="okj-form-group">
                            <label class="okj-label">Durasi Produk (Hari)</label>
                            <input type="number" name="duration_days" class="okj-input" value="<?php echo $row ? esc_attr($row['duration_days']) : ''; ?>" min="0" />
                        </div>

                        <div class="okj-form-group">
                            <label class="okj-label">Tanggal Kadaluwarsa</label>
                            <input type="date" name="expires_at" class="okj-input" value="<?php echo $row ? esc_attr($row['expires_at']) : ''; ?>" readonly style="background: #f1f5f9; cursor: not-allowed; pointer-events: none;" tabindex="-1" />
                        </div>

                        <div class="okj-form-group">
                            <label class="okj-label">Harga Pembelian (Modal)</label>
                            <input type="number" name="price" class="okj-input" value="<?php echo $row ? esc_attr($row['price']) : '0'; ?>" min="0" />
                        </div>

                        <div class="okj-form-group">
                            <label class="okj-label">Status Pembayaran</label>
                            <select name="payment_status" class="okj-select">
                                <option value="pending" <?php echo $row && $row['payment_status'] === 'pending' ? 'selected' : ''; ?>>Pending / Belum Bayar</option>
                                <option value="paid" <?php echo $row && $row['payment_status'] === 'paid' ? 'selected' : ''; ?>>Paid / Lunas</option>
                            </select>
                        </div>
                    </div>

                    <div class="okj-form-group okj-mt-1">
                        <label class="okj-label">Bukti Pembayaran / Lampiran (Gambar)</label>
                        <input type="file" name="payment_attachments" accept="image/*" />
                        <?php if ($row && $row['payment_attachments']): ?>
                            <div class="okj-mt-1">
                                <img src="<?php echo esc_url($row['payment_attachments']); ?>" style="max-width: 150px; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" />
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="okj-form-group okj-mt-1">
                        <label class="okj-label">Deskripsi</label>
                        <textarea name="description" class="okj-input" rows="2"><?php echo $row ? esc_textarea($row['description']) : ''; ?></textarea>
                    </div>

                    <div class="okj-form-group okj-mt-1">
                        <label class="okj-label">Catatan Tambahan</label>
                        <textarea name="notes" class="okj-input" rows="2"><?php echo $row ? esc_textarea($row['notes']) : ''; ?></textarea>
                    </div>

                    <div class="okj-form-actions okj-mt-2">
                        <button type="submit" class="okj-btn okj-btn-primary">Simpan Pembelian Produk</button>
                    </div>
                </form>
            </div>
        </div>

    <?php else: ?>
        <!-- List Page -->
        <div class="okj-header">
            <div>
                <h1>Daftar Pembelian Produk</h1>
                <p class="okj-subtitle">Daftar transaksi pembelian produk ke supplier eksternal.</p>
            </div>
            <div class="okj-actions">
                <a class="okj-btn okj-btn-primary" href="<?php echo admin_url('admin.php?page=okj-reseller-products&action=add'); ?>">
                    <span class="dashicons dashicons-plus"></span> Tambah Pembelian
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
                        <a href="<?php echo admin_url('admin.php?page=okj-reseller-products&status_filter=active'); ?>" 
                           class="okj-tab-item <?php echo $current_status_filter === 'active' ? 'okj-tab-active' : ''; ?>"
                           style="text-decoration: none; padding: 6px 16px; border-radius: 6px; font-weight: 600; font-size: 13px; transition: all 0.2s; color: <?php echo $current_status_filter === 'active' ? '#ffffff' : '#64748b'; ?>; background: <?php echo $current_status_filter === 'active' ? '#4f46e5' : 'transparent'; ?>; box-shadow: <?php echo $current_status_filter === 'active' ? '0 1px 3px rgba(0,0,0,0.1)' : 'none'; ?>;">
                            <span class="dashicons dashicons-yes-alt" style="font-size: 16px; width: 16px; height: 16px; margin-top: 1px; margin-right: 4px;"></span>
                            Aktif
                        </a>
                        <a href="<?php echo admin_url('admin.php?page=okj-reseller-products&status_filter=expired'); ?>" 
                           class="okj-tab-item <?php echo $current_status_filter === 'expired' ? 'okj-tab-active' : ''; ?>"
                           style="text-decoration: none; padding: 6px 16px; border-radius: 6px; font-weight: 600; font-size: 13px; transition: all 0.2s; color: <?php echo $current_status_filter === 'expired' ? '#ffffff' : '#64748b'; ?>; background: <?php echo $current_status_filter === 'expired' ? '#4f46e5' : 'transparent'; ?>; box-shadow: <?php echo $current_status_filter === 'expired' ? '0 1px 3px rgba(0,0,0,0.1)' : 'none'; ?>;">
                            <span class="dashicons dashicons-no-alt" style="font-size: 16px; width: 16px; height: 16px; margin-top: 1px; margin-right: 4px;"></span>
                            Expired
                        </a>
                        <a href="<?php echo admin_url('admin.php?page=okj-reseller-products&status_filter=all'); ?>" 
                           class="okj-tab-item <?php echo $current_status_filter === 'all' ? 'okj-tab-active' : ''; ?>"
                           style="text-decoration: none; padding: 6px 16px; border-radius: 6px; font-weight: 600; font-size: 13px; transition: all 0.2s; color: <?php echo $current_status_filter === 'all' ? '#ffffff' : '#64748b'; ?>; background: <?php echo $current_status_filter === 'all' ? '#4f46e5' : 'transparent'; ?>; box-shadow: <?php echo $current_status_filter === 'all' ? '0 1px 3px rgba(0,0,0,0.1)' : 'none'; ?>;">
                            <span class="dashicons dashicons-category" style="font-size: 16px; width: 16px; height: 16px; margin-top: 1px; margin-right: 4px;"></span>
                            Semua Data
                        </a>
                    </div>
                    <?php if (!empty($rows)): ?>
                        <div class="okj-search-container" style="display: flex; gap: 8px; align-items: center;">
                            <input type="text" class="okj-input okj-table-search" placeholder="Cari data..." style="max-width: 250px; width: 100%; margin-bottom: 0;" />
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (empty($rows)): ?>
                    <div class="okj-empty-state" style="padding: 48px 24px; text-align: center;">
                        <span class="dashicons dashicons-info" style="font-size: 36px; width: 36px; height: 36px; color: #94a3b8; margin-bottom: 12px; display: inline-block;"></span>
                        <p style="margin: 0; font-size: 15px; color: #64748b; font-weight: 500;">Tidak ada pembelian produk dengan status filter ini.</p>
                    </div>
                <?php else: ?>
                    <table class="okj-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Produk</th>
                                <th>Catatan / Akun</th>
                                <th>Seller/Supplier/Provider</th>
                                <th>Tanggal Beli</th>
                                <th>Masa Expired</th>
                                <th>Harga Beli</th>
                                <th>In Use (Customer)</th>
                                <th>Status Bayar</th>
                                <th>Bukti Bayar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $r): ?>
                                <tr>
                                    <td><code><?php echo esc_html(substr($r['id'], 0, 8)); ?></code></td>
                                    <td>
                                        <strong><?php echo esc_html($r['product_name']); ?></strong>
                                        <div style="margin-top: 4px; display: flex; gap: 4px; align-items: center; flex-wrap: wrap;">
                                            <?php if (!empty($r['category'])): ?>
                                                <span class="okj-badge okj-badge-secondary" style="font-size: 10px; padding: 1px 6px;"><?php echo esc_html($r['category']); ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($r['duration_days'])): ?>
                                                <span class="okj-badge" style="background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; font-size: 10px; padding: 1px 6px; font-weight: 600;"><?php echo esc_html($r['duration_days']); ?> Hari</span>
                                            <?php endif; ?>
                                            <?php if (!empty($r['tags'])): 
                                                $tags_arr = array_map('trim', explode(',', $r['tags']));
                                                foreach ($tags_arr as $tg):
                                                    if ($tg !== ''):
                                            ?>
                                                <span class="okj-badge" style="background: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe; font-size: 10px; padding: 1px 6px;"><?php echo esc_html($tg); ?></span>
                                            <?php 
                                                    endif;
                                                endforeach;
                                            endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if (!empty($r['notes']) || !empty($r['description'])): ?>
                                            <a href="#" class="okj-view-detail" 
                                               data-name="<?php echo esc_attr($r['product_name']); ?>" 
                                               data-description="<?php echo esc_attr(wp_strip_all_tags($r['description'])); ?>" 
                                               data-notes="<?php echo esc_attr(wp_strip_all_tags($r['notes'])); ?>" 
                                               style="text-decoration: none; color: #4338ca; display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; background: #e0e7ff; border-radius: 6px; border: 1px solid #c7d2fe; transition: all 0.2s;"
                                               title="Lihat Detail & Catatan Akun">
                                                <span class="dashicons dashicons-visibility" style="font-size: 18px; width: 18px; height: 18px;"></span>
                                            </a>
                                        <?php else: ?>
                                            <span class="okj-text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($r['seller_name'])): ?>
                                            <a href="#" class="okj-view-seller-detail" 
                                               data-name="<?php echo esc_attr($r['seller_name']); ?>"
                                               data-email="<?php echo esc_attr(!empty($r['seller_email']) ? $r['seller_email'] : '-'); ?>"
                                               data-phone="<?php echo esc_attr(!empty($r['seller_phone']) ? $r['seller_phone'] : '-'); ?>"
                                               data-telegram="<?php echo esc_attr(!empty($r['seller_telegram']) ? $r['seller_telegram'] : '-'); ?>"
                                               data-whatsapp="<?php echo esc_attr(!empty($r['seller_whatsapp']) ? $r['seller_whatsapp'] : '-'); ?>"
                                               style="text-decoration: none; color: #4f46e5; font-weight: 600; border-bottom: 1px dashed #4f46e5; padding-bottom: 2px;"
                                               title="Lihat Detail Kontak Seller">
                                                <?php echo esc_html($r['seller_name']); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="okj-text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo esc_html($r['purchase_date']); ?></td>
                                    <td>
                                        <span class="dashicons dashicons-calendar-alt okj-text-muted" style="font-size: 14px; width: 14px; height: 14px; vertical-align: middle;"></span>
                                        <?php echo esc_html($r['expires_at'] ?: '-'); ?>
                                    </td>
                                    <td><strong>Rp <?php echo number_format_i18n($r['price'], 0); ?></strong></td>
                                    <td>
                                        <?php if (!empty($r['in_use_customers'])): ?>
                                            <span class="okj-badge" style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; font-weight: 600; padding: 4px 8px; display: inline-flex; align-items: center; gap: 4px; border-radius: 6px; font-size: 12px;" title="Sedang digunakan oleh customer">
                                                <span class="dashicons dashicons-admin-users" style="font-size: 14px; width: 14px; height: 14px;"></span>
                                                <?php echo esc_html($r['in_use_customers']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="okj-badge" style="background: #f8fafc; color: #94a3b8; border: 1px solid #e2e8f0; font-weight: 500; padding: 2px 6px; border-radius: 4px; font-size: 11px;" title="Stok pembelian ini belum dialokasikan ke customer">
                                                - Belum Digunakan -
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($r['payment_status'] === 'paid'): ?>
                                            <span class="okj-badge okj-badge-success">Paid</span>
                                        <?php else: ?>
                                            <span class="okj-badge okj-badge-warning">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($r['payment_attachments'])): ?>
                                            <a href="#" class="okj-view-payment-proof" 
                                               data-url="<?php echo esc_url($r['payment_attachments']); ?>"
                                               style="text-decoration: none; color: #4f46e5; font-weight: 600; display: inline-flex; align-items: center; border-bottom: 1px dashed #4f46e5; padding-bottom: 2px;"
                                               title="Lihat Bukti Pembayaran">
                                                <span class="dashicons dashicons-image-filter" style="margin-right: 4px; font-size: 16px; width: 16px; height: 16px;"></span>
                                                Lihat
                                            </a>
                                        <?php else: ?>
                                            <span class="okj-text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="okj-row-actions">
                                            <a class="okj-btn-link" href="<?php echo admin_url('admin.php?page=okj-reseller-products&action=edit&id=' . $r['id']); ?>">
                                                <span class="dashicons dashicons-edit"></span> Edit
                                            </a>
                                            <a class="okj-btn-link okj-btn-link-danger" href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=okj_delete_reseller_product&id=' . $r['id']), 'okj_delete_reseller_product_' . $r['id']); ?>" onclick="return confirm('Hapus data pembelian ini?');">
                                                <span class="dashicons dashicons-trash"></span> Hapus
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <?php if (isset($total_pages) && $total_pages > 1): 
                        $current_offset = ($paged - 1) * $per_page;
                    ?>
                        <div class="okj-pagination">
                            <div class="okj-pagination-info">
                                Menampilkan <?php echo ($current_offset + 1); ?> - <?php echo min($total_rows, $current_offset + $per_page); ?> dari <?php echo $total_rows; ?> data
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

<!-- Modal Popup Bukti Pembayaran -->
<div id="wrpmAttachmentModal" class="okj-modal" style="display: none; position: fixed; z-index: 999999; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); align-items: center; justify-content: center;">
    <div class="okj-modal-content" style="background-color: #ffffff; border-radius: 12px; max-width: 600px; width: 90%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); border: 1px solid #e2e8f0; animation: wrpmFadeIn 0.25s ease-out;">
        <div class="okj-modal-header" style="padding: 16px 24px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: #0f172a; display: flex; align-items: center;">
                <span class="dashicons dashicons-image-filter" style="margin-right: 8px; color: #4f46e5; font-size: 20px; width: 20px; height: 20px;"></span>
                Bukti Pembayaran / Lampiran
            </h3>
            <span class="okj-attachment-modal-close" style="color: #94a3b8; font-size: 28px; font-weight: bold; cursor: pointer; line-height: 1; transition: color 0.2s;">&times;</span>
        </div>
        <div class="okj-modal-body" style="padding: 24px; text-align: center; background: #f8fafc;">
            <img id="wrpmAttachmentImg" src="" alt="Bukti Pembayaran" style="max-width: 100%; max-height: 450px; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); border: 1px solid #e2e8f0;" />
        </div>
        <div class="okj-modal-footer" style="padding: 12px 24px; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end;">
            <a id="wrpmAttachmentDownloadBtn" href="" download class="okj-btn okj-btn-primary" style="margin-right: 8px; text-decoration: none; padding: 8px 16px; border-radius: 6px; font-weight: 500; display: inline-flex; align-items: center;">
                <span class="dashicons dashicons-download" style="margin-right: 6px; font-size: 16px; width: 16px; height: 16px;"></span> Unduh Gambar
            </a>
            <button class="okj-btn okj-btn-secondary okj-attachment-modal-close-btn" style="cursor: pointer; padding: 8px 16px; border-radius: 6px; background: #f1f5f9; color: #334155; border: 1px solid #e2e8f0; font-weight: 500;">Tutup</button>
        </div>
    </div>
</div>

<style>
@keyframes wrpmFadeIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}
.okj-attachment-modal-close:hover {
    color: #475569 !important;
}
.okj-view-payment-proof:hover {
    color: #4338ca !important;
    border-bottom-color: #4338ca !important;
}
</style>

<!-- Modal Quick Add Seller -->
<div id="wrpmQuickAddSellerModal" class="okj-modal" style="display: none; position: fixed; z-index: 999999; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); align-items: center; justify-content: center;">
    <div class="okj-modal-content" style="background-color: #ffffff; border-radius: 12px; max-width: 450px; width: 90%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); border: 1px solid #e2e8f0; animation: wrpmFadeIn 0.25s ease-out; margin: auto;">
        <div class="okj-modal-header" style="padding: 16px 24px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: #0f172a; display: flex; align-items: center;">
                <span class="dashicons dashicons-admin-users" style="margin-right: 8px; color: #4f46e5;"></span>
                Tambah Seller Baru (Cepat)
            </h3>
            <span class="okj-quick-seller-close" style="color: #94a3b8; font-size: 24px; font-weight: bold; cursor: pointer; line-height: 1;">&times;</span>
        </div>
        <div class="okj-modal-body" style="padding: 20px 24px;">
            <div class="okj-form-group" style="margin-bottom: 12px;">
                <label class="okj-label">Nama Seller <span class="okj-required">*</span></label>
                <input type="text" id="wrpmQuickSellerName" class="okj-input" placeholder="Masukkan nama seller..." required />
            </div>
            <div class="okj-form-group" style="margin-bottom: 12px;">
                <label class="okj-label">No. WhatsApp</label>
                <input type="text" id="wrpmQuickSellerWhatsapp" class="okj-input" placeholder="628123456789..." />
            </div>
            <div class="okj-form-group" style="margin-bottom: 0;">
                <label class="okj-label">Email</label>
                <input type="email" id="wrpmQuickSellerEmail" class="okj-input" placeholder="seller@email.com..." />
            </div>
        </div>
        <div class="okj-modal-footer" style="padding: 12px 24px; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end; gap: 8px;">
            <button type="button" class="okj-btn okj-btn-secondary okj-quick-seller-close-btn" style="cursor: pointer;">Batal</button>
            <button type="button" id="wrpmQuickSellerSubmitBtn" class="okj-btn okj-btn-primary" style="cursor: pointer; display: inline-flex; align-items: center;">
                <span class="okj-spinner" style="display: none; border: 2px solid #ffffff; border-top: 2px solid transparent; border-radius: 50%; width: 12px; height: 12px; margin-right: 6px; animation: wrpmSpin 1s linear infinite;"></span>
                Simpan Seller
            </button>
        </div>
    </div>
</div>

<!-- Modal Popup Detail Produk & Catatan Akun -->
<div id="wrpmDetailModal" class="okj-modal" style="display: none; position: fixed; z-index: 999999; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); align-items: center; justify-content: center;">
    <div class="okj-modal-content" style="background-color: #ffffff; border-radius: 12px; max-width: 550px; width: 90%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); border: 1px solid #e2e8f0; animation: wrpmFadeIn 0.25s ease-out; margin: auto;">
        <div class="okj-modal-header" style="padding: 16px 24px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
            <h3 id="wrpmModalTitle" style="margin: 0; font-size: 1.25rem; font-weight: 700; color: #0f172a;">Detail Produk</h3>
            <span class="okj-modal-close" style="color: #94a3b8; font-size: 28px; font-weight: bold; cursor: pointer; line-height: 1; transition: color 0.2s;">&times;</span>
        </div>
        <div class="okj-modal-body" style="padding: 24px; color: #334155; font-size: 0.95rem; line-height: 1.6;">
            <div style="margin-bottom: 20px;">
                <h4 style="margin: 0 0 8px 0; font-size: 0.9rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">Deskripsi Produk</h4>
                <div id="wrpmModalDescription" style="background: #f8fafc; border: 1px solid #f1f5f9; padding: 12px; border-radius: 8px; min-height: 40px; white-space: pre-wrap;">-</div>
            </div>
            <div>
                <h4 style="margin: 0 0 8px 0; font-size: 0.9rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">Catatan Akun / Kredensial / Info Supplier</h4>
                <div id="wrpmModalNotes" style="background: #fffbeb; border: 1px solid #fef3c7; padding: 12px; border-radius: 8px; min-height: 40px; color: #92400e; white-space: pre-wrap; font-family: monospace;">-</div>
            </div>
        </div>
        <div class="okj-modal-footer" style="padding: 12px 24px; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end;">
            <button class="okj-btn okj-btn-secondary okj-modal-close-btn" style="cursor: pointer; padding: 8px 16px; border-radius: 6px; background: #f1f5f9; color: #334155; border: 1px solid #e2e8f0; font-weight: 500;">Tutup</button>
        </div>
    </div>
</div>

<!-- Modal Popup Detail Seller -->
<div id="wrpmSellerModal" class="okj-modal" style="display: none; position: fixed; z-index: 999999; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); align-items: center; justify-content: center;">
    <div class="okj-modal-content" style="background-color: #ffffff; border-radius: 12px; max-width: 500px; width: 90%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); border: 1px solid #e2e8f0; animation: wrpmFadeIn 0.25s ease-out; margin: auto;">
        <div class="okj-modal-header" style="padding: 16px 24px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: #0f172a; display: flex; align-items: center;">
                <span class="dashicons dashicons-businessman" style="margin-right: 8px; color: #4f46e5; font-size: 20px; width: 20px; height: 20px;"></span>
                Detail Seller
            </h3>
            <span class="okj-seller-modal-close" style="color: #94a3b8; font-size: 28px; font-weight: bold; cursor: pointer; line-height: 1; transition: color 0.2s;">&times;</span>
        </div>
        <div class="okj-modal-body" style="padding: 24px; color: #334155; font-size: 0.95rem; line-height: 1.6;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 10px 0; font-weight: 600; color: #64748b; width: 35%;">Nama Seller</td>
                    <td id="wrpmSellerName" style="padding: 10px 0; color: #0f172a; font-weight: 600;">-</td>
                </tr>
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 10px 0; font-weight: 600; color: #64748b;">Email</td>
                    <td id="wrpmSellerEmail" style="padding: 10px 0; color: #0f172a;">-</td>
                </tr>
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 10px 0; font-weight: 600; color: #64748b;">Telepon</td>
                    <td id="wrpmSellerPhone" style="padding: 10px 0; color: #0f172a;">-</td>
                </tr>
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 10px 0; font-weight: 600; color: #64748b;">Telegram</td>
                    <td id="wrpmSellerTelegram" style="padding: 10px 0; color: #0f172a;">-</td>
                </tr>
                <tr>
                    <td style="padding: 10px 0; font-weight: 600; color: #64748b;">WhatsApp</td>
                    <td id="wrpmSellerWhatsapp" style="padding: 10px 0; color: #0f172a;">-</td>
                </tr>
            </table>
        </div>
        <div class="okj-modal-footer" style="padding: 12px 24px; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end;">
            <button class="okj-btn okj-btn-secondary okj-seller-modal-close-btn" style="cursor: pointer; padding: 8px 16px; border-radius: 6px; background: #f1f5f9; color: #334155; border: 1px solid #e2e8f0; font-weight: 500;">Tutup</button>
        </div>
    </div>
</div>

<style>
@keyframes wrpmSpin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
