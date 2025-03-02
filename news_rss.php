<?php
require_once 'config.php';

// Получение последних новостей
$news = $conn->query("SELECT * FROM news ORDER BY created_at DESC LIMIT 20");

// Формирование RSS
header('Content-Type: application/rss+xml; charset=utf-8');

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<rss version="2.0">
    <channel>
        <title>Наши новости</title>
        <link><?php echo 'http://' . $_SERVER['HTTP_HOST']; ?></link>
        <description>Последние новости нашего сайта</description>
        <language>ru</language>
        
        <?php while ($item = $news->fetch_assoc()): ?>
            <item>
                <title><?php echo htmlspecialchars($item['title']); ?></title>
                <link><?php echo 'http://' . $_SERVER['HTTP_HOST'] . '/news.php?id=' . $item['id']; ?></link>
                <description><?php echo htmlspecialchars($item['content']); ?></description>
                <pubDate><?php echo date('r', strtotime($item['created_at'])); ?></pubDate>
                <guid><?php echo 'http://' . $_SERVER['HTTP_HOST'] . '/news.php?id=' . $item['id']; ?></guid>
            </item>
        <?php endwhile; ?>
    </channel>
</rss>