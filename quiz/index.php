<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Periksa apakah user diblokir
checkBlockedUser();

// Redirect ke halaman quiz jika sudah mengisi data
if (isset($_SESSION['participant_index'])) {
    header('Location: quiz.php');
    exit;
}

// Ambil data quiz
$quizData = getQuizData();

// Cek apakah quiz tersedia (ada soal dan kuota belum penuh)
$participantCount = getParticipantCount();
$hasQuestions = !empty($quizData['questions']);
$quizAvailable = $hasQuestions && ($participantCount < MAX_PARTICIPANTS);

// Proses form pendaftaran
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $quizAvailable) {
    $username = trim($_POST['username']);
    $telegramId = trim($_POST['telegram_id']);
    
    // Validasi input
    if (empty($username) || empty($telegramId)) {
        $error = 'Username dan ID Telegram harus diisi!';
    } elseif (isUserBlocked($username, $telegramId, getUserIP())) {
        $error = 'Anda tidak dapat mengikuti quiz!';
    } else {
        // Tambahkan peserta
        $participantIndex = addParticipant($username, $telegramId);
        $_SESSION['participant_index'] = $participantIndex;
        $_SESSION['username'] = $username;
        $_SESSION['telegram_id'] = $telegramId;
        
        // Redirect ke halaman quiz
        header('Location: quiz.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Berhadiah Dana - Sathya Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="quiz-card">
            <div class="store-header">
                <h1 class="store-title">Sathya Store</h1>
                <p class="store-tagline">Quiz Berhadiah Dana</p>
            </div>
            
            <?php if (!$hasQuestions): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> Maaf, quiz sedang tidak tersedia. Silakan coba lagi nanti.
                </div>
            <?php elseif (!$quizAvailable): ?>
                <div class="alert alert-error">
                    <i class="fas fa-users-slash"></i> Maaf, kuota peserta quiz sudah penuh.
                </div>
            <?php else: ?>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <div class="welcome-message">
                    <h2>Selamat Datang di Quiz Sathya Store!</h2>
                    <p>Isi data diri Anda untuk mengikuti quiz dan berkesempatan mendapatkan hadiah dari Dana!</p>
                </div>
                
                <form method="POST" class="form">
                    <div class="form-group">
                        <label for="username"><i class="fas fa-user"></i> Username Telegram:</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="telegram_id"><i class="fab fa-telegram"></i> ID Telegram:</label>
                        <input type="text" id="telegram_id" name="telegram_id" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-play"></i> Mulai Quiz
                    </button>
                </form>
            <?php endif; ?>
            
            <?php if ($hasQuestions): ?>
                <div class="participant-count">
                    <i class="fas fa-users"></i> Peserta: <?php echo $participantCount; ?> / <?php echo MAX_PARTICIPANTS; ?>
                </div>
            <?php endif; ?>
            
            <div class="footer">
                <p>&copy; 2023 Sathya Store. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>