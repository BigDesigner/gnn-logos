<?php
/**
 * Admin Panel & Shortcode Generator UI for GNN Logos.
 *
 * @package GNN_Logos
 * @since   1.0.0
 */

defined('ABSPATH') || exit;

/**
 * Class GNN_Logos_Admin
 */
class GNN_Logos_Admin
{
    /**
     * Menu Position in WordPress Admin Sidebar.
     * Position '79.109' as a string literal per ADR 0004 & ADR 0010.
     */
    const MENU_POSITION = '79.109';

    /**
     * Constructor.
     */
    public function __construct()
    {
        add_action('admin_menu', array($this, 'register_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    /**
     * Register Admin Menus and Submenus.
     */
    public function register_admin_menu()
    {
        // 1. Top-level GNN Logos menu positioned at '79.109'
        add_menu_page(
            __('GNN Logos', 'gnn-logos'),
            __('GNN Logos', 'gnn-logos'),
            'manage_options',
            'gnn-logos',
            array($this, 'page_redirect_to_all'),
            'dashicons-images-alt2',
            self::MENU_POSITION
        );

        // 2. Submenu: Tüm Logolar & Sertifikalar
        add_submenu_page(
            'gnn-logos',
            __('Tüm Logolar & Sertifikalar', 'gnn-logos'),
            __('Tüm Logolar & Sertifikalar', 'gnn-logos'),
            'manage_options',
            'edit.php?post_type=' . GNN_Logos_CPT::POST_TYPE
        );

        // 3. Submenu: Yeni Ekle
        add_submenu_page(
            'gnn-logos',
            __('Yeni Ekle', 'gnn-logos'),
            __('Yeni Ekle', 'gnn-logos'),
            'manage_options',
            'post-new.php?post_type=' . GNN_Logos_CPT::POST_TYPE
        );

        // 4. Submenu: Logo Grupları
        add_submenu_page(
            'gnn-logos',
            __('Logo Grupları', 'gnn-logos'),
            __('Logo Grupları', 'gnn-logos'),
            'manage_options',
            'edit-tags.php?taxonomy=' . GNN_Logos_CPT::TAXONOMY . '&post_type=' . GNN_Logos_CPT::POST_TYPE
        );

        // 5. Submenu: Shortcode Oluşturucu
        add_submenu_page(
            'gnn-logos',
            __('Shortcode Oluşturucu', 'gnn-logos'),
            __('Shortcode Oluşturucu', 'gnn-logos'),
            'manage_options',
            'gnn-logos-generator',
            array($this, 'page_generator')
        );

        // 6. Submenu: Güncellemeleri Kontrol Et
        add_submenu_page(
            'gnn-logos',
            __('Güncellemeleri Kontrol Et', 'gnn-logos'),
            __('Güncellemeleri Kontrol Et', 'gnn-logos'),
            'manage_options',
            'gnn-logos-check-update',
            array($this, 'handle_manual_update_redirect')
        );
    }

    /**
     * Redirect top-level click to All Logos list table.
     */
    public function page_redirect_to_all()
    {
        wp_safe_redirect(admin_url('edit.php?post_type=' . GNN_Logos_CPT::POST_TYPE));
        exit;
    }

    /**
     * Redirect to manual update check url with nonce.
     */
    public function handle_manual_update_redirect()
    {
        if (!current_user_can('update_plugins')) {
            wp_die(esc_html__('Bu sayfaya erişim yetkiniz bulunmuyor.', 'gnn-logos'));
        }
        $update_url = wp_nonce_url(admin_url('plugins.php?gnn_logos_check_update=1'), 'gnn_logos_manual_update');
        wp_safe_redirect($update_url);
        exit;
    }

    /**
     * Enqueue Admin scripts and styles.
     *
     * @param string $hook_suffix Admin page hook.
     */
    public function enqueue_admin_assets($hook_suffix)
    {
        global $post_type;

        $is_gnn_cpt = (GNN_Logos_CPT::POST_TYPE === $post_type);
        $is_generator = (strpos($hook_suffix, 'gnn-logos-generator') !== false);

        if (!$is_gnn_cpt && !$is_generator) {
            return;
        }

        // Enqueue WP Media Library dependencies
        wp_enqueue_media();

        wp_enqueue_style(
            'gnn-logos-admin',
            GNN_LOGOS_URL . 'assets/css/gnn-logos-admin.css',
            array(),
            GNN_LOGOS_VERSION
        );

        wp_enqueue_script(
            'gnn-logos-admin',
            GNN_LOGOS_URL . 'assets/js/gnn-logos-admin.js',
            array('jquery'),
            GNN_LOGOS_VERSION,
            true
        );

        wp_localize_script('gnn-logos-admin', 'gnnLogosAdmin', array(
            'chooseLogoText' => __('Logo Seçin', 'gnn-logos'),
            'useLogoText'    => __('Bu Logoyu Kullan', 'gnn-logos'),
            'copiedText'     => __('Shortcode Kopyalandı!', 'gnn-logos'),
        ));
    }

    /**
     * Render Interactive Shortcode Generator Page.
     */
    public function page_generator()
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Bu sayfaya erişim yetkiniz bulunmuyor.', 'gnn-logos'));
        }

        // Fetch groups/terms
        $terms = get_terms(array(
            'taxonomy'   => GNN_Logos_CPT::TAXONOMY,
            'hide_empty' => false,
        ));
        ?>
        <div class="wrap gnn-admin-wrap">
            <h1 class="wp-heading-inline">
                <span class="dashicons dashicons-images-alt2" style="font-size:28px; width:28px; height:28px; vertical-align:middle; margin-right:6px;"></span>
                <?php esc_html_e('GNN Logos - Shortcode Oluşturucu & Sihirbaz', 'gnn-logos'); ?>
            </h1>
            <p class="description">
                <?php esc_html_e('İstediğiniz vitrin ayarlarını belirleyin, canlı olarak oluşturulan shortcode\'u kopyalayıp dilediğiniz sayfaya veya yazıya ekleyin.', 'gnn-logos'); ?>
            </p>
            <hr class="wp-header-end">

            <!-- Hazır Şablon Butonları -->
            <div class="gnn-presets-card">
                <h3><?php esc_html_e('Hızlı Şablonlar:', 'gnn-logos'); ?></h3>
                <div class="gnn-preset-buttons">
                    <button type="button" class="button" id="gnn-preset-certs">
                        <span class="dashicons dashicons-awards"></span>
                        <?php esc_html_e('Sertifikalar (TS EN Kart Düzeni)', 'gnn-logos'); ?>
                    </button>
                    <button type="button" class="button" id="gnn-preset-marquee">
                        <span class="dashicons dashicons-leftright"></span>
                        <?php esc_html_e('Referanslar (Sonsuz Kayan Şerit)', 'gnn-logos'); ?>
                    </button>
                    <button type="button" class="button" id="gnn-preset-partners">
                        <span class="dashicons dashicons-slides"></span>
                        <?php esc_html_e('Çözüm Ortakları (Karusel Kaydırıcı)', 'gnn-logos'); ?>
                    </button>
                    <button type="button" class="button" id="gnn-preset-grid">
                        <span class="dashicons dashicons-grid-view"></span>
                        <?php esc_html_e('Standart Izgara (Grid)', 'gnn-logos'); ?>
                    </button>
                </div>
            </div>

            <div class="gnn-generator-layout">
                <!-- Sol Kolon: Form Ayarları -->
                <div class="gnn-generator-form-card">
                    <h2><?php esc_html_e('Vitrin Parametreleri', 'gnn-logos'); ?></h2>

                    <form id="gnn-shortcode-builder-form">
                        <!-- 1. Logo Grubu -->
                        <div class="gnn-form-group">
                            <label for="sc_group"><strong><?php esc_html_e('Logo Grubu / Kategori:', 'gnn-logos'); ?></strong></label>
                            <select id="sc_group" class="widefat">
                                <option value=""><?php esc_html_e('-- Tüm Gruplar --', 'gnn-logos'); ?></option>
                                <?php if (!empty($terms) && !is_wp_error($terms)) : ?>
                                    <?php foreach ($terms as $term) : ?>
                                        <option value="<?php echo esc_attr($term->slug); ?>"><?php echo esc_html($term->name . ' (' . $term->count . ')'); ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- 2. Vitrin Düzeni -->
                        <div class="gnn-form-group">
                            <label for="sc_layout"><strong><?php esc_html_e('Vitrin / Animasyon Modu:', 'gnn-logos'); ?></strong></label>
                            <select id="sc_layout" class="widefat">
                                <option value="carousel"><?php esc_html_e('Carousel / Slider (Sağa-Sola Kaydırıcı)', 'gnn-logos'); ?></option>
                                <option value="marquee"><?php esc_html_e('Continuous Ticker / Marquee (Sonsuz Akan Şerit)', 'gnn-logos'); ?></option>
                                <option value="grid"><?php esc_html_e('Responsive Grid (Statik Izgara)', 'gnn-logos'); ?></option>
                            </select>
                        </div>

                        <!-- 3. Kart / Görsel Stili -->
                        <div class="gnn-form-group">
                            <label for="sc_style"><strong><?php esc_html_e('Kart & Çerçeve Stili:', 'gnn-logos'); ?></strong></label>
                            <select id="sc_style" class="widefat">
                                <option value="card"><?php esc_html_e('Modern Kart (Gölge & Çerçeve - Sertifikalar İçin Önerilir)', 'gnn-logos'); ?></option>
                                <option value="bordered"><?php esc_html_e('İnce Çerçeveli (Bordered)', 'gnn-logos'); ?></option>
                                <option value="minimal"><?php esc_html_e('Minimal / Sade (Çerçevesiz, Şeffaf)', 'gnn-logos'); ?></option>
                            </select>
                        </div>

                        <!-- 4. En-Boy Oranı (Aspect Ratio) -->
                        <div class="gnn-form-group">
                            <label for="sc_aspect_ratio"><strong><?php esc_html_e('En-Boy Oranı (Aspect Ratio):', 'gnn-logos'); ?></strong></label>
                            <select id="sc_aspect_ratio" class="widefat">
                                <option value="auto"><?php esc_html_e('Otomatik (Görselin Kendi Oranı)', 'gnn-logos'); ?></option>
                                <option value="4/3">4:3 (Sertifikalara Uygun Dikdörtgen)</option>
                                <option value="16/9">16:9 (Geniş Dikdörtgen)</option>
                                <option value="1/1">1:1 (Kare Mühür/Logo)</option>
                                <option value="3/2">3:2</option>
                                <option value="2/1">2:1 (Yatay İnce)</option>
                            </select>
                        </div>

                        <!-- 5. Sütun Sayıları -->
                        <div class="gnn-form-row">
                            <div class="gnn-form-col">
                                <label for="sc_columns"><?php esc_html_e('Masaüstü Sütun:', 'gnn-logos'); ?></label>
                                <input type="number" id="sc_columns" min="1" max="10" value="4">
                            </div>
                            <div class="gnn-form-col">
                                <label for="sc_columns_tablet"><?php esc_html_e('Tablet Sütun:', 'gnn-logos'); ?></label>
                                <input type="number" id="sc_columns_tablet" min="1" max="6" value="3">
                            </div>
                            <div class="gnn-form-col">
                                <label for="sc_columns_mobile"><?php esc_html_e('Mobil Sütun:', 'gnn-logos'); ?></label>
                                <input type="number" id="sc_columns_mobile" min="1" max="4" value="2">
                            </div>
                        </div>

                        <!-- 6. Boşluk (Gap) -->
                        <div class="gnn-form-group">
                            <label for="sc_gap"><strong><?php esc_html_e('Logolar Arası Boşluk:', 'gnn-logos'); ?></strong></label>
                            <input type="text" id="sc_gap" value="20px" class="regular-text">
                        </div>

                        <!-- 7. Sertifika Rozeti & Metin Seçenekleri -->
                        <div class="gnn-form-group">
                            <label><strong><?php esc_html_e('Metin ve Rozet Seçenekleri:', 'gnn-logos'); ?></strong></label>
                            <div class="gnn-checkbox-list">
                                <label>
                                    <input type="checkbox" id="sc_show_code" checked>
                                    <?php esc_html_e('Sertifika / Standart Kodunu Göster (TS EN 12201-2 vb. Rozet)', 'gnn-logos'); ?>
                                </label>
                                <label>
                                    <input type="checkbox" id="sc_show_title">
                                    <?php esc_html_e('Logo / Firma Başlığını Göster', 'gnn-logos'); ?>
                                </label>
                                <label>
                                    <input type="checkbox" id="sc_show_desc">
                                    <?php esc_html_e('Açıklama / Kurum Adını Göster', 'gnn-logos'); ?>
                                </label>
                                <label>
                                    <input type="checkbox" id="sc_grayscale">
                                    <?php esc_html_e('Siyah-Beyaz Başlat (Fareyle Üzerine Gelince Renklensin)', 'gnn-logos'); ?>
                                </label>
                            </div>
                        </div>

                        <!-- 7.1 Rozet Dizilimi, Yatay ve Dikey Hizalama -->
                        <div class="gnn-form-row gnn-form-group" id="gnn-badges-options-box">
                            <div class="gnn-form-col">
                                <label for="sc_badges_layout"><strong><?php esc_html_e('Rozet Dizilimi:', 'gnn-logos'); ?></strong></label>
                                <select id="sc_badges_layout" class="widefat">
                                    <option value="wrap"><?php esc_html_e('Yan Yana (Akıcı / Wrap)', 'gnn-logos'); ?></option>
                                    <option value="stacked"><?php esc_html_e('Alt Alta (Dikey Sıralı)', 'gnn-logos'); ?></option>
                                </select>
                            </div>
                            <div class="gnn-form-col">
                                <label for="sc_badges_align"><strong><?php esc_html_e('Yatay Hizalama:', 'gnn-logos'); ?></strong></label>
                                <select id="sc_badges_align" class="widefat">
                                    <option value="center"><?php esc_html_e('Ortalı (Center)', 'gnn-logos'); ?></option>
                                    <option value="left"><?php esc_html_e('Sola Hizalı (Left)', 'gnn-logos'); ?></option>
                                    <option value="right"><?php esc_html_e('Sağa Hizalı (Right)', 'gnn-logos'); ?></option>
                                </select>
                            </div>
                            <div class="gnn-form-col">
                                <label for="sc_badges_valign"><strong><?php esc_html_e('Dikey Hizalama:', 'gnn-logos'); ?></strong></label>
                                <select id="sc_badges_valign" class="widefat">
                                    <option value="bottom"><?php esc_html_e('Altta (Kart Altına Sabitli - Bottom)', 'gnn-logos'); ?></option>
                                    <option value="top"><?php esc_html_e('Üstte (Logonun Hemen Altında - Top)', 'gnn-logos'); ?></option>
                                    <option value="center"><?php esc_html_e('Ortada (Dikey Ortalı - Center)', 'gnn-logos'); ?></option>
                                </select>
                            </div>
                        </div>

                        <!-- 8. Carousel / Marquee Özel Ayarları -->
                        <div class="gnn-form-group" id="gnn-carousel-options-box">
                            <label><strong><?php esc_html_e('Karusel / Marquee Ayarları:', 'gnn-logos'); ?></strong></label>
                            <div class="gnn-checkbox-list">
                                <label>
                                    <input type="checkbox" id="sc_autoplay" checked>
                                    <?php esc_html_e('Otomatik Kaydır (Autoplay)', 'gnn-logos'); ?>
                                </label>
                                <label>
                                    <input type="checkbox" id="sc_arrows" checked>
                                    <?php esc_html_e('Karusel İleri/Geri Ok Butonlarını Göster', 'gnn-logos'); ?>
                                </label>
                                <label>
                                    <input type="checkbox" id="sc_dots">
                                    <?php esc_html_e('Sayfalama Noktalarını (Dots) Göster', 'gnn-logos'); ?>
                                </label>
                                <label>
                                    <input type="checkbox" id="sc_pause_on_hover" checked>
                                    <?php esc_html_e('Fare Üzerine Gelince Kaymayı Duraklat', 'gnn-logos'); ?>
                                </label>
                            </div>
                            <div class="gnn-form-row" style="margin-top:10px;">
                                <div class="gnn-form-col">
                                    <label for="sc_autoplay_speed"><?php esc_html_e('Karusel Hızı (ms):', 'gnn-logos'); ?></label>
                                    <input type="number" id="sc_autoplay_speed" value="3000" step="500">
                                </div>
                                <div class="gnn-form-col">
                                    <label for="sc_marquee_speed"><?php esc_html_e('Marquee Akış Süresi:', 'gnn-logos'); ?></label>
                                    <input type="text" id="sc_marquee_speed" value="25s">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Sağ Kolon: Canlı Shortcode & Kopyalama -->
                <div class="gnn-generator-preview-card">
                    <h2><?php esc_html_e('Oluşturulan Shortcode', 'gnn-logos'); ?></h2>
                    <p><?php esc_html_e('Bu kodu kopyalayıp Gutenberg veya klasik editörde dilediğiniz sayfaya yapıştırın:', 'gnn-logos'); ?></p>

                    <div class="gnn-code-box">
                        <textarea id="gnn-output-shortcode" readonly rows="5" class="widefat">[gnn_logos]</textarea>
                    </div>

                    <button type="button" class="button button-primary button-hero" id="gnn-copy-shortcode-btn">
                        <span class="dashicons dashicons-clipboard"></span>
                        <?php esc_html_e('Shortcode\'u Kopyala', 'gnn-logos'); ?>
                    </button>
                    <span id="gnn-copy-feedback" class="gnn-copy-feedback" style="display:none;"></span>

                    <hr style="margin:25px 0;">

                    <div class="gnn-info-box">
                        <h4><?php esc_html_e('İpuçları & Standartlar', 'gnn-logos'); ?></h4>
                        <ul>
                            <li><strong>TS EN Sertifikaları için:</strong> <code>style="card"</code> ve <code>aspect_ratio="4/3"</code> kombinasyonu ideal kart görünümü sağlar.</li>
                            <li><strong>Sonsuz Akış için:</strong> <code>layout="marquee"</code> ile sayfada pürüzsüz kayan logo bandı oluşturabilirsiniz.</li>
                            <li><strong>Sıfır Yük:</strong> Eklenti yalnızca shortcode kullanılan sayfalarda CSS ve JS yükler. Harici kütüphane içermez.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
