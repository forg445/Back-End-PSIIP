<?php
// Получение активного опроса
$poll = $conn->query("SELECT * FROM polls WHERE active = 1 ORDER BY created_at DESC LIMIT 1")->fetch_assoc();

if ($poll) {
    $poll_id = $poll['id'];
    
    // Проверка, голосовал ли пользователь
    $user_id = $_SESSION['user_id'];
    $has_voted = $conn->query("SELECT id FROM poll_votes 
                              WHERE poll_id = $poll_id AND user_id = $user_id")->num_rows > 0;
    
    // Получение вариантов ответа
    $options = $conn->query("SELECT * FROM poll_options WHERE poll_id = $poll_id");
    
    // Обработка голосования
    if (isset($_POST['vote']) && !$has_voted) {
        $option_id = (int)$_POST['option_id'];
        
        $conn->query("INSERT INTO poll_votes (poll_id, user_id, option_id) 
                     VALUES ($poll_id, $user_id, $option_id)");
        $conn->query("UPDATE poll_options SET votes = votes + 1 WHERE id = $option_id");
        
        $has_voted = true;
        header('Location: ?page=poll');
        exit;
    }
    
    // Получение общего количества голосов
    $total_votes = $conn->query("SELECT COUNT(*) as total FROM poll_votes 
                                WHERE poll_id = $poll_id")->fetch_assoc()['total'];
}
?>

<style>
    .poll-container {
        max-width: 600px;
        margin: 0 auto;
        background: white;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .poll-question {
        font-size: 1.2em;
        margin-bottom: 20px;
    }
    .poll-option {
        margin-bottom: 15px;
    }
    .poll-option label {
        display: block;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 4px;
        cursor: pointer;
    }
    .poll-option label:hover {
        background: #e9ecef;
    }
    .progress {
        height: 20px;
        background: #e9ecef;
        border-radius: 4px;
        margin-top: 5px;
        overflow: hidden;
    }
    .progress-bar {
        height: 100%;
        background: #007bff;
        transition: width 0.3s ease;
    }
    .poll-results {
        margin-top: 20px;
    }
</style>

<div class="poll-container">
    <?php if ($poll): ?>
        <div class="poll-question">
            <?php echo htmlspecialchars($poll['question']); ?>
        </div>

        <?php if (!$has_voted): ?>
            <form method="post">
                <?php while ($option = $options->fetch_assoc()): ?>
                    <div class="poll-option">
                        <label>
                            <input type="radio" name="option_id" 
                                   value="<?php echo $option['id']; ?>" required>
                            <?php echo htmlspecialchars($option['option_text']); ?>
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
                while ($option = $options->fetch_assoc()):
                    $percentage = $total_votes ? round(($option['votes'] / $total_votes) * 100) : 0;
                ?>
                    <div class="poll-option">
                        <div>
                            <?php echo htmlspecialchars($option['option_text']); ?> 
                            (<?php echo $percentage; ?>%)
                        </div>
                        <div class="progress">
                            <div class="progress-bar" style="width: <?php echo $percentage; ?>%"></div>
                        </div>
                    </div>
                <?php endwhile; ?>
                <p>Всего голосов: <?php echo $total_votes; ?></p>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <p>В данный момент нет активных опросов.</p>
    <?php endif; ?>
</div>