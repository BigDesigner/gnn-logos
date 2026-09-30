# Implementation Plan - WordPress Logo & Sertifika Vitrini (GNN Logos)

Referanslar, çözüm ortakları, sertifikalar ve sponsorlar gibi logoları WordPress sitelerinde kolayca yönetmek; en-boy oranı (aspect ratio) korumalı, harici ağır kütüphaneler içermeyen, yüksek performanslı ve modern animasyonlu (Grid, Carousel, Sonsuz Kayan Ticker) bir vitrin eklentisi geliştirme planıdır.

---

## 1. Goal Description

WordPress kullanıcılarının harici sayfa oluşturuculara veya ağır JavaScript kütüphanelerine muhtaç kalmadan, referans/çözüm ortağı logolarının yanı sıra **TS EN 12201-2, TS EN ISO 1452-2, TS EN 1555-2** gibi sertifika ve standart kodlarını şık kart tasarımlarıyla listeleyebilmesi, `[gnn_logos]` shortcode'u ile diledikleri sayfaya ekleyebilmesi ve GitHub üzerinden otomatik güncellenebilmesi hedeflenmektedir.

### Temel Prensipler & Ekosistem Standartları:
- **Geliştirici & Ekosistem Kimliği:** Author: `BigDesigner`, Author URI: `https://github.com/BigDesigner`, Eklenti URL: `https://github.com/BigDesigner/gnn-logos`.
- **Menü Sıralaması:** WordPress sidebar'ında Ayarlar bölümü bitişiğindeki GNN ailesi bandında tam olarak `'79.109'` pozisyonunda yer alır.
- **GitHub Otomatik Güncelleyici (`inc/updater.php`):** GitHub Releases API entegrasyonu ile yerel tek tıkla güncelleme denetimi ve kurulumu.
- **Ultra Hafif (Zero Dependency):** Frontend'de harici kütüphane (jQuery slider, Swiper vb.) **kesinlikle kullanılmaz**. Saf CSS (CSS Scroll-Snap & GPU-hızlandırmalı Keyframe Marquee) ve 3 KB'tan küçük mikro Vanilla JS kullanılır.
- **Sertifika & Başlık Alanı Tasarımı:** Sertifikalar için görselin altına şık, modern tipografiye sahip rozet/kart (badge/card) tarzında standart kodu ve başlık ekleme desteği.
- **En-Boy Oranı & Çözünürlük Bütünlüğü:** Farklı ebatlardaki logoların bozulmasını önlemek için modern CSS `aspect-ratio` ve `object-fit: contain` desteği.
- **Grup / Kategori Desteği:** "Referanslar", "Çözüm Ortakları", "Sertifikalar", "Markalar" gibi bağımsız gruplar.
- **WP Media Uploader Entegrasyonu:** WordPress yerel ortam kütüphanesinden ("Gözat") tek tıkla logo seçimi.

---

## 2. Hardened Architecture & Security Invariants

### 2.1 Güvenlik ve Yetkilendirme Sınırları (.specs/boundary-conditions.md Uyarınca)
1. **Direct Access Guard:** Tüm PHP dosyalarının ilk satırında `defined('ABSPATH') || exit;` kontrolü zorunludur.
2. **CSRF & Nonce Savunması:** Meta kutusu kaydetme işlemlerinde `wp_nonce_field('gnn_logos_meta_action', 'gnn_logos_meta_nonce')` ve `wp_verify_nonce()` doğrulaması yapılır.
3. **Yetki Kontrolü (BOLA/IDOR Koruması):** Post meta kayıtlarında `current_user_can('edit_post', $post_id)`, admin paneli ve güncelleme tetikleyicilerinde `current_user_can('manage_options')` / `current_user_can('update_plugins')` zorunludur.
4. **Girdi Temizliği (Sanitization):**
   - Sertifika kodları, başlıklar, açıklamalar: `sanitize_text_field(wp_unslash(...))`
   - Yönlendirme linkleri: `esc_url_raw(wp_unslash(...))`
   - Görsel ID'leri ve sayısal değerler: `absint(...)`
   - Seçenek enums (`aspect_ratio`, `layout`, `target`): `in_array()` beyaz liste kontrolü.
5. **Çıktı Güvenliği (XSS Koruması):** HTML içi `esc_html()`, öznitelik içi `esc_attr()`, bağlantı içi `esc_url()`.
6. **SSRF Savunması (Güncelleyici):** İndirme paketinin host doğrulaması yalnızca `github.com` veya `codeload.github.com` ile sınırlandırılır.

---

## 3. Shipping Layout & File Structure

Paketleme ve GitHub Releases entegrasyonu (ADR-0004) gereği, kurulabilir WordPress eklentisi `gnn-logos/` alt dizini içinde yapılandırılır:

```
c:\Users\bigde\.antigravity\gnn-logos\
├── .github/
│   └── workflows/
│       └── release.yml                 # GitHub Actions otomatik zip oluşturucu ve release yayınlayıcı
├── .gitignore                          # OS/IDE ve gereksiz dosya engelleyici
├── README.md                           # GitHub depo dokümantasyonu
├── implementation_plan.md              # Sentinel denetimli aktif plan
└── gnn-logos/                          # Dağıtıma ve kuruluma hazır eklenti dizini
    ├── gnn-logos.php                   # Ana eklenti başlığı, kancalar, bağış & güncelleme linkleri
    ├── readme.txt                      # WordPress standart readme dosyası
    ├── uninstall.php                   # Temiz kaldırma rutini
    ├── inc/
    │   └── updater.php                 # GitHub Releases API entegre yerel güncelleyici (GNN_Logos_Updater)
    ├── includes/
    │   ├── class-gnn-logos-cpt.php     # CPT (gnn_logo), Taxonomy (gnn_logo_group), Meta Boxes & Nonce
    │   ├── class-gnn-logos-shortcode.php # [gnn_logos] güvenli sorgu ve HTML renderlayıcı
    │   └── class-gnn-logos-admin.php   # Menü '79.109', Shortcode Sihirbazı UI ve script yükleyici
    └── assets/
        ├── css/
        │   ├── gnn-logos-frontend.css  # Kart stilleri, sertifika rozetleri, CSS Scroll-Snap & GPU Marquee (<10KB)
        │   └── gnn-logos-admin.css     # WP Media uploader ve generator stilleri
        └── js/
            ├── gnn-logos-frontend.js   # Mikro Vanilla JS (touch swipe, autoplay, pause-on-hover) (<3KB)
            └── gnn-logos-admin.js      # wp.media kütüphanesi açma ve görsel seçim mekanizması
```

---

## 4. Component Details & Hardened Code Specifications

### 4.1 Ana Eklenti Dosyası (`gnn-logos/gnn-logos.php`)
- **Eklenti Başlığı:**
  ```php
  <?php
  /**
   * Plugin Name: GNN Logos
   * Plugin URI:  https://github.com/BigDesigner/gnn-logos
   * Description: WordPress için ultra hafif logo ve sertifika vitrini. CSS Scroll-Snap karusel, GPU destekli sonsuz marquee ve estetik sertifika kartları (TS EN 12201-2 vb.) sunar.
   * Version:     1.0.0
   * Author:      BigDesigner
   * Author URI:  https://github.com/BigDesigner
   * Text Domain: gnn-logos
   * License:     GPLv2 or later
   */
  defined('ABSPATH') || exit;
  ```
- **Aksiyon Linkleri (`plugin_action_links`):**
  - Donate: `https://buymeacoffee.com/bigdesigner`
  - Sihirbaz: `admin.php?page=gnn-logos-generator`
  - Güncelleme Kontrolü: `plugins.php?gnn_logos_check_update=1` (Nonce korumalı).
- **Yükleyiciler:** `inc/updater.php`, `class-gnn-logos-cpt.php`, `class-gnn-logos-shortcode.php`, `class-gnn-logos-admin.php`.

### 4.2 GitHub Yerel Güncelleyici (`gnn-logos/inc/updater.php`)
- Sınıf: `GNN_Logos_Updater`
- Repo: `BigDesigner/gnn-logos`
- Transient: `gnn_logos_github_update_check` (12 saat TTL).
- Hook'lar: `pre_set_site_transient_update_plugins`, `plugins_api`, `upgrader_post_install`, `admin_init`.
- Güvenlik: İndirme URL'i host doğrulaması (`github.com`), versiyon regex doğrulaması (`/^\d+\.\d+\.\d+$/`).

### 4.3 CPT & Meta Kutusu Motoru (`gnn-logos/includes/class-gnn-logos-cpt.php`)
- **Post Type:** `gnn_logo` (Etiket: "Logo & Sertifikalar", `show_in_menu => false` - ana menü admin sınıfında `'79.109'` altına bağlanır).
- **Taxonomy:** `gnn_logo_group` (Referanslar, Çözüm Ortakları, Sertifikalar).
- **Meta Box Alanları:**
  - `_gnn_logo_id` (Görsel attachment ID - `absint`).
  - `_gnn_cert_code` (Standart / Sertifika kodu - `sanitize_text_field`).
  - `_gnn_description` (Alt açıklama / Kurum adı - `sanitize_text_field`).
  - `_gnn_link_url` (Yönlendirme linki - `esc_url_raw`).
  - `_gnn_link_target` (`_self` veya `_blank` - whitelist kontrolü).
  - `_gnn_aspect_ratio` (`auto`, `16:9`, `4:3`, `1:1`, `3:2`).

### 4.4 Admin Menüsü ve Shortcode Sihirbazı (`gnn-logos/includes/class-gnn-logos-admin.php`)
- **Menü Kaydı:**
  ```php
  add_menu_page(
      __('GNN Logos', 'gnn-logos'),
      __('GNN Logos', 'gnn-logos'),
      'manage_options',
      'gnn-logos',
      array($this, 'page_all_items'),
      'dashicons-images-alt2',
      '79.109' // Kesin string literal
  );
  ```
- **Alt Menüler:**
  - Tüm Logolar (`edit.php?post_type=gnn_logo`)
  - Yeni Ekle (`post-new.php?post_type=gnn_logo`)
  - Logo Grupları (`edit-tags.php?taxonomy=gnn_logo_group&post_type=gnn_logo`)
  - Shortcode Oluşturucu (`gnn-logos-generator`)
  - Güncelleme Kontrolü (`gnn-logos-check-update`)
- **Sihirbaz Arayüzü:** Kategori, vitrin düzeni (`grid`, `carousel`, `marquee`), kart biçimi (`card`, `bordered`, `minimal`), en-boy oranı ve sütun sayısı seçimiyle tek tıkla `[gnn_logos ...]` kopyalama.

### 4.5 Shortcode Render Motoru (`gnn-logos/includes/class-gnn-logos-shortcode.php`)
- `[gnn_logos]` shortcode'unun işlenmesi.
- `WP_Query` ile güvenli sorgu.
- Görseller için `wp_get_attachment_image()` ile responsive `srcset` ve `loading="lazy"` kullanımı.
- Sertifika kodu (`_gnn_cert_code`) varsa şık rozet `.gnn-cert-code` çıktısı.

### 4.6 Ultra Hafif Frontend Varlıkları (`gnn-logos/assets/`)
- CSS Grid ve CSS Scroll-Snap carousel.
- Sonsuz akan Marquee için pure CSS GPU hızlandırmalı transformasyon:
  ```css
  @keyframes gnn-marquee-scroll {
      0% { transform: translate3d(0, 0, 0); }
      100% { transform: translate3d(-50%, 0, 0); }
  }
  ```
- Sertifika kart tasarımı (`.gnn-logo-card`) ve tipografi (`.gnn-cert-code`).
- Vanilla JS (<3KB) ile dokunmatik swipe ve autoplay desteği.

---

## 5. Verification Plan

### Automated / CLI Syntax Tests
- Tüm PHP dosyalarında `php -l` sözdizimi doğrulaması:
  ```powershell
  Get-ChildItem -Path gnn-logos -Filter *.php -Recurse | ForEach-Object { php -l $_.FullName }
  ```

### Manual Verification Steps
1. **Admin Menü & Konum Doğrulaması:**
   - WP Admin sol menüsünde Ayarlar'ın hemen üstünde `'79.109'` pozisyonunda "GNN Logos" menüsünün belirdiği teyit edilecek.
2. **Gözat / Logo Yükleme Testi:**
   - Yeni Ekle ekranında WP Media Library açılarak logo seçilecek, önizleme ve silme butonları test edilecek.
3. **Sertifika Kodu ve Rozet Testi:**
   - `TS EN 12201-2`, `TS EN ISO 1452-2`, `TS EN 1555-2` kodları girilecek ve kaydedilecek.
   - `[gnn_logos group="sertifikalar" layout="grid" style="card" show_code="true"]` ile kartların altındaki rozetlerin tipografisi kontrol edilecek.
4. **Carousel & Marquee Animasyon Testi:**
   - Carousel kaydırma okları, swipe ve otomatik oynatma döngüsü test edilecek.
   - Marquee'nin pürüzsüz GPU akışı ve fare üzerine gelindiğinde duraklaması teyit edilecek.
5. **Güncelleyici ve Aksiyon Linkleri Testi:**
   - Eklentiler listesinde "Donate", "Settings", "Check Updates" linkleri doğrulanacak.
   - "Check Updates" tıklandığında nonce doğrulamasıyla güncelleme kontrolü tetiklenecek.

---

### Audit Notes (Sentinel Plan Audit Hardening)
- **Hardening 1 (Dizin Hiyerarşisi):** Sibling eklentiler (`gnn-smtpmail`, `gnn-terms-popup`) ve GitHub Actions `.github/workflows/release.yml` uyumluluğu için tüm kaynak dosyalar `gnn-logos/` alt dizini altına taşınacak şekilde plan yeniden yapılandırıldı.
- **Hardening 2 (Menü Slot Pozisyonu):** ADR-0010 ve ADR-0004 uyarınca menü konumu `'79.109'` string sabiti olarak kilitlendi.
- **Hardening 3 (Güvenlik & SSRF Savunması):** Otomatik güncelleyiciye paket indirme adresi için `github.com` host doğrulaması ve semver regex kontrolü eklendi.
- **Hardening 4 (Anti-Eager & Yetkilendirme):** Tüm meta işlemlerinde `wp_verify_nonce` ve `current_user_can('edit_post')` kontrolleri plana açık kod bloklarıyla eklendi.
- **Hardening 5 (Sıfır Bağımlılık Garantisi):** Frontend varlık bütçesi (CSS < 10KB, JS < 3KB) teyit edildi; jQuery veya harici CDN bağlantıları kesin olarak engellendi.
