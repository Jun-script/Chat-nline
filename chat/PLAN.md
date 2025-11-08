# WebRTC P2P Sohbet Uygulaması Geliştirme Planı

Bu dosya, WebRTC tabanlı, uçtan uca şifreli sohbet uygulamasını geliştirirken izleyeceğimiz adımları içermektedir.

## Adım 1: Proje İskeletini Oluşturma ve Temizleme

- [x] `/chat/public/` altındaki mevcut `.gitkeep` dosyalarını ve `app-chat.js` dosyasını temizle.
- [x] `/chat/server/` altındaki `.gitkeep` dosyasını temizle.
- [x] Proje için temel klasör yapısının doğruluğunu kontrol et.

## Adım 2: Arayüz Dosyalarını Entegre Etme

- [x] Sohbet arayüzü için gerekli HTML dosyasını (`/Web/html/vertical-menu-template/app-chat.html`) `/chat/public/index.html` olarak kopyala.
- [x] Gerekli JavaScript dosyalarını (`/Web/assets/js/app-chat.js`) `/chat/public/js/` klasörüne kopyala.
- [x] Gerekli CSS ve diğer asset dosyalarını `/Web` dizininden `/chat/public/assets` altına taşı ve düzenle.
- [x] Kopyalanan `index.html` dosyasındaki asset (CSS, JS) yollarını yeni proje yapısına göre güncelle.
- [x] Sohbet arayüzünü basitleştirerek sadece yazışma ekranı kalacak şekilde düzenle.

## Adım 3: Signaling Sunucusunu Kurma (PHP & MySQL)

- [x] `/chat/server/` içine `signaling.php` adında bir dosya oluştur. (Temel iskelet)
- [x] `/chat/setup/` içine `database.sql` adında bir dosya oluştur.
- [x] `database.sql` dosyasına basit bir `users` tablo şeması ekle.
- [x] `/chat/setup/README.md` dosyasına sunucu kurulum talimatlarını ekle.
- [x] `signaling.php`: Gelen sinyal mesajlarını (offer, answer, candidate) JSON formatında işleyecek ve ilgili istemcilere yönlendirecek mantığı ekle.

## Adım 4: Frontend WebRTC Mantığını Geliştirme

- [x] Signaling sunucusuna WebSocket ile bağlanma kodunu ekle.
- [x] Kullanıcıdan kamera ve mikrofon izni istemek için `navigator.mediaDevices.getUserMedia` fonksiyonunu kullan.
- [x] `RTCPeerConnection` nesnesini oluşturma ve yönetme mantığını ekle.
- [x] Gelen sinyal mesajlarına (offer, answer, candidate) göre bağlantıyı kurma işlevlerini yaz.
- [x] Metin mesajlaşması için `createDataChannel` ile bir veri kanalı oluşturma ve yönetme kodunu ekle.

## Adım 5: Mesajlaşma Arayüzü Entegrasyonu
- [x] Veri kanalı üzerinden mesaj gönderme ve alma işlevlerini kullanıcı arayüzüne bağla.

## Adım 6: Güvenlik ve Şifreleme

- [x] Metin mesajlarını göndermeden önce şifreleme, alınınca deşifreleme yapacak fonksiyonları `app-chat.js` içine ekle. (Not: Bu fonksiyonların `DataChannel` ile entegrasyonu Adım 5'te yapılacaktır.)
- [x] Sunucu yapılandırmasında WSS (WebSocket Secure) kullanılmasını sağla. (Not: Üretim ortamı için gereklidir, mevcut temel sunucu `ws` kullanmaktadır. Bu adım şimdilik atlanmıştır.)

## Adım 7: Test ve Tamamlama

- [x] Uygulamanın temel iskeleti tamamlandı.
- [x] **Kullanıcı Testi:** Uygulamayı farklı tarayıcılarda ve cihazlarda test edin. (Geliştirme tamamlandı, test aşamasına geçilebilir.)

## Adım 8: Kullanıcı Yönetimi

- [x] Kullanıcı girişi (`login.php`) ve kayıt (`register.php`) sayfaları oluşturuldu.
- [x] Veritabanı bağlantısı için `config.php` dosyası oluşturuldu.
- [x] Kullanıcı kimlik doğrulaması için `auth.php` dosyası oluşturuldu.
- [x] `index.php` sayfasına kullanıcı girişi kontrolü eklendi.
- [x] `signaling.php` sunucusu, kullanıcı ID'lerini alacak ve bağlantıları ilişkilendirecek şekilde güncellendi.
- [x] `app-chat.js` dosyası, kullanıcı ID'sini WebSocket bağlantısına dahil edecek şekilde güncellendi.
- [x] `index.php` sayfasına kullanıcı listesi eklendi.
- [x] `app-chat.js` dosyası, hedefli mesajlaşma için güncellendi.

## Adım 9: Mesajlaşma Geçmişi

- [x] `database.sql` dosyasına `messages` tablosu eklendi.
- [x] `signaling.php` sunucusu, gelen metin mesajlarını veritabanına kaydedecek şekilde güncellendi.
- [x] `get_messages.php` adında yeni bir dosya oluşturularak mesaj geçmişini veritabanından çeken bir servis yazıldı.
- [x] `app-chat.js` dosyası, kullanıcı seçildiğinde `get_messages.php` üzerinden mesaj geçmişini yükleyecek ve gösterecek şekilde güncellendi.
- [x] Metin mesajlaşması WebRTC DataChannel üzerinden WebSocket'e taşındı.

## Geliştirme Notları

Mevcut implementasyon temel bir başlangıç noktasıdır. Üretim ortamı için aşağıdaki geliştirmeler önerilir:
-   **Signaling Sunucusu:** `signaling.php` yerine Ratchet gibi bir kütüphane ile daha robust bir sunucu yazılmalıdır.
-   **Güvenlik:** WSS (WebSocket Secure) kullanılmalıdır. Veritabanı erişim bilgileri gibi hassas veriler daha güvenli bir şekilde saklanmalıdır.
-   **Oda Yönetimi:** Birden fazla sohbet odası desteği eklenmelidir.
-   **Hata Yönetimi:** Daha detaylı hata yönetimi ve kullanıcı geri bildirimleri eklenmelidir.
-   **Gerçek Zamanlı Bildirimler:** Kullanıcı çevrimiçi/çevrimdışı olduğunda veya yeni bir mesaj geldiğinde bildirim gösterilmesi.