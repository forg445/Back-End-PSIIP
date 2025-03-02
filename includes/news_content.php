<?php
// Параметры пагинации
$page = isset($_GET['news_page']) ? (int)$_GET['news_page'] : 1;
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
        header('Location: dashboard.php?page=news');
        exit;
    }
}
?>

<style>
    .news-container {
        max-width: 800px;
        margin: 0 auto;
    }
    .news-item {
        margin-bottom: 30px;
        padding: 20px;
        background: white;
        border-radius: 5px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .news-image {
        max-width: 100%;
        height: auto;
        margin-bottom: 15px;
        border-radius: 5px;
    }
    .news-date {
        color: #666;
        font-size: 0.9em;
        margin-bottom: 10px;
    }
    .news-title {
        font-size: 1.5em;
        margin-bottom: 15px;
        color: #333;
    }
    .news-content {
        line-height: 1.6;
        color: #444;
    }
    .admin-form {
        margin-bottom: 30px;
        padding: 20px;
        background: white;
        border-radius: 5px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
</style>

<div class="news-container">
    <?php if (isset($_SESSION['is_admin'])): ?>
        <div class="admin-form">
            <h2>Добавить новость</h2>
            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <input type="text" name="title" placeholder="Заголовок" required>
                </div>
                <div class="form-group">
                    <textarea name="content" rows="5" placeholder="Содержание новости" required></textarea>
                </div>
                <div class="form-group">
                    <input type="file" name="image" accept="image/*">
                </div>
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
    
    <!-- Пагинация -->
    <div class="pagination">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=news&news_page=<?php echo $i; ?>" 
               class="<?php echo $page == $i ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
    </div>
</div>