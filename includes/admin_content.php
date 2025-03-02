<?php
// Включаем отображение ошибок
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Проверка прав администратора
if (!isAdmin()) {
    header('Location: ?page=news');
    exit;
}

// Автоматическое создание папок при необходимости
$upload_path = __DIR__ . '/../uploads/products';
if (!file_exists($upload_path)) {
    mkdir($upload_path, 0777, true);
}

// Обработка добавления товара
if (isset($_POST['add_product'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $category_id = (int)$_POST['category_id'];
    $description = $conn->real_escape_string($_POST['description']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    
    // Загрузка изображения
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_filename = uniqid() . '.' . $ext;
            $upload_path = 'uploads/products/' . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image = $upload_path;
            } else {
                $error = "Ошибка при загрузке файла: " . error_get_last()['message'];
            }
        } else {
            $error = "Недопустимый тип файла. Разрешены только: " . implode(', ', $allowed);
        }
    }
    
    if (!isset($error)) {
        $sql = "INSERT INTO products (name, category_id, description, price, stock, image) 
                VALUES ('$name', $category_id, '$description', $price, $stock, '$image')";
        
        if ($conn->query($sql)) {
            $success = "Товар успешно добавлен!";
        } else {
            $error = "Ошибка при добавлении товара: " . $conn->error;
        }
    }
}

// Обработка создания опроса
if (isset($_POST['create_poll'])) {
    $question = $conn->real_escape_string($_POST['question']);
    $options = isset($_POST['options']) ? $_POST['options'] : [];
    
    if (empty($options)) {
        $error = "Необходимо добавить хотя бы два варианта ответа";
    } else {
        // Деактивируем старые опросы
        $conn->query("UPDATE polls SET active = 0");
        
        // Добавляем новый опрос
        $sql = "INSERT INTO polls (question, active) VALUES ('$question', 1)";
        if ($conn->query($sql)) {
            $poll_id = $conn->insert_id;
            
            // Добавляем варианты ответов
            foreach ($options as $option) {
                $option = $conn->real_escape_string($option);
                $sql = "INSERT INTO poll_options (poll_id, option_text) VALUES ($poll_id, '$option')";
                if (!$conn->query($sql)) {
                    $error = "Ошибка при добавлении вариантов ответа: " . $conn->error;
                    break;
                }
            }
            
            if (!isset($error)) {
                $success = "Опрос успешно создан!";
            }
        } else {
            $error = "Ошибка при создании опроса: " . $conn->error;
        }
    }
}

// Статистика для дашборда
$stats = [
    'users' => $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'],
    'orders' => $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'],
    'products' => $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'],
    'feedback' => $conn->query("SELECT COUNT(*) as count FROM feedback")->fetch_assoc()['count']
];

// Получение секции админ-панели
$section = isset($_GET['section']) ? $_GET['section'] : 'dashboard';
?>

<div class="admin-container">
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <div class="admin-nav">
        <a href="?page=admin&section=dashboard" 
           class="<?php echo $section == 'dashboard' ? 'active' : ''; ?>">
            Дашборд
        </a>
        <a href="?page=admin&section=products" 
           class="<?php echo $section == 'products' ? 'active' : ''; ?>">
            Управление товарами
        </a>
        <a href="?page=admin&section=orders" 
           class="<?php echo $section == 'orders' ? 'active' : ''; ?>">
            Заказы
        </a>
        <a href="?page=admin&section=users" 
           class="<?php echo $section == 'users' ? 'active' : ''; ?>">
            Пользователи
        </a>
        <a href="?page=admin&section=polls" 
           class="<?php echo $section == 'polls' ? 'active' : ''; ?>">
            Управление опросами
        </a>
    </div>

    <?php if ($section == 'dashboard'): ?>
        <!-- Дашборд -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['users']; ?></div>
                <div>Пользователей</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['orders']; ?></div>
                <div>Заказов</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['products']; ?></div>
                <div>Товаров</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['feedback']; ?></div>
                <div>Сообщений</div>
            </div>
        </div>

        <div class="admin-section">
            <h3>Последние заказы</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Пользователь</th>
                        <th>Сумма</th>
                        <th>Статус</th>
                        <th>Дата</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $recent_orders = $conn->query("
                        SELECT o.*, u.username 
                        FROM orders o 
                        JOIN users u ON o.user_id = u.id 
                        ORDER BY o.created_at DESC 
                        LIMIT 5
                    ");
                    if ($recent_orders):
                        while ($order = $recent_orders->fetch_assoc()):
                    ?>
                        <tr>
                            <td><?php echo $order['id']; ?></td>
                            <td><?php echo htmlspecialchars($order['username']); ?></td>
                            <td><?php echo number_format($order['total_amount'], 2); ?> руб.</td>
                            <td><?php echo $order['status']; ?></td>
                            <td><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></td>
                        </tr>
                    <?php 
                        endwhile;
                    endif;
                    ?>
                </tbody>
            </table>
        </div>

    <?php elseif ($section == 'products'): ?>
        <!-- Управление товарами -->
        <div class="admin-section">
            <h3>Добавить товар</h3>
            <form method="post" enctype="multipart/form-data" action="?page=admin&section=products">
                <div class="form-group">
                    <label>Название:</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Категория:</label>
                    <select name="category_id" required>
                        <?php
                        $categories = $conn->query("SELECT * FROM categories ORDER BY name");
                        while ($category = $categories->fetch_assoc()):
                        ?>
                            <option value="<?php echo $category['id']; ?>">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Описание:</label>
                    <textarea name="description" required rows="5"></textarea>
                </div>
                <div class="form-group">
                    <label>Цена:</label>
                    <input type="number" name="price" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Количество на складе:</label>
                    <input type="number" name="stock" required>
                </div>
                <div class="form-group">
                    <label>Изображение:</label>
                    <input type="file" name="image" accept="image/*">
                </div>
                <button type="submit" name="add_product">Добавить товар</button>
            </form>
        </div>

    <?php elseif ($section == 'polls'): ?>
        <!-- Управление опросами -->
        <div class="admin-section">
            <h3>Создать опрос</h3>
            <form method="post" action="?page=admin&section=polls">
                <div class="form-group">
                    <label>Вопрос:</label>
                    <input type="text" name="question" required>
                </div>
                <div id="options-container">
                    <div class="form-group">
                        <label>Вариант ответа 1:</label>
                        <input type="text" name="options[]" required>
                    </div>
                    <div class="form-group">
                        <label>Вариант ответа 2:</label>
                        <input type="text" name="options[]" required>
                    </div>
                </div>
                <button type="button" onclick="addOption()">Добавить вариант</button>
                <button type="submit" name="create_poll">Создать опрос</button>
            </form>
        </div>
    <?php endif; ?>
</div>

<script>
function addOption() {
    const container = document.getElementById('options-container');
    const optionCount = container.children.length + 1;
    
    const div = document.createElement('div');
    div.className = 'form-group';
    div.innerHTML = `
        <label>Вариант ответа ${optionCount}:</label>
        <input type="text" name="options[]" required>
    `;
    
    container.appendChild(div);
}
</script>