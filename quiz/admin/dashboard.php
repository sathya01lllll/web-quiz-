<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Redirect jika belum login
if (!isAdminLoggedIn()) {
    header('Location: login.php');
    exit;
}

$quizData = getQuizData();
$blockedUsers = getBlockedUsers();

// Tampilkan peringatan jika tidak ada soal
$hasQuestions = !empty($quizData['questions']);

// Proses update link Dana
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['dana_link'])) {
        $quizData['dana_link'] = trim($_POST['dana_link']);
        saveQuizData($quizData);
        header('Location: dashboard.php?success=link_updated');
        exit;
    }
    
    // Proses tambah pertanyaan
    if (isset($_POST['add_question'])) {
        $newQuestion = [
            'question' => trim($_POST['question']),
            'options' => [
                trim($_POST['option1']),
                trim($_POST['option2']),
                trim($_POST['option3']),
                trim($_POST['option4'])
            ],
            'correct_answer' => (int)$_POST['correct_answer']
        ];
        
        $quizData['questions'][] = $newQuestion;
        saveQuizData($quizData);
        header('Location: dashboard.php?success=question_added');
        exit;
    }
    
    // Proses edit pertanyaan
    if (isset($_POST['edit_question'])) {
        $questionIndex = (int)$_POST['question_index'];
        
        if (isset($quizData['questions'][$questionIndex])) {
            $quizData['questions'][$questionIndex] = [
                'question' => trim($_POST['question']),
                'options' => [
                    trim($_POST['option1']),
                    trim($_POST['option2']),
                    trim($_POST['option3']),
                    trim($_POST['option4'])
                ],
                'correct_answer' => (int)$_POST['correct_answer']
            ];
            
            saveQuizData($quizData);
            header('Location: dashboard.php?success=question_updated');
            exit;
        }
    }
    
    // Proses update judul quiz
    if (isset($_POST['quiz_title'])) {
        $quizData['title'] = trim($_POST['quiz_title']);
        saveQuizData($quizData);
        header('Location: dashboard.php?success=title_updated');
        exit;
    }
}

// Proses hapus pertanyaan
if (isset($_GET['delete_question'])) {
    $questionIndex = (int)$_GET['delete_question'];
    
    if (isset($quizData['questions'][$questionIndex])) {
        array_splice($quizData['questions'], $questionIndex, 1);
        saveQuizData($quizData);
        header('Location: dashboard.php?success=question_deleted');
        exit;
    }
}

// Proses reset quiz
if (isset($_GET['action']) && $_GET['action'] === 'reset_quiz') {
    $quizData['participants'] = [];
    saveQuizData($quizData);
    header('Location: dashboard.php?success=quiz_reset');
    exit;
}

// Proses hapus blokir
if (isset($_GET['unblock'])) {
    $unblockIndex = (int)$_GET['unblock'];
    
    if (isset($blockedUsers[$unblockIndex])) {
        array_splice($blockedUsers, $unblockIndex, 1);
        saveBlockedUsers($blockedUsers);
        header('Location: dashboard.php?success=user_unblocked');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Sathya Store</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="admin-dashboard">
            <div class="admin-header">
                <h1><i class="fas fa-crown"></i> Admin Dashboard - Sathya Store</h1>
                <div class="admin-welcome">
                    Selamat datang, <strong><?php echo $_SESSION['admin_username']; ?></strong>!
                </div>
            </div>
            
            <?php if (!$hasQuestions): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> <strong>Peringatan!</strong> Tidak ada soal quiz. Pengguna tidak dapat mengikuti quiz hingga Anda menambahkan soal.
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php
                    switch ($_GET['success']) {
                        case 'link_updated':
                            echo 'Link Dana berhasil diperbarui!';
                            break;
                        case 'quiz_reset':
                            echo 'Quiz berhasil direset!';
                            break;
                        case 'user_blocked':
                            echo 'User berhasil diblokir!';
                            break;
                        case 'user_unblocked':
                            echo 'User berhasil dibuka blokirnya!';
                            break;
                        case 'question_added':
                            echo 'Pertanyaan berhasil ditambahkan!';
                            break;
                        case 'question_updated':
                            echo 'Pertanyaan berhasil diperbarui!';
                            break;
                        case 'question_deleted':
                            echo 'Pertanyaan berhasil dihapus!';
                            break;
                        case 'title_updated':
                            echo 'Judul quiz berhasil diperbarui!';
                            break;
                    }
                    ?>
                </div>
            <?php endif; ?>
            
            <div class="admin-stats">
                <div class="stat-card">
                    <h3><i class="fas fa-users"></i> Jumlah Peserta</h3>
                    <p><?php echo count($quizData['participants']); ?></p>
                </div>
                
                <div class="stat-card">
                    <h3><i class="fas fa-trophy"></i> Peserta Berhasil</h3>
                    <p><?php
                        $successCount = 0;
                        foreach ($quizData['participants'] as $participant) {
                            if ($participant['success']) $successCount++;
                        }
                        echo $successCount;
                    ?></p>
                </div>
                
                <div class="stat-card">
                    <h3><i class="fas fa-question-circle"></i> Jumlah Pertanyaan</h3>
                    <p><?php echo count($quizData['questions']); ?></p>
                </div>
                
                <div class="stat-card">
                    <h3><i class="fas fa-ban"></i> User Terblokir</h3>
                    <p><?php echo count($blockedUsers); ?></p>
                </div>
            </div>
            
            <div class="admin-section">
                <h2><i class="fas fa-edit"></i> Update Judul Quiz</h2>
                <form method="POST" class="form">
                    <div class="form-group">
                        <label for="quiz_title">Judul Quiz:</label>
                        <input type="text" id="quiz_title" name="quiz_title" value="<?php echo htmlspecialchars($quizData['title']); ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Judul</button>
                </form>
            </div>
            
            <div class="admin-section">
                <h2><i class="fas fa-link"></i> Update Link Dana</h2>
                <form method="POST" class="form">
                    <div class="form-group">
                        <label for="dana_link">Link Dana:</label>
                        <input type="url" id="dana_link" name="dana_link" value="<?php echo htmlspecialchars($quizData['dana_link']); ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Link</button>
                </form>
            </div>
            
            <div class="admin-section">
                <h2><i class="fas fa-cog"></i> Kelola Quiz</h2>
                <a href="dashboard.php?action=reset_quiz" class="btn btn-warning" onclick="return confirm('Yakin ingin mereset quiz? Semua data peserta akan dihapus.')">
                    <i class="fas fa-trash"></i> Reset Quiz
                </a>
            </div>
            
            <div class="admin-section">
                <h2><i class="fas fa-plus-circle"></i> Tambah Pertanyaan Baru</h2>
                <form method="POST" class="form">
                    <div class="form-group">
                        <label for="question">Pertanyaan:</label>
                        <textarea id="question" name="question" rows="3" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="option1">Opsi 1 (Jawaban Benar):</label>
                        <input type="text" id="option1" name="option1" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="option2">Opsi 2:</label>
                        <input type="text" id="option2" name="option2" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="option3">Opsi 3:</label>
                        <input type="text" id="option3" name="option3" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="option4">Opsi 4:</label>
                        <input type="text" id="option4" name="option4" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="correct_answer">Jawaban Benar:</label>
                        <select id="correct_answer" name="correct_answer" required>
                            <option value="0">Opsi 1</option>
                            <option value="1">Opsi 2</option>
                            <option value="2">Opsi 3</option>
                            <option value="3">Opsi 4</option>
                        </select>
                    </div>
                    
                    <button type="submit" name="add_question" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Pertanyaan
                    </button>
                </form>
            </div>
            
            <div class="admin-section">
                <h2><i class="fas fa-list"></i> Daftar Pertanyaan</h2>
                <?php if (empty($quizData['questions'])): ?>
                    <p>Belum ada pertanyaan.</p>
                <?php else: ?>
                    <?php foreach ($quizData['questions'] as $index => $question): ?>
                        <div class="question-item">
                            <h3>Pertanyaan #<?php echo $index + 1; ?></h3>
                            <p><strong><?php echo htmlspecialchars($question['question']); ?></strong></p>
                            <ol type="A">
                                <?php foreach ($question['options'] as $optIndex => $option): ?>
                                    <li class="<?php echo $optIndex === $question['correct_answer'] ? 'correct-answer' : ''; ?>">
                                        <?php echo htmlspecialchars($option); ?>
                                        <?php if ($optIndex === $question['correct_answer']): ?>
                                            <span class="correct-badge">✓ Benar</span>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ol>
                            
                            <div class="question-actions">
                                <button type="button" class="btn btn-secondary" onclick="editQuestion(<?php echo $index; ?>)">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <a href="dashboard.php?delete_question=<?php echo $index; ?>" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus pertanyaan ini?')">
                                    <i class="fas fa-trash"></i> Hapus
                                </a>
                            </div>
                            
                            <!-- Form Edit (Awalnya Disembunyikan) -->
                            <div id="edit-form-<?php echo $index; ?>" class="edit-form" style="display: none;">
                                <h4>Edit Pertanyaan</h4>
                                <form method="POST" class="form">
                                    <input type="hidden" name="question_index" value="<?php echo $index; ?>">
                                    
                                    <div class="form-group">
                                        <label for="edit_question_<?php echo $index; ?>">Pertanyaan:</label>
                                        <textarea id="edit_question_<?php echo $index; ?>" name="question" rows="3" required><?php echo htmlspecialchars($question['question']); ?></textarea>
                                    </div>
                                    
                                    <?php foreach ($question['options'] as $optIndex => $option): ?>
                                        <div class="form-group">
                                            <label for="edit_option<?php echo $optIndex + 1; ?>_<?php echo $index; ?>">Opsi <?php echo $optIndex + 1; ?>:</label>
                                            <input type="text" id="edit_option<?php echo $optIndex + 1; ?>_<?php echo $index; ?>" name="option<?php echo $optIndex + 1; ?>" value="<?php echo htmlspecialchars($option); ?>" required>
                                        </div>
                                    <?php endforeach; ?>
                                    
                                    <div class="form-group">
                                        <label for="edit_correct_answer_<?php echo $index; ?>">Jawaban Benar:</label>
                                        <select id="edit_correct_answer_<?php echo $index; ?>" name="correct_answer" required>
                                            <option value="0" <?php echo $question['correct_answer'] === 0 ? 'selected' : ''; ?>>Opsi 1</option>
                                            <option value="1" <?php echo $question['correct_answer'] === 1 ? 'selected' : ''; ?>>Opsi 2</option>
                                            <option value="2" <?php echo $question['correct_answer'] === 2 ? 'selected' : ''; ?>>Opsi 3</option>
                                            <option value="3" <?php echo $question['correct_answer'] === 3 ? 'selected' : ''; ?>>Opsi 4</option>
                                        </select>
                                    </div>
                                    
                                    <button type="submit" name="edit_question" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Simpan Perubahan
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="cancelEdit(<?php echo $index; ?>)">
                                        <i class="fas fa-times"></i> Batal
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="admin-section">
                <h2><i class="fas fa-users"></i> Daftar Peserta</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>ID Telegram</th>
                                <th>IP Address</th>
                                <th>Waktu</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($quizData['participants'] as $index => $participant): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($participant['username']); ?></td>
                                    <td><?php echo htmlspecialchars($participant['telegram_id']); ?></td>
                                    <td><?php echo htmlspecialchars($participant['ip']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', $participant['timestamp']); ?></td>
                                    <td>
                                        <span class="status <?php echo $participant['success'] ? 'success' : 'failed'; ?>">
                                            <?php echo $participant['success'] ? 'Berhasil' : 'Gagal'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="block_user.php?username=<?php echo urlencode($participant['username']); ?>&telegram_id=<?php echo urlencode($participant['telegram_id']); ?>&ip=<?php echo urlencode($participant['ip']); ?>" class="btn btn-danger" onclick="return confirm('Yakin ingin memblokir user ini?')">
                                            <i class="fas fa-ban"></i> Blokir
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="admin-section">
                <h2><i class="fas fa-user-slash"></i> User Terblokir</h2>
                <?php if (empty($blockedUsers)): ?>
                    <p>Tidak ada user yang diblokir.</p>
                <?php else: ?>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>ID Telegram</th>
                                    <th>IP Address</th>
                                    <th>Waktu Diblokir</th>
                                    <th>Diblokir Oleh</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($blockedUsers as $index => $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['telegram_id']); ?></td>
                                        <td><?php echo htmlspecialchars($user['ip']); ?></td>
                                        <td><?php echo date('d/m/Y H:i', $user['timestamp']); ?></td>
                                        <td><?php echo htmlspecialchars($user['blocked_by']); ?></td>
                                        <td>
                                            <a href="dashboard.php?unblock=<?php echo $index; ?>" class="btn btn-success" onclick="return confirm('Yakin ingin membuka blokir user ini?')">
                                                <i class="fas fa-check"></i> Buka Blokir
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="admin-actions">
                <a href="logout.php" class="btn btn-secondary">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>

    <script>
    function editQuestion(index) {
        // Sembunyikan semua form edit lainnya
        document.querySelectorAll('.edit-form').forEach(form => {
            form.style.display = 'none';
        });
        
        // Tampilkan form edit untuk pertanyaan ini
        document.getElementById('edit-form-' + index).style.display = 'block';
    }
    
    function cancelEdit(index) {
        // Sembunyikan form edit
        document.getElementById('edit-form-' + index).style.display = 'none';
    }
    </script>
</body>
</html>