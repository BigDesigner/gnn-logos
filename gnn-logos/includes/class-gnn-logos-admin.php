<?php
/**
 * Admin Panel, Live Preview AJAX & Interactive Shortcode Generator UI for GNN Logos.
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
     * Position '79.109' as a string literal per ADR 0004.
     */
    const MENU_POSITION = '79.109';

    /**
     * Constructor.
     */
    public function __construct()
    {
        add_action('admin_menu', array($this, 'register_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_ajax_gnn_logos_preview', array($this, 'ajax_live_preview'));
    }

    /**
     * Register Admin Menus and Submenus.
     */
    public function register_admin_menu()
    {
        $parent_slug = 'edit.php?post_type=' . GNN_Logos_CPT::POST_TYPE;

        // 1. Top-level GNN Logos menu positioned at '79.109', opening All Logos directly
        add_menu_page(
            __('Tüm Logolar & Sertifikalar', 'gnn-logos'),
            __('GNN Logos', 'gnn-logos'),
            'manage_options',
            $parent_slug,
            '',
            'dashicons-images-alt2',
            self::MENU_POSITION
        );

        // 2. Submenu: Tüm Logolar & Sertifikalar
        add_submenu_page(
            $parent_slug,
            __('Tüm Logolar & Sertifikalar', 'gnn-logos'),
            __('Tüm Logolar & Sertifikalar', 'gnn-logos'),
            'manage_options',
            $parent_slug
        );

        // 3. Submenu: Yeni Ekle
        add_submenu_page(
            $parent_slug,
            __('Yeni Ekle', 'gnn-logos'),
            __('Yeni Ekle', 'gnn-logos'),
            'manage_options',
            'post-new.php?post_type=' . GNN_Logos_CPT::POST_TYPE
        );

        // 4. Submenu: Logo Grupları
        add_submenu_page(
            $parent_slug,
            __('Logo Grupları', 'gnn-logos'),
            __('Logo Grupları', 'gnn-logos'),
            'manage_options',
            'edit-tags.php?taxonomy=' . GNN_Logos_CPT::TAXONOMY . '&post_type=' . GNN_Logos_CPT::POST_TYPE
        );

        // 5. Submenu: Shortcode Oluşturucu
        add_submenu_page(
            $parent_slug,
            __('Shortcode Oluşturucu', 'gnn-logos'),
            __('Shortcode Oluşturucu', 'gnn-logos'),
            'manage_options',
            'gnn-logos-generator',
            array($this, 'page_generator')
        );
    }

    /**
     * Enqueue Admin scripts and styles conditionally.
     *
     * @param string $hook_suffix Admin page hook.
     */
    public function enqueue_admin_assets($hook_suffix)
    {
        global $post_type;

        $is_gnn_cpt   = (GNN_Logos_CPT::POST_TYPE === $post_type);
        $is_generator = (strpos($hook_suffix, 'gnn-logos-generator') !== false);

        if (!$is_gnn_cpt && !$is_generator) {
            return;
        }

        wp_enqueue_media();

        $script_deps = array('jquery');
        if ($is_gnn_cpt && 'edit.php' === $hook_suffix) {
            $script_deps[] = 'jquery-ui-sortable';
        }

        // Load frontend engine inside generator so Live Preview renders identically to live site
        if ($is_generator) {
            wp_enqueue_style('gnn-logos-frontend');
            wp_enqueue_script('gnn-logos-frontend');
        }

        wp_enqueue_style(
            'gnn-logos-admin',
            GNN_LOGOS_URL . 'assets/css/gnn-logos-admin.css',
            array(),
            GNN_LOGOS_VERSION
        );

        wp_enqueue_script(
            'gnn-logos-admin',
            GNN_LOGOS_URL . 'assets/js/gnn-logos-admin.js',
            $script_deps,
            GNN_LOGOS_VERSION,
            true
        );

        $paged    = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
        $per_page = (int) get_user_option('edit_' . GNN_Logos_CPT::POST_TYPE . '_per_page');
        if ($per_page <= 0) {
            $per_page = 20;
        }

        wp_localize_script('gnn-logos-admin', 'gnnLogosAdmin', array(
            'ajaxUrl'         => admin_url('admin-ajax.php'),
            'nonce'           => wp_create_nonce('gnn_logos_admin_nonce'),
            'pageOffset'      => ($paged - 1) * $per_page,
            'chooseLogoText'  => __('Logo Seçin', 'gnn-logos'),
            'useLogoText'     => __('Bu Logoyu Kullan', 'gnn-logos'),
            'bulkTitleText'   => __('Toplu Logo Seçin (Çoklu Seçim)', 'gnn-logos'),
            'bulkButtonText'  => __('Seçili Logoları Ekle', 'gnn-logos'),
            'noImageText'     => __('Görsel seçilmedi', 'gnn-logos'),
            'copiedText'      => __('Shortcode Kopyalandı!', 'gnn-logos'),
            'savingOrderText' => __('Sıralama kaydediliyor...', 'gnn-logos'),
            'bulkLoadingText' => __('Logolar ekleniyor...', 'gnn-logos'),
            'previewEmpty'    => __('Bu filtreye uygun yayımlanmış logo bulunamadı. Önce logo ekleyin.', 'gnn-logos'),
            'previewError'    => __('Önizleme yüklenirken bir hata oluştu.', 'gnn-logos'),
        ));
    }

    /**
     * AJAX: Render live visual preview for the Shortcode Generator.
     */
    public function ajax_live_preview()
    {
        check_ajax_referer('gnn_logos_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Yetkiniz bulunmuyor.', 'gnn-logos')), 403);
        }

        $raw_atts = isset($_POST['atts']) && is_array($_POST['atts']) ? $_POST['atts'] : array();
        $clean    = array();
        $defaults = GNN_Logos_Shortcode::get_defaults();

        foreach ($defaults as $key => $default_val) {
            if (isset($raw_atts[$key])) {
                $clean[$key] = sanitize_text_field(wp_unslash($raw_atts[$key]));
            }
        }

        $html = GNN_Logos::instance()->shortcode->render_shortcode($clean);

        wp_send_json_success(array(
            'html'  => $html,
            'empty' => ('' === trim($html)),
        ));
    }

    /**
     * Render Interactive Shortcode Generator Page with Live Visual Preview.
     */
    public function page_generator()
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Bu sayfaya erişim yetkiniz bulunmuyor.', 'gnn-logos'));
        }

        $terms = get_terms(array(
            'taxonomy'   => GNN_Logos_CPT::TAXONOMY,
            'hide_empty' => false,
        ));
        ?>
        <div class="wrap gnn-admin-wrap">
            <h1 class="wp-heading-inline">
                <span class="dashicons dashicons-images-alt2 gnn-page-icon"></span>
                <?php esc_html_e('GNN Logos - Shortcode Oluşturucu & Canlı Önizleme', 'gnn-logos'); ?>
            </h1>
            <p class="description">
                <?php esc_html_e('Vitrin ayarlarını belirleyin, canlı önizlemede açık/koyu arka planda test edin ve oluşturulan shortcode\'u tek tıkla kopyalayın.', 'gnn-logos'); ?>
            </p>
            <hr class="wp-header-end">

            <!-- Hızlı Şablonlar -->
            <div class="gnn-presets-card">
                <h3><?php esc_html_e('Hızlı Şablonlar:', 'gnn-logos'); ?></h3>
                <div class="gnn-preset-buttons">
                    <button type="button" class="button" id="gnn-preset-certs">
                        <span class="dashicons dashicons-awards"></span>
                        <?php esc_html_e('Sertifikalar (Kart + Lightbox)', 'gnn-logos'); ?>
                    </button>
                    <button type="button" class="button" id="gnn-preset-marquee">
                        <span class="dashicons dashicons-leftright"></span>
                        <?php esc_html_e('Referanslar (Kayan Şerit)', 'gnn-logos'); ?>
                    </button>
                    <button type="button" class="button" id="gnn-preset-dark-footer">
                        <span class="dashicons dashicons-visibility"></span>
                        <?php esc_html_e('Koyu Footer Bandı (Beyaz Logo)', 'gnn-logos'); ?>
                    </button>
                    <button type="button" class="button" id="gnn-preset-partners">
                        <span class="dashicons dashicons-slides"></span>
                        <?php esc_html_e('Çözüm Ortakları (Karusel)', 'gnn-logos'); ?>
                    </button>
                    <button type="button" class="button" id="gnn-preset-glass">
                        <span class="dashicons dashicons-grid-view"></span>
                        <?php esc_html_e('Glassmorphism Vitrin (Dark)', 'gnn-logos'); ?>
                    </button>
                </div>
            </div>

            <!-- Canlı Görsel Önizleme Alanı -->
            <div class="gnn-live-preview-card">
                <div class="gnn-live-preview-header">
                    <h2><?php esc_html_e('Canlı Görsel Önizleme', 'gnn-logos'); ?></h2>
                    <div class="gnn-canvas-switcher" role="group" aria-label="<?php esc_attr_e('Önizleme arka planı', 'gnn-logos'); ?>">
                        <button type="button" class="button is-active" data-canvas="light"><?php esc_html_e('Açık Tuval', 'gnn-logos'); ?></button>
                        <button type="button" class="button" data-canvas="dark"><?php esc_html_e('Koyu Tuval', 'gnn-logos'); ?></button>
                        <button type="button" class="button" data-canvas="checker"><?php esc_html_e('Şeffaf Izgara', 'gnn-logos'); ?></button>
                    </div>
                </div>
                <div id="gnn-live-preview-stage" class="gnn-preview-stage gnn-canvas-light">
                    <div class="gnn-preview-loading"><?php esc_html_e('Önizleme yükleniyor...', 'gnn-logos'); ?></div>
                </div>
            </div>

            <div class="gnn-generator-layout">
                <!-- Sol Kolon: Form Ayarları -->
                <div class="gnn-generator-form-card">
                    <h2><?php esc_html_e('Vitrin Parametreleri', 'gnn-logos'); ?></h2>

                    <form id="gnn-shortcode-builder-form">
                        <!-- 1. Logo Grubu & Düzen -->
                        <div class="gnn-form-row gnn-form-group">
                            <div class="gnn-form-col">
                                <label for="sc_group"><strong><?php esc_html_e('Logo Grubu:', 'gnn-logos'); ?></strong></label>
                                <select id="sc_group" class="widefat">
                                    <option value=""><?php esc_html_e('-- Tüm Gruplar --', 'gnn-logos'); ?></option>
                                    <?php if (!empty($terms) && !is_wp_error($terms)) : ?>
                                        <?php foreach ($terms as $term) : ?>
                                            <option value="<?php echo esc_attr($term->slug); ?>"><?php echo esc_html($term->name . ' (' . $term->count . ')'); ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="gnn-form-col">
                                <label for="sc_layout"><strong><?php esc_html_e('Vitrin Modu:', 'gnn-logos'); ?></strong></label>
                                <select id="sc_layout" class="widefat">
                                    <option value="carousel"><?php esc_html_e('Carousel / Slider (Kaydırıcı)', 'gnn-logos'); ?></option>
                                    <option value="marquee"><?php esc_html_e('Continuous Marquee (Sonsuz Şerit)', 'gnn-logos'); ?></option>
                                    <option value="grid"><?php esc_html_e('Responsive Grid (Izgara)', 'gnn-logos'); ?></option>
                                </select>
                            </div>
                        </div>

                        <!-- 2. Stil, Tema ve Logo Filtresi -->
                        <div class="gnn-form-row gnn-form-group">
                            <div class="gnn-form-col">
                                <label for="sc_style"><strong><?php esc_html_e('Kart Stili:', 'gnn-logos'); ?></strong></label>
                                <select id="sc_style" class="widefat">
                                    <option value="minimal"><?php esc_html_e('Minimal (Çerçevesiz)', 'gnn-logos'); ?></option>
                                    <option value="card"><?php esc_html_e('Modern Kart (Gölge & Çerçeve)', 'gnn-logos'); ?></option>
                                    <option value="bordered"><?php esc_html_e('İnce Çerçeveli (Bordered)', 'gnn-logos'); ?></option>
                                    <option value="glass"><?php esc_html_e('Glassmorphism (Buzlu Cam)', 'gnn-logos'); ?></option>
                                </select>
                            </div>
                            <div class="gnn-form-col">
                                <label for="sc_theme"><strong><?php esc_html_e('Renk Teması:', 'gnn-logos'); ?></strong></label>
                                <select id="sc_theme" class="widefat">
                                    <option value="light"><?php esc_html_e('Açık (Light)', 'gnn-logos'); ?></option>
                                    <option value="dark"><?php esc_html_e('Koyu (Dark)', 'gnn-logos'); ?></option>
                                    <option value="auto"><?php esc_html_e('Otomatik (Sistem Teması)', 'gnn-logos'); ?></option>
                                </select>
                            </div>
                            <div class="gnn-form-col">
                                <label for="sc_logo_filter"><strong><?php esc_html_e('Logo Renk Filtresi:', 'gnn-logos'); ?></strong></label>
                                <select id="sc_logo_filter" class="widefat">
                                    <option value="none"><?php esc_html_e('Orijinal Renkler', 'gnn-logos'); ?></option>
                                    <option value="grayscale"><?php esc_html_e('Gri Ton (Hover Renkli)', 'gnn-logos'); ?></option>
                                    <option value="monochrome-white"><?php esc_html_e('Monokrom Beyaz (Koyu Zemin)', 'gnn-logos'); ?></option>
                                    <option value="monochrome-dark"><?php esc_html_e('Monokrom Siyah (Açık Zemin)', 'gnn-logos'); ?></option>
                                </select>
                            </div>
                        </div>

                        <!-- 3. En-Boy Oranı, Logo Yüksekliği & Boşluk -->
                        <div class="gnn-form-row gnn-form-group">
                            <div class="gnn-form-col">
                                <label for="sc_aspect_ratio"><strong><?php esc_html_e('En-Boy Oranı:', 'gnn-logos'); ?></strong></label>
                                <select id="sc_aspect_ratio" class="widefat">
                                    <option value="auto"><?php esc_html_e('Otomatik', 'gnn-logos'); ?></option>
                                    <option value="4/3">4:3 (Sertifika)</option>
                                    <option value="16/9">16:9 (Geniş)</option>
                                    <option value="1/1">1:1 (Kare)</option>
                                    <option value="3/2">3:2</option>
                                    <option value="2/1">2:1 (Yatay İnce)</option>
                                </select>
                            </div>
                            <div class="gnn-form-col">
                                <label for="sc_logo_height"><strong><?php esc_html_e('Logo Yüksekliği:', 'gnn-logos'); ?></strong></label>
                                <input type="text" id="sc_logo_height" placeholder="örn: 56px" value="">
                            </div>
                            <div class="gnn-form-col">
                                <label for="sc_gap"><strong><?php esc_html_e('Boşluk (Gap):', 'gnn-logos'); ?></strong></label>
                                <input type="text" id="sc_gap" value="20px">
                            </div>
                        </div>

                        <!-- 4. Sütun Sayıları (Grid & Carousel için) -->
                        <div class="gnn-form-row gnn-form-group" id="gnn-columns-row">
                            <div class="gnn-form-col">
                                <label for="sc_columns"><?php esc_html_e('Masaüstü Sütun:', 'gnn-logos'); ?></label>
                                <input type="number" id="sc_columns" min="1" max="12" value="4">
                            </div>
                            <div class="gnn-form-col">
                                <label for="sc_columns_tablet"><?php esc_html_e('Tablet Sütun:', 'gnn-logos'); ?></label>
                                <input type="number" id="sc_columns_tablet" min="1" max="8" value="3">
                            </div>
                            <div class="gnn-form-col">
                                <label for="sc_columns_mobile"><?php esc_html_e('Mobil Sütun:', 'gnn-logos'); ?></label>
                                <input type="number" id="sc_columns_mobile" min="1" max="6" value="2">
                            </div>
                        </div>

                        <!-- 5. Metin, Rozet & Lightbox Seçenekleri -->
                        <div class="gnn-form-group">
                            <label><strong><?php esc_html_e('Görünüm & Etkileşim Seçenekleri:', 'gnn-logos'); ?></strong></label>
                            <div class="gnn-checkbox-list">
                                <label>
                                    <input type="checkbox" id="sc_show_code" checked>
                                    <?php esc_html_e('Sertifika / Standart Rozetlerini Göster', 'gnn-logos'); ?>
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
                                    <input type="checkbox" id="sc_lightbox">
                                    <?php esc_html_e('Sertifika / Logo Lightbox Modalını Etkinleştir (Tıklayınca Büyük Görünüm)', 'gnn-logos'); ?>
                                </label>
                            </div>
                        </div>

                        <!-- 5.1 Rozet Hizalama -->
                        <div class="gnn-form-row gnn-form-group" id="gnn-badges-options-box">
                            <div class="gnn-form-col">
                                <label for="sc_badges_layout"><strong><?php esc_html_e('Rozet Dizilimi:', 'gnn-logos'); ?></strong></label>
                                <select id="sc_badges_layout" class="widefat">
                                    <option value="wrap"><?php esc_html_e('Yan Yana (Wrap)', 'gnn-logos'); ?></option>
                                    <option value="stacked"><?php esc_html_e('Alt Alta (Stacked)', 'gnn-logos'); ?></option>
                                </select>
                            </div>
                            <div class="gnn-form-col">
                                <label for="sc_badges_align"><strong><?php esc_html_e('Yatay Hizalama:', 'gnn-logos'); ?></strong></label>
                                <select id="sc_badges_align" class="widefat">
                                    <option value="center"><?php esc_html_e('Ortalı', 'gnn-logos'); ?></option>
                                    <option value="left"><?php esc_html_e('Sola', 'gnn-logos'); ?></option>
                                    <option value="right"><?php esc_html_e('Sağa', 'gnn-logos'); ?></option>
                                </select>
                            </div>
                            <div class="gnn-form-col">
                                <label for="sc_badges_valign"><strong><?php esc_html_e('Dikey Hizalama:', 'gnn-logos'); ?></strong></label>
                                <select id="sc_badges_valign" class="widefat">
                                    <option value="bottom"><?php esc_html_e('Altta (Bottom)', 'gnn-logos'); ?></option>
                                    <option value="top"><?php esc_html_e('Üstte (Top)', 'gnn-logos'); ?></option>
                                    <option value="center"><?php esc_html_e('Ortada (Center)', 'gnn-logos'); ?></option>
                                </select>
                            </div>
                        </div>

                        <!-- 6. Karusel Özel Ayarları -->
                        <div class="gnn-form-group" id="gnn-carousel-options-box">
                            <label><strong><?php esc_html_e('Karusel Ayarları:', 'gnn-logos'); ?></strong></label>
                            <div class="gnn-checkbox-list">
                                <label>
                                    <input type="checkbox" id="sc_autoplay" checked>
                                    <?php esc_html_e('Otomatik Kaydır (Autoplay)', 'gnn-logos'); ?>
                                </label>
                                <label>
                                    <input type="checkbox" id="sc_arrows" checked>
                                    <?php esc_html_e('İleri / Geri Ok Butonlarını Göster', 'gnn-logos'); ?>
                                </label>
                                <label>
                                    <input type="checkbox" id="sc_dots">
                                    <?php esc_html_e('Sayfalama Noktalarını (Dots) Göster', 'gnn-logos'); ?>
                                </label>
                                <label>
                                    <input type="checkbox" id="sc_pause_on_hover" checked>
                                    <?php esc_html_e('Fare Üzerine Gelince Duraklat', 'gnn-logos'); ?>
                                </label>
                            </div>
                            <div class="gnn-form-row gnn-subrow">
                                <div class="gnn-form-col">
                                    <label for="sc_autoplay_speed"><?php esc_html_e('Geçiş Aralığı (ms):', 'gnn-logos'); ?></label>
                                    <input type="number" id="sc_autoplay_speed" value="3000" min="500" step="500">
                                </div>
                            </div>
                        </div>

                        <!-- 7. Marquee Özel Ayarları -->
                        <div class="gnn-form-group" id="gnn-marquee-options-box" style="display:none;">
                            <label><strong><?php esc_html_e('Sonsuz Şerit (Marquee) Ayarları:', 'gnn-logos'); ?></strong></label>
                            <div class="gnn-form-row gnn-subrow">
                                <div class="gnn-form-col">
                                    <label for="sc_marquee_speed"><?php esc_html_e('Döngü Süresi:', 'gnn-logos'); ?></label>
                                    <input type="text" id="sc_marquee_speed" value="25s">
                                </div>
                                <div class="gnn-form-col">
                                    <label for="sc_direction"><?php esc_html_e('Akış Yönü:', 'gnn-logos'); ?></label>
                                    <select id="sc_direction" class="widefat">
                                        <option value="left"><?php esc_html_e('Sola Doğru (Left)', 'gnn-logos'); ?></option>
                                        <option value="right"><?php esc_html_e('Sağa Doğru (Right)', 'gnn-logos'); ?></option>
                                    </select>
                                </div>
                                <div class="gnn-form-col">
                                    <label for="sc_item_width"><?php esc_html_e('Kutu Genişliği:', 'gnn-logos'); ?></label>
                                    <input type="text" id="sc_item_width" placeholder="örn: 170px" value="">
                                </div>
                            </div>
                            <div class="gnn-checkbox-list gnn-subrow">
                                <label>
                                    <input type="checkbox" id="sc_fade_edges" checked>
                                    <?php esc_html_e('Kenarlarda Yumuşak Geçiş Maskesi (Fade Edges)', 'gnn-logos'); ?>
                                </label>
                                <label>
                                    <input type="checkbox" id="sc_marquee_pause" checked>
                                    <?php esc_html_e('Fare Üzerine Gelince Duraklat', 'gnn-logos'); ?>
                                </label>
                            </div>
                        </div>

                        <!-- 8. Sorgu & Sıralama -->
                        <div class="gnn-form-row gnn-form-group">
                            <div class="gnn-form-col">
                                <label for="sc_orderby"><strong><?php esc_html_e('Sıralama Ölçütü:', 'gnn-logos'); ?></strong></label>
                                <select id="sc_orderby" class="widefat">
                                    <option value="menu_order"><?php esc_html_e('Özel Sıra (Sürükle-Bırak)', 'gnn-logos'); ?></option>
                                    <option value="title"><?php esc_html_e('Başlık (A-Z)', 'gnn-logos'); ?></option>
                                    <option value="date"><?php esc_html_e('Eklenme Tarihi', 'gnn-logos'); ?></option>
                                    <option value="rand"><?php esc_html_e('Rastgele', 'gnn-logos'); ?></option>
                                </select>
                            </div>
                            <div class="gnn-form-col">
                                <label for="sc_order"><strong><?php esc_html_e('Yön:', 'gnn-logos'); ?></strong></label>
                                <select id="sc_order" class="widefat">
                                    <option value="ASC"><?php esc_html_e('Artan (ASC)', 'gnn-logos'); ?></option>
                                    <option value="DESC"><?php esc_html_e('Azalan (DESC)', 'gnn-logos'); ?></option>
                                </select>
                            </div>
                            <div class="gnn-form-col">
                                <label for="sc_limit"><strong><?php esc_html_e('Limit (-1 = Tümü):', 'gnn-logos'); ?></strong></label>
                                <input type="number" id="sc_limit" value="-1" min="-1" max="200">
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Sağ Kolon: Shortcode Çıktısı & Kopyalama -->
                <div class="gnn-generator-preview-card">
                    <h2><?php esc_html_e('Oluşturulan Shortcode', 'gnn-logos'); ?></h2>
                    <p><?php esc_html_e('Kodu kopyalayıp herhangi bir sayfaya yapıştırın veya Gutenberg blok ekle menüsünden "GNN Logos" bloğunu doğrudan kullanın:', 'gnn-logos'); ?></p>

                    <div class="gnn-code-box">
                        <textarea id="gnn-output-shortcode" readonly rows="5" class="widefat">[gnn_logos]</textarea>
                    </div>

                    <button type="button" class="button button-primary button-hero" id="gnn-copy-shortcode-btn">
                        <span class="dashicons dashicons-clipboard"></span>
                        <?php esc_html_e('Shortcode\'u Kopyala', 'gnn-logos'); ?>
                    </button>
                    <span id="gnn-copy-feedback" class="gnn-copy-feedback" style="display:none;"></span>

                    <hr class="gnn-divider">

                    <div class="gnn-info-box">
                        <h4><?php esc_html_e('AAA İpuçları', 'gnn-logos'); ?></h4>
                        <ul>
                            <li><strong><?php esc_html_e('Koyu Footerlar için:', 'gnn-logos'); ?></strong> <code>theme="dark"</code> + <code>logo_filter="monochrome-white"</code> + <code>logo_height="44px"</code> kullanın.</li>
                            <li><strong><?php esc_html_e('Sertifikalar için:', 'gnn-logos'); ?></strong> <code>style="card"</code> + <code>aspect_ratio="4/3"</code> + <code>lightbox="true"</code> ile tam ekran inceleme sağlayın.</li>
                            <li><strong><?php esc_html_e('Gutenberg Bloğu:', 'gnn-logos'); ?></strong> <?php esc_html_e('Blok Editör ve FSE Footer içinde doğrudan "GNN Logos" bloğunu arayarak ekleyebilirsiniz.', 'gnn-logos'); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
