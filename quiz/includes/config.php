<?php
// Konfigurasi database (jika menggunakan database)
define('DB_HOST', 'localhost');
define('DB_NAME', 'sathyas1_quiz');
define('DB_USER', 'sathyas1_quiz');
define('DB_PASS', 'qF64VTJk4RqQAPt8DrVh');

// Konfigurasi lainnya
define('MAX_PARTICIPANTS', 25); // Jumlah maksimal peserta quiz
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'admin123'); // Ganti dengan password yang lebih kuat

// Path file data
define('QUIZ_DATA_FILE', __DIR__ . '/../data/quiz_data.json');
define('BLOCKED_USERS_FILE', __DIR__ . '/../data/blocked_users.json');

// Konfigurasi Telegram (jika menggunakan notifikasi)
define('TELEGRAM_BOT_TOKEN', '8238568333:AAFwsKGHh9aIgpJNux3jpVMPkMuWxZZwnFg');
define('TELEGRAM_CHAT_ID', '-1003049225874');

// Mulai session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set timezone
date_default_timezone_set('Asia/Makassar');
?>
