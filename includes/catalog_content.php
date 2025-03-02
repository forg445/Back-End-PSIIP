<?php
// Получение категорий
$categories = $conn->query("SELECT * FROM categories ORDER BY name");

// Получение выбранной категории
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;

// Получение товаров
$products_query = "SELECT * FROM products";
if ($category_id > 0) {
    $products_query .= " WHERE category_id = $category_id";
}
$products_query .= " ORDER BY name";
$products = $conn->query($products_query);
?>

<div class="catalog-container">
    <div class="categories">
        <h3>Категории</h3>
        <a href="?page=catalog" class="category-link <?php echo $category_id == 0 ? 'active' : ''; ?>">
            Все товары
        </a>
        <?php while ($category = $categories->fetch_assoc()): ?>
            <a href="?page=catalog&category=<?php echo $category['id']; ?>" 
               class="category-link <?php echo $category_id == $category['id'] ? 'active' : ''; ?>">
                <?php echo htmlspecialchars($category['name']); ?>
            </a>
        <?php endwhile; ?>
    </div>

    <div class="products">
        <?php if ($products && $products->num_rows > 0): ?>
            <div class="products-grid">
                <?php while ($product = $products->fetch_assoc()): ?>
                    <div class="product-card">
                        <?php if ($product['image']): ?>
                            <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <?php endif; ?>
                        
                        <div class="product-info">
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="description"><?php echo htmlspecialchars($product['description']); ?></p>
                            <p class="price"><?php echo number_format($product['price'], 2); ?> руб.</p>
                            
                            <form method="post" action="?page=cart">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <div class="quantity-input">
                                    <label>Количество:</label>
                                    <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
                                </div>
                                <button type="submit" name="add_to_cart" class="btn-add-to-cart"
                                        <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                                    <?php echo $product['stock'] > 0 ? 'В корзину' : 'Нет в наличии'; ?>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>Товары не найдены.</p>
        <?php endif; ?>
    </div>
</div>

<style>
.catalog-container {
    display: flex;
    gap: 30px;
    padding: 20px;
    max-width: 1200px;
    margin: 0 auto;
}

.categories {
    width: 200px;
    flex-shrink: 0;
}

.category-link {
    display: block;
    padding: 10px;
    text-decoration: none;
    color: #333;
    border-radius: 4px;
    margin-bottom: 5px;
}

.category-link:hover {
    background: #f8f9fa;
}

.category-link.active {
    background: #007bff;
    color: white;
}

.products {
    flex-grow: 1;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
}

.product-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.product-card img {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.product-info {
    padding: 15px;
}

.product-info h3 {
    margin: 0 0 10px 0;
    font-size: 1.1em;
}

.description {
    color: #666;
    margin-bottom: 10px;
    font-size: 0.9em;
}

.price {
    font-size: 1.2em;
    font-weight: bold;
    color: #007bff;
    margin-bottom: 15px;
}

.quantity-input {
    margin-bottom: 10px;
}

.quantity-input label {
    display: block;
    margin-bottom: 5px;
}

.quantity-input input {
    width: 60px;
    padding: 5px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.btn-add-to-cart {
    width: 100%;
    padding: 10px;
    background: #28a745;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.btn-add-to-cart:hover {
    background: #218838;
}

.btn-add-to-cart:disabled {
    background: #6c757d;
    cursor: not-allowed;
}

@media (max-width: 768px) {
    .catalog-container {
        flex-direction: column;
    }
    
    .categories {
        width: 100%;
    }
}
</style>