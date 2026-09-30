/**
 * GNN Logos - Admin JavaScript
 * Handles WordPress Media Uploader and Interactive Shortcode Generator.
 *
 * @package GNN_Logos
 * @version 1.0.1
 */

jQuery(document).ready(function ($) {
    'use strict';

    /* ==========================================================================
       1. WordPress Media Uploader Integration
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
                title: (window.gnnLogosAdmin && window.gnnLogosAdmin.chooseLogoText) || 'Logo Seçin',
                button: {
                    text: (window.gnnLogosAdmin && window.gnnLogosAdmin.useLogoText) || 'Bu Logoyu Kullan'
                },
                library: {
                    type: 'image'
                },
                multiple: false
            });

            mediaFrame.on('select', function () {
                var attachment = mediaFrame.state().get('selection').first().toJSON();
                if (!attachment || !attachment.id) return;

                $logoIdInput.val(attachment.id);

                var previewUrl = attachment.url;
                if (attachment.sizes && attachment.sizes.medium) {
                    previewUrl = attachment.sizes.medium.url;
                }

                $previewBox.addClass('has-image').html('<img src="' + previewUrl + '" alt="Logo Önizleme">');
                $removeBtn.show();
            });

            mediaFrame.open();
        });

        $removeBtn.on('click', function (e) {
            e.preventDefault();
            $logoIdInput.val('');
            $previewBox.removeClass('has-image').html('<span class="gnn-no-image-text">Görsel seçilmedi</span>');
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

        // Split by comma or newline for bulk pasting
        var items = rawVal.split(/[,\n]+/);
        items.forEach(function (item) {
            var clean = item.trim();
            if (!clean) return;

            // Check if already in list
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
       2. Interactive Shortcode Generator Logic
       ========================================================================== */
    var $form = $('#gnn-shortcode-builder-form');
    var $output = $('#gnn-output-shortcode');
    var $copyBtn = $('#gnn-copy-shortcode-btn');
    var $feedback = $('#gnn-copy-feedback');

    function updateShortcode() {
        if (!$form.length || !$output.length) return;

        var group = $('#sc_group').val();
        var layout = $('#sc_layout').val();
        var style = $('#sc_style').val();
        var aspectRatio = $('#sc_aspect_ratio').val();
        var columns = parseInt($('#sc_columns').val(), 10) || 4;
        var columnsTablet = parseInt($('#sc_columns_tablet').val(), 10) || 3;
        var columnsMobile = parseInt($('#sc_columns_mobile').val(), 10) || 2;
        var gap = $('#sc_gap').val().trim() || '20px';

        var showCode = $('#sc_show_code').is(':checked');
        var showTitle = $('#sc_show_title').is(':checked');
        var showDesc = $('#sc_show_desc').is(':checked');
        var grayscale = $('#sc_grayscale').is(':checked');

        var autoplay = $('#sc_autoplay').is(':checked');
        var arrows = $('#sc_arrows').is(':checked');
        var dots = $('#sc_dots').is(':checked');
        var pauseOnHover = $('#sc_pause_on_hover').is(':checked');
        var autoplaySpeed = parseInt($('#sc_autoplay_speed').val(), 10) || 3000;
        var marqueeSpeed = $('#sc_marquee_speed').val().trim() || '25s';

        // Toggle layout specific options visibility
        if (layout === 'grid') {
            $('#gnn-carousel-options-box').slideUp(150);
        } else {
            $('#gnn-carousel-options-box').slideDown(150);
        }

        var parts = ['gnn_logos'];

        if (group) parts.push('group="' + group + '"');
        if (layout !== 'carousel') parts.push('layout="' + layout + '"');
        if (style !== 'minimal') parts.push('style="' + style + '"');
        if (aspectRatio !== 'auto') parts.push('aspect_ratio="' + aspectRatio + '"');

        if (columns !== 4) parts.push('columns="' + columns + '"');
        if (columnsTablet !== 3) parts.push('columns_tablet="' + columnsTablet + '"');
        if (columnsMobile !== 2) parts.push('columns_mobile="' + columnsMobile + '"');
        if (gap !== '20px') parts.push('gap="' + gap + '"');

        if (showCode) parts.push('show_code="true"');
        if (showTitle) parts.push('show_title="true"');
        if (showDesc) parts.push('show_desc="true"');
        if (grayscale) parts.push('grayscale="true"');

        if (layout === 'carousel') {
            if (!autoplay) parts.push('autoplay="false"');
            if (autoplay && autoplaySpeed !== 3000) parts.push('autoplay_speed="' + autoplaySpeed + '"');
            if (!arrows) parts.push('arrows="false"');
            if (dots) parts.push('dots="true"');
            if (!pauseOnHover) parts.push('pause_on_hover="false"');
        } else if (layout === 'marquee') {
            if (marqueeSpeed !== '25s') parts.push('speed="' + marqueeSpeed + '"');
            if (!pauseOnHover) parts.push('pause_on_hover="false"');
        }

        $output.val('[' + parts.join(' ') + ']');
    }

    if ($form.length) {
        $form.on('change input', 'input, select', updateShortcode);

        // Preset 1: Sertifikalar (TS EN Kart Düzeni)
        $('#gnn-preset-certs').on('click', function () {
            $('#sc_layout').val('grid');
            $('#sc_style').val('card');
            $('#sc_aspect_ratio').val('4/3');
            $('#sc_columns').val(4);
            $('#sc_show_code').prop('checked', true);
            $('#sc_show_title').prop('checked', false);
            $('#sc_grayscale').prop('checked', false);
            updateShortcode();
        });

        // Preset 2: Referanslar (Sonsuz Marquee Şeridi)
        $('#gnn-preset-marquee').on('click', function () {
            $('#sc_layout').val('marquee');
            $('#sc_style').val('minimal');
            $('#sc_aspect_ratio').val('auto');
            $('#sc_grayscale').prop('checked', true);
            $('#sc_show_code').prop('checked', false);
            $('#sc_marquee_speed').val('25s');
            updateShortcode();
        });

        // Preset 3: Çözüm Ortakları (Karusel)
        $('#gnn-preset-partners').on('click', function () {
            $('#sc_layout').val('carousel');
            $('#sc_style').val('bordered');
            $('#sc_aspect_ratio').val('16/9');
            $('#sc_columns').val(5);
            $('#sc_autoplay').prop('checked', true);
            $('#sc_arrows').prop('checked', true);
            $('#sc_show_code').prop('checked', false);
            updateShortcode();
        });

        // Preset 4: Standart Izgara (Grid)
        $('#gnn-preset-grid').on('click', function () {
            $('#sc_layout').val('grid');
            $('#sc_style').val('minimal');
            $('#sc_aspect_ratio').val('auto');
            $('#sc_columns').val(4);
            $('#sc_grayscale').prop('checked', false);
            updateShortcode();
        });

        // Copy button
        $copyBtn.on('click', function (e) {
            e.preventDefault();
            var text = $output.val();
            if (!text) return;

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(function () {
                    showFeedback();
                });
            } else {
                $output.select();
                document.execCommand('copy');
                showFeedback();
            }
        });

        function showFeedback() {
            var msg = (window.gnnLogosAdmin && window.gnnLogosAdmin.copiedText) || 'Shortcode Kopyalandı!';
            $feedback.text(msg).stop(true, true).fadeIn(200).delay(2000).fadeOut(400);
        }

        // Initialize on load
        updateShortcode();
    }
});
