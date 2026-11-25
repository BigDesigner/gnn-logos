=== GNN Logos ===
Contributors: BigDesigner
Donate link: https://buymeacoffee.com/bigdesigner
Tags: logos, logo showcase, carousel, marquee, certificates, partners, references
Requires at least: 5.8
Tested up to: 7.1
Requires PHP: 8.0
Stable tag: 2.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

WordPress için ultra hafif, sıfır harici bağımlılıklı logo, çözüm ortağı ve sertifika vitrini eklentisi.

== Description ==

GNN Logos; WordPress sitenizde referanslar, çözüm ortakları, sponsorlar ve kalite sertifikalarını (TS EN 12201-2, TS EN ISO 1452-2 vb.) modern ve estetik düzenlerde sunmanızı sağlayan yüksek performanslı bir vitrin eklentisidir.

Sitenizi yavaşlatan ağır harici JavaScript kütüphaneleri (Slick, Swiper 150KB+, jQuery eklentileri) KULLANMAZ. Modern CSS Scroll-Snap, çok gruplu kusursuz GPU marquee, dahili Lightbox ve sıfır bağımlılıklı Vanilla JS ile çalışır.

= Temel Özellikler =
* **Native Gutenberg Bloğu (API v3):** Blok Editörü ve Site Editor (FSE) içinde canlı önizlemeli `GNN Logos` bloğu (`wide` ve `full` hizalama desteği).
* **Sürükle-Bırak Sıralama & Toplu Yükleme:** Yönetim listesinde satırları sürükleyerek anlık `menu_order` sıralaması ve Ortam Kütüphanesi'nden çoklu seçimle tek tıkta toplu logo oluşturma.
* **Canlı Görsel Önizlemeli Shortcode Sihirbazı:** Açık/Koyu/Dama zemin seçicisi, 5 hazır şablon ve anlık render önizleme.
* **Light, Dark, Auto Temalar & Glassmorphism:** Koyu/açık tema token sistemi, buzlu cam (`glass`) kart stili ve koyu footer'lar için `monochrome-white` / `monochrome-dark` / `grayscale` logo filtreleri.
* **Erişilebilir Dahili Lightbox:** Sertifika ve logoları tam boyutta, rozetleri ve dış belge bağlantısıyla açan sıfır bağımlılıklı modal.
* **Kusursuz 3 Vitrin Modu:**
  1. Carousel / Slider (Hatasız son slayt geçişi, dinamik erişilebilir noktalar, akıcı sürükleme, IntersectionObserver + sekme görünürlüğü korumalı autoplay)
  2. Continuous Ticker / Marquee (Sıfır sıçramalı çok gruplu döngü, 1–3 logoda dahi 4K ekranı dolduran otomatik klonlama, kenar yumuşatma maskesi)
  3. Responsive Grid (Çok sütunlu estetik ızgara)
* **Sertifika & Standart Rozetleri:** Logoların altına TS EN 12201-2 gibi çoklu standart kodlarını yatay/dikey hizalamalı rozetlerle ekleme.
* **GitHub Entegre Güncelleyici:** Unified GNN GitHub Updater standardı ile WordPress panelinden otomatik güncelleme.

== Installation ==

1. `gnn-logos` klasörünü `/wp-content/plugins/` dizinine yükleyin.
2. WordPress Yönetim Paneli -> Eklentiler bölümünden **GNN Logos** eklentisini etkinleştirin.
3. Sol menüdeki **GNN Logos** sekmesinden logolarınızı ve sertifikalarınızı ekleyin.
4. **Shortcode Oluşturucu** sihirbazı ile oluşturduğunuz shortcode'u dilediğiniz sayfaya yapıştırın.

== Shortcode Kullanımı ==

Standart Karusel:
`[gnn_logos group="referanslar" layout="carousel" columns="5" aspect_ratio="16/9"]`

Sertifika Kart Düzeni (TS EN vb.):
`[gnn_logos group="sertifikalar" layout="grid" style="card" aspect_ratio="4/3" columns="4" show_code="true"]`

Sonsuz Kayan Logo Şeridi (Marquee):
`[gnn_logos layout="marquee" speed="25s" grayscale="true"]`

== Changelog ==

= 2.0.0 =
* Yeni: Native Gutenberg Bloğu (`gnn-logos/showcase`, `apiVersion: 3`, `ServerSideRender`, `wide`/`full` hizalama, FSE Footer/Header uyumlu).
* Yeni: Light / Dark / Auto (`prefers-color-scheme`) CSS Custom Property tema motoru, `glass` (Glassmorphism) kart stili ve `logo_filter` (`none`, `grayscale`, `monochrome-white`, `monochrome-dark`) desteği.
* Yeni: Erişilebilir sıfır bağımlılıklı sertifika/logo Lightbox modalı (`lightbox="true"`, sıfır `innerHTML`, odak geri yükleme, `Escape`/backdrop kapatma).
* Yeni: Yeni shortcode ve blok parametreleri: `theme`, `style="glass"`, `logo_filter`, `logo_height`, `item_width`, `fade_edges`, `lightbox`.
* Yeni: Yönetim tablosunda sürükle-bırak `menu_order` sıralaması (`gnn_logos_reorder` AJAX), tek tıkla Toplu Logo Yükleyici (`gnn_logos_bulk_create` AJAX), `gnn_logo_group` kategori filtresi ve sıralanabilir `Sıra` sütunu.
* Yeni: Shortcode Oluşturucu'ya Açık/Koyu/Dama zemin seçicili Canlı Görsel Önizleme (`gnn_logos_preview` AJAX), 5 hazır şablon ve düzen odaklı akıllı kontrol panelleri eklendi.
* Düzeltme: Marquee motoru çok gruplu `.gnn-marquee-group` + `ResizeObserver` dinamik klonlama ile sıfır sıçrama ve 1–4 logoda bile 4K/ultrawide doluluk sağlayacak şekilde yeniden yazıldı.
* Düzeltme: Carousel `goNext()` son slayt atlama (off-by-one) hatası giderildi; ulaşılamayan fazla noktalar gizlendi, tüm öğeler sığdığında ok/nokta otomatik gizleme, akıcı `clientX` sürükleme (`scroll-snap` askıya alma) ve `IntersectionObserver` + `visibilitychange` + `focusin` autoplay duraklatma eklendi.
* Düzeltme: Shortcode Oluşturucu'da "Standart kodunu göster" kutusu kapatıldığında `show_code="false"` üretilmemesi hatası giderildi.
* Optimizasyon: `WP_Query` içine `no_found_rows => true` eklendi; tamamen boş kayıtların ve boş görsel kutularının render edilmesi engellendi.

= 1.2.6 =
* Geliştirme: GitHub güncelleyici (`GNN_Logos_Updater`) unified GNN GitHub Updater standardına geçirildi.

= 1.2.5 =
* Kritik Düzeltme: `GNN_Logos_Updater::after_install()` içinde `$result['destination']` ve `$plugin_dir` karşılaştırması `wp_normalize_path(untrailingslashit(...))` ile normalize edildi; WordPress'in sonu `/` ile biten dizin yolunu farklı sanıp eklenti klasörünü güncelleme sırasında silmesi engellendi.

= 1.2.4 =
* Düzeltme: Sol yönetim menüsündeki üst **GNN Logos** bağlantısı doğrudan **Tüm Logolar & Sertifikalar** (`edit.php?post_type=gnn_logo`) ekranını açacak şekilde güncellendi; mükerrer boş `GNN Logos` alt menüsü kaldırıldı.
* Düzeltme: **Güncellemeleri Kontrol Et** öğesi sol menüden kaldırıldı (güncellemeler GNN Dashboard / Eklentiler eylem bağlantısı üzerinden yönetilmektedir).

= 1.2.3 =
* Güvenlik: Shortcode `gap`, `aspect_ratio` ve `speed` parametrelerine katı regex doğrulaması eklenerek CSS enjeksiyonu riski kapatıldı; çift `esc_attr()` temizlendi.
* Geliştirme: Updater `check_for_update()` yanıtına `requires`, `tested` (`7.1`) ve `requires_php` eklendi; `get_plugin_data()` erken çeviri koruması (`$translate = false`) ve `after_install()` filesystem kontrolleri güçlendirildi.
* Geliştirme: Carousel kapsayıcısına WCAG APG `role="region"` ve `aria-roledescription="carousel"` eklendi; sürükleme sonrası yanlışlıkla link açılmasını önleyen `hasDragged` koruması eklendi.
* Geliştirme: Kök `VERSION` dosyası oluşturuldu ve `languages/gnn-logos.pot` tüm çevrilebilir dizgilerle (120 kayıt) güncellendi.

= 1.2.2 =
* Düzeltme: Marquee düzenindeki öğelere (`gnn-marquee-item`) genişlik sınırı (`clamp(140px, 16vw, 200px)`) ve `auto` en-boy oranı için maksimum yükseklik (`90px`) eklenerek yüksek çözünürlüklü logoların footer/dar alanlarda dev gibi görünmesi engellendi.
* Düzeltme: `.gnn-logos-wrapper` ve `.gnn-logos-layout-marquee` sınıflarına Flex/Grid ebeveynler içinde taşmayı önleyen `max-width: 100%; min-width: 0;` kuralları eklendi.

= 1.2.1 =
* Düzeltme: WordPress 6.7+ `_load_textdomain_just_in_time` uyarısını önlemek için `load_plugin_textdomain()` çağrısı `init` kancasına priority `1` ile taşındı.
* Düzeltme: Updater hata önbellekleme mantık hatası (`false` sentinel yerine `'error'`) giderildi ve SSRF doğrulamasına `https` şema kontrolü eklendi.
* Geliştirme: WordPress 7.1 uyumluluğu güncellendi; `plugin_info()` yanıtına `requires`, `tested`, `requires_php` ve `gnn-logos.php` başlığına minimum sürüm etiketleri eklendi.
* Geliştirme: WCAG erişilebilirlik iyileştirmeleri (marquee duplicate link `tabindex="-1"`, tablist `role="tab"`/`aria-selected`, klavye ok navigasyonu, `:focus-visible` ve `prefers-reduced-motion` desteği).
* Geliştirme: `languages/gnn-logos.pot` çeviri şablonu eklendi.

= 1.2.0 =
* Güvenlik: Eski sertifika kodları ayrıştırmasında satır sonu korunumu (`sanitize_textarea_field`), `wp_unslash()` uyumu ve `orderby` beyaz listesi eklendi.
* Güvenlik: Admin JS önizleme DOM XSS koruması ve `handle_manual_update_redirect()` yetki kontrolü eklendi.
* Geliştirme: `uninstall.php` tüm `gnn_logo` yazıları, meta verileri, `gnn_logo_group` terimleri ve transientleri tamamen silecek şekilde güncellendi.

= 1.1.1 =
* Geliştirme: Rozetler için dikey hizalama parametresi (badges_valign="bottom|top|center") eklendi. Rozetlerin kartın en altına sabitlenmesi (bottom), logonun hemen altında başlaması (top) veya kart içinde ortalanması (center) seçilebilir.
* Geliştirme: Shortcode Sihirbazı'na "Dikey Hizalama" seçim kutusu eklendi ve anlık kod çıktısına bağlandı.

= 1.1.0 =
* Geliştirme: Admin paneli butonlarındaki (Şablonlar, Kopyala, Logo Seç, Ekle) Dashicon ikonlarının dikeyde metin ile kusursuz ortalanması sağlandı.
* Düzeltme: Shortcode Sihirbazı'nda 'center' ve 'wrap' seçildiğinde rozet hizalama ve dizilim parametrelerinin (badges_align="center", badges_layout="wrap") her zaman açıkça koda eklenmesi sağlandı.
* Düzeltme: Eklenti güncellendiğinde WordPress çekirdek güncelleme önbelleğinde eski sürüm uyarısının kalması engellendi (site_transient_update_plugins temizleme ve otomatik senkronizasyon).

= 1.0.3 =
* Düzeltme: Shortcode Sihirbazı'nda 'center' ve 'wrap' seçildiğinde rozet hizalama ve dizilim parametrelerinin (badges_align="center", badges_layout="wrap") her zaman açıkça koda eklenmesi sağlandı.
* Düzeltme: Eklenti güncellendiğinde WordPress çekirdek güncelleme önbelleğinde eski sürüm uyarısının kalması engellendi (site_transient_update_plugins temizleme ve otomatik senkronizasyon).

= 1.0.2 =
* Geliştirme: Sertifika standart rozetleri için yerleşim düzeni (badges_layout="wrap|stacked" - yan yana veya alt alta) parametresi eklendi.
* Geliştirme: Sertifika standart rozetleri için yatay hizalama (badges_align="center|left|right" - ortalı, sola, sağa) parametresi eklendi.
* Geliştirme: Shortcode Oluşturucu admin sihirbazına "Rozet Dizilimi" ve "Rozet Hizalama" kontrolleri eklendi.

= 1.0.1 =
* Geliştirme: Çoklu sertifika ve standart kodları (TS EN 12201-2, TS EN ISO 1452-2 vb.) için ayrı rozet/badge sistemi eklendi.
* Geliştirme: Admin paneline dinamik etiket/çip yöneticisi ve toplu standart ekleme desteği getirildi.
* Geliştirme: Frontend'de çoklu sertifika kodlarının şık, hizalı ve taşmayan rozetler halinde sarılması (wrap) sağlandı.

= 1.0.0 =
* İlk kararlı sürüm yayınlandı.
