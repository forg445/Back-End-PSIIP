<?php
session_start();
require_once 'config.php';

// Если пользователь уже авторизован
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

// Обработка входа
if (isset($_POST['login'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM users WHERE username='$username'";
    $result = $conn->query($sql);
    
    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['is_admin'] = $row['is_admin'] == 1;
            
            header('Location: dashboard.php');
            exit;
        } else {
            $error = "Неверный пароль";
        }
    } else {
        $error = "Пользователь не найден";
    }
}

// Обработка регистрации
if (isset($_POST['register'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $admin_code = isset($_POST['admin_code']) ? trim($_POST['admin_code']) : '';
    
    // Проверка существования пользователя
    $check = $conn->query("SELECT * FROM users WHERE username='$username' OR email='$email'");
    if ($check->num_rows > 0) {
        $error = "Пользователь с таким именем или email уже существует";
    } else {
        // Определяем, будет ли пользователь администратором
        $is_admin = ($admin_code === ADMIN_CODE) ? 1 : 0;
        
        $sql = "INSERT INTO users (username, email, password, is_admin) 
                VALUES ('$username', '$email', '$password', $is_admin)";
        
        if ($conn->query($sql)) {
            $success = "Регистрация успешна! Теперь вы можете войти.";
            if ($is_admin) {
                $success .= " Вам предоставлены права администратора.";
            }
        } else {
            $error = "Ошибка при регистрации: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Вход и регистрация</title>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f8f9fa;
        }
        .container {
            max-width: 400px;
            margin: 0 auto;
        }
        .form-container {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        h2 {
            margin-top: 0;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }
        button:hover {
            background: #0056b3;
        }
        .error {
            color: #dc3545;
            margin-bottom: 10px;
            padding: 10px;
            background: #f8d7da;
            border-radius: 4px;
        }
        .success {
            color: #28a745;
            margin-bottom: 10px;
            padding: 10px;
            background: #d4edda;
            border-radius: 4px;
        }
        .form-text {
            font-size: 0.85em;
            color: #6c757d;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="form-container">
            <h2>Вход</h2>
            <form method="post">
                <div class="form-group">
                    <label>Имя пользователя:</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Пароль:</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" name="login">Войти</button>
            </form>
        </div>

        <div class="form-container">
            <h2>Регистрация</h2>
            <form method="post">
                <div class="form-group">
                    <label>Имя пользователя:</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Пароль:</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>Код администратора (необязательно):</label>
                    <input type="password" name="admin_code">
                    <small class="form-text">Оставьте пустым для регистрации обычного пользователя</small>
                </div>
                <button type="submit" name="register">Зарегистрироваться</button>
            </form>
        </div>
    </div>
</body>
</html>