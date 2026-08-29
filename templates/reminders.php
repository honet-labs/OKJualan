<?php if (!defined('ABSPATH')) { exit; } ?>
<div class="okj-wrap">
    <?php if (isset($_GET['deleted'])): ?>
        <div class="notice notice-success is-dismissible okj-mb-2" style="margin: 0 0 20px 0; padding: 12px 16px; border-left-color: #10b981; background: #ecfdf5; color: #065f46; border-radius: 8px; border-left-width: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
            <p style="margin: 0; font-weight: 600;">Data antrean reminder berhasil dihapus.</p>
        </div>
    <?php endif; ?>
    <div class="okj-header">
        <div>
            <h1>Antrean Pengiriman Reminder</h1>
            <p class="okj-subtitle">Daftar jadwal pengiriman reminder otomatis (H-7, H-3, H-1) ke pelanggan beserta log status pengiriman.</p>
        </div>
    </div>

    <div class="okj-card okj-mt-2">
        <div class="okj-card-body">
            <?php if (empty($rows)): ?>
                <div class="okj-empty-state">
                    <span class="dashicons dashicons-calendar"></span>
                    <p>Antrean reminder masih kosong. Pastikan Anda telah membuat data pelacakan produk aktif.</p>
                </div>
            <?php else: ?>
                <!-- Search & Filters Block -->
                <div class="okj-filter-bar" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px; background: rgba(248, 250, 252, 0.8); border: 1px solid #e2e8f0; padding: 18px; border-radius: 12px;">
                    <div>
                        <label class="okj-label" style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px; color: #475569;">Cari Reminder</label>
                        <input type="text" id="okj-reminder-search" class="okj-input" placeholder="Cari customer, produk..." style="width: 100%;" />
                    </div>
                    <div>
                        <label class="okj-label" style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px; color: #475569;">Filter Customer</label>
                        <select id="okj-reminder-filter-customer" class="okj-select" style="width: 100%;">
                            <option value="">-- Semua Customer --</option>
                            <?php 
                            $unique_customers = array_unique(array_column($rows, 'customer_name'));
                            sort($unique_customers);
                            foreach ($unique_customers as $cust): ?>
                                <option value="<?php echo esc_attr($cust); ?>"><?php echo esc_html($cust); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="okj-label" style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px; color: #475569;">Filter Produk</label>
                        <select id="okj-reminder-filter-product" class="okj-select" style="width: 100%;">
                            <option value="">-- Semua Produk --</option>
                            <?php 
                            $unique_products = array_unique(array_column($rows, 'product_label'));
                            sort($unique_products);
                            foreach ($unique_products as $prod): ?>
                                <option value="<?php echo esc_attr($prod); ?>"><?php echo esc_html($prod); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="okj-label" style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px; color: #475569;">Filter Milestone</label>
                        <select id="okj-reminder-filter-milestone" class="okj-select" style="width: 100%;">
                            <option value="">-- Semua Milestone --</option>
                            <option value="H-7">H-7</option>
                            <option value="H-3">H-3</option>
                            <option value="H-1">H-1</option>
                        </select>
                    </div>
                    <div>
                        <label class="okj-label" style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px; color: #475569;">Filter Status Kirim</label>
                        <select id="okj-reminder-filter-status" class="okj-select" style="width: 100%;">
                            <option value="">-- Semua Status --</option>
                            <option value="pending">Pending</option>
                            <option value="sent">Sent</option>
                        </select>
                    </div>
                </div>

                <table class="okj-table">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Produk</th>
                            <th>Milestone</th>
                            <th>Tanggal Kirim</th>
                            <th>Status Kirim</th>
                            <th>Dikirim Melalui</th>
                            <th>Waktu Kirim</th>
                            <th>Keterangan / Error</th>
                            <th>Aksi Manual</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r): ?>
                            <tr class="okj-reminder-row" 
                                data-customer="<?php echo esc_attr($r['customer_name']); ?>"
                                data-product="<?php echo esc_attr($r['product_label']); ?>"
                                data-milestone="H-<?php echo esc_attr($r['offset_days']); ?>"
                                data-status="<?php echo esc_attr($r['status']); ?>">
                                <td><strong><?php echo esc_html($r['customer_name']); ?></strong></td>
                                <td><?php echo esc_html($r['product_label']); ?></td>
                                <td>H-<?php echo esc_html($r['offset_days']); ?></td>
                                <td><span class="dashicons dashicons-clock okj-text-muted"></span> <?php echo esc_html($r['reminder_date']); ?></td>
                                <td>
                                    <?php if ($r['status'] === 'sent'): ?>
                                        <span class="okj-badge okj-badge-success">Terkirim</span>
                                    <?php else: ?>
                                        <span class="okj-badge okj-badge-warning">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html($r['sent_via'] ?: '-'); ?></td>
                                <td><?php echo esc_html($r['sent_at'] ?: '-'); ?></td>
                                <td>
                                    <?php if ($r['last_error']): ?>
                                        <small class="okj-text-danger"><?php echo esc_html($r['last_error']); ?></small>
                                    <?php else: ?>
                                        <span class="okj-text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 6px; align-items: center;">
                                        <a class="okj-btn okj-btn-secondary okj-btn-small" href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=okj_send_reminder_manual&id=' . $r['id']), 'okj_send_reminder_' . $r['id']); ?>" onclick="return confirm('Kirim reminder secara manual sekarang juga?');">
                                            <span class="dashicons dashicons-share-alt2"></span> Trigger Now
                                        </a>
                                        <a class="okj-btn okj-btn-danger okj-btn-small" href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=okj_delete_reminder&id=' . $r['id']), 'okj_delete_reminder_' . $r['id']); ?>" onclick="return confirm('Hapus antrean reminder ini?');" title="Hapus Reminder">
                                            <span class="dashicons dashicons-trash"></span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    function filterReminders() {
        var searchVal = $('#okj-reminder-search').val().toLowerCase().trim();
        var customerVal = $('#okj-reminder-filter-customer').val();
        var productVal = $('#okj-reminder-filter-product').val();
        var milestoneVal = $('#okj-reminder-filter-milestone').val();
        var statusVal = $('#okj-reminder-filter-status').val();

        var visibleCount = 0;

        $('.okj-reminder-row').each(function() {
            var $row = $(this);
            var customer = $row.data('customer');
            var product = $row.data('product');
            var milestone = $row.data('milestone');
            var status = $row.data('status');
            
            var textMatch = true;
            if (searchVal) {
                var rowText = $row.text().toLowerCase();
                textMatch = rowText.indexOf(searchVal) > -1;
            }

            var customerMatch = !customerVal || customer === customerVal;
            var productMatch = !productVal || product === productVal;
            var milestoneMatch = !milestoneVal || milestone === milestoneVal;
            var statusMatch = !statusVal || status === statusVal;

            if (textMatch && customerMatch && productMatch && milestoneMatch && statusMatch) {
                $row.show();
                visibleCount++;
            } else {
                $row.hide();
            }
        });

        // Toggle empty message if no rows match
        var $tbody = $('.okj-table tbody');
        $('#okj-reminder-no-results').remove();
        if (visibleCount === 0) {
            $tbody.after('<div id="okj-reminder-no-results" class="okj-empty-state" style="padding: 30px;"><span class="dashicons dashicons-search" style="font-size: 2rem; color: #d1d5db; margin-bottom: 8px;"></span><p>Tidak ada antrean reminder yang cocok dengan filter Anda.</p></div>');
            $('.okj-table').hide();
        } else {
            $('.okj-table').show();
        }
    }

    $('#okj-reminder-search').on('keyup input', filterReminders);
    $('#okj-reminder-filter-customer, #okj-reminder-filter-product, #okj-reminder-filter-milestone, #okj-reminder-filter-status').on('change', filterReminders);
});
</script>
