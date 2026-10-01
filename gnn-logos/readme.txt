=== GNN Logos ===
Contributors: BigDesigner
Donate link: https://buymeacoffee.com/bigdesigner
Tags: logos, logo showcase, carousel, marquee, certificates, partners, references
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 8.0
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

WordPress için ultra hafif, sıfır harici bağımlılıklı logo, çözüm ortağı ve sertifika vitrini eklentisi.

== Description ==

GNN Logos; WordPress sitenizde referanslar, çözüm ortakları, sponsorlar ve kalite sertifikalarını (TS EN 12201-2, TS EN ISO 1452-2 vb.) modern ve estetik düzenlerde sunmanızı sağlayan yüksek performanslı bir vitrin eklentisidir.

Sitenizi yavaşlatan ağır harici JavaScript kütüphaneleri (Slick, Swiper 150KB+, jQuery eklentileri) KULLANMAZ. Modern CSS Scroll-Snap, donanım (GPU) hızlandırmalı sonsuz akan marquee ve 3 KB'tan küçük mikro Vanilla JS ile çalışır.

= Temel Özellikler =
* **Ortam Kütüphanesi Entegrasyonu:** WordPress'in kendi "Gözat" penceresi üzerinden tek tıkla logo seçimi.
* **Sertifika & Standart Rozetleri:** Logoların veya belgelerin altına TS EN 12201-2 gibi standart kodlarını şık rozet/badge tipografisiyle ekleme.
* **En-Boy Oranı Koruması:** Modern CSS aspect-ratio (16:9, 4:3, 1:1, 3:2, 2:1, auto) ile logoların şeklini ve hizasını bozmadan sergileme.
* **3 Farklı Vitrin Modu:**
  1. Carousel / Slider (Ok ve dokunmatik swipe kaydırmalı, otomatik oynatmalı)
  2. Continuous Ticker / Marquee (Kesintisiz sonsuz akan pürüzsüz şerit)
  3. Responsive Grid (Çok sütunlu estetik ızgara)
* **Görsel Stilleri:** Modern Kart (Card), İnce Çerçeveli (Bordered), Minimal / Sade.
* **Hover Filtreleri:** Varsayılan gri (grayscale) başlayıp fare üzerine gelindiğinde orijinal rengini alma efekti.
* **Görsel Shortcode Sihirbazı:** Menüden ayarları seçip tek tıkla `[gnn_logos ...]` kodunu kopyalayabileceğiniz admin paneli.
* **GitHub Entegre Güncelleyici:** Yeni sürümleri doğrudan WordPress panelinden güncelleme imkanı.

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
