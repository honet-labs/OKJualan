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
                <p class="okj-subtitle">Daftarkan profil kontak customer untuk pemetaan notifikasi reminder.</p>
            </div>
            <div class="okj-actions">
                <a class="okj-btn okj-btn-secondary" href="<?php echo admin_url('admin.php?page=okj-customers'); ?>">Kembali</a>
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
                            <label class="okj-label">Nama Lengkap <span class="okj-required">*</span></label>
                            <input type="text" name="name" class="okj-input" value="<?php echo $row ? esc_attr($row['name']) : ''; ?>" required />
                        </div>

                        <div class="okj-form-group">
                            <label class="okj-label">Email</label>
                            <input type="email" name="email" class="okj-input" value="<?php echo $row ? esc_attr($row['email']) : ''; ?>" />
                        </div>

                        <div class="okj-form-group">
                            <label class="okj-label">No. Telepon</label>
                            <input type="text" name="phone" class="okj-input" value="<?php echo $row ? esc_attr($row['phone']) : ''; ?>" />
                        </div>

                        <div class="okj-form-group">
                            <label class="okj-label">ID Telegram / Chat ID</label>
                            <input type="text" name="telegram" class="okj-input" value="<?php echo $row ? esc_attr($row['telegram']) : ''; ?>" placeholder="Contoh: 123456789 atau nama username" />
                        </div>

                        <div class="okj-form-group">
                            <label class="okj-label">WhatsApp (Format Internasional)</label>
                            <input type="text" name="whatsapp" class="okj-input" value="<?php echo $row ? esc_attr($row['whatsapp']) : ''; ?>" placeholder="Contoh: 628123456789" />
                        </div>
                    </div>

                    <div class="okj-form-actions okj-mt-2">
                        <button type="submit" class="okj-btn okj-btn-primary">Simpan Profil Customer</button>
                    </div>
                </form>
            </div>
        </div>

    <?php else: ?>
        <!-- List Page -->
        <div class="okj-header">
            <div>
                <h1>Daftar Data Customer</h1>
                <p class="okj-subtitle">Manajemen data customer terdaftar dan detail kontak notifikasi mereka.</p>
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
                        <span class="dashicons dashicons-info"></span>
                        <p>Belum ada data customer terdaftar.</p>
                    </div>
                <?php else: ?>
                    <div style="display: flex; justify-content: flex-end; margin-bottom: 16px;">
                        <input type="text" class="okj-input okj-table-search" placeholder="Cari data..." style="max-width: 300px; width: 100%;" />
                    </div>
                    <table class="okj-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama Customer</th>
                                <th>Email</th>
                                <th>Telepon</th>
                                <th>Telegram Chat ID</th>
                                <th>WhatsApp</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $r): ?>
                                <tr>
                                    <td><code><?php echo esc_html(substr($r['id'], 0, 8)); ?></code></td>
                                    <td>
                                        <strong><?php echo esc_html($r['name']); ?></strong>
                                        <div style="margin-top: 4px; display: flex; gap: 4px; align-items: center;">
                                            <?php if (!empty($r['active_services_count']) && $r['active_services_count'] > 0): ?>
                                                <span class="okj-badge okj-badge-success" style="font-size: 10px; padding: 1px 6px;">
                                                    <?php echo (int)$r['active_services_count']; ?> Layanan Aktif
                                                </span>
                                            <?php else: ?>
                                                <span class="okj-badge" style="background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; font-size: 10px; padding: 1px 6px;">
                                                    Tidak ada layanan aktif
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td><?php echo esc_html($r['email'] ?: '-'); ?></td>
                                    <td><?php echo esc_html($r['phone'] ?: '-'); ?></td>
                                    <td><code><?php echo esc_html($r['telegram'] ?: '-'); ?></code></td>
                                    <td>
                                        <?php if ($r['whatsapp']): ?>
                                            <a href="https://wa.me/<?php echo esc_attr($r['whatsapp']); ?>" target="_blank" class="okj-whatsapp-link">
                                                <span class="dashicons dashicons-whatsapp"></span> <?php echo esc_html($r['whatsapp']); ?>
                                            </a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="okj-row-actions">
                                            <a class="okj-btn-link" href="<?php echo admin_url('admin.php?page=okj-customers&action=edit&id=' . $r['id']); ?>">
                                                <span class="dashicons dashicons-edit"></span> Edit
                                            </a>
                                            <a class="okj-btn-link okj-btn-link-danger" href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=okj_delete_customer&id=' . $r['id']), 'okj_delete_customer_' . $r['id']); ?>" onclick="return confirm('Hapus customer ini? Semua data produk aktif terkait mungkin akan terdampak.');">
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
