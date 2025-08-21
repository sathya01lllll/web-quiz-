<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Redirect jika belum login
if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Periksa parameter
if (!isset($_GET['username']) || !isset($_GET['telegram_id']) || !isset($_GET['ip'])) {
    header('Location: dashboard.php');
    exit;
}

$username = $_GET['username'];
$telegramId = $_GET['telegram_id'];
$ip = $_GET['ip'];

// Blokir user
$blockedUsers = getBlockedUsers();

// Cek apakah user sudah diblokir
$alreadyBlocked = false;
foreach ($blockedUsers as $user) {
    if ($user['username'] === $username && 
        $user['telegram_id'] === $telegramId && 
        $user['ip'] === $ip) {
        $alreadyBlocked = true;
        break;
    }
}

if (!$alreadyBlocked) {
    $blockedUsers[] = [
        'username' => $username,
        'telegram_id' => $telegramId,
        'ip' => $ip,
        'timestamp' => time(),
        'blocked_by' => $_SESSION['admin_username']
    ];
    
    saveBlockedUsers($blockedUsers);
}

header('Location: dashboard.php?success=user_blocked');
exit;