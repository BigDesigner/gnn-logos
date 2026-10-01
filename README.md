# GNN Logos

WordPress logo, referans ve kalite standardı/sertifika vitrin eklentisi.

[![GitHub release](https://img.shields.io/github/v/release/BigDesigner/gnn-logos)](https://github.com/BigDesigner/gnn-logos/releases)
[![License: GPL-2.0-or-later](https://img.shields.io/badge/License-GPLv2%2B-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Buy Me A Coffee](https://img.shields.io/badge/Donate-Buy%20Me%20A%20Coffee-yellow.svg)](https://buymeacoffee.com/bigdesigner)

---

## Mimari ve Teknik Özellikler

- **Frontend Motoru:** Harici JavaScript veya slider kütüphanesi (Swiper, Slick, Owl vb.) barındırmaz. CSS Scroll-Snap, donanım hızlandırmalı CSS keyframes ve Vanilla JavaScript ile çalışır.
- **Koşullu Yükleme (Conditional Enqueue):** CSS ve JS dosyaları yalnızca sayfada `[gnn_logos]` shortcode'u bulunduğunda çağrılır.
- **Standart & Rozet Sistemi:** `_gnn_cert_codes` meta dizisi üzerinden tekli veya çoklu standart rozetleri (TS EN, ISO vb.) desteklenir.
- **En-Boy Oranı Kontrolü:** Modern CSS `aspect-ratio` (`16/9`, `4/3`, `1/1`, `3/2`, `2/1`, `auto`) ve `object-fit: contain` ile görsel hizalaması sağlanır.
- **Yönetim Paneli:** WordPress menüsünde `'79.109'` pozisyonunda yer alır.
- **Güncelleme Mekanizması:** GitHub Releases API entegrasyonu (`inc/updater.php`) ile WordPress çekirdek güncelleme hattına (`pre_set_site_transient_update_plugins` ve `site_transient_update_plugins`) bağlanır.

---

## Sistem Gereksinimleri

- **PHP:** 8.0 veya üzeri
- **WordPress:** 5.8 veya üzeri

---

## Kurulum

1. Eklenti dizinini `/wp-content/plugins/gnn-logos` yoluna yerleştirin.
2. WordPress Yönetim Paneli -> **Eklentiler** sayfasından **GNN Logos** eklentisini etkinleştirin.
3. Yönetim panelinde **GNN Logos** menüsü altından logolarınızı ekleyin veya **Shortcode Oluşturucu** arayüzünü kullanın.

---

## Shortcode Parametreleri (`[gnn_logos]`)

| Parametre | Tip | Varsayılan | Kabul Edilen Değerler / Açıklama |
|---|---|---|---|
| `group` | string | `""` | Taksonomi terim slug'ı (`gnn_logo_group`). Boş bırakılırsa tüm gruplar listelenir. |
| `layout` | string | `carousel` | `carousel`, `marquee`, `grid` |
| `style` | string | `minimal` | `card`, `bordered`, `minimal` |
| `aspect_ratio` | string | `auto` | `16/9`, `4/3`, `1/1`, `3/2`, `2/1`, `auto` |
| `columns` | int | `4` | Masaüstü ekranlar için sütun sayısı (1-10) |
| `columns_tablet`| int | `3` | Tablet ekranlar için sütun sayısı (1-8) |
| `columns_mobile`| int | `2` | Mobil ekranlar için sütun sayısı (1-4) |
| `gap` | string | `20px` | Öğeler arası boşluk CSS değeri (örn: `15px`, `1.5rem`) |
| `show_code` | bool | `true` | Sertifika / standart kod rozetlerinin görünürlüğü (`true` / `false`) |
| `badges_layout` | string | `wrap` | Rozet dizilimi: `wrap` (akıcı / yan yana), `stacked` (dikey / alt alta) |
| `badges_align` | string | `center` | Rozet yatay hizalaması: `center`, `left`, `right` |
| `badges_valign`| string | `bottom` | Rozet dikey hizalaması: `bottom` (kart altına sabitli), `top` (logonun hemen altında), `center` (dikey ortalı) |
| `show_title` | bool | `false` | Logo başlığının görünürlüğü (`true` / `false`) |
| `show_desc` | bool | `false` | Logo açıklamasının görünürlüğü (`true` / `false`) |
| `grayscale` | bool | `false` | Başlangıçta CSS gri tonlama filtresi (`true` / `false`) |
| `autoplay` | bool | `true` | Karusel otomatik kaydırma (`true` / `false`) |
| `autoplay_speed`| int | `3000` | Karusel slayt geçiş aralığı (milisaniye) |
| `speed` | string | `25s` | Marquee döngü animasyon süresi (CSS zaman değeri, örn: `20s`) |
| `direction` | string | `left` | Marquee akış yönü: `left`, `right` |
| `arrows` | bool | `true` | Karusel navigasyon okları (`true` / `false`) |
| `dots` | bool | `false` | Karusel sayfalama noktaları (`true` / `false`) |
| `pause_on_hover`| bool | `true` | Fare üzerine gelindiğinde animasyonu duraklatma (`true` / `false`) |
| `limit` | int | `-1` | Listelenecek maksimum öğe sayısı (`-1` limitsiz) |
| `orderby` | string | `menu_order` | Sıralama alanı: `menu_order`, `date`, `title`, `rand` |
| `order` | string | `ASC` | Sıralama yönü: `ASC`, `DESC` |

---

## Veri Yapısı ve Post Meta Anahtarları

- **CPT Slug:** `gnn_logo`
- **Taksonomi:** `gnn_logo_group` (hiyerarşik)
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
