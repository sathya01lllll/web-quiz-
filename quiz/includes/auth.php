<?php
require_once 'config.php';
require_once 'functions.php';

// Fungsi untuk memeriksa apakah admin sudah login
function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// Fungsi untuk login admin
function adminLogin($username, $password) {
    if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        return true;
    }
    return false;
}

// Fungsi untuk logout admin
function adminLogout() {
    unset($_SESSION['admin_logged_in']);
    unset($_SESSION['admin_username']);
    session_destroy();
}

// Fungsi untuk memeriksa apakah pengguna sudah mengikuti quiz
function hasUserTakenQuiz() {
    return isset($_SESSION['participant_index']);
}

// Fungsi untuk memeriksa apakah quiz sedang berjalan
function isQuizInProgress() {
    return isset($_SESSION['current_question']);
}

// Fungsi untuk memeriksa dan menangani user yang diblokir
function checkBlockedUser() {
    require_once 'functions.php';
    
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