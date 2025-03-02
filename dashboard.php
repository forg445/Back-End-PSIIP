<?php
session_start();
require_once 'config.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Получение активной страницы
$page = isset($_GET['page']) ? $_GET['page'] : 'news';

// Проверка доступа к админ-панели
if ($page === 'admin' && !isAdmin()) {
    header('Location: ?page=news');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Личный кабинет</title>
    <meta charset="utf-8">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }
        .container {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 250px;
            background: #343a40;
            color: white;
            padding: 20px;
        }
        .content {
            flex: 1;
            padding: 20px;
            background: #f8f9fa;
            overflow-y: auto;
        }
        .nav-link {
            display: block;
            padding: 10px;
            color: #fff;
            text-decoration: none;
            margin: 5px 0;
            border-radius: 5px;
        }
        .nav-link:hover,
        .nav-link.active {
            background: #007bff;
        }
        .user-info {
            padding: 20px 0;
            border-bottom: 1px solid #495057;
            margin-bottom: 20px;
        }
        .cart-count {
            background: #dc3545;
            color: white;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 0.8em;
            margin-left: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div class="user-info">
                <div>Привет, <?php echo htmlspecialchars($_SESSION['username']); ?>!</div>
                <?php if (isAdmin()): ?>
                    <small>(Администратор)</small>
                <?php endif; ?>
            </div>
            
            <nav>
                <!-- Общие ссылки для всех пользователей -->
                <a href="?page=news" class="nav-link <?php echo $page == 'news' ? 'active' : ''; ?>">
                    Новости
                </a>
                
                <a href="?page=catalog" class="nav-link <?php echo $page == 'catalog' ? 'active' : ''; ?>">
                    Каталог
                </a>
                
                <a href="?page=cart" class="nav-link <?php echo $page == 'cart' ? 'active' : ''; ?>">
                    Корзина
                    <?php if (!empty($_SESSION['cart'])): ?>
                        <span class="cart-count"><?php echo count($_SESSION['cart']); ?></span>
                    <?php endif; ?>
                </a>
                
                <a href="?page=guestbook" class="nav-link <?php echo $page == 'guestbook' ? 'active' : ''; ?>">
                    Гостевая книга
                </a>
                
                <a href="?page=contact" class="nav-link <?php echo $page == 'contact' ? 'active' : ''; ?>">
                    Обратная связь
                </a>
                
                <a href="?page=poll" class="nav-link <?php echo $page == 'poll' ? 'active' : ''; ?>">
                    Опросы
                </a>
                
                <!-- Админ-панель только для администраторов -->
                <?php if (isAdmin()): ?>
                    <a href="?page=admin" class="nav-link <?php echo $page == 'admin' ? 'active' : ''; ?>">
                        Админ-панель
                    </a>
                <?php endif; ?>
                
                <a href="logout.php" class="nav-link">Выход</a>
            </nav>
        </div>
        
        <div class="content">
            <div class="header">
                <h1 class="page-title">
                    <?php
                    switch ($page) {
                        case 'news': echo 'Новости'; break;
                        case 'catalog': echo 'Каталог товаров'; break;
                        case 'cart': echo 'Корзина'; break;
                        case 'guestbook': echo 'Гостевая книга'; break;
                        case 'contact': echo 'Обратная связь'; break;
                        case 'poll': echo 'Опросы'; break;
                        case 'admin': echo 'Админ-панель'; break;
                        default: echo 'Новости';
                    }
                    ?>
                </h1>
            </div>

            <?php
            // Подключение соответствующего контента
            $include_path = 'includes/' . $page . '_content.php';
            if (file_exists($include_path)) {
                include $include_path;
            } else {
                echo '<p>Страница не найдена</p>';
            }
            ?>
        </div>
    </div>
</body>
</html>