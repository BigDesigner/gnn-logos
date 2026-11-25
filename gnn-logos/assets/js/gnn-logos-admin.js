/**
 * GNN Logos - Admin JavaScript
 * Handles Media Uploader, Tag Chips, Drag-and-Drop Reorder,
 * Bulk Upload, and Live-Preview Shortcode Generator.
 *
 * @package GNN_Logos
 * @version 2.0.0
 */

jQuery(document).ready(function ($) {
    'use strict';

    var cfg = window.gnnLogosAdmin || {};

    /* ==========================================================================
       1. WordPress Media Uploader (Single CPT Edit Screen)
       ========================================================================== */
    var mediaFrame;
    var $selectBtn = $('#gnn-select-logo-btn');
    var $removeBtn = $('#gnn-remove-logo-btn');
    var $logoIdInput = $('#gnn_logo_id');
    var $previewBox = $('#gnn-logo-preview');

    if ($selectBtn.length) {
        $selectBtn.on('click', function (e) {
            e.preventDefault();
            if (mediaFrame) {
                mediaFrame.open();
                return;
            }
            mediaFrame = wp.media({
                title: cfg.chooseLogoText || 'Logo Seçin',
                button: { text: cfg.useLogoText || 'Bu Logoyu Kullan' },
                library: { type: 'image' },
                multiple: false
            });

            mediaFrame.on('select', function () {
                var attachment = mediaFrame.state().get('selection').first().toJSON();
                if (!attachment || !attachment.id) return;
                $logoIdInput.val(attachment.id);
                var previewUrl = (attachment.sizes && attachment.sizes.medium) ? attachment.sizes.medium.url : attachment.url;
                $previewBox.addClass('has-image').empty().append(
                    $('<img>').attr('src', previewUrl).attr('alt', 'Logo Önizleme')
                );
                $removeBtn.show();
            });

            mediaFrame.open();
        });

        $removeBtn.on('click', function (e) {
            e.preventDefault();
            $logoIdInput.val('');
            $previewBox.removeClass('has-image').empty().append(
                $('<span class="gnn-no-image-text"></span>').text(cfg.noImageText || 'Görsel seçilmedi')
            );
            $removeBtn.hide();
        });
    }

    /* ==========================================================================
       1.1 Standards / Certificate Tag Chips Manager
       ========================================================================== */
    var $tagsList = $('#gnn-standards-tags-list');
    var $newStdInput = $('#gnn-new-standard-input');
    var $addStdBtn = $('#gnn-add-standard-btn');

    function addStandardsFromInput() {
        var rawVal = $newStdInput.val().trim();
        if (!rawVal) return;

        rawVal.split(/[,\n]+/).forEach(function (item) {
            var clean = item.trim();
            if (!clean) return;
            var exists = false;
            $tagsList.find('.gnn-tag-text').each(function () {
                if ($(this).text().trim().toUpperCase() === clean.toUpperCase()) {
                    exists = true;
                    return false;
                }
            });
            if (!exists) {
                var $chip = $(
                    '<span class="gnn-tag-chip">' +
                        '<span class="gnn-tag-text"></span>' +
                        '<input type="hidden" name="gnn_cert_codes[]">' +
                        '<button type="button" class="gnn-tag-remove" aria-label="Kaldır">&times;</button>' +
                    '</span>'
                );
                $chip.find('.gnn-tag-text').text(clean);
                $chip.find('input').val(clean);
                $tagsList.append($chip);
            }
        });

        $newStdInput.val('').focus();
    }

    if ($addStdBtn.length) {
        $addStdBtn.on('click', function (e) {
            e.preventDefault();
            addStandardsFromInput();
        });
        $newStdInput.on('keydown', function (e) {
            if (e.which === 13) {
                e.preventDefault();
                addStandardsFromInput();
            }
        });
        $tagsList.on('click', '.gnn-tag-remove', function (e) {
            e.preventDefault();
            $(this).closest('.gnn-tag-chip').fadeOut(150, function () {
                $(this).remove();
            });
        });
    }

    /* ==========================================================================
       2. CPT List Table: Drag-and-Drop Reordering & Bulk Upload
       ========================================================================== */
    var $tableBody = $('.post-type-gnn_logo #the-list');
    var $tableStatus = $('#gnn-table-status');

    function showTableStatus(msg, isError) {
        if (!$tableStatus.length) return;
        $tableStatus
            .removeClass('is-error is-success')
            .addClass(isError ? 'is-error' : 'is-success')
            .text(msg)
            .stop(true, true)
            .fadeIn(150);
    }

    if ($tableBody.length && typeof $tableBody.sortable === 'function') {
        $tableBody.sortable({
            items: 'tr',
            handle: '.gnn-drag-handle',
            axis: 'y',
            cursor: 'grabbing',
            placeholder: 'gnn-sort-placeholder',
            helper: function (e, ui) {
                ui.children().each(function () {
                    $(this).width($(this).width());
                });
                return ui;
            },
            update: function () {
                var order = [];
                $tableBody.find('tr').each(function () {
                    var idAttr = $(this).attr('id') || '';
                    var match = idAttr.match(/^post-(\d+)$/);
                    if (match) order.push(parseInt(match[1], 10));
                });

                if (!order.length || !cfg.ajaxUrl) return;
                showTableStatus(cfg.savingOrderText || 'Sıralama kaydediliyor...', false);

                $.post(cfg.ajaxUrl, {
                    action: 'gnn_logos_reorder',
                    nonce: cfg.nonce,
                    offset: cfg.pageOffset || 0,
                    order: order
                }).done(function (res) {
                    if (res && res.success) {
                        if (res.data && res.data.orders) {
                            $.each(res.data.orders, function (pid, ord) {
                                $('#post-' + pid).find('.gnn-order-num').text(ord);
                            });
                        }
                        showTableStatus((res.data && res.data.message) || 'Sıralama kaydedildi.', false);
                        setTimeout(function () { $tableStatus.fadeOut(400); }, 2000);
                    } else {
                        showTableStatus((res && res.data && res.data.message) || 'Hata oluştu.', true);
                    }
                }).fail(function () {
                    showTableStatus('Sunucu bağlantı hatası.', true);
                });
            }
        });
    }

    var bulkFrame;
    var $bulkBtn = $('#gnn-bulk-upload-btn');
    if ($bulkBtn.length) {
        $bulkBtn.on('click', function (e) {
            e.preventDefault();
            if (bulkFrame) {
                bulkFrame.open();
                return;
            }
            bulkFrame = wp.media({
                title: cfg.bulkTitleText || 'Toplu Logo Seçin',
                button: { text: cfg.bulkButtonText || 'Seçili Logoları Ekle' },
                library: { type: 'image' },
                multiple: true
            });

            bulkFrame.on('select', function () {
                var selection = bulkFrame.state().get('selection');
                var ids = [];
                selection.each(function (att) {
                    var json = att.toJSON();
                    if (json && json.id) ids.push(json.id);
                });
                if (!ids.length) return;

                var group = $('#gnn-bulk-group').val() || '';
                $bulkBtn.prop('disabled', true);
                showTableStatus(cfg.bulkLoadingText || 'Logolar ekleniyor...', false);

                $.post(cfg.ajaxUrl, {
                    action: 'gnn_logos_bulk_create',
                    nonce: cfg.nonce,
                    attachment_ids: ids,
                    group: group
                }).done(function (res) {
                    if (res && res.success) {
                        showTableStatus((res.data && res.data.message) || 'Logolar eklendi.', false);
                        setTimeout(function () { window.location.reload(); }, 600);
                    } else {
                        $bulkBtn.prop('disabled', false);
                        showTableStatus((res && res.data && res.data.message) || 'Hata oluştu.', true);
                    }
                }).fail(function () {
                    $bulkBtn.prop('disabled', false);
                    showTableStatus('Sunucu bağlantı hatası.', true);
                });
            });

            bulkFrame.open();
        });
    }

    /* ==========================================================================
       3. Interactive Shortcode Generator + Live Visual Preview
       ========================================================================== */
    var $form = $('#gnn-shortcode-builder-form');
    var $output = $('#gnn-output-shortcode');
    var $copyBtn = $('#gnn-copy-shortcode-btn');
    var $feedback = $('#gnn-copy-feedback');
    var $stage = $('#gnn-live-preview-stage');
    var previewTimer = null;
    var previewRequest = null;

    function collectAtts() {
        var layout = $('#sc_layout').val() || 'carousel';
        var showCode = $('#sc_show_code').is(':checked');
        var pauseHover = (layout === 'marquee')
            ? $('#sc_marquee_pause').is(':checked')
            : $('#sc_pause_on_hover').is(':checked');

        return {
            group: $('#sc_group').val() || '',
            layout: layout,
            style: $('#sc_style').val() || 'minimal',
            theme: $('#sc_theme').val() || 'light',
            logo_filter: $('#sc_logo_filter').val() || 'none',
            aspect_ratio: $('#sc_aspect_ratio').val() || 'auto',
            logo_height: $('#sc_logo_height').val().trim(),
            item_width: $('#sc_item_width').val().trim(),
            columns: parseInt($('#sc_columns').val(), 10) || 4,
            columns_tablet: parseInt($('#sc_columns_tablet').val(), 10) || 3,
            columns_mobile: parseInt($('#sc_columns_mobile').val(), 10) || 2,
            gap: $('#sc_gap').val().trim() || '20px',
            show_code: showCode ? 'true' : 'false',
            badges_layout: $('#sc_badges_layout').val() || 'wrap',
            badges_align: $('#sc_badges_align').val() || 'center',
            badges_valign: $('#sc_badges_valign').val() || 'bottom',
            show_title: $('#sc_show_title').is(':checked') ? 'true' : 'false',
            show_desc: $('#sc_show_desc').is(':checked') ? 'true' : 'false',
            lightbox: $('#sc_lightbox').is(':checked') ? 'true' : 'false',
            autoplay: $('#sc_autoplay').is(':checked') ? 'true' : 'false',
            autoplay_speed: parseInt($('#sc_autoplay_speed').val(), 10) || 3000,
            arrows: $('#sc_arrows').is(':checked') ? 'true' : 'false',
            dots: $('#sc_dots').is(':checked') ? 'true' : 'false',
            pause_on_hover: pauseHover ? 'true' : 'false',
            speed: $('#sc_marquee_speed').val().trim() || '25s',
            direction: $('#sc_direction').val() || 'left',
            fade_edges: $('#sc_fade_edges').is(':checked') ? 'true' : 'false',
            orderby: $('#sc_orderby').val() || 'menu_order',
            order: $('#sc_order').val() || 'ASC',
            limit: parseInt($('#sc_limit').val(), 10) || -1
        };
    }

    function setCanvasMode(mode) {
        $stage.removeClass('gnn-canvas-light gnn-canvas-dark gnn-canvas-checker').addClass('gnn-canvas-' + mode);
        $('.gnn-canvas-switcher button').removeClass('is-active').filter('[data-canvas="' + mode + '"]').addClass('is-active');
    }

    function refreshLivePreview(atts) {
        if (!$stage.length || !cfg.ajaxUrl) return;

        clearTimeout(previewTimer);
        previewTimer = setTimeout(function () {
            if (previewRequest && previewRequest.readyState !== 4) {
                previewRequest.abort();
            }

            $stage.addClass('is-loading');

            previewRequest = $.post(cfg.ajaxUrl, {
                action: 'gnn_logos_preview',
                nonce: cfg.nonce,
                atts: atts
            }).done(function (res) {
                $stage.removeClass('is-loading');
                if (res && res.success) {
                    if (res.data.empty) {
                        $stage.empty().append(
                            $('<div class="gnn-preview-empty"></div>').text(cfg.previewEmpty || 'Logo bulunamadı.')
                        );
                    } else {
                        $stage.html(res.data.html);
                        if (typeof window.GNNLogosInit === 'function') {
                            window.GNNLogosInit($stage[0]);
                        }
                    }
                } else {
                    $stage.empty().append(
                        $('<div class="gnn-preview-empty"></div>').text(cfg.previewError || 'Önizleme yüklenemedi.')
                    );
                }
            }).fail(function (xhr, status) {
                if (status === 'abort') return;
                $stage.removeClass('is-loading').empty().append(
                    $('<div class="gnn-preview-empty"></div>').text(cfg.previewError || 'Önizleme yüklenemedi.')
                );
            });
        }, 220);
    }

    function updateShortcode() {
        if (!$form.length || !$output.length) return;

        var a = collectAtts();

        // Toggle layout-specific controls cleanly
        if (a.layout === 'carousel') {
            $('#gnn-columns-row').slideDown(150);
            $('#gnn-carousel-options-box').slideDown(150);
            $('#gnn-marquee-options-box').slideUp(150);
        } else if (a.layout === 'marquee') {
            $('#gnn-columns-row').slideUp(150);
            $('#gnn-carousel-options-box').slideUp(150);
            $('#gnn-marquee-options-box').slideDown(150);
        } else {
            $('#gnn-columns-row').slideDown(150);
            $('#gnn-carousel-options-box').slideUp(150);
            $('#gnn-marquee-options-box').slideUp(150);
        }

        if (a.show_code === 'true') {
            $('#gnn-badges-options-box').slideDown(150);
        } else {
            $('#gnn-badges-options-box').slideUp(150);
        }

        var parts = ['gnn_logos'];

        if (a.group) parts.push('group="' + a.group + '"');
        if (a.layout !== 'carousel') parts.push('layout="' + a.layout + '"');
        if (a.style !== 'minimal') parts.push('style="' + a.style + '"');
        if (a.theme !== 'light') parts.push('theme="' + a.theme + '"');
        if (a.logo_filter !== 'none') parts.push('logo_filter="' + a.logo_filter + '"');
        if (a.aspect_ratio !== 'auto') parts.push('aspect_ratio="' + a.aspect_ratio + '"');
        if (a.logo_height) parts.push('logo_height="' + a.logo_height + '"');
        if (a.gap !== '20px') parts.push('gap="' + a.gap + '"');

        if (a.layout !== 'marquee') {
            if (a.columns !== 4) parts.push('columns="' + a.columns + '"');
            if (a.columns_tablet !== 3) parts.push('columns_tablet="' + a.columns_tablet + '"');
            if (a.columns_mobile !== 2) parts.push('columns_mobile="' + a.columns_mobile + '"');
        }

        // Fixed bug: explicitly output show_code="false" when unchecked!
        if (a.show_code === 'true') {
            parts.push('show_code="true"');
            parts.push('badges_layout="' + a.badges_layout + '"');
            parts.push('badges_align="' + a.badges_align + '"');
            parts.push('badges_valign="' + a.badges_valign + '"');
        } else {
            parts.push('show_code="false"');
        }

        if (a.show_title === 'true') parts.push('show_title="true"');
        if (a.show_desc === 'true') parts.push('show_desc="true"');
        if (a.lightbox === 'true') parts.push('lightbox="true"');

        if (a.layout === 'carousel') {
            if (a.autoplay === 'false') parts.push('autoplay="false"');
            if (a.autoplay === 'true' && a.autoplay_speed !== 3000) parts.push('autoplay_speed="' + a.autoplay_speed + '"');
            if (a.arrows === 'false') parts.push('arrows="false"');
            if (a.dots === 'true') parts.push('dots="true"');
            if (a.pause_on_hover === 'false') parts.push('pause_on_hover="false"');
        } else if (a.layout === 'marquee') {
            if (a.speed !== '25s') parts.push('speed="' + a.speed + '"');
            if (a.direction !== 'left') parts.push('direction="' + a.direction + '"');
            if (a.item_width) parts.push('item_width="' + a.item_width + '"');
            if (a.fade_edges === 'false') parts.push('fade_edges="false"');
            if (a.pause_on_hover === 'false') parts.push('pause_on_hover="false"');
        }

        if (a.orderby !== 'menu_order') parts.push('orderby="' + a.orderby + '"');
        if (a.order !== 'ASC') parts.push('order="' + a.order + '"');
        if (a.limit !== -1) parts.push('limit="' + a.limit + '"');

        $output.val('[' + parts.join(' ') + ']');
        refreshLivePreview(a);
    }

    if ($form.length) {
        $form.on('change input', 'input, select', updateShortcode);

        // Auto-switch preview canvas when user toggles theme/white monochrome
        $('#sc_theme, #sc_logo_filter').on('change', function () {
            if ($('#sc_theme').val() === 'dark' || $('#sc_logo_filter').val() === 'monochrome-white') {
                setCanvasMode('dark');
            } else if ($('#sc_theme').val() === 'light') {
                setCanvasMode('light');
            }
        });

        $('.gnn-canvas-switcher').on('click', 'button', function () {
            setCanvasMode($(this).data('canvas'));
        });

        // Preset 1: Sertifikalar (Kart + Lightbox)
        $('#gnn-preset-certs').on('click', function () {
            $('#sc_layout').val('grid');
            $('#sc_style').val('card');
            $('#sc_theme').val('light');
            $('#sc_logo_filter').val('none');
            $('#sc_aspect_ratio').val('4/3');
            $('#sc_logo_height').val('');
            $('#sc_columns').val(4);
            $('#sc_show_code').prop('checked', true);
            $('#sc_badges_layout').val('wrap');
            $('#sc_badges_align').val('center');
            $('#sc_badges_valign').val('bottom');
            $('#sc_show_title').prop('checked', false);
            $('#sc_lightbox').prop('checked', true);
            setCanvasMode('light');
            updateShortcode();
        });

        // Preset 2: Referanslar (Kayan Şerit)
        $('#gnn-preset-marquee').on('click', function () {
            $('#sc_layout').val('marquee');
            $('#sc_style').val('minimal');
            $('#sc_theme').val('light');
            $('#sc_logo_filter').val('grayscale');
            $('#sc_aspect_ratio').val('auto');
            $('#sc_logo_height').val('52px');
            $('#sc_item_width').val('170px');
            $('#sc_show_code').prop('checked', false);
            $('#sc_show_title').prop('checked', false);
            $('#sc_lightbox').prop('checked', false);
            $('#sc_marquee_speed').val('25s');
            $('#sc_direction').val('left');
            $('#sc_fade_edges').prop('checked', true);
            setCanvasMode('light');
            updateShortcode();
        });

        // Preset 3: Koyu Footer Bandı (Dark / Monochrome White)
        $('#gnn-preset-dark-footer').on('click', function () {
            $('#sc_layout').val('marquee');
            $('#sc_style').val('minimal');
            $('#sc_theme').val('dark');
            $('#sc_logo_filter').val('monochrome-white');
            $('#sc_aspect_ratio').val('auto');
            $('#sc_logo_height').val('44px');
            $('#sc_item_width').val('160px');
            $('#sc_gap').val('36px');
            $('#sc_show_code').prop('checked', false);
            $('#sc_show_title').prop('checked', false);
            $('#sc_lightbox').prop('checked', false);
            $('#sc_marquee_speed').val('28s');
            $('#sc_fade_edges').prop('checked', true);
            setCanvasMode('dark');
            updateShortcode();
        });

        // Preset 4: Çözüm Ortakları (Karusel)
        $('#gnn-preset-partners').on('click', function () {
            $('#sc_layout').val('carousel');
            $('#sc_style').val('bordered');
            $('#sc_theme').val('light');
            $('#sc_logo_filter').val('grayscale');
            $('#sc_aspect_ratio').val('16/9');
            $('#sc_logo_height').val('');
            $('#sc_columns').val(5);
            $('#sc_autoplay').prop('checked', true);
            $('#sc_arrows').prop('checked', true);
            $('#sc_dots').prop('checked', true);
            $('#sc_show_code').prop('checked', false);
            $('#sc_lightbox').prop('checked', false);
            setCanvasMode('light');
            updateShortcode();
        });

        // Preset 5: Glassmorphism Vitrin (Dark Glass)
        $('#gnn-preset-glass').on('click', function () {
            $('#sc_layout').val('grid');
            $('#sc_style').val('glass');
            $('#sc_theme').val('dark');
            $('#sc_logo_filter').val('monochrome-white');
            $('#sc_aspect_ratio').val('16/9');
            $('#sc_logo_height').val('');
            $('#sc_columns').val(4);
            $('#sc_show_code').prop('checked', false);
            $('#sc_show_title').prop('checked', false);
            $('#sc_lightbox').prop('checked', false);
            setCanvasMode('dark');
            updateShortcode();
        });

        // Copy button
        $copyBtn.on('click', function (e) {
            e.preventDefault();
            var text = $output.val();
            if (!text) return;

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(showFeedback);
            } else {
                $output.select();
                document.execCommand('copy');
                showFeedback();
            }
        });

        function showFeedback() {
            $feedback.text(cfg.copiedText || 'Shortcode Kopyalandı!').stop(true, true).fadeIn(200).delay(2000).fadeOut(400);
        }

        updateShortcode();
    }
});
