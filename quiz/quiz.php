<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Periksa apakah user diblokir
checkBlockedUser();

// Redirect ke halaman utama jika belum mengisi data
if (!isset($_SESSION['participant_index'])) {
    header('Location: index.php');
    exit;
}

// Ambil data quiz
$quizData = getQuizData();
$questions = $quizData['questions'];
$totalQuestions = count($questions);

// Redirect ke halaman utama jika tidak ada soal
if ($totalQuestions === 0) {
    unset($_SESSION['participant_index']);
    header('Location: index.php');
    exit;
}

// Cek apakah quiz sudah dimulai
if (!isset($_SESSION['current_question'])) {
    $_SESSION['current_question'] = 0;
    $_SESSION['answers'] = array_fill(0, $totalQuestions, null);
    $_SESSION['start_time'] = time();
}

$currentQuestionIndex = $_SESSION['current_question'];

// Proses jawaban
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['answer']) && $currentQuestionIndex < $totalQuestions) {
        // Simpan jawaban
        $_SESSION['answers'][$currentQuestionIndex] = (int)$_POST['answer'];
        
        // Lanjut ke pertanyaan berikutnya
        $_SESSION['current_question']++;
        
        // Jika sudah menjawab semua pertanyaan, redirect ke hasil
        if ($_SESSION['current_question'] >= $totalQuestions) {
            header('Location: process_quiz.php');
            exit;
        }
    }
    
    // Redirect untuk menghindari resubmit form
    header('Location: quiz.php');
    exit;
}

// Jika sudah selesai semua pertanyaan, redirect ke hasil
if ($currentQuestionIndex >= $totalQuestions) {
    header('Location: process_quiz.php');
    exit;
}

$currentQuestion = $questions[$currentQuestionIndex];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz - Pertanyaan <?php echo $currentQuestionIndex + 1; ?> - Sathya Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="quiz-card">
            <div class="quiz-header">
                <h1 class="store-title">Sathya Store Quiz</h1>
                <div class="question-progress">
                    Pertanyaan <?php echo $currentQuestionIndex + 1; ?> dari <?php echo $totalQuestions; ?>
                </div>
            </div>
            
            <div class="progress-bar">
                <div class="progress" style="width: <?php echo (($currentQuestionIndex + 1) / $totalQuestions) * 100; ?>%"></div>
            </div>
            
            <div class="question-container">
                <p class="question"><?php echo htmlspecialchars($currentQuestion['question']); ?></p>
                
                <form method="POST" class="options-form">
                    <?php foreach ($currentQuestion['options'] as $index => $option): ?>
                        <div class="option">
                            <input type="radio" id="option<?php echo $index; ?>" name="answer" value="<?php echo $index; ?>" required>
                            <label for="option<?php echo $index; ?>">
                                <span class="option-letter"><?php echo chr(65 + $index); ?></span>
                                <?php echo htmlspecialchars($option); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-arrow-right"></i> Lanjut
                    </button>
                </form>
            </div>
            
            <div class="footer">
                <p>&copy; 2025 Sathya Store. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>