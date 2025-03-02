<?php
session_start();
require_once 'config.php';

// Добавление сообщения
if (isset($_POST['add_message'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $message = $conn->real_escape_string($_POST['message']);
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NULL';
    
    $sql = "INSERT INTO guestbook (user_id, name, message) VALUES ($user_id, '$name', '$message')";
    $conn->query($sql);
}

// Получение сообщений с пагинацией
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$start = ($page - 1) * $per_page;

$total = $conn->query("SELECT COUNT(*) as count FROM guestbook")->fetch_assoc()['count'];
$total_pages = ceil($total / $per_page);

$messages = $conn->query("SELECT g.*, u.username 
                         FROM guestbook g 
                         LEFT JOIN users u ON g.user_id = u.id 
                         ORDER BY g.created_at DESC 
                         LIMIT $start, $per_page");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Гостевая книга</title>
    <meta charset="utf-8">
    <style>
        .message {
            border: 1px solid #ddd;
            margin: 10px 0;
            padding: 15px;
            border-radius: 5px;
        }
        .message-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            color: #666;
        }
        .pagination {
            margin: 20px 0;
        }
        .pagination a {
            padding: 5px 10px;
            margin: 0 5px;
            border: 1px solid #ddd;
            text-decoration: none;
        }
        .pagination a.active {
            background: #007bff;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Гостевая книга</h1>
        
        <!-- Форма добавления сообщения -->
        <form method="post">
            <div>
                <label>Имя:</label>
                <input type="text" name="name" required 
                       value="<?php echo isset($_SESSION['username']) ? $_SESSION['username'] : ''; ?>">
            </div>
            <div>
                <label>Сообщение:</label>
                <textarea name="message" required></textarea>
            </div>
            <button type="submit" name="add_message">Отправить</button>
        </form>

        <!-- Сообщения -->
        <?php while ($message = $messages->fetch_assoc()): ?>
            <div class="message">
                <div class="message-header">
                    <span>
                        <?php echo $message['username'] ? $message['username'] : $message['name']; ?>
                    </span>
                    <span><?php echo date('d.m.Y H:i', strtotime($message['created_at'])); ?></span>
                </div>
                <div class="message-content">
                    <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                </div>
            </div>
        <?php endwhile; ?>

        <!-- Пагинация -->
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" 
                   class="<?php echo $page == $i ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    </div>
</body>
</html>