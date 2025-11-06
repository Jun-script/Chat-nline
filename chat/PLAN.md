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

## Adım 3: Signaling Sunucusunu Kurma (PHP & MySQL)

- [x] `/chat/server/` içine `signaling.php` adında bir dosya oluştur. (Temel iskelet)
- [x] `/chat/setup/` içine `database.sql` adında bir dosya oluştur.
- [x] `database.sql` dosyasına basit bir `users` tablo şeması ekle.
- [x] `/chat/setup/README.md` dosyasına sunucu kurulum talimatlarını ekle.
- [x] `signaling.php`: Gelen sinyal mesajlarını (offer, answer, candidate) JSON formatında işleyecek ve ilgili istemcilere yönlendirecek mantığı ekle.

## Adım 4: Frontend WebRTC Mantığını Geliştirme

- [ ] `/chat/public/js/app-chat.js` dosyasını aç.
- [x] Signaling sunucusuna WebSocket ile bağlanma kodunu ekle.
- [x] Kullanıcıdan kamera ve mikrofon izni istemek için `navigator.mediaDevices.getUserMedia` fonksiyonunu kullan.
- [x] `RTCPeerConnection` nesnesini oluşturma ve yönetme mantığını ekle.
- [x] Gelen sinyal mesajlarına (offer, answer, candidate) göre bağlantıyı kurma işlevlerini yaz.
- [x] Metin mesajlaşması için `createDataChannel` ile bir veri kanalı oluşturma ve yönetme kodunu ekle.

## Adım 5: Güvenlik ve Şifreleme

- [x] Metin mesajlarını göndermeden önce şifreleme, alınınca deşifreleme yapacak fonksiyonları `app-chat.js` içine ekle.
- [ ] Sunucu yapılandırmasında WSS (WebSocket Secure) kullanılmasını sağla. (Not: Üretim ortamı için gereklidir, mevcut temel sunucu `ws` kullanmaktadır.)

## Adım 6: Test ve Tamamlama

- [x] Uygulamanın temel iskeleti tamamlandı.
- [ ] **Kullanıcı Testi:** Uygulamayı farklı tarayıcılarda ve cihazlarda test edin.
- [ ] **Geliştirme Notları:** Mevcut implementasyon temel bir başlangıç noktasıdır. Üretim ortamı için aşağıdaki geliştirmeler önerilir:
    -   **Signaling Sunucusu:** `signaling.php` yerine Ratchet gibi bir kütüphane ile daha robust bir sunucu yazılmalıdır.
    -   **Güvenlik:** WSS (WebSocket Secure) kullanılmalıdır.
    -   **Kullanıcı Yönetimi:** Veritabanı ile entegrasyon ve kullanıcı girişi/kayıt işlemleri eklenmelidir.
    -   **Oda Yönetimi:** Birden fazla sohbet odası desteği eklenmelidir.
    -   **Hata Yönetimi:** Daha detaylı hata yönetimi ve kullanıcı geri bildirimleri eklenmelidir.