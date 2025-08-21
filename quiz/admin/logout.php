<?php
require_once '../includes/functions.php';

// Hapus session admin
unset($_SESSION['admin_logged_in']);

// Redirect ke halaman login
redirect('login.php');
?>