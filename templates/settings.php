<?php if (!defined('ABSPATH')) { exit; } ?>
<div class="okj-wrap">
    <div class="okj-header">
        <div>
            <h1>Pengaturan OKJualan</h1>
            <p class="okj-subtitle">Konfigurasikan payment gateway, notifikasi, branding struk &amp; invoice PDF, serta backup data.</p>
        </div>
    </div>

    <?php if (isset($_GET['updated'])): ?>
        <div class="notice notice-success is-dismissible okj-mb-2" style="margin: 0 0 20px 0; padding: 12px 16px; border-left-color: #10b981; background: #ecfdf5; color: #065f46; border-radius: 8px; border-left-width: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
            <p style="margin: 0; font-weight: 600;">Plugin OKJualan berhasil diperbarui ke versi terbaru dan database telah disinkronkan!</p>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['checked'])): ?>
        <div class="notice notice-info is-dismissible okj-mb-2" style="margin: 0 0 20px 0; padding: 12px 16px; border-left-color: #6366f1; background: #eff6ff; color: #1e40af; border-radius: 8px; border-left-width: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
            <p style="margin: 0; font-weight: 600;">Status rilis terbaru berhasil diperiksa dari GitHub.</p>
        </div>
    <?php endif; ?>
    <?php if (!empty($_GET['msg'])): ?>
        <div class="notice notice-info is-dismissible okj-mt-1" style="margin-left:0; padding:10px; border-left:4px solid #6366f1; background:#fff; box-shadow:0 1px 3px rgba(0,0,0,0.05); border-radius:4px;">
            <p style="margin:0; font-weight:500; color:#374151;"><?php echo esc_html(urldecode($_GET['msg'])); ?></p>
        </div>
    <?php endif; ?>

    <div class="okj-grid okj-grid-3 okj-mt-2">
        <!-- Settings Form Column -->
        <div class="okj-settings-main">
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <?php wp_nonce_field('okj_save_settings'); ?>
                <input type="hidden" name="action" value="okj_save_settings" />

                <!-- NAVIGATION TABS -->
                <div class="okj-tabs-wrapper" style="margin-bottom: 25px; border-bottom: 2px solid #e2e8f0;">
                    <ul class="okj-tabs-nav" style="display: flex; gap: 24px; list-style: none; margin: 0; padding: 0; flex-wrap: wrap;">
                        <li class="okj-tab-item active" data-tab="global" style="padding-bottom: 12px; margin-bottom: -2px; font-weight: 700; font-size: 15px; color: #4f46e5; border-bottom: 3px solid #4f46e5; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px;">
                            <span class="dashicons dashicons-admin-generic" style="font-size: 18px; width: 18px; height: 18px;"></span> Pengaturan Global &amp; Integrasi
                        </li>
                        <li class="okj-tab-item" data-tab="gateways" style="padding-bottom: 12px; margin-bottom: -2px; font-weight: 600; font-size: 15px; color: #64748b; border-bottom: 3px solid transparent; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px;">
                            <span class="dashicons dashicons-money-alt" style="font-size: 18px; width: 18px; height: 18px;"></span> Payment Gateway
                        </li>
                        <li class="okj-tab-item" data-tab="pos" style="padding-bottom: 12px; margin-bottom: -2px; font-weight: 600; font-size: 15px; color: #64748b; border-bottom: 3px solid transparent; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px;">
                            <span class="dashicons dashicons-calculator" style="font-size: 18px; width: 18px; height: 18px;"></span> Kasir POS
                        </li>
                        <li class="okj-tab-item" data-tab="support" style="padding-bottom: 12px; margin-bottom: -2px; font-weight: 600; font-size: 15px; color: #64748b; border-bottom: 3px solid transparent; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px;">
                            <span class="dashicons dashicons-format-chat" style="font-size: 18px; width: 18px; height: 18px;"></span> Dukungan Pelanggan
                        </li>
                    </ul>
                </div>

                <!-- TAB 1: GLOBAL SETTINGS -->
                <div class="okj-tab-content" id="okj-tab-global-content">
                    <!-- General Reminder Offsets -->
                <div class="okj-card">
                    <div class="okj-card-header">
                        <h2>Sistem Otomasi & Milestones</h2>
                    </div>
                    <div class="okj-card-body">
                        <div class="okj-form-group">
                            <label class="okj-label">Jarak Milestone H- (Hari, Pisahkan dengan koma)</label>
                            <input type="text" name="reminder_offsets" class="okj-input" value="<?php echo esc_attr(implode(',', !empty($settings['reminder_offsets']) ? $settings['reminder_offsets'] : [7,3,1])); ?>" placeholder="7,3,1" />
                            <small class="okj-text-muted">Interval waktu pengiriman reminder otomatis ke customer sebelum tanggal kadaluwarsa layanan.</small>
                        </div>
                        <div class="okj-form-group okj-mt-1">
                            <label class="okj-label">Waktu Cron Harian (WIB)</label>
                            <input type="time" name="cron_time" class="okj-input" value="<?php echo esc_attr(!empty($settings['cron_time']) ? $settings['cron_time'] : '08:00'); ?>" />
                        </div>
                    </div>
                </div>

                <!-- WAHA WhatsApp Gateway Config -->
                <div class="okj-card okj-mt-2">
                    <div class="okj-card-header">
                        <h2>WhatsApp Gateway (WAHA API)</h2>
                    </div>
                    <div class="okj-card-body">
                        <div class="okj-form-group">
                            <label class="okj-checkbox-label">
                                <input type="checkbox" name="waha_enabled" value="1" <?php checked(!empty($settings['waha_enabled']), 1); ?> /> Aktifkan WhatsApp Gateway (WAHA)
                            </label>
                        </div>
                        <div class="okj-form-group okj-mt-1">
                            <label class="okj-label">WAHA API URL</label>
                            <input type="url" name="waha_api_url" id="okj-waha-url" class="okj-input" value="<?php echo esc_attr(!empty($settings['waha_api_url']) ? $settings['waha_api_url'] : ''); ?>" placeholder="https://waga.honet.web.id" />
                        </div>
                        <div class="okj-form-group okj-mt-1">
                            <label class="okj-label">WAHA API Token (Bearer Authorization)</label>
                            <input type="password" name="waha_api_token" id="okj-waha-token" class="okj-input" value="<?php echo esc_attr(!empty($settings['waha_api_token']) ? $settings['waha_api_token'] : ''); ?>" />
                        </div>
                        <div class="okj-form-group okj-mt-1">
                            <label class="okj-label">Session Name (Default: default)</label>
                            <input type="text" name="waha_session_name" id="okj-waha-session" class="okj-input" value="<?php echo esc_attr(!empty($settings['waha_session_name']) ? $settings['waha_session_name'] : 'default'); ?>" />
                        </div>
                        
                        <!-- Test Connection Row -->
                        <div class="okj-mt-2" style="background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                            <div style="flex-grow: 1; min-width: 200px;">
                                <input type="text" id="okj-waha-test-phone" class="okj-input" style="height: 35px; font-size: 13px;" placeholder="Masukkan No HP Tes (Contoh: 08123...)" />
                            </div>
                            <div>
                                <button type="button" id="okj-btn-test-waha" class="okj-btn okj-btn-secondary" style="height: 35px; padding: 0 15px; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center;">
                                    <span class="dashicons dashicons-phone" style="margin-right: 5px; font-size: 16px; width: 16px; height: 16px;"></span> Test Kirim WA
                                </button>
                            </div>
                            <div id="okj-waha-test-status" style="font-size: 13px; font-weight: 600;"></div>
                        </div>
                    </div>
                </div>

                <!-- Telegram Integration -->
                <div class="okj-card okj-mt-2">
                    <div class="okj-card-header">
                        <h2>Telegram Bot Gateway</h2>
                    </div>
                    <div class="okj-card-body">
                        <div class="okj-form-group">
                            <label class="okj-checkbox-label">
                                <input type="checkbox" name="telegram_enabled" value="1" <?php checked(!empty($settings['telegram_enabled']), 1); ?> /> Aktifkan Telegram Notification
                            </label>
                        </div>
                        <div class="okj-form-group okj-mt-1">
                            <label class="okj-label">Telegram Bot Token</label>
                            <input type="password" name="telegram_bot_token" id="okj-tele-token" class="okj-input" value="<?php echo esc_attr(!empty($settings['telegram_bot_token']) ? $settings['telegram_bot_token'] : ''); ?>" />
                        </div>
                        <div class="okj-form-group okj-mt-1">
                            <label class="okj-label">Default Telegram Chat ID / Channel ID</label>
                            <input type="text" name="telegram_default_chat_id" id="okj-tele-chatid" class="okj-input" value="<?php echo esc_attr(!empty($settings['telegram_default_chat_id']) ? $settings['telegram_default_chat_id'] : ''); ?>" />
                        </div>
                        
                        <!-- Test Connection Row -->
                        <div class="okj-mt-2" style="background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                            <div>
                                <button type="button" id="okj-btn-test-telegram" class="okj-btn okj-btn-secondary" style="height: 35px; padding: 0 15px; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center;">
                                    <span class="dashicons dashicons-megaphone" style="margin-right: 5px; font-size: 16px; width: 16px; height: 16px;"></span> Test Kirim Telegram
                                </button>
                            </div>
                            <div id="okj-tele-test-status" style="font-size: 13px; font-weight: 600;"></div>
                        </div>
                    </div>
                </div>

                <!-- Email SMTP Configuration -->
                <div class="okj-card okj-mt-2">
                    <div class="okj-card-header">
                        <h2>SMTP Email Gateway</h2>
                    </div>
                    <div class="okj-card-body">
                        <div class="okj-form-group">
                            <label class="okj-checkbox-label">
                                <input type="checkbox" name="smtp_enabled" value="1" <?php checked(!empty($settings['smtp_enabled']), 1); ?> /> Aktifkan Pengiriman Email via SMTP Khusus
                            </label>
                        </div>
                        <div class="okj-form-grid okj-mt-1">
                            <div class="okj-form-group">
                                <label class="okj-label">SMTP Host</label>
                                <input type="text" name="smtp_host" id="okj-smtp-host" class="okj-input" value="<?php echo esc_attr(!empty($settings['smtp_host']) ? $settings['smtp_host'] : ''); ?>" />
                            </div>
                            <div class="okj-form-group">
                                <label class="okj-label">SMTP Port</label>
                                <input type="number" name="smtp_port" id="okj-smtp-port" class="okj-input" value="<?php echo esc_attr(!empty($settings['smtp_port']) ? $settings['smtp_port'] : '587'); ?>" />
                            </div>
                            <div class="okj-form-group">
                                <label class="okj-label">SMTP Username</label>
                                <input type="text" name="smtp_user" id="okj-smtp-user" class="okj-input" value="<?php echo esc_attr(!empty($settings['smtp_user']) ? $settings['smtp_user'] : ''); ?>" />
                            </div>
                            <div class="okj-form-group">
                                <label class="okj-label">SMTP Password</label>
                                <input type="password" name="smtp_pass" id="okj-smtp-pass" class="okj-input" value="<?php echo esc_attr(!empty($settings['smtp_pass']) ? $settings['smtp_pass'] : ''); ?>" />
                            </div>
                            <div class="okj-form-group">
                                <label class="okj-label">SMTP Secure</label>
                                <select name="smtp_secure" id="okj-smtp-secure" class="okj-select">
                                    <option value="tls" <?php echo !empty($settings['smtp_secure']) && $settings['smtp_secure'] === 'tls' ? 'selected' : ''; ?>>TLS (Rekomendasi)</option>
                                    <option value="ssl" <?php echo !empty($settings['smtp_secure']) && $settings['smtp_secure'] === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                    <option value="none" <?php echo !empty($settings['smtp_secure']) && $settings['smtp_secure'] === 'none' ? 'selected' : ''; ?>>None</option>
                                </select>
                            </div>
                            <div class="okj-form-group">
                                <label class="okj-label">Sender Email (From)</label>
                                <input type="email" name="smtp_from_email" id="okj-smtp-from-email" class="okj-input" value="<?php echo esc_attr(!empty($settings['smtp_from_email']) ? $settings['smtp_from_email'] : ''); ?>" />
                            </div>
                            <div class="okj-form-group">
                                <label class="okj-label">Sender Name</label>
                                <input type="text" name="smtp_from_name" id="okj-smtp-from-name" class="okj-input" value="<?php echo esc_attr(!empty($settings['smtp_from_name']) ? $settings['smtp_from_name'] : ''); ?>" />
                            </div>
                        </div>
                        
                        <!-- Test Connection Row -->
                        <div class="okj-mt-2" style="background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                            <div>
                                <button type="button" id="okj-btn-test-smtp" class="okj-btn okj-btn-secondary" style="height: 35px; padding: 0 15px; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center;">
                                    <span class="dashicons dashicons-email" style="margin-right: 5px; font-size: 16px; width: 16px; height: 16px;"></span> Test Kirim Email SMTP (Ke Sender Email)
                                </button>
                            </div>
                            <div id="okj-smtp-test-status" style="font-size: 13px; font-weight: 600;"></div>
                        </div>
                    </div>
                </div>

                <!-- Customizer Branding Invoice PDF -->
                <div class="okj-card okj-mt-2">
                    <div class="okj-card-header">
                        <h2>Kustomisasi Invoice PDF & Branding</h2>
                    </div>
                    <div class="okj-card-body">
                        <div class="okj-form-grid">
                            <div class="okj-form-group">
                                <label class="okj-label">Judul Invoice Dokumen</label>
                                <input type="text" name="pdf_invoice_title" class="okj-input" value="<?php echo esc_attr(!empty($settings['pdf_invoice_title']) ? $settings['pdf_invoice_title'] : 'INVOICE'); ?>" placeholder="INVOICE" />
                            </div>
                            <div class="okj-form-group">
                                <label class="okj-label">Warna Primer Invoice (HEX)</label>
                                <input type="color" name="pdf_primary_color" class="okj-input-color" value="<?php echo esc_attr(!empty($settings['pdf_primary_color']) ? $settings['pdf_primary_color'] : '#1e293b'); ?>" />
                            </div>
                            <div class="okj-form-group">
                                <label class="okj-label">Nama Perusahaan / Toko</label>
                                <input type="text" name="pdf_company_name" class="okj-input" value="<?php echo esc_attr(!empty($settings['pdf_company_name']) ? $settings['pdf_company_name'] : ''); ?>" />
                            </div>
                            <div class="okj-form-group">
                                <label class="okj-label">Alamat / Lokasi</label>
                                <input type="text" name="pdf_company_address" class="okj-input" value="<?php echo esc_attr(!empty($settings['pdf_company_address']) ? $settings['pdf_company_address'] : ''); ?>" />
                            </div>
                            <div class="okj-form-group">
                                <label class="okj-label">Kontak Support (Telp/WA)</label>
                                <input type="text" name="pdf_company_phone" class="okj-input" value="<?php echo esc_attr(!empty($settings['pdf_company_phone']) ? $settings['pdf_company_phone'] : ''); ?>" />
                            </div>
                        </div>
                        <div class="okj-form-group okj-mt-1">
                            <label class="okj-label">Instruksi & Detail Rekening Pembayaran</label>
                            <textarea name="pdf_payment_details" class="okj-input" rows="4"><?php echo esc_textarea(!empty($settings['pdf_payment_details']) ? $settings['pdf_payment_details'] : ''); ?></textarea>
                            <small class="okj-text-muted">Akan ditampilkan di bagian bawah invoice PDF cetak.</small>
                        </div>
                    </div>
                </div>

                <!-- Separation Milestone Reminder Templates -->
                <div class="okj-card okj-mt-2">
                    <div class="okj-card-header">
                        <h2>Template Notifikasi (Terpisah per Milestone)</h2>
                    </div>
                    <div class="okj-card-body">
                        <!-- Email template -->
                        <div class="okj-form-group">
                            <label class="okj-label">Subjek Email Reminder</label>
                            <input type="text" name="email_subject" class="okj-input" value="<?php echo esc_attr(!empty($settings['email_subject']) ? $settings['email_subject'] : '[Reminder] {product_label} akan expired'); ?>" />
                        </div>
                        <div class="okj-form-group okj-mt-1">
                            <label class="okj-label">Template Email Body</label>
                            <textarea name="email_template" class="okj-input" rows="4"><?php echo esc_textarea(!empty($settings['email_template']) ? $settings['email_template'] : ''); ?></textarea>
                        </div>

                        <!-- Telegram template -->
                        <div class="okj-form-group okj-mt-2">
                            <label class="okj-label">Template Telegram Message</label>
                            <textarea name="telegram_template" class="okj-input" rows="3"><?php echo esc_textarea(!empty($settings['telegram_template']) ? $settings['telegram_template'] : ''); ?></textarea>
                        </div>

                        <!-- WhatsApp General Template -->
                        <div class="okj-form-group okj-mt-2">
                            <label class="okj-label">Template WhatsApp (General)</label>
                            <textarea name="whatsapp_template" class="okj-input" rows="3"><?php echo esc_textarea(!empty($settings['whatsapp_template']) ? $settings['whatsapp_template'] : ''); ?></textarea>
                        </div>

                        <!-- Milestone H-7 WhatsApp Template -->
                        <div class="okj-form-group okj-mt-2">
                            <label class="okj-label">Template WhatsApp (Khusus H-7)</label>
                            <textarea name="whatsapp_template_h7" class="okj-input" rows="3"><?php echo esc_textarea(!empty($settings['whatsapp_template_h7']) ? $settings['whatsapp_template_h7'] : ''); ?></textarea>
                        </div>

                        <!-- Milestone H-3 WhatsApp Template -->
                        <div class="okj-form-group okj-mt-2">
                            <label class="okj-label">Template WhatsApp (Khusus H-3)</label>
                            <textarea name="whatsapp_template_h3" class="okj-input" rows="3"><?php echo esc_textarea(!empty($settings['whatsapp_template_h3']) ? $settings['whatsapp_template_h3'] : ''); ?></textarea>
                        </div>

                        <!-- Milestone H-1 WhatsApp Template -->
                        <div class="okj-form-group okj-mt-2">
                            <label class="okj-label">Template WhatsApp (Khusus H-1)</label>
                            <textarea name="whatsapp_template_h1" class="okj-input" rows="3"><?php echo esc_textarea(!empty($settings['whatsapp_template_h1']) ? $settings['whatsapp_template_h1'] : ''); ?></textarea>
                        </div>

                        <div class="okj-mt-1" style="display: flex; align-items: center; justify-content: space-between; background: #f8fafc; padding: 10px 15px; border-radius: 8px; border: 1px dashed #cbd5e1; flex-wrap: wrap; gap: 10px;">
                            <small class="okj-text-muted" style="margin: 0; font-weight: 500;">Variabel dasar yang didukung: <code>{customer_name}</code>, <code>{product_label}</code>, <code>{expires_at}</code>, <code>{price}</code>...</small>
                            <button type="button" class="okj-btn okj-btn-secondary okj-btn-small" id="okj-btn-show-shortcodes" style="padding: 6px 12px; font-size: 11px; display: inline-flex; align-items: center; font-weight: 600;">
                                <span class="dashicons dashicons-editor-help" style="font-size: 14px; width: 14px; height: 14px; margin-right: 4px; vertical-align: text-bottom;"></span> Lihat Semua Variabel (Shortcode)
                            </button>
                        </div>
                    </div>
                </div>

<!-- Modal Popup for Shortcodes -->
<div id="okj-shortcode-modal" class="okj-modal" style="display: none; position: fixed; z-index: 99999; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); align-items: center; justify-content: center;">
    <div class="okj-modal-content" style="background-color: #fff; margin: auto; padding: 25px; border-radius: 12px; max-width: 600px; width: 90%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); border: 1px solid #e2e8f0; animation: wrpmFadeIn 0.3s ease; position: relative;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
            <h3 style="margin: 0; font-size: 18px; color: #1e293b; font-weight: 700; display: flex; align-items: center; font-family: inherit;">
                <span class="dashicons dashicons-editor-code" style="margin-right: 8px; color: #6366f1; font-size: 20px; width: 20px; height: 20px;"></span>
                Daftar Lengkap Variabel / Shortcode Notifikasi
            </h3>
            <span id="okj-modal-close" style="color: #64748b; font-size: 24px; font-weight: bold; cursor: pointer; transition: color 0.2s; line-height: 1;" onmouseover="this.style.color='#0f172a'" onmouseout="this.style.color='#64748b'">&times;</span>
        </div>
        
        <p style="font-size: 13px; color: #64748b; margin-top: 0; margin-bottom: 15px; line-height: 1.5;">Gunakan shortcode di bawah ini pada template subjek email, isi email, pesan Telegram, atau template WhatsApp. <strong>Klik pada shortcode untuk menyalin secara cepat.</strong></p>
        
        <div style="max-height: 320px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 8px;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; margin: 0;">
                <thead>
                    <tr style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                        <th style="padding: 12px; font-weight: 600; color: #334155;">Shortcode</th>
                        <th style="padding: 12px; font-weight: 600; color: #334155;">Keterangan</th>
                        <th style="padding: 12px; font-weight: 600; color: #334155;">Contoh Output</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 10px 12px;"><code class="okj-copyable-code" style="cursor: pointer; background: #e0e7ff; color: #4f46e5; font-weight: 600; padding: 3px 6px; border-radius: 4px; font-size: 12px;" title="Klik untuk menyalin">{customer_name}</code></td>
                        <td style="padding: 10px 12px; color: #334155; font-weight: 500;">Nama customer</td>
                        <td style="padding: 10px 12px; color: #64748b; font-style: italic;">Yusha</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 10px 12px;"><code class="okj-copyable-code" style="cursor: pointer; background: #e0e7ff; color: #4f46e5; font-weight: 600; padding: 3px 6px; border-radius: 4px; font-size: 12px;" title="Klik untuk menyalin">{customer_email}</code></td>
                        <td style="padding: 10px 12px; color: #334155; font-weight: 500;">Email customer</td>
                        <td style="padding: 10px 12px; color: #64748b; font-style: italic;">yusha@example.com</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 10px 12px;"><code class="okj-copyable-code" style="cursor: pointer; background: #e0e7ff; color: #4f46e5; font-weight: 600; padding: 3px 6px; border-radius: 4px; font-size: 12px;" title="Klik untuk menyalin">{customer_phone}</code></td>
                        <td style="padding: 10px 12px; color: #334155; font-weight: 500;">Nomor HP/WhatsApp customer</td>
                        <td style="padding: 10px 12px; color: #64748b; font-style: italic;">08123456789</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 10px 12px;"><code class="okj-copyable-code" style="cursor: pointer; background: #e0e7ff; color: #4f46e5; font-weight: 600; padding: 3px 6px; border-radius: 4px; font-size: 12px;" title="Klik untuk menyalin">{customer_telegram}</code></td>
                        <td style="padding: 10px 12px; color: #334155; font-weight: 500;">Username Telegram customer</td>
                        <td style="padding: 10px 12px; color: #64748b; font-style: italic;">@yushamember</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 10px 12px;"><code class="okj-copyable-code" style="cursor: pointer; background: #e0e7ff; color: #4f46e5; font-weight: 600; padding: 3px 6px; border-radius: 4px; font-size: 12px;" title="Klik untuk menyalin">{customer_whatsapp}</code></td>
                        <td style="padding: 10px 12px; color: #334155; font-weight: 500;">WhatsApp customer terformat</td>
                        <td style="padding: 10px 12px; color: #64748b; font-style: italic;">628123456789</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 10px 12px;"><code class="okj-copyable-code" style="cursor: pointer; background: #e0e7ff; color: #4f46e5; font-weight: 600; padding: 3px 6px; border-radius: 4px; font-size: 12px;" title="Klik untuk menyalin">{product_label}</code></td>
                        <td style="padding: 10px 12px; color: #334155; font-weight: 500;">Label/Nama Layanan</td>
                        <td style="padding: 10px 12px; color: #64748b; font-style: italic;">VPS SG 8GB</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 10px 12px;"><code class="okj-copyable-code" style="cursor: pointer; background: #e0e7ff; color: #4f46e5; font-weight: 600; padding: 3px 6px; border-radius: 4px; font-size: 12px;" title="Klik untuk menyalin">{expires_at}</code></td>
                        <td style="padding: 10px 12px; color: #334155; font-weight: 500;">Tanggal kadaluwarsa layanan</td>
                        <td style="padding: 10px 12px; color: #64748b; font-style: italic;"><?php echo date_i18n(get_option('date_format'), time() + 7 * DAY_IN_SECONDS); ?></td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 10px 12px;"><code class="okj-copyable-code" style="cursor: pointer; background: #e0e7ff; color: #4f46e5; font-weight: 600; padding: 3px 6px; border-radius: 4px; font-size: 12px;" title="Klik untuk menyalin">{start_date}</code></td>
                        <td style="padding: 10px 12px; color: #334155; font-weight: 500;">Tanggal mulai aktif layanan</td>
                        <td style="padding: 10px 12px; color: #64748b; font-style: italic;"><?php echo date_i18n(get_option('date_format'), time()); ?></td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 10px 12px;"><code class="okj-copyable-code" style="cursor: pointer; background: #e0e7ff; color: #4f46e5; font-weight: 600; padding: 3px 6px; border-radius: 4px; font-size: 12px;" title="Klik untuk menyalin">{duration_days}</code></td>
                        <td style="padding: 10px 12px; color: #334155; font-weight: 500;">Durasi masa aktif layanan</td>
                        <td style="padding: 10px 12px; color: #64748b; font-style: italic;">30 Hari</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 10px 12px;"><code class="okj-copyable-code" style="cursor: pointer; background: #e0e7ff; color: #4f46e5; font-weight: 600; padding: 3px 6px; border-radius: 4px; font-size: 12px;" title="Klik untuk menyalin">{price}</code></td>
                        <td style="padding: 10px 12px; color: #334155; font-weight: 500;">Harga jual layanan</td>
                        <td style="padding: 10px 12px; color: #64748b; font-style: italic;">Rp 150,000</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 10px 12px;"><code class="okj-copyable-code" style="cursor: pointer; background: #e0e7ff; color: #4f46e5; font-weight: 600; padding: 3px 6px; border-radius: 4px; font-size: 12px;" title="Klik untuk menyalin">{remaining_days}</code></td>
                        <td style="padding: 10px 12px; color: #334155; font-weight: 500;">Sisa hari kadaluwarsa (milestone)</td>
                        <td style="padding: 10px 12px; color: #64748b; font-style: italic;">7 Hari</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 10px 12px;"><code class="okj-copyable-code" style="cursor: pointer; background: #e0e7ff; color: #4f46e5; font-weight: 600; padding: 3px 6px; border-radius: 4px; font-size: 12px;" title="Klik untuk menyalin">{notes}</code></td>
                        <td style="padding: 10px 12px; color: #334155; font-weight: 500;">Catatan layanan aktif</td>
                        <td style="padding: 10px 12px; color: #64748b; font-style: italic;">VPS OS Ubuntu 22.04</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 10px 12px;"><code class="okj-copyable-code" style="cursor: pointer; background: #e0e7ff; color: #4f46e5; font-weight: 600; padding: 3px 6px; border-radius: 4px; font-size: 12px;" title="Klik untuk menyalin">{invoice_url}</code></td>
                        <td style="padding: 10px 12px; color: #334155; font-weight: 500;">Link unduh PDF Invoice digital</td>
                        <td style="padding: 10px 12px; color: #64748b; font-style: italic; overflow-wrap: anywhere; max-width: 150px;">http://domain.com/...pdf</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 10px 12px;"><code class="okj-copyable-code" style="cursor: pointer; background: #e0e7ff; color: #4f46e5; font-weight: 600; padding: 3px 6px; border-radius: 4px; font-size: 12px;" title="Klik untuk menyalin">{company_name}</code></td>
                        <td style="padding: 10px 12px; color: #334155; font-weight: 500;">Nama perusahaan Anda (Branding)</td>
                        <td style="padding: 10px 12px; color: #64748b; font-style: italic;">HONET Labs</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 10px 12px;"><code class="okj-copyable-code" style="cursor: pointer; background: #e0e7ff; color: #4f46e5; font-weight: 600; padding: 3px 6px; border-radius: 4px; font-size: 12px;" title="Klik untuk menyalin">{company_address}</code></td>
                        <td style="padding: 10px 12px; color: #334155; font-weight: 500;">Alamat kantor/toko Anda</td>
                        <td style="padding: 10px 12px; color: #64748b; font-style: italic;">Jakarta, Indonesia</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 10px 12px;"><code class="okj-copyable-code" style="cursor: pointer; background: #e0e7ff; color: #4f46e5; font-weight: 600; padding: 3px 6px; border-radius: 4px; font-size: 12px;" title="Klik untuk menyalin">{company_phone}</code></td>
                        <td style="padding: 10px 12px; color: #334155; font-weight: 500;">No. HP Support CS</td>
                        <td style="padding: 10px 12px; color: #64748b; font-style: italic;">+62899999999</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 12px;"><code class="okj-copyable-code" style="cursor: pointer; background: #e0e7ff; color: #4f46e5; font-weight: 600; padding: 3px 6px; border-radius: 4px; font-size: 12px;" title="Klik untuk menyalin">{payment_details}</code></td>
                        <td style="padding: 10px 12px; color: #334155; font-weight: 500;">Detail Pembayaran/Rekening</td>
                        <td style="padding: 10px 12px; color: #64748b; font-style: italic;">Bank BCA 123456 a/n HONET</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div style="margin-top: 20px; display: flex; justify-content: flex-end;">
            <button type="button" class="okj-btn okj-btn-primary" id="okj-modal-close-btn" style="padding: 8px 20px; font-weight: 600;">Tutup</button>
        </div>
    </div>
</div>

<style>
@keyframes wrpmFadeIn {
    from { opacity: 0; transform: translateY(-12px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes okjPulse {
    0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
    70% { box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
    100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
}
</style>

<script>
jQuery(document).ready(function($) {
    var safeAjaxUrl = typeof ajaxurl !== 'undefined' ? ajaxurl.replace(/^http:/i, window.location.protocol) : '/wp-admin/admin-ajax.php';

    // Tabs Navigation Switcher
    $('.okj-tab-item').on('click', function() {
        var targetTab = $(this).data('tab');
        
        // Active nav state
        $('.okj-tab-item').removeClass('active').css({
            'color': '#64748b',
            'border-bottom-color': 'transparent',
            'font-weight': '600'
        });
        
        $(this).addClass('active').css({
            'color': '#4f46e5',
            'border-bottom-color': '#4f46e5',
            'font-weight': '700'
        });
        
        // Active content state
        $('.okj-tab-content').hide();
        $('#okj-tab-' + targetTab + '-content').fadeIn(200);
    });

    // Show Modal
    $('#okj-btn-show-shortcodes').on('click', function(e) {
        e.preventDefault();
        $('#okj-shortcode-modal').css('display', 'flex');
    });

    // Close Modal
    $('#okj-modal-close, #okj-modal-close-btn').on('click', function() {
        $('#okj-shortcode-modal').hide();
    });

    // Close on outer click
    $(window).on('click', function(e) {
        if ($(e.target).is('#okj-shortcode-modal')) {
            $('#okj-shortcode-modal').hide();
        }
    });

    // Click to Copy Shortcode
    $('.okj-copyable-code').on('click', function() {
        var code = $(this).text();
        var $el = $(this);
        navigator.clipboard.writeText(code).then(function() {
            var origColor = $el.css('color');
            var origBg = $el.css('background');
            
            $el.css({
                'color': '#fff',
                'background': '#10b981'
            }).attr('title', 'Tersalin!');
            
            setTimeout(function() {
                $el.css({
                    'color': origColor,
                    'background': origBg
                }).attr('title', 'Klik untuk menyalin');
            }, 1000);
        });
    });

    // Test WAHA Gateway
    $('#okj-btn-test-waha').on('click', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var $status = $('#okj-waha-test-status');
        var phone = $('#okj-waha-test-phone').val().trim();
        
        if (!phone) {
            $status.css('color', '#ef4444').text('Nomor HP wajib diisi untuk tes!');
            return;
        }

        $btn.prop('disabled', true).text('Mengirim...');
        $status.css('color', '#4b5563').text('Menghubungkan ke WAHA...');

        $.post(safeAjaxUrl, {
            action: 'okj_test_waha',
            waha_api_url: $('#okj-waha-url').val(),
            waha_api_token: $('#okj-waha-token').val(),
            waha_session_name: $('#okj-waha-session').val(),
            target_phone: phone
        }, function(resp) {
            $btn.prop('disabled', false).html('<span class="dashicons dashicons-phone" style="margin-right: 5px; font-size: 16px; width: 16px; height: 16px;"></span> Test Kirim WA');
            if (resp.success) {
                $status.css('color', '#10b981').text(resp.data.message);
            } else {
                $status.css('color', '#ef4444').text(resp.data.message);
            }
        }).fail(function() {
            $btn.prop('disabled', false).html('<span class="dashicons dashicons-phone" style="margin-right: 5px; font-size: 16px; width: 16px; height: 16px;"></span> Test Kirim WA');
            $status.css('color', '#ef4444').text('Terjadi error jaringan atau server.');
        });
    });

    // Test Telegram Bot
    $('#okj-btn-test-telegram').on('click', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var $status = $('#okj-tele-test-status');
        
        $btn.prop('disabled', true).text('Mengirim...');
        $status.css('color', '#4b5563').text('Menghubungkan ke Telegram...');

        $.post(safeAjaxUrl, {
            action: 'okj_test_telegram',
            telegram_bot_token: $('#okj-tele-token').val(),
            telegram_default_chat_id: $('#okj-tele-chatid').val()
        }, function(resp) {
            $btn.prop('disabled', false).html('<span class="dashicons dashicons-megaphone" style="margin-right: 5px; font-size: 16px; width: 16px; height: 16px;"></span> Test Kirim Telegram');
            if (resp.success) {
                $status.css('color', '#10b981').text(resp.data.message);
            } else {
                $status.css('color', '#ef4444').text(resp.data.message);
            }
        }).fail(function() {
            $btn.prop('disabled', false).html('<span class="dashicons dashicons-megaphone" style="margin-right: 5px; font-size: 16px; width: 16px; height: 16px;"></span> Test Kirim Telegram');
            $status.css('color', '#ef4444').text('Terjadi error jaringan atau server.');
        });
    });

    // Test SMTP Email
    $('#okj-btn-test-smtp').on('click', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var $status = $('#okj-smtp-test-status');
        
        $btn.prop('disabled', true).text('Mengirim...');
        $status.css('color', '#4b5563').text('Mengirim email uji coba...');

        $.post(safeAjaxUrl, {
            action: 'okj_test_smtp',
            smtp_host: $('#okj-smtp-host').val(),
            smtp_port: $('#okj-smtp-port').val(),
            smtp_user: $('#okj-smtp-user').val(),
            smtp_pass: $('#okj-smtp-pass').val(),
            smtp_secure: $('#okj-smtp-secure').val(),
            smtp_from_email: $('#okj-smtp-from-email').val(),
            smtp_from_name: $('#okj-smtp-from-name').val()
        }, function(resp) {
            $btn.prop('disabled', false).html('<span class="dashicons dashicons-email" style="margin-right: 5px; font-size: 16px; width: 16px; height: 16px;"></span> Test Kirim Email SMTP (Ke Sender Email)');
            if (resp.success) {
                $status.css('color', '#10b981').text(resp.data.message);
            } else {
                $status.css('color', '#ef4444').text(resp.data.message);
            }
        }).fail(function() {
            $btn.prop('disabled', false).html('<span class="dashicons dashicons-email" style="margin-right: 5px; font-size: 16px; width: 16px; height: 16px;"></span> Test Kirim Email SMTP (Ke Sender Email)');
            $status.css('color', '#ef4444').text('Terjadi error jaringan atau server.');
        });
    });
});
</script>

                <!-- GitHub Updater API Config -->
                <div class="okj-card okj-mt-2">
                    <div class="okj-card-header">
                        <h2>GitHub Auto-Updater</h2>
                    </div>
                    <div class="okj-card-body">
                        <div class="okj-form-group">
                            <label class="okj-label">Repositori GitHub (Format: username/repo)</label>
                            <input type="text" name="github_repo" class="okj-input" value="<?php echo esc_attr(!empty($settings['github_repo']) ? $settings['github_repo'] : ''); ?>" placeholder="honet-labs/OKJualan" />
                        </div>
                        <div class="okj-form-group okj-mt-1">
                            <label class="okj-label">Personal Access Token GitHub (Gunakan jika repositori private)</label>
                            <input type="password" name="github_token" class="okj-input" value="<?php echo esc_attr(!empty($settings['github_token']) ? $settings['github_token'] : ''); ?>" />
                        </div>

                        <!-- Status Deteksi Versi Otomatis -->
                        <?php
                        $installed_ver = OKJ_App::VERSION;
                        $latest_gh_ver = OKJ_Updater::get_latest_version_cached();
                        ?>
                        <div class="okj-form-group okj-mt-2" style="background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 1px dashed #cbd5e1; padding-bottom: 8px;">
                                <span style="font-weight: 700; color: #475569; font-size: 13px; display: inline-flex; align-items: center; gap: 4px;">
                                    <span class="dashicons dashicons-update" style="font-size: 16px; width: 16px; height: 16px; color: #4f46e5;"></span> Status Rilis & Deteksi Versi Otomatis
                                </span>
                                <a href="?page=okj-settings&check_release=1" class="okj-btn okj-btn-secondary okj-btn-small" style="padding: 4px 10px; font-size: 11px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; text-decoration: none;">
                                    <span class="dashicons dashicons-image-rotate" style="font-size: 13px; width: 13px; height: 13px; vertical-align: middle;"></span> Cek Rilis Baru
                                </a>
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 8px; font-size: 13px;">
                                <div style="display: flex; justify-content: space-between;">
                                    <span style="color: #64748b;">Versi Terpasang (Lokal):</span>
                                    <span style="font-weight: 700; color: #1e293b;">v<?php echo esc_html($installed_ver); ?></span>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="color: #64748b;">Rilis GitHub Terbaru:</span>
                                    <?php if ($latest_gh_ver === false): ?>
                                        <span style="font-weight: 600; color: #ef4444;">Belum dikonfigurasi</span>
                                    <?php elseif ($latest_gh_ver === 'unknown'): ?>
                                        <span style="font-weight: 600; color: #eab308; display: inline-flex; align-items: center; gap: 4px;">
                                            <span class="dashicons dashicons-warning" style="font-size: 16px; width: 16px; height: 16px;"></span> Gagal memuat (Periksa Repositori/Token Anda)
                                        </span>
                                    <?php else: ?>
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <span style="font-weight: 700; color: #0284c7; background: #e0f2fe; padding: 2px 8px; border-radius: 4px; font-family: monospace;">v<?php echo esc_html($latest_gh_ver); ?></span>
                                            <?php if (version_compare($installed_ver, $latest_gh_ver, '<')): ?>
                                                <span style="font-weight: 700; color: #ef4444; background: #fee2e2; padding: 2px 8px; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px; animation: okjPulse 2s infinite;">
                                                    <span class="dashicons dashicons-warning" style="font-size: 14px; width: 14px; height: 14px; color: #ef4444;"></span> Update Tersedia!
                                                </span>
                                            <?php else: ?>
                                                <span style="font-weight: 700; color: #15803d; background: #dcfce7; padding: 2px 8px; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px;">
                                                    <span class="dashicons dashicons-yes" style="font-size: 16px; width: 16px; height: 16px; color: #15803d;"></span> Up to Date
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- 1-Click Update Action Area -->
                            <?php if ($latest_gh_ver && $latest_gh_ver !== 'unknown' && version_compare($installed_ver, $latest_gh_ver, '<')): ?>
                                <div style="margin-top: 15px; padding: 16px; background: #eff6ff; border: 1.5px solid #bfdbfe; border-radius: 8px;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                                        <div>
                                            <strong style="color: #1e40af; font-size: 14px; display: block; margin-bottom: 2px;">Versi v<?php echo esc_html($latest_gh_ver); ?> telah tersedia untuk diinstal!</strong>
                                            <small style="color: #3b82f6;">Klik tombol di samping untuk mengunduh dan memperbarui plugin secara otomatis.</small>
                                        </div>
                                        <button type="button" id="okj-btn-trigger-update" class="okj-btn okj-btn-primary" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); padding: 9px 18px; font-size: 13px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);">
                                            <span class="dashicons dashicons-update" style="font-size: 16px; width: 16px; height: 16px;"></span> Perbarui Sekarang (1-Click)
                                        </button>
                                    </div>
                                    <div id="okj-update-progress-box" style="display: none; margin-top: 12px; padding: 12px 14px; background: #ffffff; border: 1px solid #93c5fd; border-radius: 6px;">
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <span class="okj-spinner" style="display: inline-block; border: 2.5px solid #e2e8f0; border-top: 2.5px solid #2563eb; border-radius: 50%; width: 18px; height: 18px; animation: wrpmSpin 1s linear infinite;"></span>
                                            <span id="okj-update-status-msg" style="font-size: 13px; font-weight: 600; color: #1e3a8a;">Sedang mengunduh dan memasang rilis terbaru dari GitHub...</span>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div> <!-- End #okj-tab-global-content -->

                <!-- TAB 2: POS SETTINGS -->
                <div class="okj-tab-content" id="okj-tab-pos-content" style="display: none;">
                    <!-- POS Specific Settings Card -->
                    <div class="okj-card">
                        <div class="okj-card-header">
                            <h2>Metode Pembayaran Mesin Kasir POS</h2>
                        </div>
                        <div class="okj-card-body">
                            <p class="okj-text-muted" style="margin-bottom: 20px;">Pilih metode pembayaran apa saja yang ingin Anda aktifkan saat kasir melakukan checkout pesanan di aplikasi POS.</p>
                            
                            <div class="okj-form-group" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                                <!-- Cash/Tunai -->
                                <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 15px; display: flex; align-items: flex-start; gap: 12px; transition: all 0.2s;">
                                    <input type="checkbox" name="pos_enable_cash" value="1" <?php checked(!isset($settings['pos_enable_cash']) || $settings['pos_enable_cash'] == 1, 1); ?> style="width: 20px; height: 20px; margin-top: 2px; cursor: pointer;" />
                                    <div>
                                        <label style="font-weight: 700; color: #1e293b; display: block; margin-bottom: 4px; font-size: 14px; cursor: pointer;">
                                            <span class="dashicons dashicons-money" style="color: #10b981; font-size: 18px; width: 18px; height: 18px; vertical-align: text-bottom; margin-right: 4px;"></span> Cash / Tunai
                                        </label>
                                        <small class="okj-text-muted" style="font-size: 11px;">Menerima pembayaran tunai langsung di toko/kasir.</small>
                                    </div>
                                </div>

                                <!-- Transfer Bank -->
                                <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 15px; display: flex; align-items: flex-start; gap: 12px; transition: all 0.2s;">
                                    <input type="checkbox" name="pos_enable_transfer" value="1" <?php checked(!isset($settings['pos_enable_transfer']) || $settings['pos_enable_transfer'] == 1, 1); ?> style="width: 20px; height: 20px; margin-top: 2px; cursor: pointer;" />
                                    <div>
                                        <label style="font-weight: 700; color: #1e293b; display: block; margin-bottom: 4px; font-size: 14px; cursor: pointer;">
                                            <span class="dashicons dashicons-bank" style="color: #3b82f6; font-size: 18px; width: 18px; height: 18px; vertical-align: text-bottom; margin-right: 4px;"></span> Transfer Bank
                                        </label>
                                        <small class="okj-text-muted" style="font-size: 11px;">Menerima pembayaran transfer bank manual.</small>
                                    </div>
                                </div>

                                <!-- QRIS / E-Wallet -->
                                <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 15px; display: flex; align-items: flex-start; gap: 12px; transition: all 0.2s;">
                                    <input type="checkbox" name="pos_enable_qris" value="1" <?php checked(!isset($settings['pos_enable_qris']) || $settings['pos_enable_qris'] == 1, 1); ?> style="width: 20px; height: 20px; margin-top: 2px; cursor: pointer;" />
                                    <div>
                                        <label style="font-weight: 700; color: #1e293b; display: block; margin-bottom: 4px; font-size: 14px; cursor: pointer;">
                                            <span class="dashicons dashicons-smartphone" style="color: #a855f7; font-size: 18px; width: 18px; height: 18px; vertical-align: text-bottom; margin-right: 4px;"></span> QRIS / E-Wallet
                                        </label>
                                        <small class="okj-text-muted" style="font-size: 11px;">Menerima scan barcode QRIS dan e-wallet digital.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> <!-- End #okj-tab-pos-content -->

                <!-- ======================================================================= -->
                <!-- TAB: PAYMENT GATEWAYS (SumoPod, Midtrans, Tripay, Manual, QRIS)        -->
                <!-- ======================================================================= -->
                <div class="okj-tab-content" id="okj-tab-gateways-content" style="display: none;">
                    <!-- SumoPod Payment Gateway -->
                    <div class="okj-card">
                        <div class="okj-card-header" style="background: linear-gradient(135deg, #059669 0%, #047857 100%); padding: 18px 20px; color: #ffffff;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <h2 style="color: #ffffff; margin: 0; font-size: 1.15rem; display: flex; align-items: center; gap: 8px;">
                                        <span class="dashicons dashicons-shield" style="font-size: 20px; width: 20px; height: 20px;"></span>
                                        SumoPod Payment Gateway (QRIS &amp; Otomatisasi)
                                    </h2>
                                    <p style="margin: 4px 0 0 0; color: #d1fae5; font-size: 12px;">Gateway pembayaran resmi SumoPod terintegrasi verifikasi webhook Svix signature (whsec_) &amp; token.</p>
                                </div>
                                <span class="okj-badge" style="background: #ffffff; color: #047857; font-weight: 700; font-size: 11px; padding: 4px 8px;">Rekomendasi</span>
                            </div>
                        </div>
                        <div class="okj-card-body">
                            <div class="okj-form-group">
                                <label class="okj-checkbox-label" style="font-weight: 700; font-size: 14px; color: #065f46;">
                                    <input type="checkbox" name="sumopod_enabled" value="1" <?php checked(!empty($settings['sumopod_enabled']), 1); ?> /> Aktifkan SumoPod Payment Gateway
                                </label>
                            </div>

                            <div style="background: #f0fdf4; border: 1.5px solid #bbf7d0; border-radius: 8px; padding: 14px; margin-top: 12px; margin-bottom: 16px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                    <label class="okj-label" style="font-size: 12px; font-weight: 700; color: #166534; margin: 0;">Webhook Notification URL (Salin ke Dashboard SumoPod)</label>
                                    <a href="<?php echo esc_url(home_url('/webhook')); ?>" target="_blank" style="font-size: 11.5px; color: #059669; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 3px;">
                                        <span class="dashicons dashicons-external" style="font-size: 14px; width: 14px; height: 14px;"></span> Uji Status Webhook di Browser
                                    </a>
                                </div>
                                <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 8px;">
                                    <input type="text" id="okj_sumopod_webhook_url" class="okj-input" value="<?php echo esc_url(home_url('/?okj_webhook=payment')); ?>" readonly style="background: #ffffff; font-family: monospace; font-size: 12.5px;" />
                                    <button type="button" class="okj-btn okj-btn-secondary" onclick="navigator.clipboard.writeText(document.getElementById('okj_sumopod_webhook_url').value); alert('URL Webhook berhasil disalin!');" style="white-space: nowrap;">
                                        Salin URL Utama
                                    </button>
                                </div>
                                <div style="display: flex; gap: 8px; align-items: center;">
                                    <input type="text" id="okj_sumopod_clean_url" class="okj-input" value="<?php echo esc_url(home_url('/webhook')); ?>" readonly style="background: #ffffff; font-family: monospace; font-size: 12.5px;" />
                                    <button type="button" class="okj-btn okj-btn-secondary" onclick="navigator.clipboard.writeText(document.getElementById('okj_sumopod_clean_url').value); alert('URL Webhook Alternatif berhasil disalin!');" style="white-space: nowrap;">
                                        Salin Clean URL
                                    </button>
                                </div>
                                <small style="color: #15803d; font-size: 11px; display: block; margin-top: 6px;">
                                    Kedua URL di atas aktif &amp; didukung. Event SumoPod: <code>payment.completed</code>, <code>payment.failed</code>, <code>payment.expired</code>, <code>payment.test</code>
                                </small>
                            </div>

                            <div class="okj-form-grid">
                                <div class="okj-form-group">
                                    <label class="okj-label">Environment Mode</label>
                                    <?php $sumo_mode = !empty($settings['sumopod_mode']) ? $settings['sumopod_mode'] : 'sandbox'; ?>
                                    <select name="sumopod_mode" class="okj-select" style="width: 100%;">
                                        <option value="sandbox" <?php selected($sumo_mode, 'sandbox'); ?>>🧪 Sandbox Mode (https://api-pay-sandbox.sumopod.com)</option>
                                        <option value="production" <?php selected($sumo_mode, 'production'); ?>>🚀 Production / Live (https://api-pay.sumopod.com)</option>
                                    </select>
                                </div>

                                <div class="okj-form-group">
                                    <label class="okj-label">Metode Pembayaran Default</label>
                                    <?php $sumo_method = !empty($settings['sumopod_default_method']) ? $settings['sumopod_default_method'] : 'qris'; ?>
                                    <select name="sumopod_default_method" class="okj-select" style="width: 100%;">
                                        <option value="qris" <?php selected($sumo_method, 'qris'); ?>>QRIS (Semua E-Wallet &amp; Mobile Banking)</option>
                                        <option value="va" <?php selected($sumo_method, 'va'); ?>>Virtual Account (BCA, Mandiri, BRI, BNI)</option>
                                    </select>
                                </div>

                                <div class="okj-form-group" style="grid-column: span 2;">
                                    <label class="okj-label">SumoPod API Key</label>
                                    <input type="password" name="sumopod_api_key" class="okj-input" value="<?php echo esc_attr(!empty($settings['sumopod_api_key']) ? $settings['sumopod_api_key'] : ''); ?>" placeholder="Masukkan API Key dari Dashboard SumoPod" />
                                </div>

                                <div class="okj-form-group">
                                    <label class="okj-label">Webhook Secret (Svix Signature)</label>
                                    <input type="password" name="sumopod_webhook_secret" class="okj-input" value="<?php echo esc_attr(!empty($settings['sumopod_webhook_secret']) ? $settings['sumopod_webhook_secret'] : ''); ?>" placeholder="whsec_..." />
                                    <small class="okj-text-muted">Digunakan untuk validasi signature HMAC-SHA256.</small>
                                </div>

                                <div class="okj-form-group">
                                    <label class="okj-label">Webhook Token (Opsi Cadangan)</label>
                                    <input type="password" name="sumopod_webhook_token" class="okj-input" value="<?php echo esc_attr(!empty($settings['sumopod_webhook_token']) ? $settings['sumopod_webhook_token'] : ''); ?>" placeholder="whtok_..." />
                                    <small class="okj-text-muted">Token header HTTP_X_WEBHOOK_TOKEN.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Midtrans Gateway -->
                    <div class="okj-card okj-mt-2">
                        <div class="okj-card-header">
                            <h2>Midtrans Payment Gateway</h2>
                        </div>
                        <div class="okj-card-body">
                            <div class="okj-form-group">
                                <label class="okj-checkbox-label">
                                    <input type="checkbox" name="midtrans_enabled" value="1" <?php checked(!empty($settings['midtrans_enabled']), 1); ?> /> Aktifkan Midtrans Snap Gateway
                                </label>
                            </div>
                            <div class="okj-form-grid okj-mt-1">
                                <div class="okj-form-group">
                                    <label class="okj-checkbox-label">
                                        <input type="checkbox" name="midtrans_is_production" value="1" <?php checked(!empty($settings['midtrans_is_production']), 1); ?> /> Mode Production (Live)
                                    </label>
                                </div>
                                <div class="okj-form-group"></div>
                                <div class="okj-form-group">
                                    <label class="okj-label">Server Key</label>
                                    <input type="password" name="midtrans_server_key" class="okj-input" value="<?php echo esc_attr(!empty($settings['midtrans_server_key']) ? $settings['midtrans_server_key'] : ''); ?>" />
                                </div>
                                <div class="okj-form-group">
                                    <label class="okj-label">Client Key</label>
                                    <input type="text" name="midtrans_client_key" class="okj-input" value="<?php echo esc_attr(!empty($settings['midtrans_client_key']) ? $settings['midtrans_client_key'] : ''); ?>" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tripay Gateway -->
                    <div class="okj-card okj-mt-2">
                        <div class="okj-card-header">
                            <h2>Tripay Payment Gateway</h2>
                        </div>
                        <div class="okj-card-body">
                            <div class="okj-form-group">
                                <label class="okj-checkbox-label">
                                    <input type="checkbox" name="tripay_enabled" value="1" <?php checked(!empty($settings['tripay_enabled']), 1); ?> /> Aktifkan Tripay Gateway
                                </label>
                            </div>
                            <div class="okj-form-grid okj-mt-1">
                                <div class="okj-form-group">
                                    <label class="okj-label">Mode Tripay</label>
                                    <?php $tripay_m = !empty($settings['tripay_mode']) ? $settings['tripay_mode'] : 'sandbox'; ?>
                                    <select name="tripay_mode" class="okj-select" style="width: 100%;">
                                        <option value="sandbox" <?php selected($tripay_m, 'sandbox'); ?>>Sandbox (Testing)</option>
                                        <option value="production" <?php selected($tripay_m, 'production'); ?>>Production (Live)</option>
                                    </select>
                                </div>
                                <div class="okj-form-group">
                                    <label class="okj-label">Kode Merchant</label>
                                    <input type="text" name="tripay_merchant_code" class="okj-input" value="<?php echo esc_attr(!empty($settings['tripay_merchant_code']) ? $settings['tripay_merchant_code'] : ''); ?>" />
                                </div>
                                <div class="okj-form-group">
                                    <label class="okj-label">API Key</label>
                                    <input type="password" name="tripay_api_key" class="okj-input" value="<?php echo esc_attr(!empty($settings['tripay_api_key']) ? $settings['tripay_api_key'] : ''); ?>" />
                                </div>
                                <div class="okj-form-group">
                                    <label class="okj-label">Private Key</label>
                                    <input type="password" name="tripay_private_key" class="okj-input" value="<?php echo esc_attr(!empty($settings['tripay_private_key']) ? $settings['tripay_private_key'] : ''); ?>" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Manual Bank Transfer & Static QRIS -->
                    <div class="okj-card okj-mt-2">
                        <div class="okj-card-header">
                            <h2>Transfer Bank Manual &amp; QRIS Statis Toko</h2>
                        </div>
                        <div class="okj-card-body">
                            <div class="okj-form-grid">
                                <div class="okj-form-group" style="grid-column: span 2;">
                                    <label class="okj-checkbox-label" style="font-weight: 700;">
                                        <input type="checkbox" name="manual_transfer_enabled" value="1" <?php checked(!empty($settings['manual_transfer_enabled']), 1); ?> /> Aktifkan Pembayaran Transfer Bank Manual
                                    </label>
                                </div>
                                <div class="okj-form-group">
                                    <label class="okj-label">Nama Bank</label>
                                    <input type="text" name="manual_bank_name" class="okj-input" value="<?php echo esc_attr(!empty($settings['manual_bank_name']) ? $settings['manual_bank_name'] : ''); ?>" placeholder="Contoh: BCA / Bank Mandiri" />
                                </div>
                                <div class="okj-form-group">
                                    <label class="okj-label">Nomor Rekening</label>
                                    <input type="text" name="manual_account_number" class="okj-input" value="<?php echo esc_attr(!empty($settings['manual_account_number']) ? $settings['manual_account_number'] : ''); ?>" placeholder="1234567890" />
                                </div>
                                <div class="okj-form-group" style="grid-column: span 2;">
                                    <label class="okj-label">Atas Nama Rekening</label>
                                    <input type="text" name="manual_account_holder" class="okj-input" value="<?php echo esc_attr(!empty($settings['manual_account_holder']) ? $settings['manual_account_holder'] : ''); ?>" placeholder="Nama pemilik rekening..." />
                                </div>

                                <div class="okj-form-group okj-mt-2" style="grid-column: span 2; border-top: 1px solid #f1f5f9; padding-top: 16px;">
                                    <label class="okj-checkbox-label" style="font-weight: 700;">
                                        <input type="checkbox" name="static_qris_enabled" value="1" <?php checked(!empty($settings['static_qris_enabled']), 1); ?> /> Aktifkan Scan QRIS Statis Toko
                                    </label>
                                </div>
                                <div class="okj-form-group" style="grid-column: span 2;">
                                    <label class="okj-label">URL Gambar Barcode QRIS Statis</label>
                                    <input type="url" name="static_qris_image_url" class="okj-input" value="<?php echo esc_url(!empty($settings['static_qris_image_url']) ? $settings['static_qris_image_url'] : ''); ?>" placeholder="https://domain.com/wp-content/uploads/qris.jpg" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div> <!-- End #okj-tab-gateways-content -->

                <!-- ======================================================================= -->
                <!-- TAB: DUKUNGAN PELANGGAN (Feature 10: WhatsApp, Email, FAQ)             -->
                <!-- ======================================================================= -->
                <div class="okj-tab-content" id="okj-tab-support-content" style="display: none;">
                    <!-- WhatsApp CS & Email Helpdesk -->
                    <div class="okj-card">
                        <div class="okj-card-header" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%); padding: 18px 20px; color: #ffffff;">
                            <h2 style="color: #ffffff; margin: 0; font-size: 1.15rem; display: flex; align-items: center; gap: 8px;">
                                <span class="dashicons dashicons-format-chat" style="font-size: 20px; width: 20px; height: 20px;"></span>
                                Kanal Dukungan Pelanggan (Helpdesk &amp; Live Chat)
                            </h2>
                            <p style="margin: 4px 0 0 0; color: #c7d2fe; font-size: 12px;">Konfigurasi nomor WhatsApp CS interaktif dan alamat email bantuan untuk pelanggan Anda.</p>
                        </div>
                        <div class="okj-card-body">
                            <div class="okj-form-grid">
                                <div class="okj-form-group">
                                    <label class="okj-label">Nomor WhatsApp Customer Service <span class="okj-required">*</span></label>
                                    <input type="text" name="support_wa_number" class="okj-input" value="<?php echo esc_attr(!empty($settings['support_wa_number']) ? $settings['support_wa_number'] : ''); ?>" placeholder="Contoh: 628123456789" />
                                    <small class="okj-text-muted">Tombol WhatsApp mengambang akan otomatis mengarahkan chat ke nomor ini.</small>
                                </div>

                                <div class="okj-form-group">
                                    <label class="okj-label">Email Helpdesk / Dukungan</label>
                                    <input type="email" name="support_email" class="okj-input" value="<?php echo esc_attr(!empty($settings['support_email']) ? $settings['support_email'] : ''); ?>" placeholder="support@okjualan.com" />
                                </div>

                                <div class="okj-form-group" style="grid-column: span 2;">
                                    <label class="okj-label">Pesan Sapaan Otomatis WhatsApp</label>
                                    <textarea name="support_wa_greeting" class="okj-input" rows="2" placeholder="Halo Admin OKJualan, saya memerlukan bantuan terkait pemesanan produk..."><?php echo !empty($settings['support_wa_greeting']) ? esc_textarea($settings['support_wa_greeting']) : 'Halo Admin OKJualan, saya ingin bantuan seputar pesanan saya.'; ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Management -->
                    <div class="okj-card okj-mt-2">
                        <div class="okj-card-header">
                            <h2>Kelola FAQ (Pertanyaan yang Sering Diajukan)</h2>
                        </div>
                        <div class="okj-card-body">
                            <div class="okj-form-group">
                                <label class="okj-label">Data FAQ JSON (Pertanyaan &amp; Jawaban)</label>
                                <?php
                                $default_faqs = [
                                    ["q" => "Bagaimana cara melakukan pembayaran?", "a" => "Pembayaran dapat dilakukan melalui scan QRIS (SumoPod/Statis) dari aplikasi e-wallet / m-banking, atau transfer bank manual."],
                                    ["q" => "Kapan produk/layanan saya aktif?", "a" => "Pesanan yang dibayar via QRIS otomatis akan langsung aktif. Untuk transfer manual akan diverifikasi oleh admin maksimal 10 menit."],
                                    ["q" => "Bagaimana cara memperpanjang masa aktif?", "a" => "Anda dapat menghubungi CS kami atau memperbarui masa aktif langsung sebelum jatuh tempo dari link pelacakan pesanan Anda."]
                                ];
                                $faq_val = !empty($settings['support_faq_json']) ? $settings['support_faq_json'] : wp_json_encode($default_faqs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                                ?>
                                <textarea name="support_faq_json" class="okj-input" rows="8" style="font-family: monospace; font-size: 12px;"><?php echo esc_textarea($faq_val); ?></textarea>
                                <small class="okj-text-muted">Format JSON array objek dengan properti <code>"q"</code> (Pertanyaan) dan <code>"a"</code> (Jawaban). FAQ ini akan ditampilkan di halaman Dukungan Pelanggan.</small>
                            </div>
                        </div>
                    </div>
                </div> <!-- End #okj-tab-support-content -->

                <div class="okj-form-actions okj-mt-2">
                    <button type="submit" class="okj-btn okj-btn-primary">Simpan Semua Pengaturan</button>
                </div>
            </form>
        </div>

        <!-- Sidebar Actions Column (Backup & Restore) -->
        <div>
            <!-- JSON Backup Card -->
            <div class="okj-card">
                <div class="okj-card-header">
                    <h2>Ekspor Data Backup JSON</h2>
                </div>
                <div class="okj-card-body">
                    <p class="okj-text-muted" style="margin-bottom:15px;">Ekspor seluruh basis data master harga, reseller product, customer, active product, reminder, logs, dan pengaturan plugin ke dalam 1 file JSON.</p>
                    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                        <?php wp_nonce_field('okj_backup_data'); ?>
                        <input type="hidden" name="action" value="okj_backup_data" />
                        <button type="submit" class="okj-btn okj-btn-primary" style="width: 100%;">
                            <span class="dashicons dashicons-download"></span> Ekspor Data (JSON)
                        </button>
                    </form>
                </div>
            </div>

            <!-- JSON Restore Card -->
            <div class="okj-card okj-mt-2">
                <div class="okj-card-header">
                    <h2>Impor Data & Restorasi</h2>
                </div>
                <div class="okj-card-body">
                    <p class="okj-text-muted" style="margin-bottom:15px;">Unggah file backup JSON yang sebelumnya diekspor untuk melakukan restorasi database secara cepat.</p>
                    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" enctype="multipart/form-data">
                        <?php wp_nonce_field('okj_restore_data'); ?>
                        <input type="hidden" name="action" value="okj_restore_data" />
                        <div class="okj-form-group">
                            <input type="file" name="restore_file" accept=".json" required />
                        </div>
                        <button type="submit" class="okj-btn okj-btn-secondary" style="width: 100%; margin-top:15px;" onclick="return confirm('PENTING: Mengimpor backup akan mengosongkan dan menimpa database aktif saat ini. Lanjutkan?');">
                            <span class="dashicons dashicons-upload"></span> Mulai Impor & Restorasi
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
