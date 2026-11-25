# Geliştirici Rehberi, Sözleşmeler ve Hata Ayıklama

<!-- Verified from: .specs/constitution.md -->
<!-- Verified from: .specs/boundary-conditions.md -->
<!-- Verified from: .github/workflows/release.yml -->

## 1. Mühendislik Disiplinleri ve Kod Standartları

### 1.1 WordPress Kodlama Standartları (WPCS)
- **Doğrudan Erişim Koruması:** Her PHP dosyası `defined('ABSPATH') || exit;` ile başlamalıdır (`uninstall.php` için `defined('WP_UNINSTALL_PLUGIN') || exit;`).
- **Unslashing ve Sanitizasyon:** Süper globaller (`$_POST`, `$_GET`) `wp_unslash()` olmadan sanitize edilmemelidir.
- **AJAX Güvenliği:** Her `wp_ajax_*` işleyicisi önce `check_ajax_referer('gnn_logos_admin_nonce', 'nonce')`, ardından ilgili `current_user_can(...)` denetimini çalıştırmalıdır.
- **Bağlamsal Kaçış ve DOM Güvenliği:** PHP çıktılarında `esc_html()`, `esc_attr()`, `esc_url()`, `esc_html__()` zorunludur. Önyüz JS dosyasında (`gnn-logos-frontend.js`) `innerHTML` kesinlikle kullanılmaz.
- **Sıfır Harici Önyüz Bağımlılığı (ADR-0010):** jQuery slider, harici lightbox, npm runtime paketi veya CDN scripti yasaktır. Önyüz bütçesi sıkıştırılmamış CSS `< 28 KB` (~23.6 KB), JS `< 20 KB` (~17.7 KB) sınırındadır.

---

## 2. Test ve Statik Analiz Komutları

```powershell
# 1. Tüm PHP dosyaları sözdizimi doğrulaması:
Get-ChildItem -Recurse -Filter *.php gnn-logos | ForEach-Object { php -l $_.FullName }

# 2. JS sözdizimi doğrulaması:
node --check gnn-logos/assets/js/gnn-logos-frontend.js
node --check gnn-logos/assets/js/gnn-logos-admin.js
node --check gnn-logos/blocks/showcase/editor.js

# 3. Gutenberg block.json şema doğrulaması:
Get-Content gnn-logos/blocks/showcase/block.json -Raw | ConvertFrom-Json
```

---

## 3. Hata Ayıklama Reçeteleri

### 3.1 Güncelleme Kontrolünü Zorlama
`https://ornek-site.com/wp-admin/plugins.php?gnn_logos_check_update=1` (Eklentiler sayfasındaki **Check Updates** bağlantısı üzerinden nonce ile çağrılır). `gnn_logos_github_update_check` transientini temizler ve `update-core.php?force-check=1` ekranına yönlendirir.

### 3.2 Canlı Önizleme veya Sürükle-Bırak Sıralama Çalışmıyorsa
- `GNN_Logos_Admin::enqueue_admin_assets()` içinde `gnnLogosAdmin` nesnesinin (`ajaxUrl`, `nonce`, `pageOffset`) enjekte edildiğini doğrulayın.
- Sürükle-bırak sıralama yalnızca `edit.php?post_type=gnn_logo` ekranında ve `jquery-ui-sortable` yüklü olduğunda aktifleşir.
- Shortcode Oluşturucu canlı önizlemesi her AJAX yanıtından sonra `window.GNNLogosInit(stageElement)` fonksiyonunu çağırarak Marquee, Carousel ve Lightbox motorlarını yeniden başlatır.

### 3.3 Gutenberg Bloğu Önizleme Stilleri
`GNN_Logos_Shortcode::register_assets()` kancası `init` priority `5` ile çalışarak `gnn-logos-frontend` stilini `GNN_Logos_Block::register_block()` (`init` priority `10`) öncesinde kaydeder; böylece `block.json` içindeki `"style": "gnn-logos-frontend"` Site Editor (FSE) iframe'inde de yüklenir.

---

## 4. Sürüm ve Dağıtım Kontrol Listesi

- [ ] Kök `VERSION`, `gnn-logos/gnn-logos.php` (`Version` ve `GNN_LOGOS_VERSION`), `gnn-logos/readme.txt` (`Stable tag` ve Changelog), `gnn-logos/blocks/showcase/block.json` (`version`), `gnn-logos/languages/gnn-logos.pot` (`Project-Id-Version`) ve `README.md` rozetini güncelleyin.
- [ ] Kod değişikliklerini ayrı commit, sürüm artışını `chore(release): bump version to X.Y.Z` ayrı commit olarak kaydedin.
- [ ] Tüm PHP (`php -l`), JS (`node --check`) ve `block.json` doğrulamalarını 0 hatayla geçin.
- [ ] `git push origin main`, `git tag -a vX.Y.Z -m "Release vX.Y.Z"`, `git push origin vX.Y.Z` ve `gh workflow run release.yml` komutlarını çalıştırın.
- [ ] GitHub Releases üzerinde `gnn-logos-X.Y.Z.zip` paketinin oluşturulduğunu doğrulayın.
