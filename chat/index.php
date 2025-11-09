<?php
session_start();
require_once 'config.php'; // Veritabanı ve diğer ayarlar için

// API isteklerini işle
if (isset($_GET['action'])) {
    require_once 'pages/api_handler.php';
    exit;
}

// Oturum ve sayfa yönlendirme mantığı
$is_logged_in = isset($_SESSION['user_id']);
$page = $_GET['page'] ?? ($is_logged_in ? 'chat' : 'login');

// Giriş yapmamış kullanıcıları korumalı sayfalardan uzaklaştır
if (!$is_logged_in && !in_array($page, ['login', 'register'])) {
    header('Location: ?page=login');
    exit;
}

// Giriş yapmış kullanıcıları login/register sayfalarından uzaklaştır
if ($is_logged_in && in_array($page, ['login', 'register'])) {
    header('Location: ?page=chat');
    exit;
}

switch ($page) {
    case 'login':
        require_once 'pages/header.php';
        require_once 'pages/login.php';
        require_once 'pages/footer.php';
        break;
    case 'register':
        require_once 'pages/header.php';
        require_once 'pages/register.php';
        require_once 'pages/footer.php';
        break;
    case 'chat':
    default:
        require_once 'pages/header.php';
        require_once 'pages/sidebar.php';
        require_once 'pages/chat.php';
        require_once 'pages/footer.php';
        break;
}