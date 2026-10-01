# Geliştirici Rehberi, Sözleşmeler ve Hata Ayıklama

<!-- Verified from: .specs/constitution.md#L1-L34 -->
<!-- Verified from: .specs/boundary-conditions.md#L1-L50 -->
<!-- Verified from: .github/workflows/release.yml#L1-L50 -->

## 1. Mühendislik Disiplinleri ve Kod Standartları

### 1.1 WordPress Kodlama Standartları (WPCS)
- **Doğrudan Erişim Koruması:** Her PHP dosyası mutlaka şu kontrolle başlamalıdır:
  ```php
  defined('ABSPATH') || exit;
  ```
  *(Yaşam döngüsü kaldırma dosyası için: `defined('WP_UNINSTALL_PLUGIN') || exit;`).*
- **Ters Eğik Çizgi Arındırma (Unslashing) ve Sanitizasyon:** Süper globaller (`$_POST`, `$_GET`) arındırılmadan asla sanitize edilmemelidir:
  ```php
  $clean = sanitize_text_field(wp_unslash($_POST['key']));
  $clean_url = esc_url_raw(wp_unslash($_POST['url']));
  ```
- **Bağlamsal Çıktı Kaçışı (Contextual Output Escaping):** HTML içine aktarılan her dinamik değer kesinlikle bağlama uygun kaçış fonksiyonundan geçirilmelidir:
  - HTML metin düğümü: `esc_html($var)`
  - Etiket özniteliği: `esc_attr($var)`
  - URL bağlantısı: `esc_url($var)`
  - Çoklu dil destekli metin: `esc_html__('Metin', 'gnn-logos')`
- **Sıfır Harici Önyüz Bağımlılığı Kuralı:**
  - jQuery slider eklentileri (Slick, Owl Carousel) veya ağır harici kütüphanelerin (Swiper 150KB+) eklenmesi kesinlikle yasaktır.
  - Karusel etkileşimleri yalnızca yerel CSS Scroll-Snap + hafif Vanilla JS (<3 KB) ile çalışmalıdır.
  - Kayan şerit (marquee) animasyonları %100 saf CSS GPU keyframe dönüşümleri (`translate3d`) ile yürütülmelidir.

---

## 2. Test ve Statik Analiz Rehberi

### 2.1 Sözdizim Doğrulama (`php -l`)
Her commit öncesinde, değiştirilen veya eklenen tüm PHP dosyaları sözdizim hatalarına karşı taranmalıdır:
```bash
# Windows PowerShell toplu doğrulama:
Get-ChildItem -Recurse -Filter *.php gnn-logos | ForEach-Object { php -l $_.FullName }

# Linux / macOS Bash:
find gnn-logos -name "*.php" -exec php -l {} \;
```

### 2.2 Güvenlik Denetimi ve Sentinel Araçları
```bash
# Sentinel Hafıza Bankası sağlık kontrolü:
/sentinel-doctor

# Mimari Karar Kayıtları (ADR) kontrolü:
/sentinel-adr

# Teknik dokümantasyonu yeniden derleme:
/sentinel-docgen
```

---

## 3. Yerel Hata Ayıklama ve Sorun Giderme Reçeteleri

### 3.1 WordPress Hata Günlüğü (Debug Logging)
Çalışma zamanı hatalarını ve veritabanı uyarılarını izlemek için `wp-config.php` dosyasına ekleyin:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```
Günlük kayıtları `/wp-content/debug.log` dosyasına yazılacaktır.

### 3.2 Güncelleme Kontrolünü Zorlama ve Transient Temizleme
GitHub'a yeni bir sürüm gönderildiğinde WordPress panelinde "Güncelleme Mevcut" uyarısı hemen belirmemişse:
1. Şu adrese gidin:
   `https://ornek-site.com/wp-admin/plugins.php?gnn_logos_check_update=1`
2. Bu işlem `GNN_Logos_Updater::handle_manual_check()` fonksiyonunu tetikleyerek `gnn_logos_github_update_check` ve `_site_transient_update_plugins` önbelleklerini temizler ve güvenle `update-core.php?force-check=1` sayfasına yönlendirir.

### 3.3 Ortam Seçici Scriptinin Yüklenmemesi
"Gözat / Logo Seç" butonu WordPress ortam modalını açmıyorsa:
- Hedef yönetim ekranında `wp_enqueue_media()` çağrısının yapıldığını doğrulayın.
- `class-gnn-logos-admin.php#L130-L135` içinde bu scriptlerin yalnızca `post_type === 'gnn_logo'` veya `page === 'gnn-logos-generator'` ekranlarında koşullu olarak yüklendiğini unutmayın.

---

## 4. Git ve Katkı Sağlama İş Akışı

- **Dal Stratejisi:** Geliştirmeler doğrudan `main` üzerinde sürüm hazırlığı veya özellik dalları (`feat/ozellik-adi`, `fix/hata-adi`) şeklinde yürütülür.
- **Commit Formatı:** Conventional Commits standardı benimsenmiştir:
  - `feat: release v1.2.0 with security hardening`
  - `fix(cpt): preserve newlines in legacy cert codes`
  - `docs(readme): fix shields badge endpoints with static svg`
  - `chore(sentinel): standardize ADR lineage and memory bank baseline`
- **Etiketleme (Tagging) Protokolü:** Sürümler kesin semantik versiyonlama ile etiketlenmelidir:
  ```bash
  git tag -a v1.2.0 -m "Release v1.2.0: Security hardening, sanitization alignment, and complete lifecycle cleanup"
  git push origin v1.2.0
  ```

---

## 5. Sürüm ve Dağıtım Kontrol Listesi

GitHub Releases üzerinden yeni bir sürüm yayımlamadan önce:
- [ ] `gnn-logos/gnn-logos.php` içindeki sürüm numarasını güncelleyin (`Version: X.Y.Z` başlığı ve `GNN_LOGOS_VERSION` sabiti).
- [ ] `gnn-logos/readme.txt` içindeki `Stable tag: X.Y.Z` değerini güncelleyin.
- [ ] `gnn-logos-frontend.css`, `gnn-logos-admin.css`, `gnn-logos-frontend.js`, `gnn-logos-admin.js` dosyalarındaki `@version X.Y.Z` değerlerini güncelleyin.
- [ ] Tüm PHP dosyalarında `php -l` çalıştırarak 0 hata olduğunu doğrulayın.
- [ ] `README.md` dosyasındaki rozet URL'lerinin hedef sürümle eşleştiğini kontrol edin.
- [ ] Değişiklikleri `main` dalına commit edip pushlayın.
- [ ] `vX.Y.Z` Git etiketini oluşturup `origin`e gönderin.
- [ ] GitHub Actions manuel yayın iş akışını tetikleyin: `gh workflow run release.yml`.
- [ ] GitHub Releases sayfasında `gnn-logos-X.Y.Z.zip` arşivinin üretildiğini doğrulayın.
- [ ] Canlı bir WordPress kurulumunda güncellemenin sorunsuz yüklendiğini teyit edin.
