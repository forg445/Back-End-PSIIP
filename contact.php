<?php
session_start();
require_once 'config.php';

// Создаем таблицу для обратной связи, если еще не создана
$conn->query("CREATE TABLE IF NOT EXISTS feedback (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

if (isset($_POST['send_feedback'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $subject = $conn->real_escape_string($_POST['subject']);
    $message = $conn->real_escape_string($_POST['message']);
    
    // Сохраняем в БД
    $sql = "INSERT INTO feedback (name, email, subject, message) 
            VALUES ('$name', '$email', '$subject', '$message')";
    
    if ($conn->query($sql)) {
        // Отправляем email администратору
        $to = "admin@example.com"; // Замените на реальный email
        $headers = "From: $email\r\n";
        $headers .= "Reply-To: $email\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        
        $email_message = "
            <h2>Новое сообщение с формы обратной связи</h2>
            <p><strong>Имя:</strong> $name</p>
            <p><strong>Email:</strong> $email</p>
            <p><strong>Тема:</strong> $subject</p>
            <p><strong>Сообщение:</strong><br>$message</p>
        ";
        
        mail($to, "Обратная связь: $subject", $email_message, $headers);
        
        $success = "Ваше сообщение успешно отправлено!";
    } else {
        $error = "Произошла ошибка при отправке сообщения.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Обратная связь</title>
    <meta charset="utf-8">
    <style>
        .contact-form {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 5px;
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
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background: #dff0d8;
            border: 1px solid #d6e9c6;
            color: #3c763d;
        }
        .alert-danger {
            background: #f2dede;
            border: 1px solid #ebccd1;
            color: #a94442;
        }
    </style>
</head>
<body>
    <div class="contact-form">
        <h1>Обратная связь</h1>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="post">
            <div class="form-group">
                <label>Имя:</label>
                <input type="text" name="name" required 
                       value="<?php echo isset($_SESSION['username']) ? $_SESSION['username'] : ''; ?>">
            </div>
            
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label>Тема:</label>
                <input type="text" name="subject" required>
            </div>
            
            <div class="form-group">
                <label>Сообщение:</label>
                <textarea name="message" required rows="5"></textarea>
            </div>
            
            <button type="submit" name="send_feedback">Отправить</button>
        </form>
    </div>
</body>
</html>