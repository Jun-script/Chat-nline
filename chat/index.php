<?php
// For now, we will just include the main chat interface.
// Later, we can add logic to check for login status.

require_once 'pages/header.php';
// The main sidebar is not needed for the full-screen messenger view.
// require_once 'pages/sidebar.php';
require_once 'pages/chat.php';
require_once 'pages/footer.php';