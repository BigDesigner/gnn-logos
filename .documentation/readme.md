# GNN Logos - Teknik Dokümantasyon Paketi

<!-- Verified from: gnn-logos/gnn-logos.php#L1-L25 -->
<!-- Verified from: gnn-logos/readme.txt#L1-L25 -->

**Mimari Kategori:** WordPress Eklentisi (Logo, Referans ve Kalite Sertifikası Vitrini)  
**Temel Teknoloji Yığını:** PHP 8.0+, Saf CSS Custom Properties (Tema Tokenları, Glassmorphism, Çok Gruplu GPU Marquee, CSS Scroll-Snap), Sıfır Bağımlılıklı Vanilla JavaScript, Zero-Build Gutenberg Block (API v3)  
**Mevcut Sürüm:** 2.0.0  
**Lisans:** GNU General Public License v2 (GPL-2.0)  

---

## 1. Yönetici Özeti

GNN Logos; WordPress sitelerinde logoları, referans çözüm ortaklarını, sponsorları ve çoklu kalite belgelerini (`TS EN 12201-2`, `TS EN ISO 1452-2`, `TS EN 1555-2` vb.) ağır harici slider kütüphanelerine (Slick, Owl Carousel, Swiper 150KB+) veya dış lightbox paketlerine ihtiyaç duymadan AAA kalitede sunmak üzere tasarlanmış, sıfır harici önyüz bağımlılıklı bir vitrin eklentisidir.

Eklentinin sunduğu temel yetenekler:
- **Native Gutenberg Bloğu (`gnn-logos/showcase`):** Zero-build `apiVersion: 3` dinamik blok; Block Editor ve Site Editor (FSE) içinde `ServerSideRender` ve `wide`/`full` genişlik desteği.
- **Tema Token Sistemi & Logo Filtreleri:** `light`, `dark` ve `auto` (`prefers-color-scheme`) temaları, `glass` (Glassmorphism) kart stili ve koyu zeminler için `monochrome-white`, `monochrome-dark`, `grayscale` filtreleri.
- **Erişilebilir Dahili Lightbox:** `lightbox="true"` ile sertifika/logoları tam boyutta, rozetleri ve harici belge/PDF bağlantısıyla açan sıfır `innerHTML` (XSS-güvenli) modal.
- **Kusursuz 3 Vitrin Modu:** Çok gruplu sıfır sıçramalı GPU Marquee (`ResizeObserver` dinamik 4K/ultrawide doluluk), son slayt atlamasız akıllı CSS Scroll-Snap Karusel (`IntersectionObserver` + `visibilitychange` + `focusin` autoplay duraklatma) ve Responsive Grid.
- **Gelişmiş Yönetim Arayüzü:** CPT listesinde sürükle-bırak `menu_order` sıralaması, çoklu ortam seçimiyle Toplu Logo Yükleyici, `gnn_logo_group` kategori filtresi ve Açık/Koyu/Dama zemin seçicili Canlı Görsel Önizlemeli Shortcode Oluşturucu.
- **Unified GNN GitHub Güncelleyici:** WordPress çekirdek güncelleme hattına (`pre_set_site_transient_update_plugins`) bağlanan, rate-limit yedekli ve etkinleştirme durumu korumalı otomatik güncelleyici.

### 1.1 İş Hedefi ve Hedef Kitle
- **Hedef Sektörler:** İmalat sanayii, mühendislik, B2B kurumsal şirketler ve e-ticaret platformları (TSE, EN, ISO kalite standartlarını ve akreditasyonlarını sergileyen kurumlar).
- **Çözülen Temel Problem:** Ağır üçüncü parti slider/lightbox kütüphanelerinin neden olduğu sayfa hızı kayıplarını, kayan şerit sıçramalarını, koyu tema kontrast bozulmalarını ve manuel yönetim darboğazlarını ortadan kaldırmak.

---

## 2. Önkoşullar ve Sistem Gereksinimleri

<!-- Verified from: gnn-logos/readme.txt#L5-L10 -->
<!-- Verified from: gnn-logos/gnn-logos.php#L1-L25 -->

| Boyut | Asgari Gereksinim | Doğrulanan Destek Hedefi | Notlar |
|---|---|---|---|
| **PHP Çalışma Zamanı** | `8.0` | `8.0`, `8.1`, `8.2`, `8.3` | Modern tip güvenliği, katı string kontrolleri ve regex doğrulamaları kullanır |
| **WordPress Çekirdeği** | `5.8` | Test edilen: `7.1` | Block API v3 (`register_block_type`), `wp_get_attachment_image`, `wp_safe_redirect` |
| **Veritabanı** | MySQL `5.7+` / MariaDB `10.3+` | WordPress varsayılanı | Özel tablo oluşturmaz; `wp_posts`, `wp_postmeta`, `wp_terms` tablolarını kullanır |
| **Harici Önyüz Bağımlılıkları** | Sıfır (`0`) | Sıfır npm / CDN / jQuery slider | Tüm önyüz etkileşimleri tek bir saf CSS ve Vanilla JS paketi ile çalışır |

---

## 3. Hızlı Başlangıç ve Kurulum

### Yöntem A: Yönetim Panelinden Yükleme (ZIP)
1. En güncel `gnn-logos-2.0.0.zip` paketini [GitHub Releases](https://github.com/BigDesigner/gnn-logos/releases) sayfasından indirin.
2. WordPress Yönetim Paneli -> **Eklentiler** -> **Yeni Eklenti Ekle** -> **Eklenti Yükle** ekranından yükleyip etkinleştirin.

### Yöntem B: Dosya Sistemi / Git Clone
1. `cd wp-content/plugins/`
2. `git clone https://github.com/BigDesigner/gnn-logos.git` (veya iç `gnn-logos/` klasörünü yerleştirin).
3. `wp plugin activate gnn-logos` veya WordPress Eklentiler ekranından etkinleştirin.

---

## 4. Doğrulama ve CI/CD Komutları

| Komut / Tetikleyici | Çalışma Ortamı | Amaç | Kaynak Kanıtı |
|---|---|---|---|
| `Get-ChildItem -Recurse -Filter *.php gnn-logos \| ForEach-Object { php -l $_.FullName }` | PowerShell (Windows) | Eklentideki tüm 7 PHP dosyasında sözdizim doğrulaması yapar | `.specs/constitution.md` |
| `node --check gnn-logos/assets/js/gnn-logos-frontend.js` | Node CLI | Frontend, admin ve blok JS dosyalarının sözdizimini doğrular | `.specs/bootstrap.md` |
| `Get-Content gnn-logos/blocks/showcase/block.json -Raw \| ConvertFrom-Json` | PowerShell | Gutenberg `block.json` şema geçerliliğini doğrular | `.specs/bootstrap.md` |
| `gh workflow run release.yml` | GitHub CLI | GitHub Actions release derleme, lint ve zip yayınlama hattını tetikler | `.github/workflows/release.yml` |

---

## 5. Yapılandırma ve Sabitler Envanteri

<!-- Verified from: gnn-logos/gnn-logos.php#L18-L23 -->
<!-- Verified from: gnn-logos/inc/updater.php#L20-L50 -->

| Tanımlayıcı | Tür | Varsayılan Değer | Açıklama | Dosya Kanıtı |
|---|---|---|---|---|
| `GNN_LOGOS_VERSION` | Sabit (string) | `'2.0.0'` | Güncel semantik sürüm etiketi (`VERSION` dosyası ile senkronize) | `gnn-logos/gnn-logos.php#L19` |
| `GNN_LOGOS_FILE` | Sabit (string) | `__FILE__` | Ana eklenti dosyasının mutlak sunucu yolu | `gnn-logos/gnn-logos.php#L20` |
| `GNN_LOGOS_DIR` | Sabit (string) | `plugin_dir_path(__FILE__)` | Eklenti kök dizini mutlak yolu | `gnn-logos/gnn-logos.php#L21` |
| `GNN_LOGOS_URL` | Sabit (string) | `plugin_dir_url(__FILE__)` | Eklenti kök dizini genel erişim URL'i | `gnn-logos/gnn-logos.php#L22` |
| `gnn-logos/showcase` | Gutenberg Bloğu | `apiVersion: 3` | Zero-build dinamik blok tanımlayıcısı | `gnn-logos/blocks/showcase/block.json#L4` |
| `$repo` | Özellik (string) | `'BigDesigner/gnn-logos'` | Uzak GitHub depo yolu | `gnn-logos/inc/updater.php` |
| `$transient_key` | Özellik (string) | `'gnn_logos_github_update_check'` | 12 saatlik (`43200s`) pozitif / 5 dakikalık (`300s`) negatif güncelleme önbelleği | `gnn-logos/inc/updater.php` |
| Menü Pozisyonu | Değer (string) | `'79.109'` | GNN ailesi standart WordPress sol menü konumu | `gnn-logos/includes/class-gnn-logos-admin.php#L45` |
