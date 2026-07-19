<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

$cart_item_id = (int) ($_POST['cart_item_id'] ?? 0);
$session_id   = cart_session_id();

if ($cart_item_id > 0) {
    $stmt = $pdo->prepare('DELETE FROM cart_items WHERE id = ? AND session_id = ?');
    $stmt->execute([$cart_item_id, $session_id]);
}

header('Location: ../cart.php');
exit;
