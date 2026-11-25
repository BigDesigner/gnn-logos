/**
 * GNN Logos - Native Gutenberg Block Editor Script (Zero-Build, API v3)
 *
 * @package GNN_Logos
 * @version 2.0.0
 */
(function (wp) {
    'use strict';

    if (!wp || !wp.blocks || !wp.element || !wp.blockEditor || !wp.components) {
        return;
    }

    var el = wp.element.createElement;
    var Fragment = wp.element.Fragment;
    var registerBlockType = wp.blocks.registerBlockType;
    var InspectorControls = wp.blockEditor.InspectorControls;
    var useBlockProps = wp.blockEditor.useBlockProps;
    var PanelBody = wp.components.PanelBody;
    var SelectControl = wp.components.SelectControl;
    var TextControl = wp.components.TextControl;
    var RangeControl = wp.components.RangeControl;
    var ToggleControl = wp.components.ToggleControl;
    var ServerSideRender = wp.serverSideRender;

    var blockData = window.gnnLogosBlockData || { groups: [] };
    var groupOptions = [{ label: '-- Tüm Gruplar --', value: '' }].concat(blockData.groups || []);

    registerBlockType('gnn-logos/showcase', {
        edit: function (props) {
            var attrs = props.attributes;
            var setAttr = props.setAttributes;
            var blockProps = useBlockProps();

            var isCarousel = attrs.layout === 'carousel';
            var isMarquee = attrs.layout === 'marquee';

            return el(
                Fragment,
                null,
                el(
                    InspectorControls,
                    null,
                    // 1. Vitrin & Grup
                    el(
                        PanelBody,
                        { title: 'Vitrin & Düzen', initialOpen: true },
                        el(SelectControl, {
                            label: 'Logo Grubu',
                            value: attrs.group,
                            options: groupOptions,
                            onChange: function (v) { setAttr({ group: v }); }
                        }),
                        el(SelectControl, {
                            label: 'Vitrin Modu',
                            value: attrs.layout,
                            options: [
                                { label: 'Carousel / Slider', value: 'carousel' },
                                { label: 'Continuous Marquee (Şerit)', value: 'marquee' },
                                { label: 'Responsive Grid (Izgara)', value: 'grid' }
                            ],
                            onChange: function (v) { setAttr({ layout: v }); }
                        }),
                        el(SelectControl, {
                            label: 'Kart Stili',
                            value: attrs.style,
                            options: [
                                { label: 'Minimal', value: 'minimal' },
                                { label: 'Modern Kart (Card)', value: 'card' },
                                { label: 'İnce Çerçeve (Bordered)', value: 'bordered' },
                                { label: 'Glassmorphism (Glass)', value: 'glass' }
                            ],
                            onChange: function (v) { setAttr({ style: v }); }
                        }),
                        el(SelectControl, {
                            label: 'Renk Teması',
                            value: attrs.theme,
                            options: [
                                { label: 'Açık (Light)', value: 'light' },
                                { label: 'Koyu (Dark)', value: 'dark' },
                                { label: 'Otomatik (Sistem)', value: 'auto' }
                            ],
                            onChange: function (v) { setAttr({ theme: v }); }
                        }),
                        el(SelectControl, {
                            label: 'Logo Renk Filtresi',
                            value: attrs.logo_filter,
                            options: [
                                { label: 'Orijinal Renkler', value: 'none' },
                                { label: 'Gri Ton (Hover Renkli)', value: 'grayscale' },
                                { label: 'Monokrom Beyaz (Koyu Zemin)', value: 'monochrome-white' },
                                { label: 'Monokrom Siyah (Açık Zemin)', value: 'monochrome-dark' }
                            ],
                            onChange: function (v) { setAttr({ logo_filter: v }); }
                        })
                    ),

                    // 2. Ölçüler & Sütunlar
                    el(
                        PanelBody,
                        { title: 'Boyutlandırma & Sütunlar', initialOpen: false },
                        el(SelectControl, {
                            label: 'En-Boy Oranı',
                            value: attrs.aspect_ratio,
                            options: [
                                { label: 'Otomatik', value: 'auto' },
                                { label: '4:3 (Sertifika)', value: '4/3' },
                                { label: '16:9 (Geniş)', value: '16/9' },
                                { label: '1:1 (Kare)', value: '1/1' },
                                { label: '3:2', value: '3/2' },
                                { label: '2:1 (Yatay İnce)', value: '2/1' }
                            ],
                            onChange: function (v) { setAttr({ aspect_ratio: v }); }
                        }),
                        el(TextControl, {
                            label: 'Logo Yüksekliği (ör. 48px)',
                            value: attrs.logo_height,
                            onChange: function (v) { setAttr({ logo_height: v }); }
                        }),
                        el(TextControl, {
                            label: 'Boşluk (Gap)',
                            value: attrs.gap,
                            onChange: function (v) { setAttr({ gap: v }); }
                        }),
                        !isMarquee && el(RangeControl, {
                            label: 'Masaüstü Sütun',
                            value: attrs.columns,
                            min: 1,
                            max: 12,
                            onChange: function (v) { setAttr({ columns: v }); }
                        }),
                        !isMarquee && el(RangeControl, {
                            label: 'Tablet Sütun',
                            value: attrs.columns_tablet,
                            min: 1,
                            max: 8,
                            onChange: function (v) { setAttr({ columns_tablet: v }); }
                        }),
                        !isMarquee && el(RangeControl, {
                            label: 'Mobil Sütun',
                            value: attrs.columns_mobile,
                            min: 1,
                            max: 6,
                            onChange: function (v) { setAttr({ columns_mobile: v }); }
                        })
                    ),

                    // 3. Rozet, Metin & Lightbox
                    el(
                        PanelBody,
                        { title: 'Rozetler, Metin & Lightbox', initialOpen: false },
                        el(ToggleControl, {
                            label: 'Sertifika Rozetlerini Göster',
                            checked: !!attrs.show_code,
                            onChange: function (v) { setAttr({ show_code: v }); }
                        }),
                        attrs.show_code && el(SelectControl, {
                            label: 'Rozet Dizilimi',
                            value: attrs.badges_layout,
                            options: [
                                { label: 'Yan Yana (Wrap)', value: 'wrap' },
                                { label: 'Alt Alta (Stacked)', value: 'stacked' }
                            ],
                            onChange: function (v) { setAttr({ badges_layout: v }); }
                        }),
                        attrs.show_code && el(SelectControl, {
                            label: 'Rozet Yatay Hizalama',
                            value: attrs.badges_align,
                            options: [
                                { label: 'Ortalı', value: 'center' },
                                { label: 'Sola', value: 'left' },
                                { label: 'Sağa', value: 'right' }
                            ],
                            onChange: function (v) { setAttr({ badges_align: v }); }
                        }),
                        attrs.show_code && el(SelectControl, {
                            label: 'Rozet Dikey Hizalama',
                            value: attrs.badges_valign,
                            options: [
                                { label: 'Altta', value: 'bottom' },
                                { label: 'Üstte', value: 'top' },
                                { label: 'Ortada', value: 'center' }
                            ],
                            onChange: function (v) { setAttr({ badges_valign: v }); }
                        }),
                        el(ToggleControl, {
                            label: 'Başlığı Göster',
                            checked: !!attrs.show_title,
                            onChange: function (v) { setAttr({ show_title: v }); }
                        }),
                        el(ToggleControl, {
                            label: 'Açıklamayı Göster',
                            checked: !!attrs.show_desc,
                            onChange: function (v) { setAttr({ show_desc: v }); }
                        }),
                        el(ToggleControl, {
                            label: 'Lightbox Modalını Etkinleştir',
                            checked: !!attrs.lightbox,
                            onChange: function (v) { setAttr({ lightbox: v }); }
                        })
                    ),

                    // 4. Animasyon (Karusel / Marquee)
                    (isCarousel || isMarquee) && el(
                        PanelBody,
                        { title: isCarousel ? 'Karusel Ayarları' : 'Marquee Ayarları', initialOpen: false },
                        isCarousel && el(ToggleControl, {
                            label: 'Otomatik Kaydır',
                            checked: !!attrs.autoplay,
                            onChange: function (v) { setAttr({ autoplay: v }); }
                        }),
                        isCarousel && attrs.autoplay && el(RangeControl, {
                            label: 'Geçiş Aralığı (ms)',
                            value: attrs.autoplay_speed,
                            min: 1000,
                            max: 10000,
                            step: 500,
                            onChange: function (v) { setAttr({ autoplay_speed: v }); }
                        }),
                        isCarousel && el(ToggleControl, {
                            label: 'Ok Butonlarını Göster',
                            checked: !!attrs.arrows,
                            onChange: function (v) { setAttr({ arrows: v }); }
                        }),
                        isCarousel && el(ToggleControl, {
                            label: 'Noktaları Göster',
                            checked: !!attrs.dots,
                            onChange: function (v) { setAttr({ dots: v }); }
                        }),
                        isMarquee && el(TextControl, {
                            label: 'Döngü Süresi (ör. 25s)',
                            value: attrs.speed,
                            onChange: function (v) { setAttr({ speed: v }); }
                        }),
                        isMarquee && el(SelectControl, {
                            label: 'Akış Yönü',
                            value: attrs.direction,
                            options: [
                                { label: 'Sola Doğru (Left)', value: 'left' },
                                { label: 'Sağa Doğru (Right)', value: 'right' }
                            ],
                            onChange: function (v) { setAttr({ direction: v }); }
                        }),
                        isMarquee && el(TextControl, {
                            label: 'Kutu Genişliği (ör. 170px)',
                            value: attrs.item_width,
                            onChange: function (v) { setAttr({ item_width: v }); }
                        }),
                        isMarquee && el(ToggleControl, {
                            label: 'Kenar Geçiş Maskesi (Fade)',
                            checked: !!attrs.fade_edges,
                            onChange: function (v) { setAttr({ fade_edges: v }); }
                        }),
                        el(ToggleControl, {
                            label: 'Fare Üzerine Gelince Duraklat',
                            checked: !!attrs.pause_on_hover,
                            onChange: function (v) { setAttr({ pause_on_hover: v }); }
                        })
                    ),

                    // 5. Sorgu & Sıralama
                    el(
                        PanelBody,
                        { title: 'Sıralama & Limit', initialOpen: false },
                        el(SelectControl, {
                            label: 'Sıralama Ölçütü',
                            value: attrs.orderby,
                            options: [
                                { label: 'Özel Sıra (menu_order)', value: 'menu_order' },
                                { label: 'Başlık', value: 'title' },
                                { label: 'Tarih', value: 'date' },
                                { label: 'Rastgele', value: 'rand' }
                            ],
                            onChange: function (v) { setAttr({ orderby: v }); }
                        }),
                        el(SelectControl, {
                            label: 'Sıralama Yönü',
                            value: attrs.order,
                            options: [
                                { label: 'Artan (ASC)', value: 'ASC' },
                                { label: 'Azalan (DESC)', value: 'DESC' }
                            ],
                            onChange: function (v) { setAttr({ order: v }); }
                        }),
                        el(RangeControl, {
                            label: 'Limit (-1 = Tümü)',
                            value: attrs.limit,
                            min: -1,
                            max: 50,
                            onChange: function (v) { setAttr({ limit: v }); }
                        })
                    )
                ),
                el(
                    'div',
                    blockProps,
                    ServerSideRender
                        ? el(ServerSideRender, {
                              block: 'gnn-logos/showcase',
                              attributes: attrs
                          })
                        : el('div', { style: { padding: '20px', border: '1px dashed #cbd5e1', textAlign: 'center' } }, 'GNN Logos Vitrin Bloğu')
                )
            );
        },
        save: function () {
            return null; // Dynamic server-side rendered block
        }
    });
})(window.wp);
