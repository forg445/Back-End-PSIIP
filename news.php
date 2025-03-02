<?php
session_start();
require_once 'config.php';

// Параметры пагинации
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 5;
$start = ($page - 1) * $per_page;

// Получение общего количества новостей
$total = $conn->query("SELECT COUNT(*) as count FROM news")->fetch_assoc()['count'];
$total_pages = ceil($total / $per_page);

// Получение новостей
$news = $conn->query("SELECT * FROM news ORDER BY created_at DESC LIMIT $start, $per_page");

// Добавление новости (только для администраторов)
if (isset($_POST['add_news']) && isset($_SESSION['is_admin'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $content = $conn->real_escape_string($_POST['content']);
    
    // Обработка загрузки изображения
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_filename = uniqid() . '.' . $ext;
            $upload_path = 'uploads/news/' . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image = $upload_path;
            }
        }
    }
    
    $sql = "INSERT INTO news (title, content, image) VALUES ('$title', '$content', '$image')";
    if ($conn->query($sql)) {
        header('Location: news.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<link rel="alternate" type="application/rss+xml" title="RSS" href="news_rss.php">
    <title>Новости</title>
    <meta charset="utf-8">
    <style>
        .news-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .news-item {
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .news-image {
            max-width: 100%;
            height: auto;
            margin-bottom: 15px;
        }
        .news-date {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 10px;
        }
        .news-title {
            font-size: 1.5em;
            margin-bottom: 15px;
        }
        .news-content {
            line-height: 1.6;
        }
        .pagination {
            margin-top: 20px;
            text-align: center;
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
        .admin-form {
            margin-bottom: 30px;
            padding: 20px;
            background: #e9ecef;
            border-radius: 5px;
        }
        .admin-form input[type="text"],
        .admin-form textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .admin-form button {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="news-container">
        <h1>Новости</h1>
        
        <?php if (isset($_SESSION['is_admin'])): ?>
            <div class="admin-form">
                <h2>Добавить новость</h2>
                <form method="post" enctype="multipart/form-data">
                    <input type="text" name="title" placeholder="Заголовок" required>
                    <textarea name="content" rows="5" placeholder="Содержание новости" required></textarea>
                    <input type="file" name="image" accept="image/*">
                    <button type="submit" name="add_news">Опубликовать</button>
                </form>
            </div>
        <?php endif; ?>
        
        <?php while ($item = $news->fetch_assoc()): ?>
            <article class="news-item">
                <?php if ($item['image']): ?>
                    <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                         alt="" class="news-image">
                <?php endif; ?>
                
                <div class="news-date">
                    <?php echo date('d.m.Y H:i', strtotime($item['created_at'])); ?>
                </div>
                
                <h2 class="news-title">
                    <?php echo htmlspecialchars($item['title']); ?>
                </h2>
                
                <div class="news-content">
                    <?php echo nl2br(htmlspecialchars($item['content'])); ?>
                </div>
            </article>
        <?php endwhile; ?>
        
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