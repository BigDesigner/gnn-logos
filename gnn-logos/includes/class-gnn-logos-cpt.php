<?php
/**
 * Custom Post Type, Taxonomy, and Meta Box Engine for GNN Logos.
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

        // Admin column customizers
        add_filter('manage_' . self::POST_TYPE . '_posts_columns', array($this, 'set_custom_columns'));
        add_action('manage_' . self::POST_TYPE . '_posts_custom_column', array($this, 'render_custom_column'), 10, 2);
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
            'name'                  => _x('Logolar & Sertifikalar', 'Post type general name', 'gnn-logos'),
            'singular_name'         => _x('Logo / Sertifika', 'Post type singular name', 'gnn-logos'),
            'menu_name'             => _x('GNN Logos', 'Admin Menu text', 'gnn-logos'),
            'name_admin_bar'        => _x('Logo / Sertifika', 'Add New on Toolbar', 'gnn-logos'),
            'add_new'               => __('Yeni Ekle', 'gnn-logos'),
            'add_new_item'          => __('Yeni Logo / Sertifika Ekle', 'gnn-logos'),
            'new_item'              => __('Yeni Logo', 'gnn-logos'),
            'edit_item'             => __('Logoyu Düzenle', 'gnn-logos'),
            'view_item'             => __('Logoyu Görüntüle', 'gnn-logos'),
            'all_items'             => __('Tüm Logolar & Sertifikalar', 'gnn-logos'),
            'search_items'          => __('Logolarda Ara', 'gnn-logos'),
            'not_found'             => __('Kayıtlı logo veya sertifika bulunamadı.', 'gnn-logos'),
            'not_found_in_trash'    => __('Çöp kutusunda logo bulunamadı.', 'gnn-logos'),
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
            'supports'           => array('title', 'page-attributes'), // page-attributes allows menu_order
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
        $cert_code    = get_post_meta($post->ID, '_gnn_cert_code', true);
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
            <!-- 1. Logo Seçimi (WordPress Ortam Kütüphanesi) -->
            <div class="gnn-meta-row">
                <label class="gnn-meta-label">
                    <strong><?php esc_html_e('Logo / Sertifika Görseli:', 'gnn-logos'); ?></strong>
                    <span class="description"><?php esc_html_e('(PNG, SVG, JPG, WebP formatları önerilir)', 'gnn-logos'); ?></span>
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

            <!-- 2. Sertifika / Standart Kodları (Çoklu Standart Desteği) -->
            <div class="gnn-meta-row">
                <label class="gnn-meta-label">
                    <strong><?php esc_html_e('Sertifika / Standart Kodları (Rozetler):', 'gnn-logos'); ?></strong>
                    <span class="description"><?php esc_html_e('Bu sertifika/logo için 1 veya birden fazla standart kodu ekleyebilirsiniz (Örn: TS EN 12201-2, TS EN ISO 1452-2, TS EN 1555-2). Her biri frontend\'de ayrı ve şık bir rozet olarak gösterilir.', 'gnn-logos'); ?></span>
                </label>

                <?php
                // Fetch existing codes (array or split legacy string)
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
                            <span class="dashicons dashicons-plus-alt2" style="vertical-align:middle; margin-top:-2px;"></span>
                            <?php esc_html_e('Standart Ekle', 'gnn-logos'); ?>
                        </button>
                    </div>
                    <span class="description" style="margin-top:6px; display:block;">
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
                <input type="text" name="gnn_description" id="gnn_description" class="widefat" value="<?php echo esc_attr($description); ?>" placeholder="Örn: TSE Onaylı Kalite Belgesi">
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

            <!-- 6. Özel En/Boy Oranı (Aspect Ratio) -->
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
        // 1. Verify Nonce
        if (!isset($_POST['gnn_logos_meta_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['gnn_logos_meta_nonce'])), 'gnn_logos_save_meta')) {
            return;
        }

        // 2. Check Autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // 3. Check User Capabilities
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // 4. Sanitize and save _gnn_logo_id
        if (isset($_POST['gnn_logo_id'])) {
            $logo_id = absint($_POST['gnn_logo_id']);
            update_post_meta($post_id, '_gnn_logo_id', $logo_id);
        }

        // 5. Sanitize and save _gnn_cert_codes and legacy _gnn_cert_code
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
            // Field was cleared
            delete_post_meta($post_id, '_gnn_cert_codes');
            delete_post_meta($post_id, '_gnn_cert_code');
        }

        // 6. Sanitize and save _gnn_description
        if (isset($_POST['gnn_description'])) {
            $desc = sanitize_text_field(wp_unslash($_POST['gnn_description']));
            update_post_meta($post_id, '_gnn_description', $desc);
        }

        // 7. Sanitize and save _gnn_link_url
        if (isset($_POST['gnn_link_url'])) {
            $link = esc_url_raw(wp_unslash($_POST['gnn_link_url']));
            update_post_meta($post_id, '_gnn_link_url', $link);
        }

        // 8. Sanitize and save _gnn_link_target
        if (isset($_POST['gnn_link_target'])) {
            $target = in_array(wp_unslash($_POST['gnn_link_target']), array('_self', '_blank'), true) ? sanitize_text_field(wp_unslash($_POST['gnn_link_target'])) : '_blank';
            update_post_meta($post_id, '_gnn_link_target', $target);
        }

        // 9. Sanitize and save _gnn_aspect_ratio
        if (isset($_POST['gnn_aspect_ratio'])) {
            $allowed_ratios = array('auto', '16:9', '4:3', '1:1', '3:2', '2:1');
            $ratio = in_array(wp_unslash($_POST['gnn_aspect_ratio']), $allowed_ratios, true) ? sanitize_text_field(wp_unslash($_POST['gnn_aspect_ratio'])) : 'auto';
            update_post_meta($post_id, '_gnn_aspect_ratio', $ratio);
        }
    }

    /**
     * Add custom columns to admin list table.
     *
     * @param array $columns Existing columns.
     * @return array
     */
    public function set_custom_columns($columns)
    {
        $new_columns = array();
        $new_columns['cb']          = $columns['cb'];
        $new_columns['gnn_preview'] = __('Görsel', 'gnn-logos');
        $new_columns['title']       = __('Başlık / Firma', 'gnn-logos');
        $new_columns['gnn_code']    = __('Standart / Sertifika Kodu', 'gnn-logos');
        $new_columns['taxonomy-' . self::TAXONOMY] = __('Logo Grubu', 'gnn-logos');
        $new_columns['gnn_link']    = __('Hedef Link', 'gnn-logos');
        $new_columns['gnn_order']   = __('Sıra', 'gnn-logos');
        $new_columns['date']        = $columns['date'];

        return $new_columns;
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
            case 'gnn_preview':
                $logo_id = absint(get_post_meta($post_id, '_gnn_logo_id', true));
                if ($logo_id) {
                    $thumb = wp_get_attachment_image($logo_id, array(60, 60), false, array(
                        'style' => 'max-width:60px; max-height:45px; object-fit:contain; border:1px solid #e2e8f0; border-radius:4px; padding:2px; background:#fff;',
                    ));
                    echo $thumb ? $thumb : '<span style="color:#94a3b8;">&mdash;</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                } else {
                    echo '<span style="color:#94a3b8;">' . esc_html__('Yok', 'gnn-logos') . '</span>';
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
                    echo '<div style="display:flex; flex-wrap:wrap; gap:4px;">';
                    foreach ($codes as $c) {
                        echo '<span style="display:inline-block; padding:2px 7px; font-weight:600; font-size:11px; background:#f1f5f9; border:1px solid #cbd5e1; border-radius:4px; color:#0f172a; white-space:nowrap;">' . esc_html($c) . '</span>';
                    }
                    echo '</div>';
                } else {
                    echo '<span style="color:#94a3b8;">&mdash;</span>';
                }
                break;

            case 'gnn_link':
                $url = get_post_meta($post_id, '_gnn_link_url', true);
                if (!empty($url)) {
                    echo '<a href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer" style="font-size:12px;">' . esc_html(wp_trim_words($url, 4, '...')) . '</a>';
                } else {
                    echo '<span style="color:#94a3b8;">&mdash;</span>';
                }
                break;

            case 'gnn_order':
                $post = get_post($post_id);
                echo esc_html($post->menu_order);
                break;
        }
    }
}
