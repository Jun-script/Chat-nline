# Kurulum Talimatları

Bu doküman, WebRTC P2P sohbet uygulamasının sunucu tarafını kurmak için gerekli adımları açıklamaktadır.

## Gereksinimler

- PHP 7.4 veya üstü (WebSocket sunucusu için `sockets` eklentisi aktif olmalı)
- MySQL veya MariaDB veritabanı
- Bir web sunucusu (Apache, Nginx vb.) - Arayüz dosyalarını sunmak için

## 1. Veritabanı Kurulumu

1.  Bir MySQL veritabanı oluşturun.
2.  Oluşturduğunuz veritabanına bu klasörde bulunan `database.sql` dosyasını içe aktarın. Bu işlem, gerekli `users` tablosunu oluşturacaktır.

    ```bash
    mysql -u [kullanici_adi] -p [veritabani_adi] < database.sql
    ```

## 2. Signaling Sunucusunu Çalıştırma

Signaling sunucusu, WebSocket bağlantılarını yönetir ve kullanıcıların birbirleriyle bağlantı kurmasını sağlar.

1.  Sunucuyu başlatmak için terminalde `/chat/server/` dizinine gidin ve aşağıdaki komutu çalıştırın:

    ```bash
    php signaling.php
    ```

2.  Sunucu varsayılan olarak `8080` portunda çalışmaya başlayacaktır. Tarayıcıdaki JavaScript kodunun bu adrese ve porta (`ws://<sunucu_ipsi>:8080`) bağlanacak şekilde ayarlandığından emin olun.

    **Not:** `signaling.php` içindeki sunucu kodu çok temel bir yapıdadır ve üretim ortamları için uygun değildir. Geliştirme ve test aşamaları için bir başlangıç noktası olarak tasarlanmıştır. Üretim için [Ratchet](http://socketo.me/) gibi daha olgun bir WebSocket kütüphanesi kullanılması şiddetle tavsiye edilir.

## 3. Arayüz Dosyalarını Sunma

`/chat/public/` klasöründeki dosyaların (index.html, JS, CSS vb.) bir web sunucusu tarafından sunulması gerekmektedir. Bu klasörü web sunucunuzun kök dizini olarak ayarlayabilirsiniz.
