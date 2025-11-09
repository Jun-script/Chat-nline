# Sohbet Uygulaması Geliştirme Planı

## Proje Amacı
Kullanıcıların, arkadaşlık kodu sistemiyle birbirlerini ekleyerek özel olarak mesajlaşabileceği, modern ve güvenli bir web tabanlı sohbet uygulaması geliştirmek.

## Teknoloji ve Kararlar
*   **Backend Dili:** PHP
*   **Veritabanı:** MySQL
*   **Frontend:** `Studio(theme)` temasından uyarlanacak statik HTML, CSS ve JavaScript.
*   **Mimari:**
    *   Arayüz, `index.php` tarafından çağrılan `header.php`, `sidebar.php`, `chat.php` gibi parçalı dosyalardan oluşacaktır.
    *   `index.php` ana yönlendirici (router) görevi görecek; kullanıcının giriş durumuna ve URL'deki `$_GET` parametrelerine göre (`?page=login`, `?page=register`, `?page=chat` gibi) ilgili sayfaları (`pages/login.php`, `pages/register.php`, `pages/chat.php`) dinamik olarak yükleyecektir.
    *   **API İşleme:** Tüm API istekleri (`?action=login`, `?action=get_contacts` gibi) de `index.php` üzerinden `pages/api_handler.php` dosyası aracılığıyla işlenecektir. Bu sayede ayrı bir `api` klasörüne gerek kalmayacaktır.
    *   Dinamik işlemler AJAX ile yönetilecektir.
*   **Gizlilik:** Kullanıcılar genel bir listede görünmeyecek. Bağlantı kurmak için her kullanıcıya özel, tahmin edilmesi zor bir "Arkadaşlık Kodu" atanacaktır.
*   **Kullanıcı Durumu:** "Son görülme" ve "okundu" bilgisi gibi özellikler eklenecektir.
*   **Güvenlik (İleri Seviye):** Mesajların iki kullanıcı arasında okunabilir bir şifreleme ile iletilmesi hedeflenmektedir.

---

## Geliştirme Adımları

### Aşama 1: Altyapı ve Dosya Yapısı
- [X] **Klasör Yapısı:** `/chat` altında `api`, `pages`, ve `assets` (içinde `css`, `js`, `img`) klasörlerini oluştur.
- [X] **Parçalı PHP Dosyaları:** `pages` klasörü içinde `header.php`, `sidebar.php` dosyalarını oluştur.
- [X] **Ana Index Dosyası:** Proje kökünde, parçalı PHP dosyalarını `require` ile birleştirecek olan `index.php` dosyasını oluştur.
- [X] **Yapılandırma Dosyası:** Veritabanı bağlantısı ve genel ayarlar için `config.php` dosyasını oluştur.
- [X] **Veritabanı Tasarımı:** `users`, `contacts` ve `messages` tablolarının yapısı tasarlandı.
- [X] **SQL Dosyası:** Tasarlanan yapıya göre `database.sql` dosyası `chat/` dizininde oluşturuldu. (Not: Daha sonra `setup/` dizinine taşınabilir.)

### Aşama 2: Arayüz İskeletinin Kurulumu
- [X] **Sayfa Dosyaları:** `pages/content.php` dosyasını `pages/chat.php` olarak yeniden adlandır. `pages/login.php` ve `pages/register.php` dosyalarını oluştur.
- [X] **`index.php` Yönlendirme Mantığı:** `index.php` dosyasını, kullanıcının giriş durumuna ve URL'deki `page` parametresine göre ilgili sayfaları (`login.php`, `register.php`, `chat.php`) yükleyecek şekilde düzenle.
- [X] **Temel HTML Yapısı:** `header.php`'ye temel HTML başlangıç, head ve body etiketlerini ekle.
- [X] **Sidebar İskeleti:** `sidebar.php` içine daraltılabilir kenar çubuğunun temel HTML iskeletini yerleştir.
- [X] **Content İskeleti:** `chat.php` içine üst bar ve ana sohbet alanı için temel HTML iskeletini yerleştir.
- [X] **Asset Entegrasyonu:** `Studio(theme)` klasöründen gerekli CSS ve JS dosyalarını `assets` klasörüne kopyala ve `header.php`'den çağır.
- [X] **Chat Arayüzü PHP Parçalara Bölme:** `index.html`'deki chat arayüzü `pages/header.php`, `pages/sidebar.php`, `pages/chat.php` ve `pages/footer.php` olarak PHP parçalarına ayrıldı.
- [X] **CSS/JS Entegrasyonu ve Düzeltmeler:** `custom.css` dosyası oluşturularak tema CSS'indeki çakışmalar giderildi ve `header.php`'ye eklendi. Gerekli JS dosyaları `footer.php`'ye eklendi.
- [X] **CSS Yolu Düzeltmeleri:** `header.php` içindeki CSS yolları, `config.php`'de tanımlanan `BASE_URL` kullanılarak güncellendi.

### Aşama 3: Kullanıcı İşlemleri
- [X] **Giriş Sayfası:** `login.php` sayfasını `Studio(theme)` temasından uyarlanarak oluşturuldu ve gerekli assetler entegre edildi. Artık `index.php` tarafından `header.php` ve `footer.php` ile birlikte dahil edilen bir parçadır. Form alanlarına `name` öznitelikleri eklendi ve "Sign up" bağlantısı güncellendi.
- [X] **Kayıt Sayfası:** `register.php` sayfasını `Studio(theme)` temasından uyarlanarak oluşturuldu ve gerekli assetler entegre edildi. Artık `index.php` tarafından `header.php` ve `footer.php` ile birlikte dahil edilen bir parçadır. Form alanlarına `name` öznitelikleri eklendi ve "Sign In" bağlantısı güncellendi.
- [X] **Oturum Kontrolü:** `index.php` ve diğer sayfalara sadece giriş yapmış kullanıcıların erişebilmesini sağla.

### Aşama 4: Backend API (İletişim) - Yeniden Düzenlendi
- [X] **API Mantığının Taşınması:** `chat/api` klasöründeki tüm API mantığı (`add_contact`, `get_contacts`, `get_messages`, `login`, `logout`, `register`, `send_message`) `chat/pages/api_handler.php` dosyasına taşındı.
- [X] **`index.php` API Yönlendirmesi:** `index.php` dosyası, `$_GET['action']` parametresine göre API isteklerini `pages/api_handler.php`'ye yönlendirecek şekilde güncellendi.
- [X] **`chat/api` Klasörünün Silinmesi:** `chat/api` klasörü manuel olarak silindi.

### Aşama 5: Entegrasyon ve Test (Frontend Odaklı)
- [ ] **AJAX Entegrasyonu:** Frontend'deki JavaScript ile tüm API'ler arasında bağlantı kur. **ÖNEMLİ:** Frontend AJAX çağrıları, `index.php?action=...` adresine yapılacak şekilde güncellenmelidir.
- [ ] **Arayüzün Doldurulması:** `chat/pages/sidebar.php` ve `chat/pages/chat.php` dosyalarındaki statik içerikler, API'den alınan dinamik verilerle doldurulmalıdır.
- [ ] **Canlı Mesajlaşma:** `setInterval` kullanarak periyodik olarak yeni mesajları kontrol et ve arayüzü güncelle.
- [ ] **Uçtan Uca Test:** Kayıt olma, giriş yapma, kullanıcı seçme ve mesajlaşma akışını baştan sona test et.

### Aşama 6: Temizlik ve Optimizasyon
- [ ] **Gereksiz Satırları Temizle:** Kullanılmayan veya gereksiz kod satırlarını temizle.
- [ ] **Pretty URL'ler:** `.htaccess` veya benzeri bir yöntemle `index.php?page=login` gibi URL'leri `/login` şeklinde daha okunabilir hale getir.

---

## Mevcut Durum ve Sonraki Adımlar (2025-11-09)

**Tamamlananlar:**
*   Backend PHP yapısı tamamen yeniden düzenlendi. Tüm API mantığı `chat/pages/api_handler.php` dosyasına taşındı ve `index.php` merkezi bir yönlendirici olarak yapılandırıldı.
*   `login.php`, `register.php`, `sidebar.php` ve `chat.php` gibi frontend PHP şablonları, dinamik veri entegrasyonu için hazırlandı ve statik içerikler yer tutucularla değiştirildi.
*   `chat/api` klasörü silindi.
*   `index.php` artık `/login`, `/register`, `/chat` gibi URL'lere gidildiğinde `pages` klasöründeki ilgili parçaları (header, footer, register.php vb.) birleştirerek tek bir ekran sunuyor.

**Kalan Görevler (Frontend Odaklı):**
1.  **Frontend AJAX Çağrılarını Güncelleme:** JavaScript dosyalarınızdaki (örneğin, `app.min.js`, `vendor.min.js` veya diğer özel JS dosyaları) tüm AJAX çağrılarını, eski `api/...` yolları yerine `index.php?action=login`, `index.php?action=register`, `index.php?action=get_contacts` vb. yeni merkezi uç noktalara işaret edecek şekilde manuel olarak güncelleyin.
2.  **Dinamik Veri Entegrasyonu:** `chat/pages/sidebar.php` ve `chat/pages/chat.php` dosyalarındaki dinamik yer tutucuları, güncellenmiş AJAX çağrıları aracılığıyla API'den alınan gerçek verilerle doldurmak için JavaScript kodunu yazın.
3.  **Canlı Mesajlaşma:** Yeni mesajları periyodik olarak kontrol etmek ve arayüzü güncellemek için JavaScript uygulayın.
4.  **Uçtan Uca Test:** Kayıt olma, giriş yapma, kişi ekleme, mesajlaşma akışını baştan sona test edin.
5.  **Gereksiz Kod Temizliği:** Kullanılmayan veya gereksiz kod satırlarını temizleyin.
6.  **Pretty URL'ler:** `.htaccess` kullanarak daha okunabilir URL'ler oluşturun.

---

## GitHub Copilot İçin Prompt Önerisi

Aşağıdaki prompt'u GitHub Copilot'a vererek, `chat/assets/js/custom.js` dosyasında AJAX çağrılarını güncelleme ve dinamik veri entegrasyonu konusunda yardım alabilirsiniz:

```
"Bu PHP tabanlı sohbet uygulamasında, tüm backend API çağrıları artık `index.php?action=...` formatında merkezi olarak işleniyor. `chat/assets/js/custom.js` dosyasında, kullanıcı girişini (login), kayıt olmayı (register), kişi listesini getirmeyi (get_contacts) ve mesaj göndermeyi (send_message) yöneten mevcut AJAX çağrılarını bul ve bunları yeni `index.php?action=...` formatına uygun şekilde güncelle. Ayrıca, `pages/sidebar.php` ve `pages/chat.php` içindeki statik yer tutucuları, bu güncellenmiş AJAX çağrılarından dönen dinamik verilerle dolduracak JavaScript kodunu ekle. Özellikle, sidebar'daki kullanıcı listesini ve chat alanındaki mesajları dinamik olarak yükle. Hata yönetimi ve yükleme durumları için basit mekanizmalar da ekle."
```
