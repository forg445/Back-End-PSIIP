<?php
// Добавление товара в корзину
if (isset($_POST['add_to_cart'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    $user_id = $_SESSION['user_id'];
    
    // Проверяем, есть ли уже этот товар в корзине
    $check = $conn->query("SELECT id, quantity FROM cart 
                          WHERE user_id = $user_id AND product_id = $product_id");
    
    if ($check->num_rows > 0) {
        // Обновляем количество
        $cart_item = $check->fetch_assoc();
        $new_quantity = $cart_item['quantity'] + $quantity;
        $conn->query("UPDATE cart SET quantity = $new_quantity 
                     WHERE id = {$cart_item['id']}");
    } else {
        // Добавляем новый товар
        $conn->query("INSERT INTO cart (user_id, product_id, quantity) 
                     VALUES ($user_id, $product_id, $quantity)");
    }
    
    $success = "Товар добавлен в корзину!";
}

// Удаление товара из корзины
if (isset($_POST['remove_from_cart'])) {
    $cart_id = (int)$_POST['cart_id'];
    $user_id = $_SESSION['user_id'];
    
    $conn->query("DELETE FROM cart WHERE id = $cart_id AND user_id = $user_id");
}

// Получение содержимого корзины
$cart_items = $conn->query("
    SELECT c.*, p.name, p.price, p.image
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = {$_SESSION['user_id']}
    ORDER BY c.created_at DESC
");

$total = 0;
?>

<div class="cart-container">
    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if ($cart_items && $cart_items->num_rows > 0): ?>
        <div class="cart-items">
            <?php while ($item = $cart_items->fetch_assoc()):
                $subtotal = $item['price'] * $item['quantity'];
                $total += $subtotal;
            ?>
                <div class="cart-item">
                    <?php if ($item['image']): ?>
                        <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                    <?php endif; ?>
                    
                    <div class="item-details">
                        <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                        <p>Цена: <?php echo number_format($item['price'], 2); ?> руб.</p>
                        <p>Количество: <?php echo $item['quantity']; ?></p>
                        <p>Подытог: <?php echo number_format($subtotal, 2); ?> руб.</p>
                        
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                            <button type="submit" name="remove_from_cart" class="btn-remove">Удалить</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
            
            <div class="cart-total">
                <h3>Итого: <?php echo number_format($total, 2); ?> руб.</h3>
                <button type="button" onclick="alert('Функция оформления заказа в разработке')">Оформить заказ</button>
            </div>
        </div>
    <?php else: ?>
        <p>Ваша корзина пуста.</p>
    <?php endif; ?>
</div>

<style>
.cart-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

.cart-item {
    display: flex;
    background: white;
    padding: 15px;
    margin-bottom: 15px;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.cart-item img {
    width: 100px;
    height: 100px;
    object-fit: cover;
    margin-right: 15px;
}

.item-details {
    flex: 1;
}

.item-details h3 {
    margin: 0 0 10px 0;
}

.btn-remove {
    background: #dc3545;
    color: white;
    border: none;
    padding: 5px 10px;
    border-radius: 3px;
    cursor: pointer;
}

.btn-remove:hover {
    background: #c82333;
}

.cart-total {
    text-align: right;
    margin-top: 20px;
    padding: 20px;
    background: white;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
</style>