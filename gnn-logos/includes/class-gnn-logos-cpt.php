<?php
/**
 * Custom Post Type, Taxonomy, Meta Box, Drag-and-Drop Reordering,
 * Group Filter, and Bulk Upload Engine for GNN Logos.
 *
 * @package GNN_Logos
 * @since   1.0.0
 */

defined('ABSPATH') || exit;

/**
 * Class GNN_Logos_CPT
 */
class GNN_Logos_CPT
{
    /**
     * Post type identifier.
     */
    const POST_TYPE = 'gnn_logo';

    /**
     * Taxonomy identifier.
     */
    const TAXONOMY = 'gnn_logo_group';

    /**
     * Constructor.
     */
    public function __construct()
    {
        add_action('init', array(__CLASS__, 'register_post_type_and_taxonomies'));
        add_action('add_meta_boxes', array($this, 'register_meta_boxes'));
        add_action('save_post_' . self::POST_TYPE, array($this, 'save_meta_boxes'), 10, 2);

        // Admin table customizers, sorting & filtering
        add_filter('manage_' . self::POST_TYPE . '_posts_columns', array($this, 'set_custom_columns'));
        add_action('manage_' . self::POST_TYPE . '_posts_custom_column', array($this, 'render_custom_column'), 10, 2);
        add_filter('manage_edit-' . self::POST_TYPE . '_sortable_columns', array($this, 'set_sortable_columns'));
        add_action('pre_get_posts', array($this, 'default_admin_order'));
        add_action('restrict_manage_posts', array($this, 'render_group_filter'));
        add_action('manage_posts_extra_tablenav', array($this, 'render_bulk_upload_toolbar'));

        // AJAX handlers for Drag-and-Drop Reordering & Bulk Upload
        add_action('wp_ajax_gnn_logos_reorder', array($this, 'ajax_reorder_logos'));
        add_action('wp_ajax_gnn_logos_bulk_create', array($this, 'ajax_bulk_create_logos'));
    }

    /**
     * Register Custom Post Type and Custom Taxonomy.
     */
    public static function register_post_type_and_taxonomies()
    {
        // 1. Register Taxonomy: Logo Grupları
        $tax_labels = array(
            'name'              => _x('Logo Grupları', 'taxonomy general name', 'gnn-logos'),
            'singular_name'     => _x('Logo Grubu', 'taxonomy singular name', 'gnn-logos'),
            'search_items'      => __('Gruplarda Ara', 'gnn-logos'),
            'all_items'         => __('Tüm Logo Grupları', 'gnn-logos'),
            'parent_item'       => __('Üst Grup', 'gnn-logos'),
            'parent_item_colon' => __('Üst Grup:', 'gnn-logos'),
            'edit_item'         => __('Grubu Düzenle', 'gnn-logos'),
            'update_item'       => __('Grubu Güncelle', 'gnn-logos'),
            'add_new_item'      => __('Yeni Logo Grubu Ekle', 'gnn-logos'),
            'new_item_name'     => __('Yeni Grup Adı', 'gnn-logos'),
            'menu_name'         => __('Logo Grupları', 'gnn-logos'),
        );

        $tax_args = array(
            'hierarchical'      => true,
            'labels'            => $tax_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'logo-group'),
            'show_in_rest'      => false,
        );

        register_taxonomy(self::TAXONOMY, array(self::POST_TYPE), $tax_args);

        // 2. Register CPT: gnn_logo
        $cpt_labels = array(
            'name'               => _x('Logolar & Sertifikalar', 'Post type general name', 'gnn-logos'),
            'singular_name'      => _x('Logo / Sertifika', 'Post type singular name', 'gnn-logos'),
            'menu_name'          => _x('GNN Logos', 'Admin Menu text', 'gnn-logos'),
            'name_admin_bar'     => _x('Logo / Sertifika', 'Add New on Toolbar', 'gnn-logos'),
            'add_new'            => __('Yeni Ekle', 'gnn-logos'),
            'add_new_item'       => __('Yeni Logo / Sertifika Ekle', 'gnn-logos'),
            'new_item'           => __('Yeni Logo', 'gnn-logos'),
            'edit_item'          => __('Logoyu Düzenle', 'gnn-logos'),
            'view_item'          => __('Logoyu Görüntüle', 'gnn-logos'),
            'all_items'          => __('Tüm Logolar & Sertifikalar', 'gnn-logos'),
            'search_items'       => __('Logolarda Ara', 'gnn-logos'),
            'not_found'          => __('Kayıtlı logo veya sertifika bulunamadı.', 'gnn-logos'),
            'not_found_in_trash' => __('Çöp kutusunda logo bulunamadı.', 'gnn-logos'),
        );

        $cpt_args = array(
            'labels'             => $cpt_labels,
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => false, // Managed under custom position '79.109' in class-gnn-logos-admin.php
            'query_var'          => false,
            'rewrite'            => false,
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array('title', 'page-attributes'),
            'show_in_rest'       => false,
        );

        register_post_type(self::POST_TYPE, $cpt_args);
    }

    /**
     * Register Meta Box for Logo/Certificate details.
     */
    public function register_meta_boxes()
    {
        add_meta_box(
            'gnn_logo_details_box',
            __('Logo & Sertifika Bilgileri', 'gnn-logos'),
            array($this, 'render_meta_box'),
            self::POST_TYPE,
            'normal',
            'high'
        );
    }

    /**
     * Render Meta Box HTML.
     *
     * @param WP_Post $post Current post object.
     */
    public function render_meta_box($post)
    {
        wp_nonce_field('gnn_logos_save_meta', 'gnn_logos_meta_nonce');

        $logo_id      = absint(get_post_meta($post->ID, '_gnn_logo_id', true));
        $description  = get_post_meta($post->ID, '_gnn_description', true);
        $link_url     = get_post_meta($post->ID, '_gnn_link_url', true);
        $link_target  = get_post_meta($post->ID, '_gnn_link_target', true);
        $aspect_ratio = get_post_meta($post->ID, '_gnn_aspect_ratio', true);

        if (empty($link_target)) {
            $link_target = '_blank';
        }
        if (empty($aspect_ratio)) {
            $aspect_ratio = 'auto';
        }

        $logo_url = '';
        if ($logo_id) {
            $image_attributes = wp_get_attachment_image_src($logo_id, 'medium');
            if ($image_attributes) {
                $logo_url = $image_attributes[0];
            }
        }
        ?>
        <div class="gnn-meta-box-wrap">
            <!-- 1. Logo Seçimi -->
            <div class="gnn-meta-row">
                <label class="gnn-meta-label">
                    <strong><?php esc_html_e('Logo / Sertifika Görseli:', 'gnn-logos'); ?></strong>
                    <span class="description"><?php esc_html_e('(PNG, SVG, JPG, WebP, AVIF formatları önerilir)', 'gnn-logos'); ?></span>
                </label>
                <div class="gnn-media-upload-area">
                    <input type="hidden" name="gnn_logo_id" id="gnn_logo_id" value="<?php echo esc_attr($logo_id); ?>">

                    <div id="gnn-logo-preview" class="gnn-logo-preview <?php echo $logo_url ? 'has-image' : ''; ?>">
                        <?php if ($logo_url) : ?>
                            <img src="<?php echo esc_url($logo_url); ?>" alt="<?php esc_attr_e('Logo Önizleme', 'gnn-logos'); ?>">
                        <?php else : ?>
                            <span class="gnn-no-image-text"><?php esc_html_e('Görsel seçilmedi', 'gnn-logos'); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="gnn-media-buttons">
                        <button type="button" class="button button-primary" id="gnn-select-logo-btn">
                            <span class="dashicons dashicons-format-image"></span>
                            <?php esc_html_e('Gözat / Logo Seç', 'gnn-logos'); ?>
                        </button>
                        <button type="button" class="button button-link-delete" id="gnn-remove-logo-btn" style="<?php echo $logo_url ? '' : 'display:none;'; ?>">
                            <?php esc_html_e('Görseli Kaldır', 'gnn-logos'); ?>
                        </button>
                    </div>
                </div>
            </div>

            <!-- 2. Sertifika / Standart Kodları -->
            <div class="gnn-meta-row">
                <label class="gnn-meta-label">
                    <strong><?php esc_html_e('Sertifika / Standart Kodları (Rozetler):', 'gnn-logos'); ?></strong>
                    <span class="description"><?php esc_html_e('Bu sertifika/logo için 1 veya birden fazla standart kodu ekleyebilirsiniz (Örn: TS EN 12201-2, TS EN ISO 1452-2, TS EN 1555-2). Her biri frontend\'de ayrı ve şık bir rozet olarak gösterilir.', 'gnn-logos'); ?></span>
                </label>

                <?php
                $cert_codes = get_post_meta($post->ID, '_gnn_cert_codes', true);
                if (!is_array($cert_codes) || empty($cert_codes)) {
                    $legacy_code = get_post_meta($post->ID, '_gnn_cert_code', true);
                    if (!empty($legacy_code)) {
                        $parts = preg_split('/[\r\n,]+/', $legacy_code);
                        $cert_codes = array_filter(array_map('trim', $parts));
                    } else {
                        $cert_codes = array();
                    }
                }
                ?>

                <div class="gnn-standards-manager">
                    <div id="gnn-standards-tags-list" class="gnn-standards-tags-list">
                        <?php if (!empty($cert_codes)) : ?>
                            <?php foreach ($cert_codes as $code_item) : ?>
                                <span class="gnn-tag-chip">
                                    <span class="gnn-tag-text"><?php echo esc_html($code_item); ?></span>
                                    <input type="hidden" name="gnn_cert_codes[]" value="<?php echo esc_attr($code_item); ?>">
                                    <button type="button" class="gnn-tag-remove" title="<?php esc_attr_e('Kaldır', 'gnn-logos'); ?>" aria-label="<?php esc_attr_e('Kaldır', 'gnn-logos'); ?>">&times;</button>
                                </span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div class="gnn-standards-input-group">
                        <input type="text" id="gnn-new-standard-input" class="regular-text" placeholder="<?php esc_attr_e('Örn: TS EN 12201-2', 'gnn-logos'); ?>">
                        <button type="button" class="button button-secondary" id="gnn-add-standard-btn">
                            <span class="dashicons dashicons-plus-alt2"></span>
                            <?php esc_html_e('Standart Ekle', 'gnn-logos'); ?>
                        </button>
                    </div>
                    <span class="description gnn-standards-hint">
                        <?php esc_html_e('İpucu: Teker teker yazıp Enter tuşuna basabilir veya birden fazla kodu virgülle ayırarak (örn: TS EN 12201-2, TS EN ISO 1452-2) tek seferde ekleyebilirsiniz.', 'gnn-logos'); ?>
                    </span>
                </div>
            </div>

            <!-- 3. Ek Açıklama / Kurum Adı -->
            <div class="gnn-meta-row">
                <label for="gnn_description" class="gnn-meta-label">
                    <strong><?php esc_html_e('Ek Açıklama / Kurum Adı (Opsiyonel):', 'gnn-logos'); ?></strong>
                    <span class="description"><?php esc_html_e('Rozetin hemen altında küçük puntolu açıklama metni.', 'gnn-logos'); ?></span>
                </label>
                <input type="text" name="gnn_description" id="gnn_description" class="widefat" value="<?php echo esc_attr($description); ?>" placeholder="<?php esc_attr_e('Örn: TSE Onaylı Kalite Belgesi', 'gnn-logos'); ?>">
            </div>

            <!-- 4. Yönlendirme Linki (URL) -->
            <div class="gnn-meta-row">
                <label for="gnn_link_url" class="gnn-meta-label">
                    <strong><?php esc_html_e('Yönlendirme Linki (URL):', 'gnn-logos'); ?></strong>
                    <span class="description"><?php esc_html_e('Logoya veya sertifika kartına tıklandığında gidilecek web adresi veya PDF belgesi linki (Opsiyonel).', 'gnn-logos'); ?></span>
                </label>
                <input type="url" name="gnn_link_url" id="gnn_link_url" class="widefat" value="<?php echo esc_url($link_url); ?>" placeholder="https://example.com/belge.pdf">
            </div>

            <!-- 5. Link Hedefi -->
            <div class="gnn-meta-row">
                <label for="gnn_link_target" class="gnn-meta-label">
                    <strong><?php esc_html_e('Bağlantı Açılış Şekli:', 'gnn-logos'); ?></strong>
                </label>
                <select name="gnn_link_target" id="gnn_link_target">
                    <option value="_blank" <?php selected($link_target, '_blank'); ?>><?php esc_html_e('Yeni Sekmede Aç (_blank)', 'gnn-logos'); ?></option>
                    <option value="_self" <?php selected($link_target, '_self'); ?>><?php esc_html_e('Aynı Sayfada Aç (_self)', 'gnn-logos'); ?></option>
                </select>
            </div>

            <!-- 6. Özel En/Boy Oranı -->
            <div class="gnn-meta-row">
                <label for="gnn_aspect_ratio" class="gnn-meta-label">
                    <strong><?php esc_html_e('Özel En / Boy Oranı (Aspect Ratio):', 'gnn-logos'); ?></strong>
                    <span class="description"><?php esc_html_e('Bu logo için özel bir en/boy çerçevesi belirleyin (varsayılan: shortcode ayarını devralır).', 'gnn-logos'); ?></span>
                </label>
                <select name="gnn_aspect_ratio" id="gnn_aspect_ratio">
                    <option value="auto" <?php selected($aspect_ratio, 'auto'); ?>><?php esc_html_e('Otomatik / Shortcode Ayarını Kullan', 'gnn-logos'); ?></option>
                    <option value="16:9" <?php selected($aspect_ratio, '16:9'); ?>>16:9 (Geniş Dikdörtgen)</option>
                    <option value="4:3" <?php selected($aspect_ratio, '4:3'); ?>>4:3 (Sertifikalara Uygun)</option>
                    <option value="1:1" <?php selected($aspect_ratio, '1:1'); ?>>1:1 (Kare Mühür/Logo)</option>
                    <option value="3:2" <?php selected($aspect_ratio, '3:2'); ?>>3:2</option>
                    <option value="2:1" <?php selected($aspect_ratio, '2:1'); ?>>2:1 (Yatay İnce)</option>
                </select>
            </div>
        </div>
        <?php
    }

    /**
     * Save Meta Box data with nonce and capability verification.
     *
     * @param int     $post_id Post ID.
     * @param WP_Post $post    Post object.
     */
    public function save_meta_boxes($post_id, $post)
    {
        if (!isset($_POST['gnn_logos_meta_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['gnn_logos_meta_nonce'])), 'gnn_logos_save_meta')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (isset($_POST['gnn_logo_id'])) {
            $logo_id = absint($_POST['gnn_logo_id']);
            update_post_meta($post_id, '_gnn_logo_id', $logo_id);
        }

        if (isset($_POST['gnn_cert_codes']) && is_array($_POST['gnn_cert_codes'])) {
            $clean_codes = array();
            foreach ($_POST['gnn_cert_codes'] as $raw_code) {
                $c = sanitize_text_field(wp_unslash($raw_code));
                if ('' !== $c) {
                    $clean_codes[] = $c;
                }
            }
            update_post_meta($post_id, '_gnn_cert_codes', $clean_codes);
            update_post_meta($post_id, '_gnn_cert_code', implode(', ', $clean_codes));
        } elseif (isset($_POST['gnn_cert_code'])) {
            $cert_code = sanitize_textarea_field(wp_unslash($_POST['gnn_cert_code']));
            update_post_meta($post_id, '_gnn_cert_code', $cert_code);
            $parts = preg_split('/[\r\n,]+/', $cert_code);
            $clean_codes = array_filter(array_map('trim', $parts));
            update_post_meta($post_id, '_gnn_cert_codes', array_values($clean_codes));
        } else {
            delete_post_meta($post_id, '_gnn_cert_codes');
            delete_post_meta($post_id, '_gnn_cert_code');
        }

        if (isset($_POST['gnn_description'])) {
            $desc = sanitize_text_field(wp_unslash($_POST['gnn_description']));
            update_post_meta($post_id, '_gnn_description', $desc);
        }

        if (isset($_POST['gnn_link_url'])) {
            $link = esc_url_raw(wp_unslash($_POST['gnn_link_url']));
            update_post_meta($post_id, '_gnn_link_url', $link);
        }

        if (isset($_POST['gnn_link_target'])) {
            $target = in_array(wp_unslash($_POST['gnn_link_target']), array('_self', '_blank'), true) ? sanitize_text_field(wp_unslash($_POST['gnn_link_target'])) : '_blank';
            update_post_meta($post_id, '_gnn_link_target', $target);
        }

        if (isset($_POST['gnn_aspect_ratio'])) {
            $allowed_ratios = array('auto', '16:9', '4:3', '1:1', '3:2', '2:1');
            $ratio = in_array(wp_unslash($_POST['gnn_aspect_ratio']), $allowed_ratios, true) ? sanitize_text_field(wp_unslash($_POST['gnn_aspect_ratio'])) : 'auto';
            update_post_meta($post_id, '_gnn_aspect_ratio', $ratio);
        }
    }

    /**
     * Add custom columns to admin list table (including drag-and-drop handle).
     *
     * @param array $columns Existing columns.
     * @return array
     */
    public function set_custom_columns($columns)
    {
        $new_columns = array();
        $new_columns['cb']                          = $columns['cb'];
        $new_columns['gnn_drag']                    = '<span class="dashicons dashicons-sort" title="' . esc_attr__('Sürükleyip Sırala', 'gnn-logos') . '"></span>';
        $new_columns['gnn_preview']                 = __('Görsel', 'gnn-logos');
        $new_columns['title']                       = __('Başlık / Firma', 'gnn-logos');
        $new_columns['gnn_code']                    = __('Standart / Sertifika Kodu', 'gnn-logos');
        $new_columns['taxonomy-' . self::TAXONOMY]  = __('Logo Grubu', 'gnn-logos');
        $new_columns['gnn_link']                    = __('Hedef Link', 'gnn-logos');
        $new_columns['gnn_order']                   = __('Sıra', 'gnn-logos');
        $new_columns['date']                        = $columns['date'];

        return $new_columns;
    }

    /**
     * Register sortable admin columns.
     *
     * @param array $columns Sortable columns.
     * @return array
     */
    public function set_sortable_columns($columns)
    {
        $columns['gnn_order'] = 'menu_order';
        return $columns;
    }

    /**
     * Default admin list order to menu_order ASC so drag-and-drop matches frontend.
     *
     * @param WP_Query $query Current query.
     */
    public function default_admin_order($query)
    {
        if (!is_admin() || !$query->is_main_query()) {
            return;
        }
        if (self::POST_TYPE === $query->get('post_type') && !$query->get('orderby')) {
            $query->set('orderby', 'menu_order');
            $query->set('order', 'ASC');
        }
    }

    /**
     * Render Logo Group dropdown filter on All Logos list table.
     *
     * @param string $post_type Current post type.
     */
    public function render_group_filter($post_type)
    {
        if (self::POST_TYPE !== $post_type) {
            return;
        }

        $selected = isset($_GET[self::TAXONOMY]) ? sanitize_title(wp_unslash($_GET[self::TAXONOMY])) : '';

        wp_dropdown_categories(array(
            'show_option_all' => __('Tüm Logo Grupları', 'gnn-logos'),
            'taxonomy'        => self::TAXONOMY,
            'name'            => self::TAXONOMY,
            'orderby'         => 'name',
            'selected'        => $selected,
            'value_field'     => 'slug',
            'hierarchical'    => true,
            'show_count'      => true,
            'hide_empty'      => false,
        ));
    }

    /**
     * Render Bulk Upload toolbar inside the top tablenav of All Logos screen.
     *
     * @param string $which Top or bottom tablenav.
     */
    public function render_bulk_upload_toolbar($which)
    {
        if ('top' !== $which) {
            return;
        }

        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if (!$screen || 'edit-' . self::POST_TYPE !== $screen->id) {
            return;
        }

        if (!current_user_can('publish_posts')) {
            return;
        }

        $terms = get_terms(array(
            'taxonomy'   => self::TAXONOMY,
            'hide_empty' => false,
        ));
        ?>
        <div class="alignleft actions gnn-bulk-toolbar">
            <select id="gnn-bulk-group" aria-label="<?php esc_attr_e('Toplu yükleme hedef grubu', 'gnn-logos'); ?>">
                <option value=""><?php esc_html_e('Grup Seç (Opsiyonel)', 'gnn-logos'); ?></option>
                <?php if (!empty($terms) && !is_wp_error($terms)) : ?>
                    <?php foreach ($terms as $term) : ?>
                        <option value="<?php echo esc_attr($term->slug); ?>"><?php echo esc_html($term->name); ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            <button type="button" class="button button-primary" id="gnn-bulk-upload-btn">
                <span class="dashicons dashicons-upload"></span>
                <?php esc_html_e('Toplu Logo Yükle', 'gnn-logos'); ?>
            </button>
            <span id="gnn-table-status" class="gnn-table-status" role="status" aria-live="polite"></span>
        </div>
        <?php
    }

    /**
     * Render custom column content.
     *
     * @param string $column  Column name.
     * @param int    $post_id Post ID.
     */
    public function render_custom_column($column, $post_id)
    {
        switch ($column) {
            case 'gnn_drag':
                echo '<span class="dashicons dashicons-menu gnn-drag-handle" title="' . esc_attr__('Sürükleyip sıralayın', 'gnn-logos') . '" aria-hidden="true"></span>';
                break;

            case 'gnn_preview':
                $logo_id = absint(get_post_meta($post_id, '_gnn_logo_id', true));
                if ($logo_id) {
                    $thumb = wp_get_attachment_image($logo_id, array(60, 60), false, array(
                        'class' => 'gnn-col-thumb',
                    ));
                    echo $thumb ? $thumb : '<span class="gnn-col-muted">&mdash;</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                } else {
                    echo '<span class="gnn-col-muted">' . esc_html__('Yok', 'gnn-logos') . '</span>';
                }
                break;

            case 'gnn_code':
                $codes = get_post_meta($post_id, '_gnn_cert_codes', true);
                if (!is_array($codes) || empty($codes)) {
                    $legacy = get_post_meta($post_id, '_gnn_cert_code', true);
                    if (!empty($legacy)) {
                        $parts = preg_split('/[\r\n,]+/', $legacy);
                        $codes = array_filter(array_map('trim', $parts));
                    }
                }
                if (!empty($codes)) {
                    echo '<div class="gnn-col-badges">';
                    foreach ($codes as $c) {
                        echo '<span class="gnn-col-badge">' . esc_html($c) . '</span>';
                    }
                    echo '</div>';
                } else {
                    echo '<span class="gnn-col-muted">&mdash;</span>';
                }
                break;

            case 'gnn_link':
                $url = get_post_meta($post_id, '_gnn_link_url', true);
                if (!empty($url)) {
                    echo '<a href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer" class="gnn-col-link">' . esc_html(wp_trim_words($url, 4, '...')) . '</a>';
                } else {
                    echo '<span class="gnn-col-muted">&mdash;</span>';
                }
                break;

            case 'gnn_order':
                $post = get_post($post_id);
                echo $post ? '<span class="gnn-order-num">' . esc_html($post->menu_order) . '</span>' : '<span class="gnn-col-muted">&mdash;</span>';
                break;
        }
    }

    /**
     * AJAX: Save drag-and-drop logo order (menu_order).
     */
    public function ajax_reorder_logos()
    {
        check_ajax_referer('gnn_logos_admin_nonce', 'nonce');

        if (!current_user_can('edit_others_posts') && !current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Yetkiniz bulunmuyor.', 'gnn-logos')), 403);
        }

        $raw_order = isset($_POST['order']) && is_array($_POST['order']) ? $_POST['order'] : array();
        $offset    = isset($_POST['offset']) ? absint($_POST['offset']) : 0;

        if (empty($raw_order)) {
            wp_send_json_error(array('message' => __('Geçersiz sıralama verisi.', 'gnn-logos')), 400);
        }

        global $wpdb;
        $updated = array();

        foreach ($raw_order as $index => $raw_id) {
            $pid = absint($raw_id);
            if ($pid <= 0) {
                continue;
            }
            $new_order = $offset + absint($index);
            $wpdb->update(
                $wpdb->posts,
                array('menu_order' => $new_order),
                array('ID' => $pid, 'post_type' => self::POST_TYPE),
                array('%d'),
                array('%d', '%s')
            );
            clean_post_cache($pid);
            $updated[$pid] = $new_order;
        }

        wp_send_json_success(array(
            'message' => __('Sıralama kaydedildi.', 'gnn-logos'),
            'orders'  => $updated,
        ));
    }

    /**
     * AJAX: Bulk create gnn_logo items from WordPress Media Library selection.
     */
    public function ajax_bulk_create_logos()
    {
        check_ajax_referer('gnn_logos_admin_nonce', 'nonce');

        if (!current_user_can('publish_posts')) {
            wp_send_json_error(array('message' => __('Logo oluşturma yetkiniz bulunmuyor.', 'gnn-logos')), 403);
        }

        $raw_ids    = isset($_POST['attachment_ids']) && is_array($_POST['attachment_ids']) ? $_POST['attachment_ids'] : array();
        $group_slug = isset($_POST['group']) ? sanitize_title(wp_unslash($_POST['group'])) : '';

        if (empty($raw_ids)) {
            wp_send_json_error(array('message' => __('Görsel seçilmedi.', 'gnn-logos')), 400);
        }

        global $wpdb;
        $max_order = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT MAX(menu_order) FROM {$wpdb->posts} WHERE post_type = %s AND post_status != 'trash'",
                self::POST_TYPE
            )
        );

        $created = 0;
        foreach ($raw_ids as $raw_att_id) {
            $att_id = absint($raw_att_id);
            if ($att_id <= 0 || 'attachment' !== get_post_type($att_id)) {
                continue;
            }

            $att_post = get_post($att_id);
            $title    = ($att_post && '' !== trim($att_post->post_title)) ? $att_post->post_title : sprintf(__('Logo #%d', 'gnn-logos'), $att_id);

            $max_order++;
            $new_post_id = wp_insert_post(array(
                'post_type'   => self::POST_TYPE,
                'post_status' => 'publish',
                'post_title'  => sanitize_text_field($title),
                'menu_order'  => $max_order,
            ), true);

            if (!is_wp_error($new_post_id) && $new_post_id > 0) {
                update_post_meta($new_post_id, '_gnn_logo_id', $att_id);
                update_post_meta($new_post_id, '_gnn_link_target', '_blank');
                update_post_meta($new_post_id, '_gnn_aspect_ratio', 'auto');

                if ('' !== $group_slug) {
                    wp_set_object_terms($new_post_id, array($group_slug), self::TAXONOMY, false);
                }
                $created++;
            }
        }

        wp_send_json_success(array(
            'created' => $created,
            'message' => sprintf(__('%d adet logo başarıyla eklendi.', 'gnn-logos'), $created),
        ));
    }
}
