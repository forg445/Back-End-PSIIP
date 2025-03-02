<?php
// Параметры пагинации
$page = isset($_GET['guestbook_page']) ? (int)$_GET['guestbook_page'] : 1;
$per_page = 10;
$start = ($page - 1) * $per_page;

// Добавление сообщения
if (isset($_POST['add_message'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $message = $conn->real_escape_string($_POST['message']);
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NULL';
    
    $sql = "INSERT INTO guestbook (user_id, name, message) VALUES ($user_id, '$name', '$message')";
    if ($conn->query($sql)) {
        $success = "Сообщение успешно добавлено!";
    } else {
        $error = "Ошибка при добавлении сообщения.";
    }
}

// Получение сообщений
$total = $conn->query("SELECT COUNT(*) as count FROM guestbook")->fetch_assoc()['count'];
$total_pages = ceil($total / $per_page);

$messages = $conn->query("
    SELECT g.*, u.username 
    FROM guestbook g 
    LEFT JOIN users u ON g.user_id = u.id 
    ORDER BY g.created_at DESC 
    LIMIT $start, $per_page
");
?>

<style>
    .guestbook-container {
        max-width: 800px;
        margin: 0 auto;
    }
    .message-form {
        background: white;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 30px;
    }
    .message-item {
        background: white;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    .message-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        color: #666;
    }
    .message-content {
        line-height: 1.6;
    }
    .form-group {
        margin-bottom: 15px;
    }
    .form-group label {
        display: block;
        margin-bottom: 5px;
    }
    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
</style>

<div class="guestbook-container">
    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="message-form">
        <h3>Оставить сообщение</h3>
        <form method="post">
            <div class="form-group">
                <label>Имя:</label>
                <input type="text" name="name" required 
                       value="<?php echo isset($_SESSION['username']) ? $_SESSION['username'] : ''; ?>">
            </div>
            <div class="form-group">
                <label>Сообщение:</label>
                <textarea name="message" required rows="5"></textarea>
            </div>
            <button type="submit" name="add_message">Отправить</button>
        </form>
    </div>

    <?php while ($message = $messages->fetch_assoc()): ?>
        <div class="message-item">
            <div class="message-header">
                <span>
                    <?php echo $message['username'] ? $message['username'] : htmlspecialchars($message['name']); ?>
                </span>
                <span>
                    <?php echo date('d.m.Y H:i', strtotime($message['created_at'])); ?>
                </span>
            </div>
            <div class="message-content">
                <?php echo nl2br(htmlspecialchars($message['message'])); ?>
            </div>
        </div>
    <?php endwhile; ?>

    <!-- Пагинация -->
    <div class="pagination">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=guestbook&guestbook_page=<?php echo $i; ?>" 
               class="<?php echo $page == $i ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
    </div>
</div>