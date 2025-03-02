<?php
session_start();
require_once 'config.php';

// Параметры пагинации
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;
$start = ($page - 1) * $per_page;

// Фильтры
$category = isset($_GET['category']) ? (int)$_GET['category'] : null;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc';
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Формирование SQL запроса
$where = [];
if ($category) $where[] = "p.category_id = $category";
if ($search) $where[] = "(p.name LIKE '%$search%' OR p.description LIKE '%$search%')";

$where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Сортировка
$sort_options = [
    'name_asc' => 'p.name ASC',
    'name_desc' => 'p.name DESC',
    'price_asc' => 'p.price ASC',
    'price_desc' => 'p.price DESC',
    'newest' => 'p.created_at DESC'
];
$order_by = $sort_options[$sort] ?? 'p.name ASC';

// Получение общего количества товаров
$total = $conn->query("SELECT COUNT(*) as count FROM products p $where_clause")->fetch_assoc()['count'];
$total_pages = ceil($total / $per_page);

// Получение товаров
$products = $conn->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    $where_clause 
    ORDER BY $order_by 
    LIMIT $start, $per_page
");

// Получение категорий для фильтра
$categories = $conn->query("SELECT * FROM categories ORDER BY name");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Каталог продукции</title>
    <meta charset="utf-8">
    <style>
        .catalog {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        .product-card {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
        }
        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            margin-bottom: 10px;
        }
        .filters {
            padding: 20px;
            background: #f8f9fa;
            margin-bottom: 20px;
        }
        .pagination {
            margin: 20px;
            text-align: center;
        }
        .pagination a {
            padding: 5px 10px;
            margin: 0 5px;
            border: 1px solid #ddd;
            text-decoration: none;
        }
        .add-to-cart {
            background: #007bff;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="filters">
        <form method="get">
            <input type="text" name="search" placeholder="Поиск..." 
                   value="<?php echo htmlspecialchars($search); ?>">
            
            <select name="category">
                <option value="">Все категории</option>
                <?php while ($cat = $categories->fetch_assoc()): ?>
                    <option value="<?php echo $cat['id']; ?>" 
                            <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                        <?php echo $cat['name']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
            
            <select name="sort">
                <option value="name_asc" <?php echo $sort == 'name_asc' ? 'selected' : ''; ?>>
                    По названию (А-Я)
                </option>
                <option value="name_desc" <?php echo $sort == 'name_desc' ? 'selected' : ''; ?>>
                    По названию (Я-А)
                </option>
                <option value="price_asc" <?php echo $sort == 'price_asc' ? 'selected' : ''; ?>>
                    По цене (возрастание)
                </option>
                <option value="price_desc" <?php echo $sort == 'price_desc' ? 'selected' : ''; ?>>
                    По цене (убывание)
                </option>
                <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>
                    Сначала новые
                </option>
            </select>
            
            <button type="submit">Применить</button>
        </form>
    </div>

    <div class="catalog">
        <?php while ($product = $products->fetch_assoc()): ?>
            <div class="product-card">
                <?php if ($product['image']): ?>
                    <img src="<?php echo $product['image']; ?>" class="product-image">
                <?php endif; ?>
                
                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                <p>Категория: <?php echo htmlspecialchars($product['category_name']); ?></p>
                <p><?php echo htmlspecialchars($product['description']); ?></p>
                <p>Цена: <?php echo number_format($product['price'], 2); ?> руб.</p>
                
                <?php if ($product['stock'] > 0): ?>
                    <button class="add-to-cart" 
                            onclick="addToCart(<?php echo $product['id']; ?>)">
                        В корзину
                    </button>
                <?php else: ?>
                    <button disabled>Нет в наличии</button>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>

    <div class="pagination">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?php echo $i; ?>&category=<?php echo $category; ?>&sort=<?php echo $sort; ?>&search=<?php echo urlencode($search); ?>" 
               class="<?php echo $page == $i ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
    </div>

    <script>
    function addToCart(productId) {
        fetch('cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'add',
                product_id: productId,
                quantity: 1
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Товар добавлен в корзину!');
            } else {
                alert('Ошибка: ' + data.message);
            }
        });
    }
    </script>
</body>
</html>