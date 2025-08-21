<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Hapus session jika ada
session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akun Diblokir - Sathya Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .blocked-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            padding: 20px;
        }
        
        .blocked-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            max-width: 500px;
            width: 100%;
            animation: fadeInUp 0.8s ease-out;
        }
        
        .blocked-icon {
            font-size: 80px;
            color: #ff6b6b;
            margin-bottom: 20px;
            animation: bounce 1s infinite alternate;
        }
        
        .blocked-title {
            color: #e74c3c;
            font-size: 28px;
            margin-bottom: 15px;
            font-weight: 700;
        }
        
        .blocked-message {
            color: #7f8c8d;
            font-size: 18px;
            margin-bottom: 30px;
            line-height: 1.5;
        }
        
        .store-name {
            color: #e74c3c;
            font-weight: bold;
            font-size: 22px;
            margin-top: 20px;
        }
        
        .contact-info {
            margin-top: 20px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 10px;
            border-left: 4px solid #e74c3c;
        }
        
        @keyframes bounce {
            0% { transform: translateY(0); }
            100% { transform: translateY(-15px); }
        }
        
        @keyframes fadeInUp {
            0% { 
                opacity: 0;
                transform: translateY(50px);
            }
            100% { 
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="blocked-container">
        <div class="blocked-card">
            <div class="blocked-icon">
                <i class="fas fa-ban"></i>
            </div>
            
            <h1 class="blocked-title">AKUN ANDA TELAH DIBLOKIR</h1>
            
            <p class="blocked-message">
                Maaf, akun Anda telah diblokir oleh administrator Sathya Store. 
                Anda tidak dapat mengikuti quiz atau mengakses halaman ini.
            </p>
            
            <div class="contact-info">
                <p>Jika Anda merasa ini adalah kesalahan, silakan hubungi admin:</p>
                <p><i class="fas fa-envelope"></i> tele : @sathyastore</p>
            </div>
            
            <div class="store-name">
                Sathya Store
            </div>
        </div>
    </div>
</body>
</html>