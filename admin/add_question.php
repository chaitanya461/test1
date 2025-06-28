<?php
require_once '../includes/config.php';
require_once '../includes/auth_functions.php';

if (!isAdmin()) {
    header("Location: ../login.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quiz_id = $_POST['quiz_id'];
    $question_text = trim($_POST['question_text']);
    $option_a = trim($_POST['option_a']);
    $option_b = trim($_POST['option_b']);
    $option_c = trim($_POST['option_c']);
    $option_d = trim($_POST['option_d']);
    $correct_answer = $_POST['correct_answer'];
    $points = intval($_POST['points']);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_answer, points) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$quiz_id, $question_text, $option_a, $option_b, $option_c, $option_d, $correct_answer, $points]);
        $success = "Question added successfully!";
    } catch (PDOException $e) {
        $error = "Error adding question: " . $e->getMessage();
    }
}

// Fetch available quizzes
$quizzes = $pdo->query("SELECT quiz_id, title FROM quizzes WHERE is_active = TRUE")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Question</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <div class="container">
        <h1>Add New Question</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="post">
            <div class="form-group">
                <label for="quiz_id">Quiz:</label>
                <select id="quiz_id" name="quiz_id" class="form-control" required>
                    <option value="">Select a Quiz</option>
                    <?php foreach ($quizzes as $quiz): ?>
                        <option value="<?php echo $quiz['quiz_id']; ?>"><?php echo htmlspecialchars($quiz['title']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="question_text">Question Text:</label>
                <textarea id="question_text" name="question_text" class="form-control" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="option_a">Option A:</label>
                <input type="text" id="option_a" name="option_a" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="option_b">Option B:</label>
                <input type="text" id="option_b" name="option_b" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="option_c">Option C:</label>
                <input type="text" id="option_c" name="option_c" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="option_d">Option D:</label>
                <input type="text" id="option_d" name="option_d" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="correct_answer">Correct Answer:</label>
                <select id="correct_answer" name="correct_answer" class="form-control" required>
                    <option value="a">Option A</option>
                    <option value="b">Option B</option>
                    <option value="c">Option C</option>
                    <option value="d">Option D</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="points">Points:</label>
                <input type="number" id="points" name="points" class="form-control" value="1" min="1" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Add Question</button>
        </form>
        
        <p><a href="dashboard.php">Back to Dashboard</a></p>
    </div>
</body>
</html>
