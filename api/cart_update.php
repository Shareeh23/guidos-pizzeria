<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

$cart_item_id = (int) ($_POST['cart_item_id'] ?? 0);
$quantity     = (int) ($_POST['quantity'] ?? 1);
$session_id   = cart_session_id();

if ($cart_item_id > 0) {
    if ($quantity <= 0) {
        $stmt = $pdo->prepare('DELETE FROM cart_items WHERE id = ? AND session_id = ?');
        $stmt->execute([$cart_item_id, $session_id]);
    } else {
        $quantity = min($quantity, 20);
        $stmt = $pdo->prepare('UPDATE cart_items SET quantity = ? WHERE id = ? AND session_id = ?');
        $stmt->execute([$quantity, $cart_item_id, $session_id]);
    }
}

header('Location: ../cart.php');
exit;
