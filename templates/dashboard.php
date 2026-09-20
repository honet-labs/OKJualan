<?php if (!defined('ABSPATH')) { exit; } ?>
<div class="okj-wrap">
    <div class="okj-header">
        <div>
            <h1>OKJualan Dashboard</h1>
            <p class="okj-subtitle">Ikhtisar performa bisnis, pemantauan stok menipis, dan pelacakan reminder produk aktif.</p>
        </div>
    </div>

    <?php if (!empty($low_stock_products)): ?>
    <!-- Low Stock Alert Banner -->
    <div class="okj-card okj-mt-2" style="border-left: 4px solid #ef4444; background: #fff5f5; margin-bottom: 20px;">
        <div class="okj-card-header" style="border-bottom: 1px solid #fee2e2; display: flex; justify-content: space-between; align-items: center; padding: 12px 18px;">
            <h2 style="color: #991b1b; display: flex; align-items: center; gap: 8px; font-size: 14px; margin: 0;">
                <span class="dashicons dashicons-warning" style="color: #ef4444; font-size: 18px; width: 18px; height: 18px;"></span>
                Peringatan: Stok Produk Menipis (&le; 3 Unit)
            </h2>
            <a href="<?php echo admin_url('admin.php?page=okj-product-prices'); ?>" class="okj-btn okj-btn-secondary" style="font-size: 11.5px; padding: 4px 10px;">Kelola Stok &raquo;</a>
        </div>
        <div class="okj-card-body" style="padding: 12px 16px;">
            <table class="okj-table" style="background: transparent;">
                <thead>
                    <tr style="background: rgba(239, 68, 68, 0.05);">
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Sisa Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($low_stock_products as $lp): ?>
                    <tr>
                        <td><strong><?php echo esc_html($lp['name']); ?></strong></td>
                        <td><?php echo esc_html($lp['category'] ?: '-'); ?></td>
                        <td>Rp <?php echo number_format_i18n((float)($lp['price'] ?? $lp['sale_price'] ?? 0), 0); ?></td>
                        <td>
                            <?php if ((int)$lp['stock'] === 0): ?>
                                <span class="okj-badge okj-badge-danger" style="font-weight: 700;">HABIS (0)</span>
                            <?php else: ?>
                                <span class="okj-badge okj-badge-warning" style="font-weight: 700;">Sisa <?php echo (int)$lp['stock']; ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a class="okj-btn-link" href="<?php echo admin_url('admin.php?page=okj-product-prices&action=edit&id=' . $lp['id']); ?>">
                                <span class="dashicons dashicons-edit"></span> Restock
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- KPIs Widgets Grid -->
    <div class="okj-grid okj-grid-4">
        <div class="okj-card okj-kpi-card">
            <div class="okj-kpi-icon"><span class="dashicons dashicons-cart"></span></div>
            <div class="okj-kpi-content">
                <span class="okj-kpi-label">Produk Reseller</span>
                <h3 class="okj-kpi-value"><?php echo number_format_i18n($total_reseller); ?></h3>
            </div>
        </div>
        <div class="okj-card okj-kpi-card okj-kpi-active">
            <div class="okj-kpi-icon"><span class="dashicons dashicons-yes-alt"></span></div>
            <div class="okj-kpi-content">
                <span class="okj-kpi-label">Produk Aktif</span>
                <h3 class="okj-kpi-value"><?php echo number_format_i18n($total_active); ?></h3>
            </div>
        </div>
        <div class="okj-card okj-kpi-card okj-kpi-expired">
            <div class="okj-kpi-icon"><span class="dashicons dashicons-warning"></span></div>
            <div class="okj-kpi-content">
                <span class="okj-kpi-label">Expired</span>
                <h3 class="okj-kpi-value"><?php echo number_format_i18n($total_expired); ?></h3>
            </div>
        </div>
        <div class="okj-card okj-kpi-card okj-kpi-income">
            <div class="okj-kpi-icon"><span class="dashicons dashicons-chart-line"></span></div>
            <div class="okj-kpi-content">
                <span class="okj-kpi-label">Total Pendapatan</span>
                <h3 class="okj-kpi-value">Rp <?php echo number_format_i18n($total_income, 0); ?></h3>
            </div>
        </div>
    </div>

    <!-- Chart Visualization Grid -->
    <div class="okj-grid okj-grid-2 okj-mt-2">
        <div class="okj-card">
            <div class="okj-card-header">
                <h2>Tren Pendapatan Bulanan</h2>
            </div>
            <div class="okj-card-body">
                <canvas id="wrpmRevenueChart" height="260"></canvas>
            </div>
        </div>
        <div class="okj-card">
            <div class="okj-card-header">
                <h2>Distribusi Status Produk</h2>
            </div>
            <div class="okj-card-body okj-center-chart">
                <canvas id="wrpmStatusChart" height="260" style="max-width: 260px;"></canvas>
            </div>
        </div>
    </div>

    <!-- Expiring Soon Tracker -->
    <div class="okj-card okj-mt-2">
        <div class="okj-card-header">
            <h2>Masa Aktif Akan Berakhir (7 Hari ke Depan)</h2>
        </div>
        <div class="okj-card-body">
            <?php if (empty($soon)): ?>
                <div class="okj-empty-state">
                    <span class="dashicons dashicons-info"></span>
                    <p>Semua produk aktif dalam kondisi aman. Tidak ada yang akan expired dalam 7 hari.</p>
                </div>
            <?php else: ?>
                <table class="okj-table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Customer</th>
                            <th>Tanggal Expired</th>
                            <th>Sisa Waktu</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($soon as $r): 
                            $diff = (int)floor((strtotime($r['expires_at']) - strtotime($today)) / DAY_IN_SECONDS);
                        ?>
                            <tr>
                                <td><strong><?php echo esc_html($r['product_label']); ?></strong></td>
                                <td><?php echo esc_html($r['customer_name']); ?></td>
                                <td><span class="dashicons dashicons-calendar-alt okj-text-muted"></span> <?php echo esc_html($r['expires_at']); ?></td>
                                <td>
                                    <?php if ($diff <= 1): ?>
                                        <span class="okj-badge okj-badge-danger"><?php echo $diff; ?> Hari Lagi</span>
                                    <?php elseif ($diff <= 3): ?>
                                        <span class="okj-badge okj-badge-warning"><?php echo $diff; ?> Hari Lagi</span>
                                    <?php else: ?>
                                        <span class="okj-badge okj-badge-success"><?php echo $diff; ?> Hari Lagi</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a class="okj-btn-link" href="<?php echo admin_url('admin.php?page=okj-active-products&action=edit&id=' . $r['id']); ?>">
                                        <span class="dashicons dashicons-edit"></span> Kelola
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$labels = [];
$values = [];
foreach ($revenue_monthly as $rm) {
    $labels[] = $rm['label'];
    $values[] = $rm['revenue'];
}
?>

<script>
jQuery(document).ready(function($) {
    // Revenue Chart
    var revCanvas = document.getElementById('wrpmRevenueChart');
    if (revCanvas) {
        new Chart(revCanvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    label: 'Pendapatan (IDR)',
                    data: <?php echo json_encode($values); ?>,
                    backgroundColor: 'rgba(99, 102, 241, 0.85)',
                    borderColor: 'rgba(99, 102, 241, 1)',
                    borderWidth: 1.5,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }

    // Status distribution
    var statusCanvas = document.getElementById('wrpmStatusChart');
    if (statusCanvas) {
        new Chart(statusCanvas.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Aktif', 'Expired'],
                datasets: [{
                    data: [<?php echo $total_active; ?>, <?php echo $total_expired; ?>],
                    backgroundColor: ['rgba(34, 197, 94, 0.85)', 'rgba(239, 68, 68, 0.85)'],
                    borderColor: ['#22c55e', '#ef4444'],
                    borderWidth: 1.5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }
});
</script>
