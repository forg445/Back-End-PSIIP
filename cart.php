<?php
session_start();
require_once 'config.php';

// Инициализация корзины
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Обработка AJAX запросов
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if ($data['action'] === 'add') {
        $product_id = (int)$data['product_id'];
        $quantity = (int)$data['quantity'];
        
        // Проверка наличия товара
        $product = $conn->query("SELECT * FROM products WHERE id = $product_id")->fetch_assoc();
        if ($product && $product['stock'] >= $quantity) {
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id] += $quantity;
            } else {
                $_SESSION['cart'][$product_id] = $quantity;
            }
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Недостаточно товара на складе']);
        }
        exit;
    }
    
    if ($data['action'] === 'update') {
        $product_id = (int)$data['product_id'];
        $quantity = (int)$data['quantity'];
        
        if ($quantity > 0) {
            $_SESSION['cart'][$product_id] = $quantity;
        } else {
            unset($_SESSION['cart'][$product_id]);
        }
        echo json_encode(['success' => true]);
        exit;
    }
    
    if ($data['action'] === 'remove') {
        $product_id = (int)$data['product_id'];
        unset($_SESSION['cart'][$product_id]);
        echo json_encode(['success' => true]);
        exit;
    }
}

// Получение товаров в корзине
$cart_items = [];
$total = 0;

if (!empty($_SESSION['cart'])) {
    $product_ids = array_keys($_SESSION['cart']);
    $products = $conn->query("SELECT * FROM products WHERE id IN (" . implode(',', $product_ids) . ")");
    
    while ($product = $products->fetch_assoc()) {
        $quantity = $_SESSION['cart'][$product['id']];
        $cart_items[] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['price'],
            'quantity' => $quantity,
            'subtotal' => $product['price'] * $quantity
        ];
        $total += $product['price'] * $quantity;
    }
}

// Оформление заказа
if (isset($_POST['checkout']) && !empty($cart_items)) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Создание заказа
    $conn->query("INSERT INTO orders (user_id, total_amount) VALUES ($user_id, $total)");
    $order_id = $conn->insert_id;
    
    // Добавление товаров в заказ
    foreach ($cart_items as $item) {
        $product_id = $item['id'];
        $quantity = $item['quantity'];
        $price = $item['price'];
        
        $conn->query("INSERT INTO order_items (order_id, product_id, quantity, price) 
                     VALUES ($order_id, $product_id, $quantity, $price)");
        
        // Обновление остатков
        $conn->query("UPDATE products SET stock = stock - $quantity WHERE id = $product_id");
    }
    
    // Очистка корзины
    $_SESSION['cart'] = [];
    
    // Отправка уведомления на email
    $to = $_SESSION['email'];
    $subject = "Заказ #$order_id успешно оформлен";
    $message = "Спасибо за заказ!\n\nДетали заказа:\n\n";
    
    foreach ($cart_items as $item) {
        $message .= "{$item['name']} x {$item['quantity']}: {$item['subtotal']} руб.\n";
    }
    
    $message .= "\nИтого: $total руб.";
    mail($to, $subject, $message);
    
    $success = "Заказ успешно оформлен! Проверьте вашу почту.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Корзина</title>
    <meta charset="utf-8">
    <style>
        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .cart-table th, .cart-table td {
            padding: 10px;
            border: 1px solid #ddd;
        }
        .quantity-input {
            width: 60px;
            padding: 5px;
        }
        .remove-item {
            color: red;
            cursor: pointer;
        }
        .total {
            font-size: 1.2em;
            font-weight: bold;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <h1>Корзина</h1>
    
    <?php if (isset($success)): ?>
        <div style="color: green; margin: 20px 0;"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if (!empty($cart_items)): ?>
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Товар</th>
                    <th>Цена</th>
                    <th>Количество</th>
                    <th>Сумма</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td><?php echo number_format($item['price'], 2); ?> руб.</td>
                        <td>
                            <input type="number" class="quantity-input" 
                                   value="<?php echo $item['quantity']; ?>" min="1"
                                   onchange="updateQuantity(<?php echo $item['id']; ?>, this.value)">
                        </td>
                        <td><?php echo number_format($item['subtotal'], 2); ?> руб.</td>
                        <td>
                            <span class="remove-item" 
                                  onclick="removeItem(<?php echo $item['id']; ?>)">
                                Удалить
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="total">
            Итого: <?php echo number_format($total, 2); ?> руб.
        </div>
        
        <form method="post">
            <button type="submit" name="checkout">Оформить заказ</button>
        </form>
    <?php else: ?>
        <p>Корзина пуста</p>
    <?php endif; ?>

    <script>
    function updateQuantity(productId, quantity) {
        fetch('cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'update',
                product_id: productId,
                quantity: parseInt(quantity)
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }

    function removeItem(productId) {
        if (confirm('Вы уверены?')) {
            fetch('cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'remove',
                    product_id: productId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        }
    }
    </script>
</body>
</html>