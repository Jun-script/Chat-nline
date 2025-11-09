# Sohbet Uygulaması Geliştirme Planı

## Proje Amacı
Kullanıcıların, arkadaşlık kodu sistemiyle birbirlerini ekleyerek özel olarak mesajlaşabileceği, modern ve güvenli bir web tabanlı sohbet uygulaması geliştirmek.

## Teknoloji ve Kararlar
*   **Backend Dili:** PHP
*   **Veritabanı:** MySQL
*   **Frontend:** `Studio(theme)` temasından uyarlanacak statik HTML, CSS ve JavaScript.
*   **Mimari:**
    *   Arayüz, `index.php` tarafından çağrılan `header.php`, `sidebar.php`, `chat.php` (eski `content.php`) gibi parçalı dosyalardan oluşacaktır.
    *   `index.php` ana yönlendirici (router) görevi görecek; kullanıcının giriş durumuna ve URL'deki `$_GET` parametrelerine göre (`?page=login`, `?page=register`, `?page=chat` gibi) ilgili sayfaları (`pages/login.php`, `pages/register.php`, `pages/chat.php`) dinamik olarak yükleyeceklerdir.
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

### Aşama 3: Kullanıcı İşlemleri
- [X] **Giriş Sayfası:** `login.php` sayfasını `Studio(theme)` temasından uyarlanarak oluşturuldu ve gerekli assetler entegre edildi.
- [X] **Kayıt Sayfası:** `register.php` sayfasını `Studio(theme)` temasından uyarlanarak oluşturuldu ve gerekli assetler entegre edildi.
- [ ] **Oturum Kontrolü:** `index.php` ve diğer sayfalara sadece giriş yapmış kullanıcıların erişebilmesini sağla.

### Aşama 4: Backend API (İletişim)
- [ ] **Kişi Ekleme API'si:** Arkadaşlık kodunu kullanarak kişi eklemeyi sağlayan `api/add_contact.php` script'ini yaz. (Frontend entegrasyonu `chat.php` içinde mevcut.)
- [ ] **Kişi Listesi API'si:** Giriş yapmış kullanıcının kişi listesini (`contacts` tablosundan) getiren `api/get_contacts.php` script'ini yaz. (Frontend entegrasyonu `chat.php` içinde mevcut.)
- [ ] **Mesajları Getirme API'si:** Seçili kişiyle olan konuşmaları getiren `api/get_messages.php` script'ini yaz. (Frontend entegrasyonu `chat.php` içinde mevcut.)
- [ ] **Mesaj Gönderme API'si:** Yeni mesaj gönderen `api/send_message.php` script'ini yaz. (Frontend entegrasyonu `chat.php` içinde mevcut.)

### Aşama 5: Entegrasyon ve Test
- [ ] **AJAX Entegrasyonu:** Frontend'deki JavaScript ile tüm API'ler arasında bağlantı kur.
- [ ] **Arayüzün Doldurulması:** Kişi listesini ve mesajları arayüze dinamik olarak yansıt.
- [ ] **Canlı Mesajlaşma:** `setInterval` kullanarak periyodik olarak yeni mesajları kontrol et ve arayüzü güncelle.
- [ ] **Uçtan Uca Test:** Kayıt olma, giriş yapma, kullanıcı seçme ve mesajlaşma akışını baştan sona test et.

---

## Arayüz Durumu (2025-11-09)

*   **Genel Yapı:** Sohbet uygulamasının temel arayüzü, `Studio(theme)` temasından uyarlanan `pages/header.php`, `pages/sidebar.php`, `pages/chat.php` ve `pages/footer.php` dosyaları kullanılarak PHP parçalarına ayrılmıştır. `chat/index.php` bu parçaları birleştirerek tam bir sohbet arayüzü sunmaktadır.
*   **Login Sayfası:** `pages/login.php` dosyası, `Studio(theme)` temasından uyarlanarak oluşturulmuş ve gerekli CSS/JS entegrasyonu sağlanmıştır.
*   **Kayıt Sayfası:** `pages/register.php` dosyası, `Studio(theme)` temasından uyarlanarak oluşturulmuş ve gerekli CSS/JS entegrasyonu sağlanmıştır.
*   **Stil Entegrasyonu:** Tema CSS ve JS dosyaları entegre edilmiş, `custom.css` ile gerekli stil çakışmaları giderilmiştir.
*   **İçerik:** Tüm içerik (kullanıcı bilgileri, sohbet geçmişi, kişi listesi) şu anda statik HTML olarak kodlanmıştır.
*   **Yapılacaklar:**
    1.  Veritabanı ve API'ler tamamlandıktan sonra statik içerik dinamik verilerle değiştirilmeli.
    2.  Kullanılmayan gereksiz satırları temizlemek.