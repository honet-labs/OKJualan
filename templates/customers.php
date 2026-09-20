<?php if (!defined('ABSPATH')) { exit; } ?>
<div class="okj-wrap">
    <?php if (isset($_GET['deleted'])): ?>
        <div class="notice notice-success is-dismissible okj-mb-2" style="margin: 0 0 20px 0; padding: 12px 16px; border-left-color: #10b981; background: #ecfdf5; color: #065f46; border-radius: 8px; border-left-width: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
            <p style="margin: 0; font-weight: 600;">Data customer berhasil dihapus.</p>
        </div>
    <?php endif; ?>

    <?php if ($action === 'add' || $action === 'edit'): ?>
        <!-- Add / Edit Page -->
        <div class="okj-header">
            <div>
                <h1><?php echo $action === 'edit' ? 'Edit Data Customer' : 'Tambah Customer Baru'; ?></h1>
                <p class="okj-subtitle">Kelola informasi data pelanggan, kontak notifikasi, dan alamat di OKJualan.</p>
            </div>
            <div class="okj-actions">
                <a class="okj-btn okj-btn-secondary" href="<?php echo admin_url('admin.php?page=okj-customers'); ?>">
                    <span class="dashicons dashicons-arrow-left-alt" style="margin-top: 3px;"></span> Kembali
                </a>
            </div>
        </div>

        <div class="okj-card okj-mt-2">
            <div class="okj-card-body">
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                    <?php wp_nonce_field('okj_save_customer'); ?>
                    <input type="hidden" name="action" value="okj_save_customer" />
                    <?php if ($row): ?>
                        <input type="hidden" name="id" value="<?php echo esc_attr($row['id']); ?>" />
                    <?php endif; ?>

                    <div class="okj-form-grid">
                        <div class="okj-form-group">
                            <label class="okj-label">Nama Customer <span class="okj-required">*</span></label>
                            <input type="text" name="name" class="okj-input" value="<?php echo $row ? esc_attr($row['name']) : ''; ?>" placeholder="Nama lengkap customer..." required />
                        </div>

                        <div class="okj-form-group">
                            <label class="okj-label">Nomor Telepon</label>
                            <input type="text" name="phone" class="okj-input" value="<?php echo $row ? esc_attr($row['phone']) : ''; ?>" placeholder="08123456789..." />
                        </div>

                        <div class="okj-form-group">
                            <label class="okj-label">WhatsApp (Format Internasional)</label>
                            <input type="text" name="whatsapp" class="okj-input" value="<?php echo $row ? esc_attr($row['whatsapp']) : ''; ?>" placeholder="628123456789..." />
                        </div>

                        <div class="okj-form-group">
                            <label class="okj-label">Email</label>
                            <input type="email" name="email" class="okj-input" value="<?php echo $row ? esc_attr($row['email']) : ''; ?>" placeholder="customer@example.com" />
                        </div>

                        <div class="okj-form-group">
                            <label class="okj-label">ID Telegram / Chat ID</label>
                            <input type="text" name="telegram" class="okj-input" value="<?php echo $row ? esc_attr($row['telegram']) : ''; ?>" placeholder="@username atau Chat ID" />
                        </div>

                        <div class="okj-form-group">
                            <label class="okj-label">Status Customer</label>
                            <?php $curr_status = $row && !empty($row['status']) ? $row['status'] : 'active'; ?>
                            <select name="status" class="okj-select" style="width: 100%;">
                                <option value="active" <?php selected($curr_status, 'active'); ?>>🟢 Aktif</option>
                                <option value="inactive" <?php selected($curr_status, 'inactive'); ?>>🔴 Nonaktif</option>
                            </select>
                        </div>

                        <div class="okj-form-group" style="grid-column: span 2;">
                            <label class="okj-label">Alamat</label>
                            <textarea name="address" class="okj-input" rows="2" placeholder="Alamat lengkap customer..."><?php echo $row && !empty($row['address']) ? esc_textarea($row['address']) : ''; ?></textarea>
                        </div>

                        <div class="okj-form-group" style="grid-column: span 2;">
                            <label class="okj-label">Keterangan Tambahan</label>
                            <textarea name="notes" class="okj-input" rows="2" placeholder="Catatan preferensi, riwayat khusus, dsb..."><?php echo $row && !empty($row['notes']) ? esc_textarea($row['notes']) : ''; ?></textarea>
                        </div>
                    </div>

                    <div class="okj-form-actions okj-mt-2" style="border-top: 1px solid #f1f5f9; padding-top: 16px;">
                        <button type="submit" class="okj-btn okj-btn-primary">
                            <span class="dashicons dashicons-saved" style="margin-top: 3px;"></span> Simpan Profil Customer
                        </button>
                    </div>
                </form>
            </div>
        </div>

    <?php else: ?>
        <!-- List Page: 7 Columns per User Requirements -->
        <div class="okj-header">
            <div>
                <h1>List Customer</h1>
                <p class="okj-subtitle">Daftar pelanggan yang membeli produk di platform OKJualan.</p>
            </div>
            <div class="okj-actions">
                <a class="okj-btn okj-btn-primary" href="<?php echo admin_url('admin.php?page=okj-customers&action=add'); ?>">
                    <span class="dashicons dashicons-plus"></span> Tambah Customer
                </a>
            </div>
        </div>

        <div class="okj-card okj-mt-2">
            <div class="okj-card-body">
                <?php if (empty($rows)): ?>
                    <div class="okj-empty-state">
                        <span class="dashicons dashicons-admin-users"></span>
                        <p>Belum ada data customer terdaftar. Tambahkan data customer pertama Anda.</p>
                    </div>
                <?php else: ?>
                    <div style="display: flex; justify-content: flex-end; margin-bottom: 16px;">
                        <input type="text" class="okj-input okj-table-search" placeholder="Cari nama, telepon, email..." style="max-width: 300px; width: 100%;" />
                    </div>
                    <div style="overflow-x: auto;">
                        <table class="okj-table">
                            <thead>
                                <tr>
                                    <th>ID Customer</th>
                                    <th>Nama Customer</th>
                                    <th>Alamat</th>
                                    <th>Nomor Telepon</th>
                                    <th>Email</th>
                                    <th>Keterangan Tambahan</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rows as $r): ?>
                                    <?php 
                                    $c_status = !empty($r['status']) ? $r['status'] : 'active';
                                    ?>
                                    <tr>
                                        <td><code><?php echo esc_html(substr($r['id'], 0, 8)); ?></code></td>
                                        <td>
                                            <strong><?php echo esc_html($r['name']); ?></strong>
                                            <?php if (!empty($r['active_products_count']) && $r['active_products_count'] > 0): ?>
                                                <div style="margin-top: 3px;">
                                                    <span class="okj-badge" style="background: #e0f2fe; color: #0369a1; font-size: 10px; padding: 1px 6px;">
                                                        <?php echo (int)$r['active_products_count']; ?> Layanan Aktif
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($r['address'])): ?>
                                                <span title="<?php echo esc_attr($r['address']); ?>" style="display: inline-block; max-width: 180px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 12px; color: #475569;">
                                                    <?php echo esc_html($r['address']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="okj-text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $phone_display = $r['phone'] ?: ($r['whatsapp'] ?: '-');
                                            ?>
                                            <div style="font-weight: 500;"><?php echo esc_html($phone_display); ?></div>
                                            <?php if (!empty($r['whatsapp'])): ?>
                                                <div style="font-size: 11px; color: #16a34a;">WA: <?php echo esc_html($r['whatsapp']); ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($r['telegram'])): ?>
                                                <div style="font-size: 11px; color: #0284c7;">TG: <?php echo esc_html($r['telegram']); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo esc_html($r['email'] ?: '-'); ?></td>
                                        <td>
                                            <?php if (!empty($r['notes'])): ?>
                                                <span title="<?php echo esc_attr($r['notes']); ?>" style="display: inline-block; max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 12px; color: #64748b;">
                                                    <?php echo esc_html($r['notes']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="okj-text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($c_status === 'active'): ?>
                                                <span class="okj-badge okj-badge-success" style="padding: 3px 8px; font-size: 11px;">Aktif</span>
                                            <?php else: ?>
                                                <span class="okj-badge okj-badge-danger" style="padding: 3px 8px; font-size: 11px;">Nonaktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div style="display: flex; gap: 6px; align-items: center;">
                                                <a class="okj-btn-link" href="<?php echo admin_url('admin.php?page=okj-customers&action=edit&id=' . $r['id']); ?>" title="Edit Customer">
                                                    <span class="dashicons dashicons-edit"></span>
                                                </a>
                                                <a class="okj-btn-link okj-btn-link-danger" href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=okj_delete_customer&id=' . $r['id']), 'okj_delete_customer_' . $r['id']); ?>" onclick="return confirm('Hapus customer ini?');" title="Hapus Customer">
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
