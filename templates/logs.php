<?php if (!defined('ABSPATH')) { exit; } ?>
<div class="okj-wrap">
    <div class="okj-header">
        <div>
            <h1>Log Aktivitas Sistem (Audit Trail)</h1>
            <p class="okj-subtitle">Daftar pencatatan aktivitas, pembuatan data, pengiriman notifikasi, dan perubahan setting plugin.</p>
        </div>
    </div>

    <div class="okj-card okj-mt-2">
        <div class="okj-card-body">
            <?php if (empty($rows)): ?>
                <div class="okj-empty-state">
                    <span class="dashicons dashicons-list-view"></span>
                    <p>Log aktivitas masih kosong.</p>
                </div>
            <?php else: ?>
                <table class="okj-table">
                    <thead>
                        <tr>
                            <th>Waktu Kejadian</th>
                            <th>Petugas (User)</th>
                            <th>Aksi</th>
                            <th>Entitas</th>
                            <th>Pesan Deskripsi</th>
                            <th>Alamat IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <td><span class="dashicons dashicons-clock okj-text-muted"></span> <?php echo esc_html($r['happened_at']); ?></td>
                                <td><strong><?php echo esc_html($r['user_login']); ?></strong> <small class="okj-text-muted">(ID: <?php echo esc_html($r['user_id']); ?>)</small></td>
                                <td>
                                    <?php if (strpos($r['action'], 'delete') !== false || strpos($r['action'], 'fail') !== false): ?>
                                        <span class="okj-badge okj-badge-danger"><?php echo esc_html($r['action']); ?></span>
                                    <?php elseif (strpos($r['action'], 'update') !== false || strpos($r['action'], 'save') !== false): ?>
                                        <span class="okj-badge okj-badge-warning"><?php echo esc_html($r['action']); ?></span>
                                    <?php else: ?>
                                        <span class="okj-badge okj-badge-success"><?php echo esc_html($r['action']); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><code><?php echo esc_html($r['entity']); ?></code></td>
                                <td><?php echo esc_html($r['message']); ?></td>
                                <td><code><?php echo esc_html($r['ip'] ?: '-'); ?></code></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <?php if (isset($total_pages) && $total_pages > 1): 
                    $current_offset = ($paged - 1) * $per_page;
                ?>
                    <div class="okj-pagination okj-mt-2">
                        <div class="okj-pagination-info">
                            Menampilkan <?php echo ($current_offset + 1); ?> - <?php echo min($total_rows, $current_offset + $per_page); ?> dari <?php echo $total_rows; ?> log aktivitas
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
</div>
