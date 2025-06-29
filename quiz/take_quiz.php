<?php
require_once '../includes/config.php';
require_once '../includes/auth_functions.php';

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit;
}

$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;

// Fetch quiz details
$quiz = $pdo->prepare("SELECT * FROM quizzes WHERE quiz_id = ? AND is_active = TRUE");
$quiz->execute([$quiz_id]);
$quiz = $quiz->fetch(PDO::FETCH_ASSOC);

if (!$quiz) {
    die("Invalid quiz or quiz not found.");
}

// Fetch questions for this quiz
$questions = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ? ORDER BY question_id");
$questions->execute([$quiz_id]);
$questions = $questions->fetchAll(PDO::FETCH_ASSOC);

if (empty($questions)) {
    die("No questions available for this quiz.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $total_questions = count($questions);
    $correct_answers = 0;
    
    foreach ($questions as $question) {
        $question_id = $question['question_id'];
        $selected_answer = $_POST['question_' . $question_id] ?? null;
        
        if ($selected_answer === $question['correct_answer']) {
            $correct_answers++;
        }
        
        // Record user response
        $stmt = $pdo->prepare("INSERT INTO user_responses (user_id, question_id, selected_answer, is_correct) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION['user_id'],
            $question_id,
            $selected_answer,
            $selected_answer === $question['correct_answer']
        ]);
    }
    
    // Calculate score
    $score = ($correct_answers / $total_questions) * 100;
    
    // Save quiz result
    $stmt = $pdo->prepare("INSERT INTO quiz_results (user_id, quiz_id, total_questions, correct_answers, score) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $_SESSION['user_id'],
        $quiz_id,
        $total_questions,
        $correct_answers,
        $score
    ]);
    
    // Redirect to results page
    header("Location: result.php?quiz_id=$quiz_id");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($quiz['title']); ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1><?php echo htmlspecialchars($quiz['title']); ?></h1>
        <p><?php echo htmlspecialchars($quiz['description']); ?></p>
        
        <form method="post">
            <?php foreach ($questions as $index => $question): ?>
                <div class="question-card">
                    <h3>Question <?php echo $index + 1; ?></h3>
                    <p><?php echo htmlspecialchars($question['question_text']); ?></p>
                    
                    <div class="options">
                        <label>
                            <input type="radio" name="question_<?php echo $question['question_id']; ?>" value="a" required>
                            <?php echo htmlspecialchars($question['option_a']); ?>
                        </label><br>
                        
                        <label>
                            <input type="radio" name="question_<?php echo $question['question_id']; ?>" value="b">
                            <?php echo htmlspecialchars($question['option_b']); ?>
                        </label><br>
                        
                        <label>
                            <input type="radio" name="question_<?php echo $question['question_id']; ?>" value="c">
                            <?php echo htmlspecialchars($question['option_c']); ?>
                        </label><br>
                        
                        <label>
                            <input type="radio" name="question_<?php echo $question['question_id']; ?>" value="d">
                            <?php echo htmlspecialchars($question['option_d']); ?>
                        </label>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <button type="submit" class="btn btn-primary">Submit Quiz</button>
        </form>
    </div>
</body>
</html>
