<?php
session_start();
require_once 'config.php';

// Создание нового опроса (для админа)
if (isset($_POST['create_poll']) && isset($_SESSION['user_id'])) {
    $question = $conn->real_escape_string($_POST['question']);
    $options = $_POST['options'];
    
    $conn->query("INSERT INTO polls (question) VALUES ('$question')");
    $poll_id = $conn->insert_id;
    
    foreach ($options as $option) {
        $option = $conn->real_escape_string($option);
        $conn->query("INSERT INTO poll_options (poll_id, option_text) 
                     VALUES ($poll_id, '$option')");
    }
}

// Голосование
if (isset($_POST['vote']) && isset($_SESSION['user_id'])) {
    $poll_id = (int)$_POST['poll_id'];
    $option_id = (int)$_POST['option_id'];
    $user_id = $_SESSION['user_id'];
    
    // Проверка, не голосовал ли уже
    $check = $conn->query("SELECT id FROM poll_votes 
                          WHERE poll_id=$poll_id AND user_id=$user_id");
    
    if ($check->num_rows == 0) {
        $conn->query("INSERT INTO poll_votes (poll_id, user_id, option_id) 
                     VALUES ($poll_id, $user_id, $option_id)");
        $conn->query("UPDATE poll_options SET votes = votes + 1 
                     WHERE id=$option_id");
    }
}

// Получение активного опроса
$poll = $conn->query("SELECT * FROM polls ORDER BY created_at DESC LIMIT 1")->fetch_assoc();
if ($poll) {
    $options = $conn->query("SELECT * FROM poll_options WHERE poll_id={$poll['id']}");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Опрос</title>
    <meta charset="utf-8">
    <style>
        .poll-container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .poll-option {
            margin: 10px 0;
        }
        .poll-results {
            margin-top: 20px;
        }
        .progress {
            height: 20px;
            background: #ddd;
            border-radius: 10px;
            margin: 5px 0;
        }
        .progress-bar {
            height: 100%;
            background: #007bff;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="poll-container">
        <?php if ($poll): ?>
            <h2><?php echo $poll['question']; ?></h2>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php
                $user_voted = $conn->query("SELECT id FROM poll_votes 
                    WHERE poll_id={$poll['id']} AND user_id={$_SESSION['user_id']}")->num_rows > 0;
                ?>
                
                <?php if (!$user_voted): ?>
                    <form method="post">
                        <input type="hidden" name="poll_id" value="<?php echo $poll['id']; ?>">
                        <?php while ($option = $options->fetch_assoc()): ?>
                            <div class="poll-option">
                                <label>
                                    <input type="radio" name="option_id" 
                                           value="<?php echo $option['id']; ?>" required>
                                    <?php echo $option['option_text']; ?>
                                </label>
                            </div>
                        <?php endwhile; ?>
                        <button type="submit" name="vote">Голосовать</button>
                    </form>
                <?php else: ?>
                    <div class="poll-results">
                        <h3>Результаты:</h3>
                        <?php
                        $options->data_seek(0);
                        $total_votes = $conn->query("SELECT COUNT(*) as total FROM poll_votes 
                            WHERE poll_id={$poll['id']}")->fetch_assoc()['total'];
                        
                        while ($option = $options->fetch_assoc()):
                            $percentage = $total_votes ? round(($option['votes'] / $total_votes) * 100) : 0;
                        ?>
                            <div class="poll-option">
                                <div><?php echo $option['option_text']; ?> (<?php echo $percentage; ?>%)</div>
                                <div class="progress">
                                    <div class="progress-bar" style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                        <p>Всего голосов: <?php echo $total_votes; ?></p>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <p>Пожалуйста, <a href="index.php">войдите</a>, чтобы проголосовать.</p>
            <?php endif; ?>
        <?php else: ?>
            <p>Нет активных опросов.</p>
        <?php endif; ?>
    </div>
</body>
</html>