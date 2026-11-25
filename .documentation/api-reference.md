# API ve Arayüz Başvuru Belirtimi

<!-- Verified from: gnn-logos/includes/class-gnn-logos-shortcode.php -->
<!-- Verified from: gnn-logos/includes/class-gnn-logos-cpt.php -->
<!-- Verified from: gnn-logos/includes/class-gnn-logos-admin.php -->
<!-- Verified from: gnn-logos/includes/class-gnn-logos-block.php -->

## 1. Yetkilendirme ve Güvenlik Sınırları

| Arayüz Türü | Yetkilendirme Mekanizması | Gerekli Yetki (Capability) | Güvenlik Korumaları |
|---|---|---|---|
| **CPT Meta Kutusu Kaydı** | Nonce & Yetki | `edit_post` (`$post_id`) | `wp_verify_nonce($_POST['gnn_logos_meta_nonce'], 'gnn_logos_save_meta')` |
| **Sürükle-Bırak Sıralama AJAX** | AJAX Nonce & Yetki | `edit_others_posts` veya `manage_options` | `check_ajax_referer('gnn_logos_admin_nonce', 'nonce')`, post type doğrulaması (`gnn_logo`) |
| **Toplu Logo Oluşturma AJAX** | AJAX Nonce & Yetki | `publish_posts` | `check_ajax_referer('gnn_logos_admin_nonce', 'nonce')`, attachment MIME doğrulaması |
| **Canlı Önizleme AJAX** | AJAX Nonce & Yetki | `manage_options` | `check_ajax_referer('gnn_logos_admin_nonce', 'nonce')`, `GNN_Logos_Shortcode` sanitizasyonu |
| **Manuel Güncelleme Kontrolü** | Nonce & Yetki | `update_plugins` | `wp_verify_nonce($_GET['_wpnonce'], 'gnn_logos_manual_update')` |
| **Önyüz Shortcode & Blok** | Halka Açık | Yok (Salt-Okunur) | Katı beyaz listeler, regex CSS koruması, bağlamsal kaçış (`esc_html`, `esc_attr`, `esc_url`) |

---

## 2. Shortcode (`[gnn_logos]`) ve Gutenberg Bloğu (`gnn-logos/showcase`) Parametreleri

Her iki arayüz aynı 30+ nitelik sözleşmesini paylaşır (`GNN_Logos_Shortcode::get_defaults()`):

| Parametre | Tip | Varsayılan | Kabul Edilen Değerler / Doğrulama |
|---|---|---|---|
| `group` | string | `""` | `gnn_logo_group` terim slug'ı (`sanitize_title`) |
| `layout` | string | `'carousel'` | `'carousel'`, `'marquee'`, `'grid'` |
| `style` | string | `'minimal'` | `'minimal'`, `'card'`, `'bordered'`, `'glass'` |
| `theme` | string | `'light'` | `'light'`, `'dark'`, `'auto'` (`prefers-color-scheme`) |
| `logo_filter` | string | `'none'` | `'none'`, `'grayscale'`, `'monochrome-white'`, `'monochrome-dark'` |
| `aspect_ratio` | string | `'auto'` | `'auto'`, `'16/9'`, `'4/3'`, `'1/1'`, `'3/2'`, `'2/1'` (regex korumalı) |
| `logo_height` | string | `""` | CSS uzunluk değeri (örn: `56px`, `4rem`; regex korumalı) |
| `item_width` | string | `""` | Marquee öğe genişliği CSS değeri (örn: `180px`; regex korumalı) |
| `columns` | integer | `4` | `1`–`12` |
| `columns_tablet` | integer | `3` | `1`–`8` |
| `columns_mobile` | integer | `2` | `1`–`4` |
| `gap` | string | `'20px'` | CSS uzunluk değeri (regex korumalı) |
| `show_code` | boolean | `true` | `'true'` / `'false'` |
| `badges_layout` | string | `'wrap'` | `'wrap'`, `'stacked'` |
| `badges_align` | string | `'center'` | `'center'`, `'left'`, `'right'` |
| `badges_valign` | string | `'bottom'` | `'bottom'`, `'top'`, `'center'` |
| `show_title` | boolean | `false` | `'true'` / `'false'` |
| `show_desc` | boolean | `false` | `'true'` / `'false'` |
| `lightbox` | boolean | `false` | `'true'` / `'false'` (Erişilebilir sertifika/logo büyütme modalı) |
| `grayscale` | boolean | `false` | Geriye uyumlu kısa yol (`logo_filter="grayscale"`) |
| `autoplay` | boolean | `true` | `'true'` / `'false'` |
| `autoplay_speed` | integer | `3000` | Milisaniye (`>= 500`) |
| `arrows` | boolean | `true` | `'true'` / `'false'` |
| `dots` | boolean | `false` | `'true'` / `'false'` |
| `speed` | string | `'25s'` | CSS süre değeri (`s` veya `ms`; regex korumalı) |
| `direction` | string | `'left'` | `'left'`, `'right'` |
| `fade_edges` | boolean | `true` | `'true'` / `'false'` (Marquee kenar maskesi) |
| `pause_on_hover` | boolean | `true` | `'true'` / `'false'` |
| `limit` | integer | `-1` | `-1` (sınırsız) veya pozitif tamsayı |
| `orderby` | string | `'menu_order'` | `menu_order`, `date`, `title`, `rand`, `ID`, `author`, `name`, `modified`, `parent`, `none` |
| `order` | string | `'ASC'` | `'ASC'`, `'DESC'` |

### 2.1 Örnek Kullanım Senaryoları

<details>
<summary><b>v2.0.0 Örnek Shortcode Senaryoları</b></summary>

#### Koyu Footer Sonsuz Kayan Şerit (Beyaz Monokrom)
```html
[gnn_logos group="referanslar" layout="marquee" style="minimal" theme="dark" logo_filter="monochrome-white" logo_height="44px" item_width="160px" speed="28s" fade_edges="true" show_code="false"]
```

#### Lightbox Destekli Glassmorphism Sertifika Vitrini
```html
[gnn_logos group="sertifikalar" layout="grid" style="glass" theme="dark" aspect_ratio="4/3" columns="4" show_code="true" lightbox="true"]
```
</details>

---

## 3. Yönetim Paneli Rotaları ve AJAX Uç Noktaları

| Rota / Action | Metot | Yetki | Amaç | Dosya Kanıtı |
|---|---|---|---|---|
| `edit.php?post_type=gnn_logo` | `GET` | `edit_posts` | Sürükle-bırak sıralama, kategori filtresi ve Toplu Logo Yükleme çubuğunu içeren ana liste | `includes/class-gnn-logos-cpt.php` |
| `edit.php?post_type=gnn_logo&page=gnn-logos-generator` | `GET` | `manage_options` | 5 şablonlu ve canlı görsel önizlemeli Shortcode Oluşturucu | `includes/class-gnn-logos-admin.php` |
| `wp_ajax_gnn_logos_reorder` | `POST` | `edit_others_posts` / `manage_options` | Satır ID dizisine göre `menu_order` değerlerini günceller | `includes/class-gnn-logos-cpt.php` |
| `wp_ajax_gnn_logos_bulk_create` | `POST` | `publish_posts` | Seçilen medya ID'lerinden toplu `gnn_logo` kayıtları oluşturur ve opsiyonel gruba atar | `includes/class-gnn-logos-cpt.php` |
| `wp_ajax_gnn_logos_preview` | `POST` | `manage_options` | Shortcode oluşturucudaki ayarları gerçek render motorundan geçirip HTML döner | `includes/class-gnn-logos-admin.php` |
| `plugins.php?gnn_logos_check_update=1` | `GET` | `update_plugins` | Nonce korumalı manuel GitHub güncelleme kontrolü | `inc/updater.php` |
