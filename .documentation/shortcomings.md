# Teknik Borç, Sınırlar ve Çalışma Zamanı Riskleri

<!-- Verified from: gnn-logos/inc/updater.php -->
<!-- Verified from: gnn-logos/includes/class-gnn-logos-shortcode.php -->
<!-- Verified from: .specs/boundary-conditions.md -->

## 1. Kod Borcu İmleri Envanteri

Tüm PHP, JavaScript, CSS ve JSON dosyalarında (`14` eklenti kaynak dosyası) `TODO`, `FIXME`, `HACK`, `BUG`, `XXX` ve `DEPRECATED` ifadeleri taranmıştır:

| İfade Türü | Dosya Yolu | Satır | Durum / Önem |
|---|---|---|---|
| Yok | N/A | N/A | [OK] 14 kaynak dosyasında teknik borç veya geçici yama bulunmamaktadır |

---

## 2. Doğrulanamayan Çalışma Zamanı Riskleri ve Dış Sınırlar

| Risk Tanımlayıcısı | İlgili Bileşen | Sınır Açıklaması | Uygulanan Mimari Önlem |
|---|---|---|---|
| **R-001: GitHub API Hız Sınırları** | `GNN_Logos_Updater` | Yetkilendirilmemiş GitHub API istekleri IP başına saatte 60 ile sınırlıdır. | 12 saatlik pozitif önbellek, 5 dakikalık negatif önbellek ve API erişilemediğinde devreye giren `wp_remote_head` + `/releases/expanded_assets/{tag}` HTML fallback mekanizması (`ADR-0009`). |
| **R-002: Dosya Taşıma İzinleri** | `GNN_Logos_Updater::after_install` | `WP_Filesystem::move()` kısıtlayıcı dosya sahipliğinde başarısız olabilir. | `wp_normalize_path(untrailingslashit(...))` normalizasyonu, `$wp_filesystem->exists()` denetimi ve WordPress standart FTP/SSH akışı. |
| **R-003: Çok Yüksek Kayıt Sayısı** | `GNN_Logos_Shortcode` | `limit="-1"` ile yüzlerce logonun tek sayfada listelenmesi bellek kullanımını artırabilir. | `WP_Query` içine `no_found_rows => true` eklenmiştir; büyük arşivlerde makul `limit` (örn: `20`–`50`) önerilir. |
| **R-004: SVG Güvenlik Politikaları** | WordPress Ortam Kütüphanesi | WordPress çekirdeği güvenlik nedeniyle varsayılan olarak SVG yüklemeyi engeller. | SVG logolar kullanılmak istendiğinde Safe SVG benzeri güvenilir bir temizleyici eklenti önerilir; eklenti kendi başına güvensiz MIME izni açmaz. |

---

## 3. v1.2.0 – v2.0.0 Arasında Çözülen Mimari ve Güvenlik Sorunları

1. **Marquee Sıçrama & Kısa Liste Boşlukları (v2.0.0):** Tek kapsayıcı `-50%` yerine çok gruplu `.gnn-marquee-group` (`-100%`) + `ResizeObserver` dinamik 4K klonlama ve `loading="eager"` uygulandı.
2. **Carousel Son Slayt Atlama & Fazla Noktalar (v2.0.0):** `goNext()` off-by-one hatası giderildi; ulaşılamayan noktalar ve statik listelerdeki oklar gizlendi; `IntersectionObserver` + `visibilitychange` + `focusin` autoplay koruması eklendi.
3. **Koyu Tema Uyumsuzluğu (v2.0.0):** CSS Custom Property token sistemi (`theme="light|dark|auto"`), `glass` stili ve `monochrome-white` / `monochrome-dark` logo filtreleri eklendi.
4. **Shortcode Oluşturucu `show_code="false"` Hatası (v2.0.0):** Kutu kapatıldığında parametrenin açıkça çıktıya eklenmesi sağlandı ve canlı görsel önizleme (`gnn_logos_preview`) eklendi.
5. **Güvenlik ve Yaşam Döngüsü (v1.2.0–v1.2.6):** Katı regex CSS doğrulamaları, `orderby` beyaz listesi, AJAX nonce/yetki denetimleri, önyüzde sıfır `innerHTML`, tam `uninstall.php` temizliği ve Unified GNN GitHub Updater standardı tamamlandı.

---

## 4. Gelecek Yol Haritası

- **Çoklu Dil Eklenti Köprüleri:** WPML ve Polylang için `wpml-config.xml` meta alan çeviri yapılandırmasının eklenmesi.
- **WordPress 7.2 Doğrulaması:** WordPress 7.2 final sürümü yayınlandığında uyumluluk testlerinin yapılıp `Tested up to` bilgisinin güncellenmesi.
