# GNN Logos

WordPress logo, referans ve kalite standardı/sertifika vitrin eklentisi.

[![GitHub release](https://img.shields.io/badge/release-v2.0.1-blue.svg)](https://github.com/BigDesigner/gnn-logos/releases)
[![License](https://img.shields.io/badge/license-GPL--2.0-blue.svg)](LICENSE)
[![Buy Me A Coffee](https://img.shields.io/badge/Donate-Buy%20Me%20A%20Coffee-yellow.svg)](https://buymeacoffee.com/bigdesigner)

---

## Mimari ve Teknik Özellikler

- **Sıfır Bağımlılıklı Frontend Motoru:** Harici slider kütüphanesi (Swiper, Slick, Owl, jQuery) barındırmaz. Çok gruplu kusursuz GPU Marquee (`ResizeObserver` dinamik 4K/ultrawide doluluk), CSS Scroll-Snap Karusel (`IntersectionObserver` + `visibilitychange` + `focusin` korumalı autoplay) ve erişilebilir Lightbox modalı Vanilla JavaScript ile çalışır.
- **Native Gutenberg Bloğu (`gnn-logos/showcase`):** Zero-build `apiVersion: 3` dinamik blok; Block Editor ve Site Editor (FSE) içinde `ServerSideRender` ve `align: ["wide", "full"]` desteği sunar.
- **Tema Token Sistemi & Logo Filtreleri:** CSS Custom Properties tabanlı `light`, `dark` ve `auto` (`prefers-color-scheme`) temaları, `glass` (Glassmorphism) stili ve koyu zeminler için `monochrome-white`, `monochrome-dark`, `grayscale` filtreleri.
- **Erişilebilir Lightbox:** `lightbox="true"` ile sertifika ve logoları tam boyutta, standart rozetleri, açıklama ve harici belge/PDF bağlantısıyla açan XSS-güvenli (sıfır `innerHTML`) modal.
- **Gelişmiş Yönetim Deneyimi:** CPT listesinde sürükle-bırak `menu_order` sıralaması, çoklu ortam seçimiyle Toplu Logo Yükleyici, `gnn_logo_group` kategori filtresi ve Açık/Koyu/Dama zemin seçicili Canlı Önizlemeli Shortcode Oluşturucu.
- **Koşullu Yükleme (Conditional Enqueue):** Frontend CSS ve JS dosyaları yalnızca sayfada `[gnn_logos]` shortcode'u veya `gnn-logos/showcase` bloğu bulunduğunda yüklenir.
- **Yönetim Paneli & Güncelleyici:** WordPress menüsünde `'79.109'` pozisyonunda yer alır. Unified GNN GitHub Updater (`inc/updater.php`) ile WordPress çekirdek güncelleme hattına bağlanır.

---

## Sistem Gereksinimleri

- **PHP:** 8.0 veya üzeri
- **WordPress:** 5.8 veya üzeri (7.1 test edildi)

---

## Kurulum

1. Eklenti dizinini `/wp-content/plugins/gnn-logos` yoluna yerleştirin.
2. WordPress Yönetim Paneli -> **Eklentiler** sayfasından **GNN Logos** eklentisini etkinleştirin.
3. Sol menüdeki **GNN Logos** sayfasından tekil veya **Toplu Logo Yükle** ile logolarınızı ekleyin, sürükle-bırak ile sıralayın.
4. Sayfalarınıza Gutenberg **GNN Logos** bloğunu ekleyin veya **Shortcode Oluşturucu** ekranından canlı önizlemeli `[gnn_logos ...]` kodunu kopyalayın.

---

## Shortcode & Blok Parametreleri (`[gnn_logos]` / `gnn-logos/showcase`)

| Parametre | Tip | Varsayılan | Kabul Edilen Değerler / Açıklama |
|---|---|---|---|
| `group` | string | `""` | Taksonomi terim slug'ı (`gnn_logo_group`). Boş bırakılırsa tüm gruplar listelenir. |
| `layout` | string | `carousel` | `carousel`, `marquee`, `grid` |
| `style` | string | `minimal` | `minimal`, `card`, `bordered`, `glass` |
| `theme` | string | `light` | `light`, `dark`, `auto` (`prefers-color-scheme`) |
| `logo_filter` | string | `none` | `none`, `grayscale`, `monochrome-white`, `monochrome-dark` |
| `aspect_ratio` | string | `auto` | `16/9`, `4/3`, `1/1`, `3/2`, `2/1`, `auto` |
| `logo_height` | string | `""` | Sabit maksimum logo yüksekliği CSS değeri (örn: `56px`, `72px`) |
| `item_width` | string | `""` | Marquee öğe genişliği CSS değeri (örn: `160px`, `200px`) |
| `columns` | int | `4` | Masaüstü ekranlar için sütun sayısı (1–12) |
| `columns_tablet` | int | `3` | Tablet ekranlar için sütun sayısı (1–8) |
| `columns_mobile` | int | `2` | Mobil ekranlar için sütun sayısı (1–4) |
| `gap` | string | `20px` | Öğeler arası boşluk CSS değeri (örn: `16px`, `1.5rem`) |
| `show_code` | bool | `true` | Sertifika / standart kod rozetlerinin görünürlüğü (`true` / `false`) |
| `badges_layout` | string | `wrap` | Rozet dizilimi: `wrap` (akıcı / yan yana), `stacked` (dikey / alt alta) |
| `badges_align` | string | `center` | Rozet yatay hizalaması: `center`, `left`, `right` |
| `badges_valign` | string | `bottom` | Rozet dikey hizalaması: `bottom`, `top`, `center` |
| `show_title` | bool | `false` | Logo başlığının görünürlüğü (`true` / `false`) |
| `show_desc` | bool | `false` | Logo açıklamasının görünürlüğü (`true` / `false`) |
| `lightbox` | bool | `false` | Tıklandığında tam boy sertifika/logo modalını aç (`true` / `false`) |
| `grayscale` | bool | `false` | Geriye uyumlu kısa yol (`logo_filter="grayscale"` ile eşdeğer) |
| `autoplay` | bool | `true` | Karusel otomatik kaydırma (`true` / `false`) |
| `autoplay_speed` | int | `3000` | Karusel slayt geçiş aralığı (milisaniye) |
| `arrows` | bool | `true` | Karusel navigasyon okları (`true` / `false`) |
| `dots` | bool | `false` | Karusel sayfalama noktaları (`true` / `false`) |
| `speed` | string | `25s` | Marquee döngü animasyon süresi (CSS zaman değeri, örn: `20s`) |
| `direction` | string | `left` | Marquee akış yönü: `left`, `right` |
| `fade_edges` | bool | `true` | Marquee sol/sağ kenar yumuşatma maskesi (`true` / `false`) |
| `pause_on_hover` | bool | `true` | Fare üzerine gelindiğinde animasyonu duraklatma (`true` / `false`) |
| `limit` | int | `-1` | Listelenecek maksimum öğe sayısı (`-1` limitsiz) |
| `orderby` | string | `menu_order` | Sıralama alanı: `menu_order`, `date`, `title`, `rand`, `ID`, `author`, `name`, `modified`, `parent`, `none` |
| `order` | string | `ASC` | Sıralama yönü: `ASC`, `DESC` |

---

## Veri Yapısı ve Post Meta Anahtarları

- **CPT Slug:** `gnn_logo`
- **Taksonomi:** `gnn_logo_group` (hiyerarşik)
- **Gutenberg Bloğu:** `gnn-logos/showcase` (`gnn-logos/blocks/showcase/block.json`)
- **AJAX Uç Noktaları (Kimlik Doğrulamalı Admin):**
  - `wp_ajax_gnn_logos_reorder`: Sürükle-bırak `menu_order` güncellemesi
  - `wp_ajax_gnn_logos_bulk_create`: Medya eki ID listesiyle toplu logo oluşturma
  - `wp_ajax_gnn_logos_preview`: Shortcode Oluşturucu canlı HTML önizleme
- **Meta Alanları:**
  - `_gnn_logo_id` (int): WordPress medya eki ID'si
  - `_gnn_cert_codes` (array): Standart kodları dizisi (örn: `['TS EN 12201-2', 'TS EN ISO 1452-2']`)
  - `_gnn_description` (string): Alt açıklama / kurum bilgisi
  - `_gnn_link_url` (string): Hedef yönlendirme URL'i
  - `_gnn_link_target` (string): `_self` veya `_blank`
  - `_gnn_aspect_ratio` (string): Özel en-boy oranı geçersiz kılma

---

## Lisans

GPL v2 veya üstü.
