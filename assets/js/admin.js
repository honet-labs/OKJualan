jQuery(document).ready(function($) {
    var safeAjaxUrl = typeof ajaxurl !== 'undefined' ? ajaxurl.replace(/^http:/i, window.location.protocol) : '/wp-admin/admin-ajax.php';

    // Initialize Select2 on all searchable select elements
    if ($.fn.select2) {
        $('.okj-select2').select2({
            placeholder: '-- Pilih --',
            allowClear: true,
            width: 'resolve'
        });

        $('.okj-select2-tags').select2({
            placeholder: 'Pilih atau ketik tag baru...',
            tags: true,
            tokenSeparators: [','],
            width: 'resolve'
        });

        $('.okj-select2-category').select2({
            placeholder: 'Pilih atau ketik kategori baru...',
            tags: true,
            allowClear: true,
            width: 'resolve'
        });
    }

    // Dynamic field calculations (e.g. autofilling dates, prices, etc)
    // When selecting master product reference, auto-fill the Seller dropdown and Duration field
    $('select[name="price_id"]').on('change', function() {
        var selectedOpt = $(this).find(':selected');
        var seller_id = selectedOpt.data('seller-id');
        var duration = selectedOpt.data('duration');

        if (seller_id) {
            $('select[name="seller_id"]').val(seller_id).trigger('change');
        } else {
            $('select[name="seller_id"]').val('').trigger('change');
        }

        if (duration !== undefined && duration !== null && duration !== '') {
            $('input[name="duration_days"]').val(duration).trigger('change');
        }
    });

    // When selecting related reseller product, auto-fill the Start Date field with reseller's purchase date
    $('select[name="reseller_product_id"]').on('change', function() {
        var selectedOpt = $(this).find(':selected');
        var purchaseDate = selectedOpt.data('purchase-date');

        if (purchaseDate) {
            $('input[name="start_date"]').val(purchaseDate).trigger('change');
        }
    });

    // Calculate and auto-fill Expiration Date based on Purchase Date + Duration
    function calculateExpiryDate() {
        var purchaseDateStr = $('input[name="purchase_date"]').val();
        var duration = parseInt($('input[name="duration_days"]').val());
        if (purchaseDateStr && !isNaN(duration) && duration >= 0) {
            purchaseDateStr = purchaseDateStr.replace(/\//g, '-');
            var parts = purchaseDateStr.split('-');
            if (parts.length === 3) {
                var year = parseInt(parts[0], 10);
                var month = parseInt(parts[1], 10) - 1; // 0-indexed
                var day = parseInt(parts[2], 10);
                var date = new Date(year, month, day);
                date.setDate(date.getDate() + duration);
                var y = date.getFullYear();
                var m = String(date.getMonth() + 1).padStart(2, '0');
                var d = String(date.getDate()).padStart(2, '0');
                $('input[name="expires_at"]').val(y + '-' + m + '-' + d);
            }
        }
    }

    $('input[name="purchase_date"], input[name="duration_days"]').on('input change', function() {
        calculateExpiryDate();
    });

    // Trigger on page load for existing edit data
    calculateExpiryDate();

    // Live table search filtering
    $('.okj-table-search').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $(this).closest('.okj-card-body').find('.okj-table tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    // Modal display logic for product details
    $('.okj-view-detail').on('click', function(e) {
        e.preventDefault();
        var name = $(this).data('name');
        var desc = $(this).data('description') || '<i>Tidak ada deskripsi.</i>';
        var notes = $(this).data('notes') || '<i>Tidak ada catatan tambahan.</i>';

        $('#wrpmModalTitle').text(name);
        $('#wrpmModalDescription').html(desc);
        $('#wrpmModalNotes').html(notes);

        $('#wrpmDetailModal').css('display', 'flex');
    });

    $('.okj-modal-close, .okj-modal-close-btn').on('click', function() {
        $('#wrpmDetailModal').css('display', 'none');
    });

    // Modal display logic for seller details
    $('.okj-view-seller-detail').on('click', function(e) {
        e.preventDefault();
        var name = $(this).data('name');
        var email = $(this).data('email') || '-';
        var phone = $(this).data('phone') || '-';
        var telegram = $(this).data('telegram') || '-';
        var whatsapp = $(this).data('whatsapp') || '-';

        $('#wrpmSellerName').text(name);
        $('#wrpmSellerEmail').text(email);
        $('#wrpmSellerPhone').text(phone);
        $('#wrpmSellerTelegram').text(telegram);
        $('#wrpmSellerWhatsapp').text(whatsapp);

        $('#wrpmSellerModal').css('display', 'flex');
    });

    $('.okj-seller-modal-close, .okj-seller-modal-close-btn').on('click', function() {
        $('#wrpmSellerModal').css('display', 'none');
    });

    // Modal display logic for customer details
    $('.okj-view-customer-detail').on('click', function(e) {
        e.preventDefault();
        var name = $(this).data('name');
        var email = $(this).data('email') || '-';
        var phone = $(this).data('phone') || '-';
        var telegram = $(this).data('telegram') || '-';
        var whatsapp = $(this).data('whatsapp') || '-';

        $('#wrpmCustomerName').text(name);
        $('#wrpmCustomerEmail').text(email);
        $('#wrpmCustomerPhone').text(phone);
        $('#wrpmCustomerTelegram').text(telegram);
        $('#wrpmCustomerWhatsapp').text(whatsapp);

        $('#wrpmCustomerModal').css('display', 'flex');
    });

    $('.okj-customer-modal-close, .okj-customer-modal-close-btn').on('click', function() {
        $('#wrpmCustomerModal').css('display', 'none');
    });

    // Modal display logic for payment attachment details
    $('.okj-view-payment-proof').on('click', function(e) {
        e.preventDefault();
        var url = $(this).data('url');
        $('#wrpmAttachmentImg').attr('src', url);
        $('#wrpmAttachmentDownloadBtn').attr('href', url);
        $('#wrpmAttachmentModal').css('display', 'flex');
    });

    $('.okj-attachment-modal-close, .okj-attachment-modal-close-btn').on('click', function() {
        $('#wrpmAttachmentModal').css('display', 'none');
    });

    // Close modals when clicking outside of them
    $(window).on('click', function(e) {
        if ($(e.target).is('#wrpmDetailModal')) {
            $('#wrpmDetailModal').css('display', 'none');
        }
        if ($(e.target).is('#wrpmSellerModal')) {
            $('#wrpmSellerModal').css('display', 'none');
        }
        if ($(e.target).is('#wrpmCustomerModal')) {
            $('#wrpmCustomerModal').css('display', 'none');
        }
        if ($(e.target).is('#wrpmAttachmentModal')) {
            $('#wrpmAttachmentModal').css('display', 'none');
        }
        if ($(e.target).is('#wrpmQuickAddSellerModal')) {
            $('#wrpmQuickAddSellerModal').css('display', 'none');
        }
        if ($(e.target).is('#wrpmQuickAddCustomerModal')) {
            $('#wrpmQuickAddCustomerModal').css('display', 'none');
        }
        if ($(e.target).is('#wrpmActiveNotesModal')) {
            $('#wrpmActiveNotesModal').css('display', 'none');
        }
        if ($(e.target).is('#okjRenewProductModal')) {
            $('#okjRenewProductModal').css('display', 'none');
        }
        if ($(e.target).is('#okjRenewalHistoryModal')) {
            $('#okjRenewalHistoryModal').css('display', 'none');
        }
    });

    // ==========================================
    // QUICK ADD SELLER AND CUSTOMER MODALS
    // ==========================================

    // Quick Add Seller Modal triggers
    $('.okj-quick-add-seller-btn').on('click', function(e) {
        e.preventDefault();
        $('#wrpmQuickAddSellerModal').css('display', 'flex');
        $('#wrpmQuickSellerName').focus();
    });

    $('.okj-quick-seller-close, .okj-quick-seller-close-btn').on('click', function() {
        $('#wrpmQuickAddSellerModal').css('display', 'none');
        // Clear fields
        $('#wrpmQuickSellerName').val('');
        $('#wrpmQuickSellerWhatsapp').val('');
        $('#wrpmQuickSellerEmail').val('');
    });

    $('#wrpmQuickSellerSubmitBtn').on('click', function(e) {
        e.preventDefault();
        var name = $('#wrpmQuickSellerName').val().trim();
        var whatsapp = $('#wrpmQuickSellerWhatsapp').val().trim();
        var email = $('#wrpmQuickSellerEmail').val().trim();

        if (!name) {
            alert('Nama Seller tidak boleh kosong.');
            return;
        }

        var $btn = $(this);
        var $spinner = $btn.find('.okj-spinner');

        $btn.prop('disabled', true);
        $spinner.show();

        $.post(safeAjaxUrl, {
            action: 'okj_quick_add_seller',
            name: name,
            whatsapp: whatsapp,
            email: email
        }, function(response) {
            $btn.prop('disabled', false);
            $spinner.hide();

            if (response.success) {
                var newId = response.data.id;
                var newName = response.data.name;

                // Add to seller_id select options
                var newOption = new Option(newName, newId, true, true);
                $('select[name="seller_id"]').append(newOption).trigger('change');

                // Close and clear modal
                $('#wrpmQuickAddSellerModal').css('display', 'none');
                $('#wrpmQuickSellerName').val('');
                $('#wrpmQuickSellerWhatsapp').val('');
                $('#wrpmQuickSellerEmail').val('');
            } else {
                alert(response.data.message || 'Terjadi kesalahan saat menyimpan Seller.');
            }
        }).fail(function() {
            $btn.prop('disabled', false);
            $spinner.hide();
            alert('Terjadi kesalahan jaringan.');
        });
    });

    // Quick Add Customer Modal triggers
    $('.okj-quick-add-customer-btn').on('click', function(e) {
        e.preventDefault();
        $('#wrpmQuickAddCustomerModal').css('display', 'flex');
        $('#wrpmQuickCustomerName').focus();
    });

    $('.okj-quick-customer-close, .okj-quick-customer-close-btn').on('click', function() {
        $('#wrpmQuickAddCustomerModal').css('display', 'none');
        // Clear fields
        $('#wrpmQuickCustomerName').val('');
        $('#wrpmQuickCustomerWhatsapp').val('');
        $('#wrpmQuickCustomerEmail').val('');
    });

    $('#wrpmQuickCustomerSubmitBtn').on('click', function(e) {
        e.preventDefault();
        var name = $('#wrpmQuickCustomerName').val().trim();
        var whatsapp = $('#wrpmQuickCustomerWhatsapp').val().trim();
        var email = $('#wrpmQuickCustomerEmail').val().trim();

        if (!name) {
            alert('Nama Customer tidak boleh kosong.');
            return;
        }

        var $btn = $(this);
        var $spinner = $btn.find('.okj-spinner');

        $btn.prop('disabled', true);
        $spinner.show();

        $.post(safeAjaxUrl, {
            action: 'okj_quick_add_customer',
            name: name,
            whatsapp: whatsapp,
            email: email
        }, function(response) {
            $btn.prop('disabled', false);
            $spinner.hide();

            if (response.success) {
                var newId = response.data.id;
                var newName = response.data.name;

                // Add to customer_id select options
                var newOption = new Option(newName, newId, true, true);
                $('select[name="customer_id"]').append(newOption).trigger('change');

                // Close and clear modal
                $('#wrpmQuickAddCustomerModal').css('display', 'none');
                $('#wrpmQuickCustomerName').val('');
                $('#wrpmQuickCustomerWhatsapp').val('');
                $('#wrpmQuickCustomerEmail').val('');
            } else {
                alert(response.data.message || 'Terjadi kesalahan saat menyimpan Customer.');
            }
        }).fail(function() {
            $btn.prop('disabled', false);
            $spinner.hide();
            alert('Terjadi kesalahan jaringan.');
        });
    });

    // Active Product Notes Modal triggers
    $('.okj-view-active-notes').on('click', function(e) {
        e.preventDefault();
        var name = $(this).data('name');
        var notes = $(this).data('notes') || 'Tidak ada catatan layanan.';
        
        $('#wrpmActiveNotesTitle').text(name);
        $('#wrpmActiveNotesContent').text(notes);
        $('#wrpmActiveNotesModal').css('display', 'flex');
    });

    $('.okj-active-notes-close, .okj-active-notes-close-btn').on('click', function() {
        $('#wrpmActiveNotesModal').css('display', 'none');
    });

    // Renewal Modal logic
    $('.okj-renew-product-btn').on('click', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var name = $(this).data('name');
        var expiry = $(this).data('expiry');
        var price = $(this).data('price');
        
        $('#okj_renew_product_id').val(id);
        $('#okj_renew_product_name').text(name);
        $('#okj_renew_old_expiry').text(expiry);
        $('#okj_renew_option_old_expiry').text('Masa Aktif Habis Lama (' + expiry + ')');
        $('#okj_renew_price').val(price);
        
        $('#okjRenewProductModal').css('display', 'flex');
    });

    $('.okj-renew-modal-close, .okj-renew-modal-close-btn').on('click', function() {
        $('#okjRenewProductModal').css('display', 'none');
    });

    // Renewal History Modal logic
    $('.okj-renewal-history-btn').on('click', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        
        $('#okj_history_content').html('<div style="text-align:center; padding: 30px;"><span class="okj-spinner" style="display:inline-block; border: 3px solid #e2e8f0; border-top: 3px solid #4f46e5; border-radius: 50%; width: 24px; height: 24px; animation: wrpmSpin 1s linear infinite;"></span><p style="margin-top:10px; color:#64748b;">Memuat riwayat...</p></div>');
        $('#okjRenewalHistoryModal').css('display', 'flex');
        
        $.get(safeAjaxUrl, {
            action: 'okj_get_renewal_history',
            active_product_id: id
        }, function(response) {
            if (response.success) {
                $('#okj_history_content').html(response.data.html);
            } else {
                $('#okj_history_content').html('<div class="okj-empty-state"><p>' + (response.data.message || 'Gagal memuat data.') + '</p></div>');
            }
        }).fail(function() {
            $('#okj_history_content').html('<div class="okj-empty-state"><p>Terjadi kesalahan jaringan.</p></div>');
        });
    });

    $('.okj-history-modal-close, .okj-history-modal-close-btn').on('click', function() {
        $('#okjRenewalHistoryModal').css('display', 'none');
    });

    // 1-Click Update Handler
    $('#okj-btn-trigger-update').on('click', function(e) {
        e.preventDefault();
        if (!confirm('Apakah Anda yakin ingin memperbarui plugin OKJualin sekarang? Berkas plugin dan skema database akan disinkronkan ke rilis terbaru secara otomatis.')) {
            return;
        }

        var $btn = $(this);
        var $box = $('#okj-update-progress-box');
        var $msg = $('#okj-update-status-msg');

        $btn.prop('disabled', true).css('opacity', '0.6');
        $box.slideDown(200);
        $msg.text('Menghubungi GitHub dan mengunduh paket pembaruan...');

        var nonce = typeof okjAdmin !== 'undefined' ? okjAdmin.nonce : '';

        $.post(safeAjaxUrl, {
            action: 'okj_trigger_1click_update',
            nonce: nonce
        }, function(response) {
            if (response.success) {
                $msg.css('color', '#15803d').html('✅ ' + (response.data.message || 'Pembaruan berhasil! Memuat ulang halaman...'));
                setTimeout(function() {
                    window.location.href = window.location.pathname + '?page=okj-settings&updated=1';
                }, 1500);
            } else {
                $btn.prop('disabled', false).css('opacity', '1');
                $msg.css('color', '#ef4444').html('❌ Gagal: ' + (response.data.message || 'Terjadi kesalahan saat memperbarui.'));
            }
        }).fail(function() {
            $btn.prop('disabled', false).css('opacity', '1');
            $msg.css('color', '#ef4444').html('❌ Terjadi kesalahan jaringan saat mencoba memperbarui.');
        });
    });

    // WooCommerce Bulk Sync Handler
    $('#okjSyncAllWcBtn').on('click', function(e) {
        e.preventDefault();
        if (!confirm('Sinkronkan seluruh master harga produk ke katalog WooCommerce sekarang?')) {
            return;
        }

        var $btn = $(this);
        var $icon = $btn.find('.okj-sync-wc-spinner');
        var originalText = $btn.find('span:last').text();

        $btn.prop('disabled', true).css('opacity', '0.7');
        $icon.addClass('okj-spin-animation');
        $btn.find('span:last').text('Mensinkronkan...');

        var nonce = typeof okjAdmin !== 'undefined' ? okjAdmin.nonce : '';

        $.post(safeAjaxUrl, {
            action: 'okj_sync_woocommerce_all',
            nonce: nonce
        }, function(response) {
            $icon.removeClass('okj-spin-animation');
            if (response.success) {
                var d = response.data;
                alert('✅ Berhasil mensinkronkan ' + d.synced + ' produk ke WooCommerce (' + d.created + ' baru, ' + d.updated + ' diperbarui).');
                window.location.reload();
            } else {
                $btn.prop('disabled', false).css('opacity', '1');
                $btn.find('span:last').text(originalText);
                alert('❌ Gagal sinkronisasi: ' + (response.data ? response.data.message : 'Unknown error'));
            }
        }).fail(function() {
            $btn.prop('disabled', false).css('opacity', '1');
            $icon.removeClass('okj-spin-animation');
            $btn.find('span:last').text(originalText);
            alert('❌ Terjadi kesalahan jaringan saat mensinkronkan ke WooCommerce.');
        });
    });

    // WooCommerce Single Product Sync Handler
    $('.okj-sync-single-wc').on('click', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var priceId = $btn.data('id');
        var priceName = $btn.data('name');

        if (!priceId) return;

        var $icon = $btn.find('.dashicons');
        $icon.addClass('okj-spin-animation');
        $btn.css('pointer-events', 'none');

        var nonce = typeof okjAdmin !== 'undefined' ? okjAdmin.nonce : '';

        $.post(safeAjaxUrl, {
            action: 'okj_sync_woocommerce_single',
            price_id: priceId,
            nonce: nonce
        }, function(response) {
            $icon.removeClass('okj-spin-animation');
            $btn.css('pointer-events', 'auto');

            if (response.success) {
                alert('✅ Produk "' + priceName + '" berhasil disinkronkan ke WooCommerce (Product ID #' + response.data.wc_product_id + ').');
                window.location.reload();
            } else {
                alert('❌ Gagal sinkronisasi: ' + (response.data ? response.data.message : 'Unknown error'));
            }
        }).fail(function() {
            $icon.removeClass('okj-spin-animation');
            $btn.css('pointer-events', 'auto');
            alert('❌ Terjadi kesalahan jaringan saat mensinkronkan produk.');
        });
    });
});
