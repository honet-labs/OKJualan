<?php if (!defined('ABSPATH')) { exit; } ?>
<div class="okj-wrap">
    <?php if (isset($_GET['deleted'])): ?>
        <div class="notice notice-success is-dismissible okj-mb-2" style="margin: 0 0 20px 0; padding: 12px 16px; border-left-color: #10b981; background: #ecfdf5; color: #065f46; border-radius: 8px; border-left-width: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
            <p style="margin: 0; font-weight: 600;">Data shortlink berhasil dihapus.</p>
        </div>
    <?php endif; ?>
    <?php if ($action === 'add' || $action === 'edit'): ?>
        <!-- Add / Edit Page -->
        <div class="okj-header">
            <div>
                <h1><?php echo $action === 'edit' ? 'Edit Shortlink Affiliate' : 'Tambah Shortlink Affiliate'; ?></h1>
                <p class="okj-subtitle">Bungkus link affiliate Anda ke dalam shortlink domain Anda sendiri.</p>
            </div>
            <div class="okj-actions">
                <a class="okj-btn okj-btn-secondary" href="<?php echo admin_url('admin.php?page=okj-shortlinks'); ?>">Kembali</a>
            </div>
        </div>

        <div class="okj-card okj-mt-2">
            <div class="okj-card-body">
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                    <?php wp_nonce_field('okj_save_shortlink'); ?>
                    <input type="hidden" name="action" value="okj_save_shortlink" />
                    <?php if ($row): ?>
                        <input type="hidden" name="id" value="<?php echo esc_attr($row['id']); ?>" />
                    <?php endif; ?>

                    <div class="okj-form-grid">
                        <div class="okj-form-group">
                            <label class="okj-label">Judul / Nama Shortlink <span class="okj-required">*</span></label>
                            <input type="text" name="title" class="okj-input" value="<?php echo $row ? esc_attr($row['title']) : ''; ?>" placeholder="Contoh: Affiliate VPS Niagahoster" required />
                        </div>

                        <div class="okj-form-group">
                            <label class="okj-label">Key Shortlink (Slug) <span class="okj-required">*</span></label>
                            <input type="text" id="okj-short-key" name="short_key" class="okj-input" value="<?php echo $row ? esc_attr($row['short_key']) : ''; ?>" placeholder="Contoh: vps-promo" required />
                            <small class="okj-text-muted" style="display: block; margin-top: 4px;">
                                Link Anda nantinya: <code id="okj-shortlink-preview"><?php echo esc_url(home_url('/go/')); ?><span id="okj-preview-key"><?php echo $row ? esc_html($row['short_key']) : 'vps-promo'; ?></span></code>
                            </small>
                        </div>
                    </div>

                    <div class="okj-form-group okj-mt-1">
                        <label class="okj-label">URL Tujuan (Destination URL) <span class="okj-required">*</span></label>
                        <input type="url" name="destination_url" class="okj-input" value="<?php echo $row ? esc_url($row['destination_url']) : ''; ?>" placeholder="https://referral-link.com/id=123" required />
                    </div>

                    <div class="okj-form-actions okj-mt-2">
                        <button type="submit" class="okj-btn okj-btn-primary">Simpan Shortlink</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('#okj-short-key').on('input', function() {
                var val = $(this).val().toLowerCase().replace(/[^a-z0-9-_]/g, '-').replace(/-+/g, '-');
                $(this).val(val);
                $('#okj-preview-key').text(val ? val : 'vps-promo');
            });
        });
        </script>

    <?php else: ?>
        <!-- List Page -->
        <div class="okj-header">
            <div>
                <h1>Daftar Shortlink Affiliate</h1>
                <p class="okj-subtitle">Bungkus dan lacak performa klik dari link affiliate/share Anda.</p>
            </div>
            <div class="okj-actions">
                <a class="okj-btn okj-btn-primary" href="<?php echo admin_url('admin.php?page=okj-shortlinks&action=add'); ?>">
                    <span class="dashicons dashicons-plus"></span> Tambah Baru
                </a>
            </div>
        </div>

        <div class="okj-card okj-mt-2">
            <div class="okj-card-body">
                <?php if (empty($rows)): ?>
                    <div class="okj-empty-state">
                        <span class="dashicons dashicons-admin-links" style="font-size: 3rem; width: 3rem; height: 3rem; color: #cbd5e1; margin-bottom: 12px;"></span>
                        <p>Belum ada shortlink yang dibuat. Mulai dengan membuat shortlink baru atau tambahkan link affiliate di Daftar Harga Produk.</p>
                    </div>
                <?php else: ?>
                    <div style="display: flex; justify-content: flex-end; margin-bottom: 16px;">
                        <input type="text" class="okj-input okj-table-search" placeholder="Cari shortlink..." style="max-width: 300px; width: 100%;" />
                    </div>
                    <table class="okj-table">
                        <thead>
                            <tr>
                                <th>Judul</th>
                                <th>Shortlink (Klik untuk Menyalin)</th>
                                <th>URL Tujuan</th>
                                <th>Jumlah Klik</th>
                                <th>Tanggal Dibuat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $r): 
                                $short_url = home_url('/go/' . $r['short_key']);
                            ?>
                                <tr>
                                    <td><strong><?php echo esc_html($r['title']); ?></strong></td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <code style="font-size: 13px; color: #4f46e5; background: #e0e7ff; padding: 4px 8px; border-radius: 4px; border: 1px solid #c7d2fe;"><?php echo esc_html($short_url); ?></code>
                                            <button class="okj-btn okj-btn-secondary okj-btn-small okj-copy-link-btn" data-link="<?php echo esc_attr($short_url); ?>" title="Salin Shortlink">
                                                <span class="dashicons dashicons-admin-page" style="font-size: 14px; width: 14px; height: 14px;"></span>
                                            </button>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="<?php echo esc_url($r['destination_url']); ?>" target="_blank" class="okj-text-muted" style="text-decoration: none; max-width: 250px; display: inline-block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            <?php echo esc_html($r['destination_url']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="okj-badge okj-badge-success" style="font-size: 12px; font-weight: 700; padding: 4px 10px;">
                                            <span class="dashicons dashicons-chart-bar" style="font-size: 14px; width: 14px; height: 14px; margin-right: 4px; margin-top: 1px; vertical-align: text-bottom;"></span> <?php echo number_format($r['clicks']); ?> Klik
                                        </span>
                                    </td>
                                    <td><?php echo esc_html(class_exists('OKJ_App') ? OKJ_App::format_datetime($r['created_at'], get_option('date_format', 'd M Y')) : date('d M Y', strtotime($r['created_at']))); ?></td>
                                    <td>
                                        <a class="okj-btn okj-btn-secondary okj-btn-small" href="<?php echo admin_url('admin.php?page=okj-shortlinks&action=edit&id=' . $r['id']); ?>">
                                            <span class="dashicons dashicons-edit"></span> Edit
                                        </a>
                                        <a class="okj-btn okj-btn-danger okj-btn-small" href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=okj_delete_shortlink&id=' . $r['id']), 'okj_delete_shortlink_' . $r['id']); ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus shortlink ini?');">
                                            <span class="dashicons dashicons-trash"></span> Hapus
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="okj-pagination okj-mt-2">
                            <span class="okj-pagination-info">Menampilkan halaman <?php echo $paged; ?> dari <?php echo $total_pages; ?> (Total: <?php echo $total_rows; ?> data)</span>
                            <div class="okj-pagination-buttons">
                                <?php if ($paged > 1): ?>
                                    <a class="okj-pagination-btn" href="<?php echo admin_url('admin.php?page=okj-shortlinks&paged=' . ($paged - 1)); ?>">&laquo; Prev</a>
                                <?php endif; ?>
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <a class="okj-pagination-btn <?php echo $paged === $i ? 'active' : ''; ?>" href="<?php echo admin_url('admin.php?page=okj-shortlinks&paged=' . $i); ?>"><?php echo $i; ?></a>
                                <?php endfor; ?>
                                <?php if ($paged < $total_pages): ?>
                                    <a class="okj-pagination-btn" href="<?php echo admin_url('admin.php?page=okj-shortlinks&paged=' . ($paged + 1)); ?>">Next &raquo;</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            // Live Search
            $('.okj-table-search').on('keyup input', function() {
                var val = $(this).val().toLowerCase().trim();
                $('.okj-table tbody tr').each(function() {
                    var title = $(this).find('strong').text().toLowerCase();
                    var link = $(this).find('code').text().toLowerCase();
                    var dest = $(this).find('a.okj-text-muted').text().toLowerCase();
                    if (title.indexOf(val) > -1 || link.indexOf(val) > -1 || dest.indexOf(val) > -1) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            // Clipboard Copying
            $('.okj-copy-link-btn').on('click', function(e) {
                e.preventDefault();
                var linkText = $(this).data('link');
                var $btn = $(this);
                
                navigator.clipboard.writeText(linkText).then(function() {
                    var originalHtml = $btn.html();
                    $btn.html('<span class="dashicons dashicons-yes" style="font-size: 14px; width: 14px; height: 14px; color: #10b981;"></span>').css('border-color', '#10b981');
                    setTimeout(function() {
                        $btn.html(originalHtml).css('border-color', '');
                    }, 1500);
                });
            });
        });
        </script>
    <?php endif; ?>
</div>
