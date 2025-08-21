<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Periksa apakah user diblokir
checkBlockedUser();

// Redirect ke halaman utama jika belum menyelesaikan quiz
if (!isset($_SESSION['quiz_result'])) {
    header('Location: index.php');
    exit;
}

$result = $_SESSION['quiz_result'];
$quizData = getQuizData();

// Hapus session result setelah digunakan
unset($_SESSION['quiz_result']);

// Jika semua jawaban benar DAN ada link Dana, redirect ke Dana setelah 5 detik
$redirectToDana = $result['all_correct'] && !empty($quizData['dana_link']);
if ($redirectToDana) {
    header("refresh:5;url=" . $quizData['dana_link']);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Quiz - Sathya Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="quiz-card">
            <div class="store-header">
                <h1 class="store-title">Sathya Store</h1>
                <p class="store-tagline">Hasil Quiz</p>
            </div>
            
            <?php if ($result['all_correct']): ?>
                <div class="success-animation">
                    <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                        <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"/>
                        <path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                    </svg>
                    <h2>Selamat! Anda Berhasil</h2>
                </div>
                <p>Anda menjawab semua pertanyaan dengan benar!</p>
                
                <?php if (!empty($quizData['dana_link'])): ?>
                    <div class="countdown-container">
                        <p>Anda akan diarahkan ke aplikasi Dana dalam <span id="countdown">5</span> detik untuk mengambil hadiah.</p>
                        <div class="countdown-bar">
                            <div class="countdown-progress"></div>
                        </div>
                    </div>
                    <p>Jika tidak redirect otomatis, <a href="<?php echo $quizData['dana_link']; ?>" class="btn btn-success"><i class="fas fa-gift"></i> Klaim Hadiah Sekarang</a></p>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> Hadiah sedang tidak tersedia. Silakan hubungi administrator.
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="failure-animation">
                    <svg class="crossmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                        <circle class="crossmark__circle" cx="26" cy="26" r="25" fill="none"/>
                        <path class="crossmark__cross" fill="none" d="M16 16 36 36 M36 16 16 36"/>
                    </svg>
                    <h2>Maaf, Anda Gagal</h2>
                </div>
                <p>Anda menjawab <?php echo $result['correct_answers']; ?> dari <?php echo $result['total_questions']; ?> pertanyaan dengan benar.</p>
                <p>Anda perlu menjawab semua pertanyaan dengan benar untuk mendapatkan hadiah saldp dana.</p>
                <a href="index.php" class="btn btn-primary"><i class="fas fa-redo"></i> Coba Lagi</a>
            <?php endif; ?>
            
            <div class="footer">
                <p>&copy; 2025 Sathya Store. All rights reserved.</p>
            </div>
        </div>
    </div>

    <?php if ($redirectToDana): ?>
    <script>
        // Countdown timer
        let seconds = 5;
        const countdownElement = document.getElementById('countdown');
        const countdownProgress = document.querySelector('.countdown-progress');
        
        const countdownInterval = setInterval(function() {
            seconds--;
            countdownElement.textContent = seconds;
            countdownProgress.style.width = (seconds / 5 * 100) + '%';
            
            if (seconds <= 0) {
                clearInterval(countdownInterval);
            }
        }, 1000);
    </script>
    <style>
        .countdown-container {
            text-align: center;
            margin: 20px 0;
        }
        
        .countdown-bar {
            height: 10px;
            background: #e2e8f0;
            border-radius: 5px;
            margin: 10px 0;
            overflow: hidden;
        }
        
        .countdown-progress {
            height: 100%;
            background: linear-gradient(90deg, #48bb78, #38a169);
            border-radius: 5px;
            width: 100%;
            transition: width 1s linear;
        }
        
        #countdown {
            font-weight: bold;
            color: #38a169;
            font-size: 1.2em;
        }
    </style>
    <?php endif; ?>
</body>
</html>