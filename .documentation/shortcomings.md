# Teknik Borç, Sınırlar ve Çalışma Zamanı Riskleri

<!-- Verified from: gnn-logos/inc/updater.php#L85-L115 -->
<!-- Verified from: gnn-logos/includes/class-gnn-logos-shortcode.php#L130-L160 -->
<!-- Verified from: .specs/boundary-conditions.md#L50-L75 -->

## 1. Kod Borcu İmleri Envanteri

Tüm PHP, JavaScript ve CSS dosyalarında `TODO`, `FIXME`, `HACK`, `BUG`, `XXX` ve `DEPRECATED` ifadeleri statik olarak taranmıştır:

| İfade Türü | Dosya Yolu | Satır | Kod Özeti / Açıklama | Durum / Önem |
|---|---|---|---|---|
| Yok | N/A | N/A | Projenin 10 kaynak dosyasında hiçbir teknik borç veya geçici yama tespit edilmedi | [OK] Temiz Taban Çizgisi |

---

## 2. Doğrulanamayan Çalışma Zamanı Riskleri ve Dış Sınırlar

<!-- Verified from: gnn-logos/inc/updater.php#L95-L110 -->

> [!WARNING]
> Statik sözdizim denetimi (`php -l`), PHP kodunun yalnızca derleme seviyesinde hatasız olduğunu kanıtlar. Aşağıdaki operasyonel sınırlar sunucu ortamına ve harici altyapılara bağlıdır:

| Risk Tanımlayıcısı | İlgili Bileşen | Sınır Açıklaması | Olası Hata Durumu | Uygulanan Mimari Önlem |
|---|---|---|---|---|
| **R-001: GitHub API Hız Sınırları** | `GNN_Logos_Updater` | GitHub'ın genel API'si yetkilendirilmemiş istekleri kaynak IP başına saatte 60 istekle sınırlar. | Paylaşımlı bir sunucuda birden fazla site güncelleme sorguladığında veya yönetici önbelleği peş peşe temizlediğinde GitHub `403 Forbidden` (`API rate limit exceeded`) dönebilir. | 12 saatlik transient önbelleği (`gnn_logos_github_update_check`) ile korunur. Başarısız istekler de 5 dakika (`300s`) önbelleğe alınarak peş peşe istek döngüleri engellenir. |
| **R-002: Dosya Taşıma İzinleri** | `GNN_Logos_Updater::after_install` | WordPress `WP_Filesystem::move()` ile GitHub'ın açtığı klasörü `wp-content/plugins/gnn-logos/` yoluna normalize eder. | Kısıtlayıcı dosya sahipliklerinde (örn: PHP `www-data` altında çalışırken dosyaların sahibi `root` ise) klasör taşıma işlemi başarısız olabilir. | Doğrudan yazma yetersizse WordPress çekirdeği otomatik olarak FTP/SSH kimlik bilgisi talep eder; standart `upgrader_post_install` hata akışı korunmuştur. |
| **R-003: Yüksek Veri Hacminde Bellek Tüketimi** | `GNN_Logos_Shortcode` | `limit="-1"` verildiğinde `WP_Query` yayımlanmış tüm `gnn_logo` yazılarını belleğe çeker. | Yüzlerce logonun bulunduğu sitelerde tek istekte tüm ek meta verilerini döngüye sokmak bellek tüketimini artırabilir. | Karusel ve kayan şerit vitrinlerinde makul limit değerleri (örn: `20` veya `30`) kullanılması önerilir. |
| **R-004: Eski Tarayıcılarda En-Boy Oranı Desteği** | `gnn-logos-frontend.css` | Modern CSS `aspect-ratio` özelliğini kullanır. | Eski tarayıcılar (Chrome <88, Safari <15) yerel CSS `aspect-ratio` desteğine sahip değildir. | Düzenin bozulmaması için `.gnn-logo-img` üzerinde `max-height: 100%` ve `object-fit: contain` kuralları ile kademeli yedekleme sağlanmıştır. |

---

## 3. Sıkılaştırılan Güvenlik Sınırları (v1.2.0 İtibarıyla)

Son Sentinel güvenlik denetiminde (Commit `5e32cbe`) tespit edilen sınır eksiklikleri kalıcı olarak çözüme kavuşturulmuştur:

1. **Eski Sertifika Kodlarında Satır Sonu Koruması (H-1):**
   - *Önceki Risk:* `sanitize_text_field()` fonksiyonunun `\r\n` karakterlerini boşluğa çevirerek `preg_split`'in satır başı ayrımını bozması.
   - *Çözüm:* `sanitize_textarea_field(wp_unslash($_POST['gnn_cert_code']))` ile değiştirildi.
2. **Shortcode `orderby` Parametresi İzin Listesi (H-3):**
   - *Önceki Risk:* Gelen sıralama metninin doğrudan `WP_Query` sorgusuna aktarılması.
   - *Çözüm:* Katı beyaz liste (`menu_order`, `date`, `title`, `rand`, `ID`, `author`, `name`, `modified`, `parent`, `none`) uygulandı.
3. **Yönetim Panelinde DOM Tabanlı XSS Önleme (H-4):**
   - *Önceki Risk:* Görsel önizlemesinde string birleştirmeli `.html('<img src="' + url + '">')` kullanımı.
   - *Çözüm:* Güvenli jQuery DOM nesne üretimi (`$('<img>').attr('src', url)`) ile değiştirildi.
4. **Kaldırma Sırasında Yetim Kalan Veritabanı Kayıtları (M-1):**
   - *Önceki Risk:* `uninstall.php` dosyasının yalnızca transientleri silip yazıları ve terimleri temizlememesi.
   - *Çözüm:* Kaldırma rutini tüm `gnn_logo` yazılarını, `postmeta` verilerini, `gnn_logo_group` terimlerini ve transientleri tamamen silecek şekilde güncellendi.

---

## 4. Ürün Yol Haritası ve Stratejik Genişleme

- **Native Editör ve Görsel Düzenleyici Desteği:** Mevcut shortcode motoruna ek olarak modern Gutenberg yerel bloğu (`@wordpress/block-editor`) ve Elementor custom widget bileşenlerinin geliştirilmesi.
- **Çoklu Dil Uyumluluğu:** Çok dilli web siteleri için WPML ve Polylang entegrasyonu sağlanarak logo başlıkları, açıklamaları ve rozet dizilimlerinin yerelleştirilmesi.
