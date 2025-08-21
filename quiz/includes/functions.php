<?php
require_once 'config.php';

// Fungsi untuk membaca data quiz
function getQuizData() {
    if (file_exists(QUIZ_DATA_FILE)) {
        $json = file_get_contents(QUIZ_DATA_FILE);
        return json_decode($json, true);
    }
    return ['title' => 'Quiz Pengetahuan Umum', 'questions' => [], 'dana_link' => '', 'participants' => []];
}

// Fungsi untuk menyimpan data quiz
function saveQuizData($data) {
    file_put_contents(QUIZ_DATA_FILE, json_encode($data, JSON_PRETTY_PRINT));
}

// Fungsi untuk membaca data user yang diblokir
function getBlockedUsers() {
    if (file_exists(BLOCKED_USERS_FILE)) {
        $json = file_get_contents(BLOCKED_USERS_FILE);
        return json_decode($json, true);
    }
    return [];
}

// Fungsi untuk menyimpan data user yang diblokir
function saveBlockedUsers($data) {
    file_put_contents(BLOCKED_USERS_FILE, json_encode($data, JSON_PRETTY_PRINT));
}

// Fungsi untuk memeriksa apakah user diblokir
function isUserBlocked($username, $telegramId, $ip) {
    $blockedUsers = getBlockedUsers();
    
    foreach ($blockedUsers as $user) {
        if ($user['username'] === $username || 
            $user['telegram_id'] === $telegramId || 
            $user['ip'] === $ip) {
            return true;
        }
    }
    
    return false;
}

// Fungsi untuk memeriksa apakah user saat ini diblokir
function isCurrentUserBlocked() {
    if (!isset($_SESSION['username']) || !isset($_SESSION['telegram_id'])) {
        return false;
    }
    
    return isUserBlocked($_SESSION['username'], $_SESSION['telegram_id'], getUserIP());
}

// Fungsi untuk mendapatkan IP address pengguna
function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

// Fungsi untuk memeriksa jumlah peserta
function getParticipantCount() {
    $quizData = getQuizData();
    return isset($quizData['participants']) ? count($quizData['participants']) : 0;
}

// Fungsi untuk menambah peserta
function addParticipant($username, $telegramId) {
    $quizData = getQuizData();
    
    if (!isset($quizData['participants'])) {
        $quizData['participants'] = [];
    }
    
    $participant = [
        'username' => $username,
        'telegram_id' => $telegramId,
        'ip' => getUserIP(),
        'timestamp' => time(),
        'success' => false
    ];
    
    $quizData['participants'][] = $participant;
    saveQuizData($quizData);
    
    return count($quizData['participants']) - 1; // Return index peserta
}

// Fungsi untuk menandai peserta berhasil
function markParticipantSuccess($participantIndex) {
    $quizData = getQuizData();
    
    if (isset($quizData['participants'][$participantIndex])) {
        $quizData['participants'][$participantIndex]['success'] = true;
        saveQuizData($quizData);
        return true;
    }
    
    return false;
}

// Fungsi untuk mengirim notifikasi Telegram (VERSI DIPERBAIKI)
function sendTelegramNotification($message) {
    // Cek apakah token dan chat ID sudah dikonfigurasi dengan benar
    if (!defined('TELEGRAM_BOT_TOKEN') || !defined('TELEGRAM_CHAT_ID') || 
        empty(TELEGRAM_BOT_TOKEN) || empty(TELEGRAM_CHAT_ID) ||
        TELEGRAM_BOT_TOKEN === '8238568333:AAFwsKGHh9aIgpJNux3jpVMPkMuWxZZwnFg' || 
        TELEGRAM_BOT_TOKEN === '8238568333:AAFwsKGHh9aIgpJNux3jpVMPkMuWxZZwnFg' ||
        TELEGRAM_CHAT_ID === '1003049225874' ||
        TELEGRAM_CHAT_ID === '-1003049225874') {
        
        error_log("Telegram notification failed: Bot token or chat ID not configured properly");
        return false;
    }
    
    $url = "https://api.telegram.org/bot" . TELEGRAM_BOT_TOKEN . "/sendMessage";
    
    $data = [
        'chat_id' => TELEGRAM_CHAT_ID,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];
    
    // Debug: Tampilkan informasi konfigurasi
    error_log("Telegram Config - Token: " . TELEGRAM_BOT_TOKEN);
    error_log("Telegram Config - Chat ID: " . TELEGRAM_CHAT_ID);
    error_log("Telegram Message: " . $message);
    
    // Gunakan cURL jika tersedia (lebih reliable)
    if (function_exists('curl_version')) {
        return sendTelegramNotificationCurl($message);
    }
    
    // Fallback ke file_get_contents
    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data),
            'timeout' => 10,
            'ignore_errors' => true
        ]
    ];
    
    try {
        $context = stream_context_create($options);
        $result = @file_get_contents($url, false, $context);
        
        if ($result === false) {
            $error = error_get_last();
            error_log("Telegram API Error: " . ($error['message'] ?? 'Unknown error'));
            return false;
        }
        
        $response = json_decode($result, true);
        
        if (!$response || !$response['ok']) {
            $errorMsg = $response['description'] ?? 'Unknown error';
            error_log("Telegram API Error: " . $errorMsg);
            
            // Simpan error ke file log untuk debugging
            file_put_contents(__DIR__ . '/../telegram_debug.log', 
                "[" . date('Y-m-d H:i:s') . "] ERROR: " . $errorMsg . "\n" .
                "URL: " . $url . "\n" .
                "Data: " . print_r($data, true) . "\n" .
                "Response: " . $result . "\n\n",
                FILE_APPEND
            );
            
            return false;
        }
        
        // Log sukses
        file_put_contents(__DIR__ . '/../telegram_debug.log', 
            "[" . date('Y-m-d H:i:s') . "] SUCCESS: Message sent\n" .
            "URL: " . $url . "\n" .
            "Data: " . print_r($data, true) . "\n" .
            "Response: " . $result . "\n\n",
            FILE_APPEND
        );
        
        return true;
    } catch (Exception $e) {
        error_log("Telegram Notification Exception: " . $e->getMessage());
        return false;
    }
}

// Fungsi menggunakan cURL (lebih reliable)
function sendTelegramNotificationCurl($message) {
    if (!defined('TELEGRAM_BOT_TOKEN') || !defined('TELEGRAM_CHAT_ID') || 
        empty(TELEGRAM_BOT_TOKEN) || empty(TELEGRAM_CHAT_ID)) {
        return false;
    }
    
    $url = "https://api.telegram.org/bot" . TELEGRAM_BOT_TOKEN . "/sendMessage";
    
    $data = [
        'chat_id' => TELEGRAM_CHAT_ID,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        error_log("cURL Error: " . $error);
        
        // Simpan error ke file log
        file_put_contents(__DIR__ . '/../telegram_debug.log', 
            "[" . date('Y-m-d H:i:s') . "] CURL ERROR: " . $error . "\n" .
            "URL: " . $url . "\n" .
            "Data: " . print_r($data, true) . "\n" .
            "HTTP Code: " . $httpCode . "\n\n",
            FILE_APPEND
        );
        
        return false;
    }
    
    if ($httpCode !== 200) {
        error_log("HTTP Error: " . $httpCode);
        
        file_put_contents(__DIR__ . '/../telegram_debug.log', 
            "[" . date('Y-m-d H:i:s') . "] HTTP ERROR: " . $httpCode . "\n" .
            "URL: " . $url . "\n" .
            "Data: " . print_r($data, true) . "\n" .
            "Response: " . $result . "\n\n",
            FILE_APPEND
        );
        
        return false;
    }
    
    $response = json_decode($result, true);
    if (!$response || !$response['ok']) {
        $errorMsg = $response['description'] ?? 'Unknown error';
        error_log("Telegram API Error: " . $errorMsg);
        
        file_put_contents(__DIR__ . '/../telegram_debug.log', 
            "[" . date('Y-m-d H:i:s') . "] API ERROR: " . $errorMsg . "\n" .
            "URL: " . $url . "\n" .
            "Data: " . print_r($data, true) . "\n" .
            "Response: " . $result . "\n\n",
            FILE_APPEND
        );
        
        return false;
    }
    
    // Log sukses
    file_put_contents(__DIR__ . '/../telegram_debug.log', 
        "[" . date('Y-m-d H:i:s') . "] SUCCESS: Message sent via cURL\n" .
        "URL: " . $url . "\n" .
        "Data: " . print_r($data, true) . "\n" .
        "Response: " . $result . "\n\n",
        FILE_APPEND
    );
    
    return true;
}

// Fungsi untuk menguji koneksi Telegram Bot
function testTelegramConnection() {
    if (!defined('TELEGRAM_BOT_TOKEN') || empty(TELEGRAM_BOT_TOKEN) ||
        TELEGRAM_BOT_TOKEN === 'MASUKKAN_TOKEN_ANDA_DISINI' || 
        TELEGRAM_BOT_TOKEN === '7123456789:AAH6Q2yzAbCdEfGhIjKlMnOpQrStUvWxYzZ') {
        return ['success' => false, 'message' => 'Token bot tidak dikonfigurasi'];
    }
    
    $url = "https://api.telegram.org/bot" . TELEGRAM_BOT_TOKEN . "/getMe";
    
    // Gunakan cURL jika tersedia
    if (function_exists('curl_version')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return ['success' => false, 'message' => 'cURL Error: ' . $error];
        }
        
        if ($httpCode !== 200) {
            return ['success' => false, 'message' => 'HTTP Error: ' . $httpCode];
        }
    } else {
        // Fallback ke file_get_contents
        $options = [
            'http' => [
                'timeout' => 10,
                'ignore_errors' => true
            ]
        ];
        
        try {
            $context = stream_context_create($options);
            $result = @file_get_contents($url, false, $context);
            
            if ($result === false) {
                return ['success' => false, 'message' => 'Tidak dapat terhubung ke API Telegram'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    $response = json_decode($result, true);
    
    if ($response && $response['ok']) {
        return [
            'success' => true, 
            'message' => 'Bot terhubung: ' . $response['result']['first_name'],
            'bot_name' => $response['result']['first_name'],
            'username' => $response['result']['username']
        ];
    } else {
        return ['success' => false, 'message' => 'Token bot tidak valid: ' . ($response['description'] ?? 'Unknown error')];
    }
}

// Fungsi untuk memeriksa dan logout user yang diblokir
function checkAndLogoutBlockedUser() {
    if (isCurrentUserBlocked()) {
        // Hapus semua session
        session_unset();
        session_destroy();
        
        // Redirect ke halaman blokir
        header('Location: blocked.php');
        exit;
    }
}
?>