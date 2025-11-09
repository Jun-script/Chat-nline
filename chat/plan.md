# Sohbet Uygulaması Geliştirme Planı

## Proje Amacı
Kullanıcıların, arkadaşlık kodu sistemiyle birbirlerini ekleyerek özel olarak mesajlaşabileceği, modern ve güvenli bir web tabanlı sohbet uygulaması geliştirmek.

## Teknoloji ve Kararlar
*   **Backend Dili:** PHP
*   **Veritabanı:** MySQL
*   **Frontend:** `Studio(theme)` temasından uyarlanacak statik HTML, CSS ve JavaScript.
*   **Mimari:**
    *   Arayüz, `index.php` tarafından çağrılan `header.php`, `sidebar.php`, `chat.php` (eski `content.php`) gibi parçalı dosyalardan oluşacaktır.
    *   `index.php` ana yönlendirici (router) görevi görecek; kullanıcının giriş durumuna ve URL'deki `$_GET` parametrelerine göre (`?page=login`, `?page=register`, `?page=chat` gibi) ilgili sayfaları (`pages/login.php`, `pages/register.php`, `pages/chat.php`) dinamik olarak yükleyecektir.
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
- [ ] **Veritabanı Tasarımı:** `users` (arkadaşlık kodu sütunu ile), `contacts` (arkadaşlık ilişkileri için) ve `messages` (okundu bilgisi ile) tablolarının yapısını tasarla.
- [ ] **SQL Dosyası:** Tasarlanan yapıya göre `database.sql` dosyasını oluştur.

### Aşama 2: Arayüz İskeletinin Kurulumu
- [ ] **Sayfa Dosyaları:** `pages/content.php` dosyasını `pages/chat.php` olarak yeniden adlandır. `pages/login.php` ve `pages/register.php` dosyalarını oluştur.
- [ ] **`index.php` Yönlendirme Mantığı:** `index.php` dosyasını, kullanıcının giriş durumuna ve URL'deki `page` parametresine göre ilgili sayfaları (`login.php`, `register.php`, `chat.php`) yükleyecek şekilde düzenle.
- [ ] **Temel HTML Yapısı:** `header.php`'ye temel HTML başlangıç, head ve body etiketlerini ekle.
- [ ] **Sidebar İskeleti:** `sidebar.php` içine daraltılabilir kenar çubuğunun temel HTML iskeletini yerleştir.
- [ ] **Content İskeleti:** `chat.php` içine üst bar ve ana sohbet alanı için temel HTML iskeletini yerleştir.
- [ ] **Asset Entegrasyonu:** `Studio(theme)` klasöründen gerekli CSS ve JS dosyalarını `assets` klasörüne kopyala ve `header.php`'den çağır.

### Aşama 3: Kullanıcı İşlemleri
- [ ] **Kayıt Sayfası:** `register.php` sayfasını ve `api/register.php` backend kodunu oluştur.
- [ ] **Giriş Sayfası:** `login.php` sayfasını ve `api/login.php` backend kodunu (session yönetimi dahil) oluştur.
- [ ] **Oturum Kontrolü:** `index.php` ve diğer sayfalara sadece giriş yapmış kullanıcıların erişebilmesini sağla.

### Aşama 4: Backend API (İletişim)
- [ ] **Kişi Ekleme API'si:** Arkadaşlık kodunu kullanarak kişi eklemeyi sağlayan `api/add_contact.php` script'ini yaz.
- [ ] **Kişi Listesi API'si:** Giriş yapmış kullanıcının kişi listesini (`contacts` tablosundan) getiren `api/get_contacts.php` script'ini yaz.
- [ ] **Mesajları Getirme API'si:** Seçili kişiyle olan konuşmaları getiren `api/get_messages.php` script'ini yaz.
- [ ] **Mesaj Gönderme API'si:** Yeni mesaj gönderen `api/send_message.php` script'ini yaz.

### Aşama 5: Entegrasyon ve Test
- [ ] **AJAX Entegrasyonu:** Frontend'deki JavaScript ile tüm API'ler arasında bağlantı kur.
- [ ] **Arayüzün Doldurulması:** Kişi listesini ve mesajları arayüze dinamik olarak yansıt.
- [ ] **Canlı Mesajlaşma:** `setInterval` kullanarak periyodik olarak yeni mesajları kontrol et ve arayüzü güncelle.
- [ ] **Uçtan Uca Test:** Kayıt olma, giriş yapma, kullanıcı seçme ve mesajlaşma akışını baştan sona test et.
