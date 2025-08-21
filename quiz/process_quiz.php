<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Periksa apakah user diblokir
checkBlockedUser();

// Redirect ke halaman utama jika belum mengisi data
if (!isset($_SESSION['participant_index']) || !isset($_SESSION['answers'])) {
    header('Location: index.php');
    exit;
}

// Ambil data quiz
$quizData = getQuizData();
$questions = $quizData['questions'];
$answers = $_SESSION['answers'];

// Redirect ke halaman utama jika tidak ada soal
if (count($questions) === 0) {
    unset($_SESSION['participant_index']);
    unset($_SESSION['answers']);
    header('Location: index.php');
    exit;
}

// Periksa jawaban
$correctAnswers = 0;
foreach ($questions as $index => $question) {
    if (isset($answers[$index]) && $answers[$index] === $question['correct_answer']) {
        $correctAnswers++;
    }
}

$allCorrect = ($correctAnswers === count($questions));

// Jika semua jawaban benar, tandai peserta sebagai sukses
if ($allCorrect) {
    markParticipantSuccess($_SESSION['participant_index']);
    
    // Kirim notifikasi ke Telegram dengan format yang lebih baik
    $message = "🎉 <b>PEMENANG QUIZ SATHYA STORE</b> 🎉\n\n";
    $message .= "📛 <b>Username:</b> " . $_SESSION['username'] . "\n";
    $message .= "🔗 <b>ID Telegram:</b> <code>" . $_SESSION['telegram_id'] . "</code>\n";
    $message .= "🌐 <b>IP Address:</b> <code>" . getUserIP() . "</code>\n";
    $message .= "📊 <b>Skor:</b> " . $correctAnswers . "/" . count($questions) . "\n";
    $message .= "⏰ <b>Waktu:</b> " . date('d/m/Y H:i:s') . "\n\n";
    $message .= "✅ <b>Status:</b> Berhak mendapatkan hadiah Dana!\n";
    $message .= "🎁 <b>Hadiah:</b> " . (empty($quizData['dana_link']) ? "Belum dikonfigurasi" : "Tersedia");
    
    // Gunakan fungsi notifikasi yang diperbaiki
    $notificationResult = sendTelegramNotification($message);
    
    // Catat hasil pengiriman notifikasi di log
    error_log("Telegram Notification " . ($notificationResult ? "BERHASIL" : "GAGAL") . 
              " untuk user: " . $_SESSION['username']);
}

// Hapus session quiz
unset($_SESSION['current_question']);
unset($_SESSION['answers']);
unset($_SESSION['start_time']);

// Simpan hasil di session untuk halaman result
$_SESSION['quiz_result'] = [
    'total_questions' => count($questions),
    'correct_answers' => $correctAnswers,
    'all_correct' => $allCorrect
];

// Redirect ke halaman hasil
header('Location: result.php');
exit;
?>