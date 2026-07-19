<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

$session_id = cart_session_id();
$user_id = $_SESSION['user_id'] ?? null;

$items_stmt = $pdo->prepare('
    SELECT ci.id AS cart_item_id, ci.quantity, v.id AS variant_id, v.name AS variant_name,
           v.price, p.name AS product_name
    FROM cart_items ci
    JOIN product_variants v ON v.id = ci.product_variant_id
    JOIN products p ON p.id = v.product_id
    WHERE ci.session_id = ?
');
$items_stmt->execute([$session_id]);
$items = $items_stmt->fetchAll();

if (empty($items)) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Your cart is empty.'];
    header('Location: ../cart.php');
    exit;
}

// Guests need a name/email so we have somewhere to send the order.
$guest_name = trim($_POST['guest_name'] ?? '');
$guest_email = trim($_POST['guest_email'] ?? '');

if (!$user_id && ($guest_name === '' || $guest_email === '')) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Please enter your name and email, or sign in, to place an order.'];
    header('Location: ../cart.php');
    exit;
}

$total = 0;
foreach ($items as $item) {
    $total += $item['price'] * $item['quantity'];
}

$pdo->beginTransaction();
try {
    $order_stmt = $pdo->prepare('
        INSERT INTO orders (user_id, guest_name, guest_email, total_amount, status)
        VALUES (?, ?, ?, ?, "pending")
    ');
    $order_stmt->execute([
        $user_id,
        $user_id ? null : $guest_name,
        $user_id ? null : $guest_email,
        $total,
    ]);
    $order_id = $pdo->lastInsertId();

    $item_stmt = $pdo->prepare('
        INSERT INTO order_items (order_id, product_variant_id, product_name, variant_name, quantity, price)
        VALUES (?, ?, ?, ?, ?, ?)
    ');
    foreach ($items as $item) {
        $item_stmt->execute([
            $order_id,
            $item['variant_id'],
            $item['product_name'],
            $item['variant_name'],
            $item['quantity'],
            $item['price'],
        ]);
    }

    $clear_stmt = $pdo->prepare('DELETE FROM cart_items WHERE session_id = ?');
    $clear_stmt->execute([$session_id]);

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Something went wrong placing your order. Please try again.'];
    header('Location: ../cart.php');
    exit;
}

$_SESSION['flash'] = ['type' => 'success', 'message' => "Order #{$order_id} placed! Thank you."];
header('Location: ../' . ($user_id ? 'account.php' : 'cart.php'));
exit;
