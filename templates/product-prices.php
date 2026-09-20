<?php if (!defined('ABSPATH')) { exit; } ?>
<div class="okj-wrap">
    <?php if (isset($_GET['deleted'])): ?>
        <div class="notice notice-success is-dismissible okj-mb-2" style="margin: 0 0 20px 0; padding: 12px 16px; border-left-color: #10b981; background: #ecfdf5; color: #065f46; border-radius: 8px; border-left-width: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
            <p style="margin: 0; font-weight: 600;">Data master harga produk berhasil dihapus.</p>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['wc_synced'])): ?>
        <div class="notice notice-success is-dismissible okj-mb-2" style="margin: 0 0 20px 0; padding: 12px 16px; border-left-color: #7c3aed; background: #f5f3ff; color: #5b21b6; border-radius: 8px; border-left-width: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
            <p style="margin: 0; font-weight: 600;"><span class="dashicons dashicons-yes-alt" style="color: #7c3aed; vertical-align: middle;"></span> Berhasil mensinkronkan <strong><?php echo intval($_GET['wc_synced']); ?></strong> produk ke katalog WooCommerce.</p>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['wc_error'])): ?>
        <div class="notice notice-error is-dismissible okj-mb-2" style="margin: 0 0 20px 0; padding: 12px 16px; border-left-color: #ef4444; background: #fef2f2; color: #991b1b; border-radius: 8px; border-left-width: 4px;">
            <p style="margin: 0; font-weight: 600;"><?php echo esc_html(urldecode($_GET['wc_error'])); ?></p>
        </div>
    <?php endif; ?>

    <?php if ($action === 'add' || $action === 'edit'): ?>
        <!-- Add / Edit Form -->
        <div class="okj-header">
            <div>
                <h1><?php echo $action === 'edit' ? 'Edit Produk' : 'Tambah Produk Baru'; ?></h1>
                <p class="okj-subtitle">Konfigurasi informasi produk, harga jual, stok, durasi, dan provider di OKJualan.</p>
            </div>
            <div class="okj-actions">
                <a class="okj-btn okj-btn-secondary" href="<?php echo admin_url('admin.php?page=okj-product-prices'); ?>">
                    <span class="dashicons dashicons-arrow-left-alt" style="margin-top: 3px;"></span> Kembali
                </a>
            </div>
        </div>

        <div class="okj-card okj-mt-2">
            <div class="okj-card-body">
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" enctype="multipart/form-data">
                    <?php wp_nonce_field('okj_save_price'); ?>
                    <input type="hidden" name="action" value="okj_save_price" />
                    <?php if ($row): ?>
                        <input type="hidden" name="id" value="<?php echo esc_attr($row['id']); ?>" />
                    <?php endif; ?>

                    <div class="okj-form-grid">
                        <div class="okj-form-group">
                            <label class="okj-label">Nama Produk <span class="okj-required">*</span></label>
                            <input type="text" name="name" class="okj-input" value="<?php echo $row ? esc_attr($row['name']) : ''; ?>" placeholder="Contoh: Netflix Premium 1 Bulan UHD" required />
                        </div>

                        <div class="okj-form-group">
                            <label class="okj-label">Kategori Produk</label>
                            <select name="category" class="okj-select okj-select2-category" style="width: 100%;">
                                <option value="">-- Pilih atau Ketik Kategori --</option>
                                <?php
                                $selected_category = $row ? trim($row['category']) : '';
                                if ($selected_category !== '') {
                                    echo '<option value="' . esc_attr($selected_category) . '" selected>' . esc_html($selected_category) . '</option>';
                                }
                                if (!empty($existing_categories)) {
                                    foreach ($existing_categories as $cat) {
                                        if ($cat !== $selected_category) {
                                            echo '<option value="' . esc_attr($cat) . '">' . esc_html($cat) . '</option>';
                                        }
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <div class="okj-form-group">
                            <label class="okj-label">Harga Jual (Rp) <span class="okj-required">*</span></label>
                            <input type="number" name="sale_price" class="okj-input" value="<?php echo $row ? esc_attr($row['sale_price']) : '0'; ?>" min="0" required />
                        </div>

                        <div class="okj-form-group">
                            <label class="okj-label">Harga Reseller / Modal (Rp)</label>
                            <input type="number" name="reseller_price" class="okj-input" value="<?php echo $row ? esc_attr($row['reseller_price']) : '0'; ?>" min="0" />
                        </div>

                        <div class="okj-form-group">
                            <label class="okj-label">Stok Produk</label>
                            <?php 
                            $curr_stock = $row && isset($row['stock']) ? (int)$row['stock'] : -1;
                            $is_unlimited = ($curr_stock < 0);
                            ?>
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <input type="number" id="okj_stock_input" name="stock" class="okj-input" value="<?php echo $is_unlimited ? '10' : esc_attr($curr_stock); ?>" min="0" style="width: 140px; <?php echo $is_unlimited ? 'display:none;' : ''; ?>" />
                                <label style="display: inline-flex; align-items: center; gap: 6px; font-weight: 500; font-size: 13px; color: #475569; cursor: pointer;">
                                    <input type="checkbox" id="okj_unlimited_stock" name="unlimited_stock" value="1" <?php checked($is_unlimited); ?> />
                                    Stok Tidak Terbatas (Unlimited)
                                </label>
                            </div>
                        </div>

                        <div class="okj-form-group">
                            <label class="okj-label">Durasi Masa Aktif (Hari) <span class="okj-required">*</span></label>
                            <input type="number" name="duration_days" class="okj-input" value="<?php echo $row ? esc_attr($row['duration_days']) : '30'; ?>" min="1" required />
                        </div>

                        <div class="okj-form-group">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                <label class="okj-label" style="margin-bottom: 0;">Seller / Supplier / Provider</label>
                                <a href="#" class="okj-quick-add-seller-btn" style="text-decoration: none; font-size: 12px; color: #4f46e5; font-weight: 600; display: inline-flex; align-items: center;">
                                    <span class="dashicons dashicons-plus-alt2" style="font-size: 14px; width: 14px; height: 14px; margin-right: 2px;"></span> Tambah Baru
                                </a>
                            </div>
                            <select name="seller_id" class="okj-select okj-select2" style="width: 100%;">
                                <option value="">-- Tanpa Provider Spesifik --</option>
                                <?php foreach ($sellers as $s): ?>
                                    <option value="<?php echo esc_attr($s['id']); ?>" <?php echo $row && $row['seller_id'] === $s['id'] ? 'selected' : ''; ?>>
                                        <?php echo esc_html($s['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="okj-form-group">
                            <label class="okj-label">Status Produk</label>
                            <?php $curr_status = $row && !empty($row['status']) ? $row['status'] : 'active'; ?>
                            <select name="status" class="okj-select" style="width: 100%;">
                                <option value="active" <?php selected($curr_status, 'active'); ?>>Aktif</option>
                                <option value="inactive" <?php selected($curr_status, 'inactive'); ?>>Nonaktif</option>
                            </select>
                        </div>

                        <div class="okj-form-group" style="grid-column: span 2;">
                            <label class="okj-label">Gambar Produk</label>
                            <div style="display: flex; gap: 16px; align-items: center;">
                                <?php $img_url = $row && !empty($row['image_url']) ? $row['image_url'] : ''; ?>
                                <div id="image_preview_wrap" style="width: 70px; height: 70px; border-radius: 8px; border: 1.5px dashed #cbd5e1; background: #f8fafc; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0;">
                                    <?php if ($img_url): ?>
                                        <img src="<?php echo esc_url($img_url); ?>" id="image_preview" style="width: 100%; height: 100%; object-fit: cover;" />
                                    <?php else: ?>
                                        <span class="dashicons dashicons-format-image" id="image_placeholder" style="font-size: 32px; color: #94a3b8; width: 32px; height: 32px;"></span>
                                        <img src="" id="image_preview" style="display: none; width: 100%; height: 100%; object-fit: cover;" />
                                    <?php endif; ?>
                                </div>
                                <div style="flex: 1;">
                                    <input type="file" name="product_image" id="product_image_input" accept="image/*" class="okj-input" style="padding: 6px;" />
                                    <input type="url" name="image_url" id="product_image_url" class="okj-input okj-mt-1" value="<?php echo esc_url($img_url); ?>" placeholder="Atau tempel URL gambar langsung (https://...)" />
                                </div>
                            </div>
                        </div>

                        <div class="okj-form-group" style="grid-column: span 2;">
                            <label class="okj-label">Deskripsi Produk</label>
                            <textarea name="description" class="okj-input" rows="3" placeholder="Jelaskan fitur, ketentuan akun, dan cara penggunaan produk..."><?php echo $row ? esc_textarea($row['description']) : ''; ?></textarea>
                        </div>

                        <div class="okj-form-group" style="grid-column: span 2;">
                            <label class="okj-label">Keterangan Tambahan (Internal)</label>
                            <textarea name="notes" class="okj-input" rows="2" placeholder="Catatan internal pengelola OKJualan..."><?php echo $row ? esc_textarea($row['notes']) : ''; ?></textarea>
                        </div>

                        <div class="okj-form-group">
                            <label class="okj-label">Tags Produk</label>
                            <select name="tags[]" class="okj-select okj-select2-tags" multiple="multiple" style="width: 100%;">
                                <?php
                                $selected_tags = ($row && !empty($row['tags'])) ? array_map('trim', explode(',', $row['tags'])) : [];
                                foreach ($selected_tags as $t) {
                                    echo '<option value="' . esc_attr($t) . '" selected>' . esc_html($t) . '</option>';
                                }
                                if (!empty($existing_tags)) {
                                    foreach ($existing_tags as $t) {
                                        if (!in_array($t, $selected_tags)) {
                                            echo '<option value="' . esc_attr($t) . '">' . esc_html($t) . '</option>';
                                        }
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <div class="okj-form-group">
                            <label class="okj-label">Link Affiliate / Referral (Opsional)</label>
                            <input type="url" name="affiliate_url" class="okj-input" value="<?php echo $row ? esc_url($row['affiliate_url']) : ''; ?>" placeholder="https://domain.com/ref?id=123" />
                            <div style="margin-top: 8px;">
                                <label style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; color: #475569; cursor: pointer;">
                                    <input type="checkbox" name="auto_create_shortlink" value="1" checked />
                                    Buat / Perbarui Shortlink Otomatis
                                </label>
                            </div>
                        </div>

                        <div class="okj-form-group">
                            <label class="okj-label">Tampilan POS & Kasir</label>
                            <label style="display: inline-flex; align-items: center; gap: 6px; font-size: 13.5px; color: #1e293b; cursor: pointer; margin-top: 8px;">
                                <input type="checkbox" name="show_in_pos" value="1" <?php echo !$row || !isset($row['show_in_pos']) || $row['show_in_pos'] == 1 ? 'checked' : ''; ?> />
                                Tampilkan di POS & Pemesanan Mandiri
                            </label>
                        </div>

                        <div class="okj-form-group">
                            <label class="okj-label">Integrasi WooCommerce</label>
                            <label style="display: inline-flex; align-items: center; gap: 6px; font-size: 13.5px; color: #1e293b; cursor: pointer; margin-top: 8px;">
                                <input type="checkbox" name="sync_to_wc" value="1" <?php echo !$row || !isset($row['sync_to_wc']) || $row['sync_to_wc'] == 1 ? 'checked' : ''; ?> />
                                Sinkronkan ke Katalog WooCommerce
                            </label>
                        </div>
                    </div>

                    <div class="okj-form-actions okj-mt-2" style="border-top: 1px solid #f1f5f9; padding-top: 16px;">
                        <button type="submit" class="okj-btn okj-btn-primary">
                            <span class="dashicons dashicons-saved" style="margin-top: 3px;"></span> Simpan Produk
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var unlimCheckbox = document.getElementById('okj_unlimited_stock');
            var stockInput = document.getElementById('okj_stock_input');
            if (unlimCheckbox && stockInput) {
                unlimCheckbox.addEventListener('change', function() {
                    stockInput.style.display = this.checked ? 'none' : 'inline-block';
                });
            }

            var fileInput = document.getElementById('product_image_input');
            var preview = document.getElementById('image_preview');
            var placeholder = document.getElementById('image_placeholder');
            if (fileInput) {
                fileInput.addEventListener('change', function() {
                    if (this.files && this.files[0]) {
                        var reader = new FileReader();
                        reader.onload = function(e) {
                            if (preview) {
                                preview.src = e.target.result;
                                preview.style.display = 'block';
                            }
                            if (placeholder) placeholder.style.display = 'none';
                        };
                        reader.readAsDataURL(this.files[0]);
                    }
                });
            }
        });
        </script>

    <?php else: ?>
        <!-- List Page: 11 Columns per User Requirements -->
        <div class="okj-header">
            <div>
                <h1>Daftar Harga Produk</h1>
                <p class="okj-subtitle">Katalog produk, harga, durasi masa aktif, stok barang, dan provider terdaftar.</p>
            </div>
            <div class="okj-actions" style="display: flex; gap: 8px; align-items: center;">
                <?php if (class_exists('WooCommerce')): ?>
                    <button type="button" id="okjSyncAllWcBtn" class="okj-btn" style="display: inline-flex; align-items: center; gap: 6px; background: #f5f3ff; color: #6d28d9; border: 1px solid #ddd6fe; font-weight: 600; cursor: pointer; padding: 8px 14px; border-radius: 6px;" title="Sinkronkan seluruh master harga ke katalog produk WooCommerce">
                        <span class="dashicons dashicons-update okj-sync-wc-spinner" style="font-size: 16px; width: 16px; height: 16px;"></span>
                        <span>Sync ke WooCommerce</span>
                    </button>
                <?php endif; ?>
                <a class="okj-btn okj-btn-primary" href="<?php echo admin_url('admin.php?page=okj-product-prices&action=add'); ?>">
                    <span class="dashicons dashicons-plus"></span> Tambah Produk
                </a>
            </div>
        </div>

        <div class="okj-card okj-mt-2">
            <div class="okj-card-body">
                <?php if (empty($rows)): ?>
                    <div class="okj-empty-state">
                        <span class="dashicons dashicons-tag"></span>
                        <p>Belum ada daftar harga produk. Klik "Tambah Produk" untuk mulai berjualan.</p>
                    </div>
                <?php else: ?>
                    <div style="display: flex; justify-content: flex-end; margin-bottom: 16px;">
                        <input type="text" class="okj-input okj-table-search" placeholder="Cari nama, kategori, provider..." style="max-width: 320px; width: 100%;" />
                    </div>
                    <div style="overflow-x: auto;">
                        <table class="okj-table">
                            <thead>
                                <tr>
                                    <th>ID Product</th>
                                    <th>Gambar</th>
                                    <th>Nama Product</th>
                                    <th>Harga Product</th>
                                    <th>Stok</th>
                                    <th>Durasi</th>
                                    <th>Kategori</th>
                                    <th>Deskripsi</th>
                                    <th>Seller/Supplier/Provider</th>
                                    <th>Keterangan</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rows as $r): ?>
                                    <?php 
                                    $p_stock = isset($r['stock']) ? (int)$r['stock'] : -1;
                                    $p_status = !empty($r['status']) ? $r['status'] : 'active';
                                    ?>
                                    <tr>
                                        <td><code><?php echo esc_html(substr($r['id'], 0, 8)); ?></code></td>
                                        <td>
                                            <?php if (!empty($r['image_url'])): ?>
                                                <img src="<?php echo esc_url($r['image_url']); ?>" alt="<?php echo esc_attr($r['name']); ?>" style="width: 44px; height: 44px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0;" />
                                            <?php else: ?>
                                                <div style="width: 44px; height: 44px; border-radius: 8px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #94a3b8;">
                                                    <span class="dashicons dashicons-format-image" style="font-size: 20px; width: 20px; height: 20px;"></span>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?php echo esc_html($r['name']); ?></strong>
                                            <div style="margin-top: 4px; display: flex; gap: 4px; align-items: center; flex-wrap: wrap;">
                                                <?php if (isset($r['show_in_pos']) && $r['show_in_pos'] == 1): ?>
                                                    <span class="okj-badge" style="background: #e0f2fe; color: #0369a1; font-size: 10px; padding: 1px 5px;">POS</span>
                                                <?php endif; ?>
                                                <?php if (!empty($r['affiliate_url'])): ?>
                                                    <span class="okj-badge" style="background: #fce7f3; color: #be185d; font-size: 10px; padding: 1px 5px;">Affiliate</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="font-weight: 700; color: #0f172a;">Rp <?php echo number_format_i18n($r['sale_price'], 0); ?></div>
                                            <?php if ((float)$r['reseller_price'] > 0): ?>
                                                <div style="font-size: 11px; color: #64748b;">Modal: Rp <?php echo number_format_i18n($r['reseller_price'], 0); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($p_stock < 0): ?>
                                                <span class="okj-badge" style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; font-weight: 600;">&infin; Unlimited</span>
                                            <?php elseif ($p_stock === 0): ?>
                                                <span class="okj-badge" style="background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; font-weight: 700;">Habis (0)</span>
                                            <?php elseif ($p_stock <= 3): ?>
                                                <span class="okj-badge okj-badge-warning" style="font-weight: 700;" title="Stok Menipis!"><?php echo $p_stock; ?> pcs</span>
                                            <?php else: ?>
                                                <span class="okj-badge" style="background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; font-weight: 600;"><?php echo $p_stock; ?> pcs</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="okj-badge okj-badge-secondary"><?php echo esc_html($r['duration_days']); ?> Hari</span></td>
                                        <td><span class="okj-badge" style="background: #f8fafc; border: 1px solid #e2e8f0; color: #334155;"><?php echo esc_html($r['category'] ?: 'Umum'); ?></span></td>
                                        <td>
                                            <?php if (!empty($r['description'])): ?>
                                                <a href="#" class="okj-view-detail" 
                                                   data-name="<?php echo esc_attr($r['name']); ?>" 
                                                   data-description="<?php echo esc_attr(wp_strip_all_tags($r['description'])); ?>" 
                                                   data-notes="<?php echo esc_attr(wp_strip_all_tags($r['notes'])); ?>" 
                                                   style="text-decoration: none; color: #4338ca; display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; background: #e0e7ff; border-radius: 6px;"
                                                   title="Lihat Deskripsi">
                                                    <span class="dashicons dashicons-visibility" style="font-size: 16px; width: 16px; height: 16px;"></span>
                                                </a>
                                            <?php else: ?>
                                                <span class="okj-text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($r['seller_name'])): ?>
                                                <a href="#" class="okj-view-seller-detail" 
                                                   data-name="<?php echo esc_attr($r['seller_name']); ?>"
                                                   data-email="<?php echo esc_attr($r['seller_email'] ?: '-'); ?>"
                                                   data-phone="<?php echo esc_attr($r['seller_phone'] ?: '-'); ?>"
                                                   data-telegram="<?php echo esc_attr($r['seller_telegram'] ?: '-'); ?>"
                                                   data-whatsapp="<?php echo esc_attr($r['seller_whatsapp'] ?: '-'); ?>"
                                                   style="text-decoration: none; color: #4f46e5; font-weight: 600;"
                                                   title="Lihat Detail Provider">
                                                    <?php echo esc_html($r['seller_name']); ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="okj-text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($r['notes'])): ?>
                                                <span title="<?php echo esc_attr($r['notes']); ?>" style="display: inline-block; max-width: 120px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 12px; color: #64748b;">
                                                    <?php echo esc_html($r['notes']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="okj-text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($p_status === 'active'): ?>
                                                <span class="okj-badge okj-badge-success" style="padding: 3px 8px; font-size: 11px;">Aktif</span>
                                            <?php else: ?>
                                                <span class="okj-badge okj-badge-danger" style="padding: 3px 8px; font-size: 11px;">Nonaktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div style="display: flex; gap: 6px; align-items: center;">
                                                <a class="okj-btn-link" href="<?php echo admin_url('admin.php?page=okj-product-prices&action=edit&id=' . $r['id']); ?>" title="Edit Produk">
                                                    <span class="dashicons dashicons-edit"></span>
                                                </a>
                                                <a class="okj-btn-link okj-btn-link-danger" href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=okj_delete_price&id=' . $r['id']), 'okj_delete_price_' . $r['id']); ?>" onclick="return confirm('Hapus produk ini?');" title="Hapus Produk">
                                                    <span class="dashicons dashicons-trash"></span>
                                                </a>
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
                                Menampilkan <?php echo ($current_offset + 1); ?> - <?php echo min($total_rows, $current_offset + $per_page); ?> dari <?php echo $total_rows; ?> produk
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

<!-- Modal Popup Detail Produk -->
<div id="wrpmDetailModal" class="okj-modal" style="display: none; position: fixed; z-index: 999999; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); align-items: center; justify-content: center;">
    <div class="okj-modal-content" style="background-color: #ffffff; border-radius: 12px; max-width: 550px; width: 90%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); border: 1px solid #e2e8f0;">
        <div class="okj-modal-header" style="padding: 16px 24px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
            <h3 id="wrpmModalTitle" style="margin: 0; font-size: 1.25rem; font-weight: 700; color: #0f172a;">Detail Produk</h3>
            <span class="okj-modal-close" style="color: #94a3b8; font-size: 28px; font-weight: bold; cursor: pointer; line-height: 1;">&times;</span>
        </div>
        <div class="okj-modal-body" style="padding: 24px; color: #334155; font-size: 0.95rem; line-height: 1.6;">
            <div style="margin-bottom: 20px;">
                <h4 style="margin: 0 0 8px 0; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">Deskripsi Produk</h4>
                <div id="wrpmModalDescription" style="background: #f8fafc; border: 1px solid #f1f5f9; padding: 12px; border-radius: 8px; min-height: 40px; white-space: pre-wrap;">-</div>
            </div>
            <div>
                <h4 style="margin: 0 0 8px 0; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">Catatan Tambahan</h4>
                <div id="wrpmModalNotes" style="background: #fffbeb; border: 1px solid #fef3c7; padding: 12px; border-radius: 8px; min-height: 40px; color: #92400e; white-space: pre-wrap;">-</div>
            </div>
        </div>
        <div class="okj-modal-footer" style="padding: 12px 24px; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end;">
            <button class="okj-btn okj-btn-secondary okj-modal-close-btn">Tutup</button>
        </div>
    </div>
</div>

<!-- Modal Popup Detail Seller/Provider -->
<div id="wrpmSellerModal" class="okj-modal" style="display: none; position: fixed; z-index: 999999; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); align-items: center; justify-content: center;">
    <div class="okj-modal-content" style="background-color: #ffffff; border-radius: 12px; max-width: 500px; width: 90%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); border: 1px solid #e2e8f0;">
        <div class="okj-modal-header" style="padding: 16px 24px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: #0f172a; display: flex; align-items: center;">
                <span class="dashicons dashicons-businessman" style="margin-right: 8px; color: #4f46e5;"></span>
                Detail Seller / Provider
            </h3>
            <span class="okj-seller-modal-close" style="color: #94a3b8; font-size: 28px; font-weight: bold; cursor: pointer; line-height: 1;">&times;</span>
        </div>
        <div class="okj-modal-body" style="padding: 24px; color: #334155; font-size: 0.95rem; line-height: 1.6;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 10px 0; font-weight: 600; color: #64748b; width: 35%;">Nama</td>
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
                    <td style="padding: 10px 0; font-weight: 600; color: #64748b;">WhatsApp</td>
                    <td id="wrpmSellerWhatsapp" style="padding: 10px 0; color: #0f172a;">-</td>
                </tr>
            </table>
        </div>
        <div class="okj-modal-footer" style="padding: 12px 24px; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end;">
            <button class="okj-btn okj-btn-secondary okj-seller-modal-close-btn">Tutup</button>
        </div>
    </div>
</div>

<!-- Modal Quick Add Seller -->
<div id="wrpmQuickAddSellerModal" class="okj-modal" style="display: none; position: fixed; z-index: 999999; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); align-items: center; justify-content: center;">
    <div class="okj-modal-content" style="background-color: #ffffff; border-radius: 12px; max-width: 460px; width: 90%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); border: 1px solid #e2e8f0; margin: auto;">
        <div class="okj-modal-header" style="padding: 16px 24px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: #0f172a; display: flex; align-items: center;">
                <span class="dashicons dashicons-admin-users" style="margin-right: 8px; color: #4f46e5;"></span>
                Tambah Seller / Provider Baru
            </h3>
            <span class="okj-quick-seller-close" style="color: #94a3b8; font-size: 24px; font-weight: bold; cursor: pointer; line-height: 1;">&times;</span>
        </div>
        <div class="okj-modal-body" style="padding: 20px 24px;">
            <div class="okj-form-group" style="margin-bottom: 12px;">
                <label class="okj-label">Nama Seller/Provider <span class="okj-required">*</span></label>
                <input type="text" id="wrpmQuickSellerName" class="okj-input" placeholder="Contoh: DigitalStore ID" required />
            </div>
            <div class="okj-form-group" style="margin-bottom: 12px;">
                <label class="okj-label">No. Telepon / WhatsApp</label>
                <input type="text" id="wrpmQuickSellerWhatsapp" class="okj-input" placeholder="08123456789..." />
            </div>
            <div class="okj-form-group" style="margin-bottom: 12px;">
                <label class="okj-label">Email</label>
                <input type="email" id="wrpmQuickSellerEmail" class="okj-input" placeholder="seller@example.com..." />
            </div>
            <div class="okj-form-group" style="margin-bottom: 0;">
                <label class="okj-label">Alamat</label>
                <textarea id="wrpmQuickSellerAddress" class="okj-input" rows="2" placeholder="Alamat seller/supplier..."></textarea>
            </div>
        </div>
        <div class="okj-modal-footer" style="padding: 12px 24px; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end; gap: 8px;">
            <button type="button" class="okj-btn okj-btn-secondary okj-quick-seller-close-btn">Batal</button>
            <button type="button" id="wrpmQuickSellerSubmitBtn" class="okj-btn okj-btn-primary" style="display: inline-flex; align-items: center;">
                <span class="okj-spinner" style="display: none; border: 2px solid #ffffff; border-top: 2px solid transparent; border-radius: 50%; width: 12px; height: 12px; margin-right: 6px; animation: wrpmSpin 1s linear infinite;"></span>
                Simpan Seller
            </button>
        </div>
    </div>
</div>
