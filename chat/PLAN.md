# WebRTC P2P Sohbet Uygulaması Geliştirme Planı

Bu dosya, WebRTC tabanlı, uçtan uca şifreli sohbet uygulamasını geliştirirken izleyeceğimiz adımları içermektedir.

## Adım 1: Proje İskeletini Oluşturma ve Temizleme

- [ ] `/chat/public/` altındaki mevcut `.gitkeep` dosyalarını ve `app-chat.js` dosyasını temizle.
- [ ] `/chat/server/` altındaki `.gitkeep` dosyasını temizle.
- [ ] Proje için temel klasör yapısının doğruluğunu kontrol et.

## Adım 2: Arayüz Dosyalarını Entegre Etme

- [ ] Sohbet arayüzü için gerekli HTML dosyasını (`/Web/html/vertical-menu-template/app-chat.html`) `/chat/public/index.html` olarak kopyala.
- [ ] Gerekli JavaScript dosyalarını (`/Web/assets/js/app-chat.js`) `/chat/public/js/` klasörüne kopyala.
- [ ] Gerekli CSS ve diğer asset dosyalarını `/Web` dizininden `/chat/public/assets` altına taşı ve düzenle.
- [ ] Kopyalanan `index.html` dosyasındaki asset (CSS, JS) yollarını yeni proje yapısına göre güncelle.

## Adım 3: Signaling Sunucusunu Kurma (PHP & MySQL)

- [ ] `/chat/server/` içine `signaling.php` adında bir dosya oluştur.
- [ ] Bu dosya içinde WebSocket bağlantılarını kabul edecek ve sinyalleşme mantığını (offer, answer, candidate mesajlarını iletme) yönetecek PHP kodunu yaz.
- [ ] `/chat/setup/` içine `database.sql` adında bir dosya oluştur.
- [ ] Bu dosyaya kullanıcıları veya odaları tutmak için basit bir tablo şeması ekle.
- [ ] `/chat/setup/README.md` dosyasına sunucu kurulumu ve veritabanı hazırlığı ile ilgili talimatları ekle.

## Adım 4: Frontend WebRTC Mantığını Geliştirme

- [ ] `/chat/public/js/app-chat.js` dosyasını aç.
- [ ] Signaling sunucusuna WebSocket ile bağlanma kodunu ekle.
- [ ] Kullanıcıdan kamera ve mikrofon izni istemek için `navigator.mediaDevices.getUserMedia` fonksiyonunu kullan.
- [ ] `RTCPeerConnection` nesnesini oluşturma ve yönetme mantığını ekle.
- [ ] Gelen sinyal mesajlarına (offer, answer, candidate) göre bağlantıyı kurma işlevlerini yaz.
- [ ] Metin mesajlaşması için `createDataChannel` ile bir veri kanalı oluşturma ve yönetme kodunu ekle.

## Adım 5: Güvenlik ve Şifreleme

- [ ] Metin mesajlarını göndermeden önce şifreleme, alınınca deşifreleme yapacak fonksiyonları `app-chat.js` içine ekle.
- [ ] Sunucu yapılandırmasında WSS (WebSocket Secure) kullanılmasını sağla.

## Adım 6: Test ve Tamamlama

- [ ] Uygulamayı farklı tarayıcılarda ve cihazlarda test et.
- [ ] Kodları temizle ve son halini ver.
