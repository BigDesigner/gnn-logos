# GNN Logos 🚀

> WordPress için ultra hafif, sıfır harici bağımlılıklı logo, çözüm ortağı ve sertifika vitrini eklentisi.

[![GitHub release](https://img.shields.io/github/v/release/BigDesigner/gnn-logos)](https://github.com/BigDesigner/gnn-logos/releases)
[![License: GPL-2.0-or-later](https://img.shields.io/badge/License-GPLv2%2B-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Buy Me A Coffee](https://img.shields.io/badge/Donate-Buy%20Me%20A%20Coffee-yellow.svg)](https://buymeacoffee.com/bigdesigner)

---

## 🌟 Öne Çıkan Özellikler

- **Ultra Hafif (Zero External Dependency):** jQuery slider eklentileri (Slick, Owl) veya ağır kütüphaneler (Swiper 150KB+) KULLANMAZ. Saf modern CSS Scroll-Snap, donanım hızlandırmalı CSS Keyframes ve <3KB mikro Vanilla JS ile çalışır.
- **Sertifika & Standart Rozetleri:** Logoların ve kalite belgelerinin altına `TS EN 12201-2`, `TS EN ISO 1452-2`, `TS EN 1555-2` gibi standart kodlarını şık rozetler halinde ekleme imkanı.
- **En-Boy Oranı Bütünlüğü:** Modern CSS `aspect-ratio` (`16/9`, `4/3`, `1/1`, `3/2`, `2/1`, `auto`) ve `object-fit: contain` desteği ile farklı ebatlardaki logolar hiçbir zaman bozulmaz veya taşmaz.
- **3 Vitrin / Animasyon Modu:**
  1. **Carousel / Slider:** Dokunmatik swipe, autoplay döngüsü, ok ve sayfalama noktaları.
  2. **Continuous Ticker / Marquee:** Kesintisiz, pürüzsüz sağa/sola sonsuz akan logo şeridi (donanımsal GPU transform).
  3. **Responsive Grid:** Çok sütunlu responsive ızgara düzeni.
- **Görsel Stilleri:** Modern Kart (`card`), İnce Çerçeve (`bordered`), Minimal (`minimal`).
- **Gözat Butonu ile Yükleme:** WordPress'in yerel ortam kütüphanesi (`wp.media`) üzerinden tek tıkla logo seçimi.
- **Shortcode Sihirbazı:** Menüden ayarları seçip tek tıkla kopyalayabileceğiniz görsel oluşturucu panel (`GNN Logos > Shortcode Oluşturucu`).
- **Otomatik Güncelleyici (`inc/updater.php`):** GitHub Releases API entegrasyonu sayesinde yeni bir sürüm çıktığında WordPress panelinden tek tıkla güncelleme.
- **Admin Menü Pozisyonu:** GNN ürün ailesi standartlarına uygun olarak `'79.109'` pozisyonunda yer alır.

---

## 📦 Kurulum

1. Reponun en son `.zip` sürümünü [GitHub Releases](https://github.com/BigDesigner/gnn-logos/releases) sayfasından indirin veya `gnn-logos` klasörünü `/wp-content/plugins/` dizinine kopyalayın.
2. WordPress Yönetim Paneli -> **Eklentiler** bölümünden **GNN Logos** eklentisini etkinleştirin.
3. Sol menüde Ayarlar'ın hemen yanında beliren **GNN Logos** sekmesinden logolarınızı ekleyin.

---

## ⚡ Shortcode Parametreleri ve Örnekleri

### 1. Sertifika Kart Düzeni (TS EN Standartları İçin)
```html
[gnn_logos group="sertifikalar" layout="grid" style="card" aspect_ratio="4/3" columns="4" show_code="true"]
```

### 2. Referanslar - Sonsuz Akan Marquee Şeridi (Siyah-Beyaz Başlayan)
```html
[gnn_logos group="referanslar" layout="marquee" speed="25s" grayscale="true"]
```

### 3. Çözüm Ortakları - Otomatik Kayan Karusel (Slider)
```html
[gnn_logos group="cozum-ortaklari" layout="carousel" columns="5" aspect_ratio="16/9" autoplay="true" autoplay_speed="3000"]
```

### Tam Parametre Tablosu

| Parametre | Varsayılan | Seçenekler / Açıklama |
|---|---|---|
| `group` | `""` | Logo grubu slug'ı (örn: `sertifikalar`, `referanslar`). Boşsa hepsi. |
| `layout` | `carousel` | `carousel`, `marquee`, `grid` |
| `style` | `minimal` | `card` (Modern kart & gölge), `bordered` (İnce çerçeve), `minimal` |
| `aspect_ratio` | `auto` | `16/9`, `4/3`, `1/1`, `3/2`, `2/1`, `auto` |
| `columns` | `4` | Masaüstü sütun sayısı (1-10) |
| `columns_tablet`| `3` | Tablet sütun sayısı |
| `columns_mobile`| `2` | Mobil sütun sayısı |
| `gap` | `20px` | Logolar arası boşluk |
| `show_code` | `true` | Sertifika / standart kodunu rozet olarak göster |
| `badges_layout` | `wrap` | Rozet dizilimi: `wrap` (yan yana / akıcı), `stacked` (alt alta / dikey) |
| `badges_align` | `center` | Rozet yatay hizalama: `center` (ortalı), `left` (sola), `right` (sağa) |
| `show_title` | `false` | Başlığı göster |
| `show_desc` | `false` | Alt açıklamayı göster |
| `grayscale` | `false` | `true` ise logolar siyah-beyaz başlar, fare üzerine gelince renklenir |
| `autoplay` | `true` | Karusel için otomatik geçiş |
| `autoplay_speed`| `3000` | Karusel otomatik geçiş süresi (milisaniye) |
| `speed` | `25s` | Marquee için kayma süresi |
| `direction` | `left` | `left`, `right` |
| `arrows` | `true` | Karusel önceki/sonraki butonları |
| `dots` | `false` | Karusel sayfalama noktaları |
| `pause_on_hover`| `true` | Fare üzerindeyken kaymayı duraklat |

---

## 🛠️ Mimari & Geliştirici Bilgisi

- **Yazar:** [BigDesigner](https://github.com/BigDesigner)
- **Destek:** [Buy Me A Coffee](https://buymeacoffee.com/bigdesigner)
- **Lisans:** GPL v2 veya üstü
