<?php
require_once '../includes/config.php';
require_once '../includes/auth_functions.php';

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit;
}

$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;

// Fetch quiz result for this user
$result = $pdo->prepare("
    SELECT qr.*, q.title, q.points_per_question, q.passing_score
    FROM quiz_results qr
    JOIN quizzes q ON qr.quiz_id = q.quiz_id
    WHERE qr.user_id = ? AND qr.quiz_id = ?
    ORDER BY qr.completed_at DESC
    LIMIT 1
");
$result->execute([$_SESSION['user_id'], $quiz_id]);
$result = $result->fetch(PDO::FETCH_ASSOC);

if (!$result) {
    die("No results found for this quiz.");
}

// Calculate total possible points and user's earned points
$total_possible_points = $result['total_questions'] * $result['points_per_question'];
$earned_points = $result['correct_answers'] * $result['points_per_question'];
$percentage = round(($earned_points / $total_possible_points) * 100, 2);

// Fetch user responses with question details
$responses = $pdo->prepare("
    SELECT ur.*, q.question_text, q.option_a, q.option_b, q.option_c, q.option_d, q.correct_answer
    FROM user_responses ur
    JOIN questions q ON ur.question_id = q.question_id
    WHERE ur.user_id = ? AND q.quiz_id = ?
    ORDER BY ur.question_id
");
$responses->execute([$_SESSION['user_id'], $quiz_id]);
$responses = $responses->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quiz Results: <?php echo htmlspecialchars($result['title']); ?></title>
    <link rel="stylesheet" href="styles1.css">
    <style>
        .correct {
            background-color: #d4edda;
            border-left: 5px solid #28a745;
            padding: 10px;
            margin-bottom: 10px;
        }
        .incorrect {
            background-color: #f8d7da;
            border-left: 5px solid #dc3545;
            padding: 10px;
            margin-bottom: 10px;
        }
        .points-summary {
            background-color: #e7f1ff;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .pass {
            color: #28a745;
            font-weight: bold;
        }
        .fail {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Quiz Results: <?php echo htmlspecialchars($result['title']); ?></h1>
        
        <div class="result-summary">
            <h2>Your Score: <?php echo $percentage; ?>%</h2>
            <div class="points-summary">
                <p><strong>Points Earned:</strong> <?php echo $earned_points; ?> out of <?php echo $total_possible_points; ?> possible points</p>
                <p><strong>Passing Score:</strong> <?php echo $result['passing_score']; ?>%</p>
                <p><strong>Result:</strong> 
                    <span class="<?php echo ($percentage >= $result['passing_score']) ? 'pass' : 'fail'; ?>">
                        <?php echo ($percentage >= $result['passing_score']) ? 'PASSED' : 'FAILED'; ?>
                    </span>
                </p>
            </div>
            <p>You answered <?php echo $result['correct_answers']; ?> out of <?php echo $result['total_questions']; ?> questions correctly.</p>
            <p>Points per question: <?php echo $result['points_per_question']; ?></p>
            <p>Completed on: <?php echo date('F j, Y, g:i a', strtotime($result['completed_at'])); ?></p>
        </div>
        
        <h3>Question Review</h3>
        <?php foreach ($responses as $index => $response): ?>
            <div class="question-review <?php echo $response['is_correct'] ? 'correct' : 'incorrect'; ?>">
                <p><strong>Question #<?php echo $index + 1; ?>:</strong> <?php echo htmlspecialchars($response['question_text']); ?></p>
                <p><strong>Your Answer:</strong> 
                    <?php 
                    $selected_option = 'option_' . $response['selected_answer'];
                    echo htmlspecialchars($response[$selected_option]);
                    if (!$response['is_correct']) {
                        echo " <span class='incorrect'>(Incorrect - 0 points)</span>";
                    } else {
                        echo " <span class='correct'>(Correct - " . $result['points_per_question'] . " points)</span>";
                    }
                    ?>
                </p>
                <?php if (!$response['is_correct']): ?>
                    <p><strong>Correct Answer:</strong> 
                        <?php 
                        $correct_option = 'option_' . $response['correct_answer'];
                        echo htmlspecialchars($response[$correct_option]);
                        ?>
                    </p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        
        <div class="navigation">
            <a href="../index.php" class="btn">Back to Home</a>
            <?php if ($percentage < $result['passing_score']): ?>
                <a href="take_quiz.php?quiz_id=<?php echo $quiz_id; ?>" class="btn">Retake Quiz</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
