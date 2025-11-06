<?php
// Bu sunucu, WebSocket protokolünün basit bir çoklu istemci implementasyonudur.
// Üretim ortamları için Ratchet (http://socketo.me/) gibi kütüphaneler önerilir.

set_time_limit(0);
ob_implicit_flush();

$address = '0.0.0.0';
$port = 8080;

if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
    die("socket_create() failed: " . socket_strerror(socket_last_error()));
}

socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1);

if (socket_bind($sock, $address, $port) === false) {
    die("socket_bind() failed: " . socket_strerror(socket_last_error($sock)));
}

if (socket_listen($sock, 5) === false) {
    die("socket_listen() failed: " . socket_strerror(socket_last_error($sock)));
}

$clients = array($sock);
$write = NULL;
$except = NULL;

echo "WebSocket Sunucusu Başlatıldı: ws://{$address}:{$port}\n";

while (true) {
    $read = $clients;
    if (socket_select($read, $write, $except, 0, 500000) < 1) {
        continue;
    }

    // Yeni bir bağlantı var mı kontrol et
    if (in_array($sock, $read)) {
        $newsock = socket_accept($sock);
        $clients[] = $newsock;
        $header = socket_read($newsock, 1024);
        perform_handshaking($header, $newsock, $address, $port);
        
        socket_getpeername($newsock, $ip);
        echo "Yeni istemci bağlandı: {$ip}\n";

        // Yeni bağlantıyı diğer okuma soketlerinden çıkar
        $key = array_search($sock, $read);
        unset($read[$key]);
    }

    // Gelen verileri işle
    foreach ($read as $read_sock) {
        $data = @socket_read($read_sock, 1024, PHP_NORMAL_READ);

        // Bağlantı kapandı mı kontrol et
        if ($data === false) {
            $key = array_search($read_sock, $clients);
            unset($clients[$key]);
            socket_getpeername($read_sock, $ip);
            echo "İstemci bağlantısı kesildi: {$ip}\n";
            continue;
        }

        $data = unmask($data);
        if($data) {
            // Gelen JSON verisini çöz
            $message = json_decode($data, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                echo "Gelen Mesaj: " . $data . "\n";

                // Diğer istemcilere broadcast yap
                foreach ($clients as $send_sock) {
                    // Ana sokete ve gönderen istemciye tekrar gönderme
                    if ($send_sock != $sock && $send_sock != $read_sock) {
                        $response = mask($data); // Gelen orijinal JSON verisini maskeleyip gönder
                        @socket_write($send_sock, $response, strlen($response));
                    }
                }
            } else {
                echo "Geçersiz JSON formatı: {$data}\n";
            }
        }
    }
}

socket_close($sock);

function perform_handshaking($receved_header, $client_conn, $host, $port)
{
    $headers = array();
    $lines = preg_split("/\r\n/", $receved_header);
    foreach ($lines as $line) {
        $line = chop($line);
        if (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) {
            $headers[$matches[1]] = $matches[2];
        }
    }

    $secKey = $headers['Sec-WebSocket-Key'];
    $secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
    $upgrade  = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
    "Upgrade: websocket\r\n" .
    "Connection: Upgrade\r\n" .
    "WebSocket-Origin: $host\r\n" .
    "WebSocket-Location: ws://$host:$port/deamon.php\r\n" .
    "Sec-WebSocket-Accept:$secAccept\r\n\r\n";
    socket_write($client_conn, $upgrade, strlen($upgrade));
}

function unmask($text)
{
    $length = ord($text[1]) & 127;
    if ($length == 126) {
        $masks = substr($text, 4, 4);
        $data = substr($text, 8);
    } elseif ($length == 127) {
        $masks = substr($text, 10, 4);
        $data = substr($text, 14);
    } else {
        $masks = substr($text, 2, 4);
        $data = substr($text, 6);
    }
    $text = "";
    for ($i = 0; $i < strlen($data); ++$i) {
        $text .= $data[$i] ^ $masks[$i % 4];
    }
    return $text;
}

function mask($text)
{
    $b1 = 0x81; // 0x80 | 0x1 (FIN | text frame)
    $length = strlen($text);

    if ($length <= 125) {
        $header = pack('CC', $b1, $length);
    } elseif ($length > 125 && $length < 65536) {
        $header = pack('CCn', $b1, 126, $length);
    } elseif ($length >= 65536) {
        $header = pack('CCNN', $b1, 127, $length);
    }
    return $header . $text;
}
?>