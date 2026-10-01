# GNN Logos - Teknik Dokümantasyon Paketi

<!-- Verified from: gnn-logos/gnn-logos.php#L1-L25 -->
<!-- Verified from: gnn-logos/readme.txt#L1-L20 -->

**Mimari Kategori:** WordPress Eklentisi (Logo, Referans ve Kalite Sertifikası Vitrini)  
**Temel Teknoloji Yığını:** PHP 8.0+, Saf CSS (GPU Keyframe Animasyonları ve CSS Scroll-Snap), Vanilla JavaScript (<3 KB)  
**Mevcut Sürüm:** 1.2.0  
**Lisans:** GNU General Public License v2 (GPL-2.0)  

---

## 1. Yönetici Özeti

GNN Logos; WordPress sitelerinde logoları, referans çözüm ortaklarını, sponsorları ve çoklu kalite belgelerini (`TS EN 12201-2`, `TS EN ISO 1452-2`, `TS EN 1555-2` vb.) üçüncü parti sayfa oluşturuculara veya ağır JavaScript slider kütüphanelerine (Slick, Owl Carousel, Swiper 150KB+) ihtiyaç duymadan sunmak üzere tasarlanmış ultra hafif, sıfır harici bağımlılıklı bir vitrin eklentisidir.

Eklentinin sunduğu temel yetenekler:
- WordPress yerel Ortam Kütüphanesi (`wp.media`) üzerinden görsel seçimi sağlayan özel yazı türü (`gnn_logo`), hiyerarşik taksonomi (`gnn_logo_group`) ve meta kutuları.
- Enter veya yapıştırma ile dinamik giriş yapılabilen ve akıcı flexbox monospace rozet hapları halinde sunulan çoklu standart rozet yönetim sistemi.
- Responsive Grid, CSS Scroll-Snap yatay Karusel ve kesintisiz sağa/sola akış destekleyen GPU hızlandırmalı Keyframe Marquee (Kayan Şerit) düzen motoru.
- Görsel bozulmalarını ve taşmalarını önleyen modern CSS en-boy oranı (`aspect-ratio`: `16/9`, `4/3`, `1/1`, `3/2`, `2/1`, `auto`) ve `object-fit: contain` desteği.
- WordPress yönetim panelinde `'79.109'` pozisyonunda yer alan interaktif Shortcode Oluşturucu arayüzü ve 4 adet hazır şablon butonu.
- WordPress çekirdek güncelleme hattına (`site_transient_update_plugins`) doğrudan bağlanan yerel GitHub Releases otomatik güncelleyicisi.

### 1.1 İş Hedefi ve Hedef Kitle
- **Hedef Sektörler:** İmalat sanayii, mühendislik, B2B kurumsal şirketler ve e-ticaret platformları (TSE, EN, ISO kalite standartlarını ve akreditasyonlarını sergileyen kurumlar).
- **Çözülen Temel Problem:** Şişkin sayfa oluşturucu eklentilerinin ve harici JavaScript slider kütüphanelerinin (Swiper 150KB+, Slick, Owl) neden olduğu sayfa açılış gecikmelerini (PageSpeed cezaları), kümülatif düzen kaymalarını (CLS) ve Core Web Vitals performans düşüşlerini tamamen ortadan kaldırmak.
- **Ajans ve Geliştirici Kolaylığı:** Dijital ajanslar ve WordPress mühendisleri için lisanslama maliyeti, CDN güvenlik zaafiyeti veya script çakışması riski olmadan müşteri sitelerine hızlıca şık ve erişilebilir vitrinler entegre etme imkanı sağlamak.

---

## 2. Önkoşullar ve Sistem Gereksinimleri

<!-- Verified from: gnn-logos/readme.txt#L5-L10 -->
<!-- Verified from: gnn-logos/gnn-logos.php#L1-L20 -->

| Boyut | Asgari Gereksinim | Doğrulanan Destek Hedefi | Notlar |
|---|---|---|---|
| **PHP Çalışma Zamanı** | `8.0` | `8.0`, `8.1`, `8.2`, `8.3` | Modern tip güvenliği, katı string kontrolleri ve null birleştirme işleçleri kullanır |
| **WordPress Çekirdeği** | `5.8` | Test edilen: `6.7` | `wp_get_attachment_image`, `wp_safe_redirect` ve yerel blok uyumluluğu gerektirir |
| **Veritabanı** | MySQL `5.7+` / MariaDB `10.3+` | WordPress varsayılanı | Standart `wp_posts`, `wp_postmeta`, `wp_terms`, `wp_term_taxonomy` tablolarını kullanır |
| **Web Sunucusu** | Apache / Nginx / LiteSpeed | HTTP/2 veya HTTP/3 önerilir | Kalıcı bağlantılar (pretty permalinks) için standart PHP rewrite modüllerini gerektirir |
| **Harici Bağımlılıklar** | Sıfır (`0`) | Sıfır npm / CDN kütüphanesi | jQuery slider eklentilerini, harici CDN scriptlerini ve yabancı yazı tiplerini tamamen dışlar |

---

## 3. Hızlı Başlangıç ve Kurulum

<!-- Verified from: .specs/bootstrap.md#L14-L20 -->

### Yöntem A: Yönetim Panelinden Yükleme (ZIP)
1. En güncel `gnn-logos.zip` paketini [GitHub Releases](https://github.com/BigDesigner/gnn-logos/releases) sayfasından indirin.
2. WordPress Yönetim Paneli -> **Eklentiler** -> **Yeni Eklenti Ekle** -> **Eklenti Yükle** alanına gidin.
3. Arşiv dosyasını seçip yükleyin ve **Eklentiyi Etkinleştir** butonuna tıklayın.

### Yöntem B: Dosya Sistemi / Git Clone
1. WordPress kurulumunuzun eklentiler dizinine gidin:
   ```bash
   cd wp-content/plugins/
   ```
2. Depoyu `gnn-logos` dizinine klonlayın:
   ```bash
   git clone https://github.com/BigDesigner/gnn-logos.git gnn-logos
   ```
3. Eklentiyi WP-CLI üzerinden etkinleştirin:
   ```bash
   wp plugin activate gnn-logos
   ```
   Veya WordPress Yönetim Paneli (**Eklentiler** -> **Yüklü Eklentiler** -> **GNN Logos**) üzerinden aktifleştirin.

---

## 4. Mevcut Doğrulama ve CI/CD Komutları

<!-- Verified from: .github/workflows/release.yml#L1-L50 -->

| Komut / Tetikleyici | Çalışma Ortamı | Amaç | Kaynak Kanıtı |
|---|---|---|---|
| `php -l <dosya.php>` | Yerel CLI | Commit öncesinde PHP dosyalarında sözdizim hatası kontrolü yapar | `.specs/constitution.md#L32` |
| `Get-ChildItem -Recurse -Filter *.php gnn-logos \| ForEach-Object { php -l $_.FullName }` | PowerShell (Windows) | Eklenti paketindeki tüm PHP dosyalarını toplu olarak doğrular | Yerel Geliştirici İş Akışı |
| `gh workflow run release.yml` | GitHub CLI | GitHub Actions release derleme ve zip paketleme sürecini tetikler | `.github/workflows/release.yml#L1-L50` |
| `git status` | Git CLI | Çalışma ağacının temizliğini ve takip durumunu doğrular | `.memory-bank/system-coherence.md#L11` |

---

## 5. Yapılandırma ve Sabitler Envanteri

<!-- Verified from: gnn-logos/gnn-logos.php#L16-L20 -->
<!-- Verified from: gnn-logos/inc/updater.php#L20-L45 -->

| Tanımlayıcı | Tür | Varsayılan Değer | Açıklama | Dosya Kanıtı |
|---|---|---|---|---|
| `GNN_LOGOS_VERSION` | Sabit (string) | `'1.2.0'` | Güncel semantik sürüm etiketi | `gnn-logos/gnn-logos.php#L17` |
| `GNN_LOGOS_FILE` | Sabit (string) | `__FILE__` | Ana eklenti yükleyici dosyasının mutlak sunucu yolu | `gnn-logos/gnn-logos.php#L18` |
| `GNN_LOGOS_DIR` | Sabit (string) | `plugin_dir_path(__FILE__)` | Eklenti kök dizininin sonu taksimli mutlak yolu | `gnn-logos/gnn-logos.php#L19` |
| `GNN_LOGOS_URL` | Sabit (string) | `plugin_dir_url(__FILE__)` | Eklenti kök dizininin genel erişim web adresi (URL) | `gnn-logos/gnn-logos.php#L20` |
| `$repo` | Özellik (string) | `'BigDesigner/gnn-logos'` | Güncelleme sorguları için uzak GitHub depo yolu | `gnn-logos/inc/updater.php#L24` |
| `$transient_key` | Özellik (string) | `'gnn_logos_github_update_check'` | Uzak GitHub release verilerini saklayan transient anahtarı | `gnn-logos/inc/updater.php#L38` |
| `$cache_duration`| Özellik (int) | `43200` (12 saat) | Güncelleme önbelleğinin saniye cinsinden yaşam süresi (TTL) | `gnn-logos/inc/updater.php#L45` |
| Menü Pozisyonu | Değer (string) | `'79.109'` | GNN ailesi için ayrılmış özel WordPress sol menü sırası | `gnn-logos/includes/class-gnn-logos-admin.php#L38` |
