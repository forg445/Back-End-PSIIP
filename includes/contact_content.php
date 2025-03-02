<?php
if (isset($_POST['send_feedback'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $subject = $conn->real_escape_string($_POST['subject']);
    $message = $conn->real_escape_string($_POST['message']);
    
    // Сохраняем в БД
    $sql = "INSERT INTO feedback (name, email, subject, message) 
            VALUES ('$name', '$email', '$subject', '$message')";
    
    if ($conn->query($sql)) {
        // Формируем сообщение для администратора
        $to = "admin@example.com"; // Замените на реальный email
        $email_subject = "Новое сообщение: " . $subject;
        $email_message = "Имя: $name\n";
        $email_message .= "Email: $email\n\n";
        $email_message .= "Сообщение:\n$message";
        
        $headers = "From: $email\r\n";
        $headers .= "Reply-To: $email\r\n";
        
        // Пытаемся отправить email, но не выводим ошибку если не получилось
        @mail($to, $email_subject, $email_message, $headers);
        
        $success = "Сообщение успешно отправлено!";
    } else {
        $error = "Ошибка при отправке сообщения: " . $conn->error;
    }
}
?>

<div class="contact-container">
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <div class="contact-form">
        <h2>Обратная связь</h2>
        <form method="post">
            <div class="form-group">
                <label>Имя:</label>
                <input type="text" name="name" required>
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
                <textarea name="message" required></textarea>
            </div>
            
            <button type="submit" name="send_feedback">Отправить</button>
        </form>
    </div>
</div>

<style>
.contact-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

.contact-form {
    background: white;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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

.form-group textarea {
    height: 150px;
}

button {
    background: #007bff;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 4px;
    cursor: pointer;
}

button:hover {
    background: #0056b3;
}

.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 4px;
}

.alert-success {
    background: #d4edda;
    color: #155724;
}

.alert-danger {
    background: #f8d7da;
    color: #721c24;
}
</style>