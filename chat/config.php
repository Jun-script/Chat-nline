<?php

// **MySQL ayarları - Bu bilgileri kendi sunucunuzdan alın** //
/** MySQL veritabanı adı */
define('DB_NAME', 'chat_nline');

/** MySQL veritabanı kullanıcısı */
define('DB_USER', 'root');

/** MySQL veritabanı parolası */
define('DB_PASSWORD', '');

/** MySQL sunucusu */
define('DB_HOST', 'localhost');

/** Veritabanı Charset kullanımı. */
define('DB_CHARSET', 'utf8mb4');

/**
 * Veritabanı bağlantısı.
 */
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASSWORD
    );
    // Hata modunu ayarla
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Veritabanı bağlantısı başarısız: " . $e->getMessage());
}

/**
 * Uygulama temel URL'si.
 * Geliştirme ortamı için dinamik olarak ayarlanır.
 * Canlı sunucuda manuel olarak ayarlamak daha güvenli olabilir.
 */
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$script_name = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
define('BASE_URL', $protocol . $host . $script_name);

?>