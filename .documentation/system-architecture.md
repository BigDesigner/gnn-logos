# Sistem Mimarisi ve Topoloji Belirtimi

<!-- Verified from: gnn-logos/gnn-logos.php#L1-L155 -->
<!-- Verified from: .specs/boundary-conditions.md#L1-L85 -->

## 1. Dizin Topolojisi

```text
gnn-logos/
├── gnn-logos.php                     # [Çekirdek] Ana giriş noktası, singleton başlatıcı, eylem bağlantıları
├── readme.txt                         # [Metadata] WordPress.org depo gereksinimleri
├── uninstall.php                      # [Yaşam Döngüsü] Yazı, terim ve transient verilerini temizleyen güvenli kaldırıcı
├── LICENSE                            # [Lisans] GNU General Public License v2
├── inc/
│   └── updater.php                    # [Güncelleyici] Transient senkronizasyonlu GitHub Releases API istemcisi
├── includes/
│   ├── class-gnn-logos-cpt.php        # [Model/Admin] 'gnn_logo' CPT, 'gnn_logo_group' Taksonomisi, Meta Kutuları
│   ├── class-gnn-logos-shortcode.php  # [Denetleyici/Görünüm] [gnn_logos] shortcode işleyicisi, sorgu ve render motoru
│   └── class-gnn-logos-admin.php      # [Yönetim] '79.109' menüsü, alt menüler, interaktif Shortcode Oluşturucu arayüzü
└── assets/
    ├── css/
    │   ├── gnn-logos-frontend.css     # [Görünüm] CSS Grid, CSS Scroll-Snap, GPU marquee keyframe stilleri (<10 KB)
    │   └── gnn-logos-admin.css        # [Yönetim Görünümü] Meta kutusu düzeni, etiket hapları, buton dikey ortalama
    └── js/
        ├── gnn-logos-frontend.js      # [Görünüm] Karusel dokunma ve otomatik kaydırma için Vanilla JS mikro motor (<5 KB)
        └── gnn-logos-admin.js         # [Yönetim] WP Media uploader, etiket hap yöneticisi, canlı shortcode sihirbazı
```

---

## 2. Bileşen Mimari Şeması

Aşağıdaki şema tarayıcı isteklerini, yönetim paneli form gönderimlerini, shortcode çözümleme sürecini, veritabanı sorgularını ve harici GitHub güncelleme hattını modellemektedir:

```mermaid
flowchart TD
    subgraph Browser["İstemci Tarayıcısı"]
        UserPage["Ziyaretçi Sayfası (Shortcode Yürütme)"]
        AdminPage["WP Yönetim Paneli (Menü '79.109')"]
    end

    subgraph WordPressCore["WordPress Çekirdeği"]
        HookLoader["plugins_loaded / init / admin_init Kancaları"]
        WpQuery["WP_Query & Veritabanı Motoru"]
        MediaLib["wp.media (Ortam Kütüphanesi Modalı)"]
        CoreTransient["Transient API (_site_transient_update_plugins)"]
    end

    subgraph PluginArchitecture["GNN Logos Eklenti Katmanı"]
        Singleton["GNN_Logos (Singleton Koordinatör)"]
        CPT["GNN_Logos_CPT (CPT & Meta Kutusu Motoru)"]
        Shortcode["GNN_Logos_Shortcode (Shortcode & HTML Motoru)"]
        Admin["GNN_Logos_Admin (Yönetim & Sihirbaz)"]
        Updater["GNN_Logos_Updater (GitHub Releases Motoru)"]
    end

    subgraph Database["WordPress Veritabanı (MySQL/MariaDB)"]
        TablePosts["wp_posts (post_type = 'gnn_logo')"]
        TablePostmeta["wp_postmeta (_gnn_logo_id, _gnn_cert_codes vb.)"]
        TableTerms["wp_terms & wp_term_taxonomy ('gnn_logo_group')"]
        TableOptions["wp_options (Transientler: update_check, update_plugins)"]
    end

    subgraph External["Harici Ağ Servisleri"]
        GitHubAPI["GitHub Releases API (api.github.com)"]
        GitHubCDN["GitHub CDN Zipball (codeload.github.com)"]
    end

    %% İstek Girişleri
    UserPage -->| [gnn_logos] render isteği| Shortcode
    AdminPage -->|CPT Form Gönderimi| CPT
    AdminPage -->|Shortcode Sihirbazı| Admin
    AdminPage -->|Logo Görseli Seçimi| MediaLib

    %% Çekirdek Dağıtım
    HookLoader --> Singleton
    Singleton --> CPT
    Singleton --> Shortcode
    Singleton --> Admin
    Singleton --> Updater

    %% Veri Etkileşimleri
    CPT -->|Yazı ve Meta Kaydeder| TablePosts
    CPT -->|Meta Kutusu Değerlerini Kaydeder| TablePostmeta
    CPT -->|Taksonomi Terimlerini Bağlar| TableTerms
    Shortcode -->|Grup ve Sıralama Sorgusu| WpQuery
    WpQuery --> TablePosts
    WpQuery --> TablePostmeta
    WpQuery --> TableTerms

    %% Yönetim & Ortam Etkileşimleri
    MediaLib -->|attachment_id aktarır| Admin
    Admin -->|Ayarları ve Önizlemeyi Sunar| AdminPage

    %% Güncelleyici Akışı
    CoreTransient -->|Filtre: site_transient_update_plugins| Updater
    Updater -->|HTTP GET İsteği (12 saat TTL)| GitHubAPI
    Updater -->|Sürüm ZIP Paketini İndirir| GitHubCDN
    Updater -->|Önbellek Durumunu Kaydeder| TableOptions
```

---

## 3. Bileşen Kütüğü

<!-- Verified from: gnn-logos/gnn-logos.php#L35-L95 -->
<!-- Verified from: gnn-logos/includes/ -->

| Bileşen | Dosya Yolu | Temel Sorumluluk | Gelen Bağımlılıklar | Giden Bağımlılıklar |
|---|---|---|---|---|
| `GNN_Logos` | `gnn-logos/gnn-logos.php` | Singleton ana koordinatör; etkinleştirme, devre dışı bırakma ve dil kancalarını bağlar | WordPress Çekirdeği (`plugins_loaded`) | `GNN_Logos_CPT`, `GNN_Logos_Shortcode`, `GNN_Logos_Admin`, `GNN_Logos_Updater` |
| `GNN_Logos_CPT` | `gnn-logos/includes/class-gnn-logos-cpt.php` | `gnn_logo` CPT ve `gnn_logo_group` taksonomisini kaydeder; meta kutusu kayıtlarını ve yönetim sütunlarını yönetir | `GNN_Logos`, Yönetim Arayüzü (`save_post`) | WordPress Veritabanı (`wp_posts`, `wp_postmeta`, `wp_terms`) |
| `GNN_Logos_Shortcode` | `gnn-logos/includes/class-gnn-logos-shortcode.php` | `[gnn_logos]` parametrelerini ayrıştırır; `orderby` beyaz listesini denetler; HTML çıktısını üretir; varlıkları koşullu yükler | WordPress Çekirdeği (`init`, `wp_enqueue_scripts`) | `wp_get_attachment_image`, `gnn-logos-frontend.css`, `gnn-logos-frontend.js` |
| `GNN_Logos_Admin` | `gnn-logos/includes/class-gnn-logos-admin.php` | `'79.109'` pozisyonunda üst seviye menüyü kaydeder; 4 hazır şablon butonlu interaktif Shortcode Oluşturucu arayüzünü sunar | `GNN_Logos`, Yönetim Ekranı Kancaları | `gnn-logos-admin.css`, `gnn-logos-admin.js`, Ortam Kütüphanesi (`wp.media`) |
| `GNN_Logos_Updater` | `gnn-logos/inc/updater.php` | GitHub Releases API ile haberleşir; yeni sürümleri sorgular; transient senkronizasyonunu ve yükleme sonrası dizin normalizasyonunu yürütür | WordPress Çekirdeği (`site_transient_update_plugins`, `plugins_api`) | GitHub API (`api.github.com`), WordPress Transients API |

---

## 4. Veri Akışı ve Yürütme Yaşam Döngüsü

### 4.1 Önyüz Shortcode İşleme Hattı
1. **Tespit:** Ziyaret edilen bir sayfa veya yazıda `[gnn_logos]` ifadesiyle karşılaşıldığında WordPress shortcode işleyicisini tetikler.
2. **Koşullu Varlık Yükleme (Conditional Enqueue):** `GNN_Logos_Shortcode::render_shortcode()` çalışarak `gnn-logos-frontend.css` (8.7 KB) ve `gnn-logos-frontend.js` (4.6 KB) dosyalarını yalnızca o sayfaya özel olarak kaydeder ve sıraya alır.
3. **Parametre Doğrulama & Sanitizasyon:** Gelen nitelikler `shortcode_atts()` ile varsayılanlarla harmanlanır ve katı beyaz listelerden geçirilir:
   - `layout`: `in_array($layout, ['grid', 'carousel', 'marquee'])`
   - `style`: `in_array($style, ['minimal', 'card', 'bordered'])`
   - `orderby`: `in_array($orderby, ['menu_order', 'date', 'title', 'rand', 'ID', 'author', 'name', 'modified', 'parent', 'none'])`
   - `badges_valign`: `in_array($badges_valign, ['top', 'center', 'bottom'])`
4. **Veritabanı Sorgusu:** `post_type = 'gnn_logo'` koşuluyla filtrelenmiş bir `WP_Query` yürütülür; `group` niteliği verilmişse taksonomi sorgusu eklenir.
5. **DOM Üretimi:** Dönen öğeler üzerinde döngü kurularak `render_item_html()` çalıştırılır:
   - `wp_get_attachment_image()` aracılığıyla yerel `srcset`, `sizes`, `loading="lazy"` ve `decoding="async"` öznitelikli görseller oluşturulur.
   - Çoklu standart dizisi `_gnn_cert_codes` (veya geriye dönük uyumluluk için `_gnn_cert_code`) okunarak bağımsız `.gnn-cert-badge` rozet hapları basılır.
   - Marquee modunda pürüzsüz sonsuz akış için öğe seti `aria-hidden="true"` kopyasıyla duplicate edilir.
6. **İstemci Tarafı Başlatma:** `gnn-logos-frontend.js` mikro motoru `.gnn-logos-layout-carousel` kapsayıcılarına bağlanarak dokunmatik kaydırma (touch swipe), yön okları ve fare üzerine gelince duraklatma dinleyicilerini etkinleştirir.

### 4.2 Otomatik Güncelleyici Yaşam Döngüsü ve Transient Senkronizasyonu
1. **Periyodik Kontrol:** Çekirdek güncelleme kontrollerinde veya `plugins.php` ekranı açıldığında `site_transient_update_plugins` filtresi tetiklenir.
2. **Transient Sorgusu:** `GNN_Logos_Updater::get_remote_release()` fonksiyonu `get_transient('gnn_logos_github_update_check')` verisini kontrol eder.
3. **Uzak İstek (Egress):** Önbellek süresi dolmuşsa GitHub Releases API uç noktasına (`https://api.github.com/repos/BigDesigner/gnn-logos/releases/latest`) 10 saniye zaman aşımlı GET isteği gönderilir.
4. **Host Doğrulaması (SSRF Koruması):** İndirme bağlantısının (`download_url`) host adresi katı şekilde `github.com`, `codeload.github.com` veya `objects.githubusercontent.com` ile eşleşmelidir.
5. **Transient Senkronizasyonu (ADR-0006):**
   - `uzak_sürüm > yerel_sürüm` ise: `$transient->response[$slug]` içine yeni sürüm nesnesi enjekte edilir.
   - `yerel_sürüm >= uzak_sürüm` ise: Yanıltıcı güncelleme bildirimlerini önlemek adına `$transient->response[$slug]` kaydı tamamen silinir ve `$transient->no_update[$slug]` alanı doldurulur.
6. **Yükleme Sonrası Normalizasyon:** `upgrader_post_install` kancası GitHub'ın açtığı `BigDesigner-gnn-logos-<hash>` klasörünü standart `gnn-logos` klasör adına taşır.

---

## 5. Eşzamanlılık ve Durum Yönetimi

<!-- Verified from: gnn-logos/inc/updater.php#L35-L65 -->
<!-- Verified from: gnn-logos/includes/class-gnn-logos-cpt.php#L260-L290 -->

| Boyut | Uygulanan Desen | Uygulama & Güvence |
|---|---|---|
| **Oturum Kilitleme** | `.memory-bank/.session.lock` | Eşzamanlı ajan yürütmelerinde 10 dakikalık kilit mekanizması |
| **Atomik Dosya Yazımı** | `.tmp.json` -> `.json` yeniden adlandırma | `active-session.json` dosyasında veri bozulmalarını önler |
| **Yarış Durumu Savunması** | WordPress Nonce & Transients | Nonce denetimleri CSRF tekrarlarını engeller; `gnn_logos_github_update_check` transient verisi GitHub API isteklerini 12 saat (`43200s`) önbelleğe alarak hız limitlerinin tükenmesini önler |
| **Önbellek Boşaltma** | Doğrudan Transient Temizliği | `update-core.php` ziyaret edildiğinde veya manuel kontrol çalıştırıldığında ilgili transientler derhal silinerek taze veri çekilir |

---

## 6. Mimari Tasarım Desenleri

<details>
<summary><b>[Derinlemesine Bakış: Mimari Tasarım Desenleri]</b></summary>

- **Singleton Deseni (`GNN_Logos`):**
  - *Kanıt:* `gnn-logos/gnn-logos.php#L35-L76`
  - *Gerekçe:* Koordinatör sınıfın bellekte tek bir örneğinin bulunmasını sağlayarak WordPress kancalarının istek başına yalnızca bir kez bağlanmasını temin eder.

- **Sorumlulukların Ayrılması (Modüler MVC Varyantı):**
  - *Kanıt:* `includes/class-gnn-logos-cpt.php` (Model/Veri), `includes/class-gnn-logos-shortcode.php` (Denetleyici/Görünüm), `includes/class-gnn-logos-admin.php` (Yönetim Arayüzü).
  - *Gerekçe:* Önyüz vitrin sunumunu yönetim paneli form mantığından ve veri tabanı kayıt süreçlerinden izole eder.

- **Sıfır Bağımlılık & Aşamalı Geliştirme (Progressive Enhancement):**
  - *Kanıt:* `gnn-logos/assets/css/gnn-logos-frontend.css` (CSS Scroll-Snap ve GPU Keyframe dönüşümleri) ve `gnn-logos/assets/js/gnn-logos-frontend.js` (<3 KB mikro denetleyici).
  - *Gerekçe:* Ağır üçüncü parti kütüphaneleri (Swiper 150KB+, Slick, Owl) tamamen terk ederek mobil cihazlarda dahi milisaniyelik Core Web Vitals (LCP, CLS, FID) sürelerini garanti altına alır.

- **Defansif Transient Senkronizasyonu:**
  - *Kanıt:* `gnn-logos/inc/updater.php#L55-L60` ve `ADR-0006`.
  - *Gerekçe:* Hem yazma (`pre_set_site_transient_update_plugins`) hem okuma (`site_transient_update_plugins`) kancalarını dinleyerek eski güncelleme rozetlerinin panelde takılı kalmasını önler.
</details>

### 6.1 Tarihsel Tercihler ve Ekosistem İtici Güçleri
- **Ekosistem Menü Konumu ('79.109'):** GNN ürün ailesi standart menü kütüğü gereğince, çok eklentili GNN kurulumlarında yönetim ergonomisini korumak için doğrudan WordPress Ayarlar (`80`) menüsünün bitişiğinde konumlandırılmıştır.
- **Sıfır Bağımlılık Kararı:** Harici slider kütüphaneleri (Slick, Owl Carousel, Swiper), jQuery script çakışmalarını, kütüphane versiyon uyumsuzluklarını ve önyüz kod şişkinliğini önlemek amacıyla bilinçli olarak reddedilmiştir. Düzen motoru yalnızca modern CSS Scroll-Snap, GPU hızlandırmalı Keyframe animasyonları ve 3 KB'tan küçük Vanilla JS dokunmatik kontrolcüsüne dayanmaktadır.
