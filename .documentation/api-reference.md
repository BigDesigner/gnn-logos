# API ve Arayüz Başvuru Belirtimi

<!-- Verified from: gnn-logos/gnn-logos.php#L120-L130 -->
<!-- Verified from: gnn-logos/includes/class-gnn-logos-admin.php#L35-L115 -->
<!-- Verified from: gnn-logos/includes/class-gnn-logos-shortcode.php#L60-L125 -->
<!-- Verified from: gnn-logos/inc/updater.php#L280-L297 -->

## 1. Yetkilendirme ve Güvenlik Sınırları

| Arayüz Türü | Yetkilendirme Mekanizması | Gerekli Yetki (Capability) | Uygulanan Güvenlik Korumaları |
|---|---|---|---|
| **CPT Meta Kutusu Kaydı** | Kriptografik Nonce & Yetki Kontrolü | `edit_post` (`$post_id`) | `wp_verify_nonce($_POST['gnn_logos_meta_nonce'], 'gnn_logos_meta_box_nonce_action')` |
| **Yönetim Ayarları & Sihirbaz** | WordPress Kullanıcı Oturumu | `manage_options` | `add_menu_page()` ve `add_submenu_page()` seviyesinde zorunlu tutulur |
| **Manuel Güncelleme Kontrolü** | Nonce & Eklenti Güncelleme Yetkisi | `update_plugins` | `wp_verify_nonce($_GET['_wpnonce'], 'gnn_logos_manual_update')` ve `current_user_can('update_plugins')` |
| **Önyüz Shortcode** | Genel Erişim | Yok (Halka Açık) | `WP_Query` ile salt-okunur sorgu, HTML etiketlerinde bağlamsal kaçış (contextual escaping) |

---

## 2. Genel Shortcode Arayüzü (`[gnn_logos]`)

Temel önyüz vitrin arayüzü `add_shortcode('gnn_logos', ...)` ile kaydedilen `[gnn_logos]` etiketidir.

### 2.1 Parametre Belirtim Tablosu

<!-- Verified from: gnn-logos/includes/class-gnn-logos-shortcode.php#L70-L140 -->

| Parametre | Veri Türü | Varsayılan | Kabul Edilen Değerler | Açıklama ve Doğrulama Kuralları |
|---|---|---|---|---|
| `group` | string | `""` | Geçerli taksonomi slug'ı | `gnn_logo_group` slug değerine göre filtreler. `sanitize_title()` ile temizlenir. Boş bırakılırsa tüm gruplar listelenir. |
| `layout` | string | `'carousel'` | `'carousel'`, `'marquee'`, `'grid'` | Vitrin sunum modu. `in_array()` ile doğrulanır. |
| `style` | string | `'minimal'` | `'minimal'`, `'card'`, `'bordered'` | Kart görsel stili. `'card'` stili çerçeve, gölge ve yuvarlatılmış köşeler uygular. |
| `aspect_ratio` | string | `'auto'` | `'16/9'`, `'4/3'`, `'1/1'`, `'3/2'`, `'2/1'`, `'auto'` | Görsel kutusuna modern CSS en-boy oranı zorlar. `:` karakterini otomatik `/` yapar. |
| `columns` | integer | `4` | `1` - `10` | Izgara ve karusel için masaüstü sütun sayısı. `absint()` ile temizlenir. |
| `columns_tablet` | integer | `3` | `1` - `8` | Tablet ekran sütun sayısı. `absint()` ile temizlenir. |
| `columns_mobile` | integer | `2` | `1` - `4` | Mobil ekran sütun sayısı. `absint()` ile temizlenir. |
| `gap` | string | `'20px'` | Geçerli CSS birimi (px, rem) | Öğeler arasındaki boşluk. `sanitize_text_field()` ile temizlenir; sayısal ise `px` eklenir. |
| `show_code` | boolean | `true` | `'true'`, `'false'` | Kalite belgesi ve standart kod rozetlerinin görünürlüğü. `filter_var(..., FILTER_VALIDATE_BOOLEAN)` ile doğrulanır. |
| `badges_layout` | string | `'wrap'` | `'wrap'`, `'stacked'` | Rozet yerleşim dizilimi: `'wrap'` akıcı flexbox yan yana, `'stacked'` tek sütun alt alta. |
| `badges_align` | string | `'center'` | `'center'`, `'left'`, `'right'` | Rozetlerin kart içindeki yatay hizalaması. |
| `badges_valign` | string | `'bottom'` | `'bottom'`, `'top'`, `'center'` | Rozetlerin kart içindeki dikey hizalaması: `'bottom'` (en alta sabitli), `'top'` (logonun hemen altında), `'center'` (dikey ortalı). |
| `show_title` | boolean | `false` | `'true'`, `'false'` | Logo ve firma başlığının görünürlüğü. |
| `show_desc` | boolean | `false` | `'true'`, `'false'` | Alt açıklama / kurum metninin görünürlüğü. |
| `grayscale` | boolean | `false` | `'true'`, `'false'` | `true` ise görseller %100 siyah-beyaz başlar, fare üzerine gelince renklenir. |
| `autoplay` | boolean | `true` | `'true'`, `'false'` | Karusel modunda slaytların otomatik kayması. |
| `autoplay_speed` | integer | `3000` | Milisaniye | Karusel otomatik geçiş aralığı (örn: `3000` = 3 saniye). `absint()` ile temizlenir. |
| `speed` | string | `'25s'` | CSS zaman birimi | Marquee kayan şeridinin tam tur dönüş süresi (örn: `20s`, `30s`). |
| `direction` | string | `'left'` | `'left'`, `'right'` | Marquee akış yönü. |
| `arrows` | boolean | `true` | `'true'`, `'false'` | Karusel navigasyon ok butonlarının görünürlüğü. |
| `dots` | boolean | `false` | `'true'`, `'false'` | Karusel alt sayfalama noktalarının görünürlüğü. |
| `pause_on_hover`| boolean | `true` | `'true'`, `'false'` | Fareyle üzerine gelindiğinde kaymayı duraklatma. |
| `limit` | integer | `-1` | Öğe sayısı | Listelenecek maksimum öğe adedi (`-1` sınırsız). `intval()` ile temizlenir. |
| `orderby` | string | `'menu_order'` | `'none'`, `'ID'`, `'author'`, `'title'`, `'name'`, `'type'`, `'date'`, `'modified'`, `'parent'`, `'rand'`, `'menu_order'` | Sıralama ölçütü. Katı beyaz liste (`$allowed_orderby`) ile doğrulanır. |
| `order` | string | `'ASC'` | `'ASC'`, `'DESC'` | Sıralama yönü. `in_array()` ile doğrulanır. |

---

### 2.2 Shortcode Kullanım Senaryoları

<details>
<summary><b>Örnek Shortcode Kullanım Senaryoları</b></summary>

#### Senaryo 1: TS EN Sertifikaları İçin Kart Izgarası
```html
[gnn_logos group="kalite-belgeleri" layout="grid" style="card" aspect_ratio="4/3" columns="4" show_code="true" badges_layout="wrap" badges_align="center" badges_valign="bottom"]
```

#### Senaryo 2: Referanslar İçin Sonsuz Kayan Şerit (Sola Doğru)
```html
[gnn_logos group="referanslar" layout="marquee" speed="20s" direction="left" grayscale="true" pause_on_hover="true"]
```

#### Senaryo 3: Çözüm Ortakları Dokunmatik Slider (Karusel)
```html
[gnn_logos group="cozum-ortaklari" layout="carousel" columns="5" aspect_ratio="16/9" autoplay="true" autoplay_speed="3000" arrows="true" dots="true"]
```
</details>

---

## 3. Yönetim Paneli Rotaları ve Kanca Başvurusu

<!-- Verified from: gnn-logos/includes/class-gnn-logos-admin.php#L35-L95 -->
<!-- Verified from: gnn-logos/inc/updater.php#L55-L65 -->

| Rota / Kanca Tanımlayıcısı | HTTP Metodu | Yetki Kontrolü | Eylem / Amaç | Dosya Kanıtı |
|---|---|---|---|---|
| `admin.php?page=gnn-logos` | `GET` | `manage_options` | Üst seviye menü girişi (`'79.109'`); otomatik olarak CPT listeleme tablosuna yönlendirir | `includes/class-gnn-logos-admin.php#L38` |
| `edit.php?post_type=gnn_logo` | `GET` | `edit_posts` | Küçük görsel, standart rozetleri ve grubu gösteren CPT yönetim tablosu | `includes/class-gnn-logos-cpt.php#L360` |
| `post-new.php?post_type=gnn_logo` | `GET` / `POST` | `edit_posts` | Yeni logo/sertifika ekleme ekranı; ortam seçici ve etiket hap yöneticisini sunar | `includes/class-gnn-logos-cpt.php#L90` |
| `edit-tags.php?taxonomy=gnn_logo_group` | `GET` / `POST` | `manage_categories` | Logo grupları için taksonomi terim yönetim ekranı | `includes/class-gnn-logos-cpt.php#L65` |
| `admin.php?page=gnn-logos-generator` | `GET` | `manage_options` | 4 hazır şablon butonlu interaktif Shortcode Oluşturucu arayüzü | `includes/class-gnn-logos-admin.php#L70` |
| `plugins.php?gnn_logos_check_update=1` | `GET` | `update_plugins` | `_wpnonce` korumalı manuel güncelleme tetikleyicisi; transientleri temizler ve çekirdek güncelleyiciye yönlendirir | `inc/updater.php#L280` |
| `site_transient_update_plugins` | Filtre Kancası | Dahili | Güncelleme kontrollerinde yeni sürüm verisini enjekte eder veya eski bildirimleri temizler | `inc/updater.php#L58` |
| `upgrader_post_install` | Filtre Kancası | `update_plugins` | GitHub ZIP açıldıktan sonra klasör adını `gnn-logos` olarak normalleştirir | `inc/updater.php#L260` |
