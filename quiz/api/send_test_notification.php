<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Cek apakah request berasal dari admin (sederhana)
$isAdminRequest = isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'admin') !== false;

if (!$isAdminRequest) {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak']);
    exit;
}

// Kirim notifikasi test
$message = "🔔 <b>Test Notifikasi dari Sathya Store Quiz</b>\n";
$message .= "Waktu: " . date('d/m/Y H:i:s') . "\n";
$message .= "Status: <code>Bot Telegram berhasil terhubung</code>\n";
$message .= "IP Server: " . ($_SERVER['SERVER_ADDR'] ?? 'Unknown') . "\n\n";
$message .= "✅ Sistem notifikasi berfungsi dengan baik!";

// Coba gunakan cURL terlebih dahulu, jika tidak tersedia gunakan fallback
if (function_exists('curl_version')) {
    $result = sendTelegramNotificationCurl($message);
} else {
    $result = sendTelegramNotification($message);
}

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Notifikasi test dikirim']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal mengirim notifikasi']);
}
?>