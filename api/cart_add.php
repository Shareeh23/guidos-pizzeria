<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

$return_to = $_POST['return_to'] ?? 'menu.php';
// Only allow redirecting back to known local pages (avoid open redirect).
$allowed_returns = ['index.php', 'menu.php', 'cart.php'];
if (!in_array($return_to, $allowed_returns, true)) {
    $return_to = 'menu.php';
}

$variant_id = (int) ($_POST['product_variant_id'] ?? 0);
$quantity   = max(1, (int) ($_POST['quantity'] ?? 1));

if ($variant_id <= 0) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Please choose an item to add.'];
    header("Location: ../{$return_to}");
    exit;
}

// Make sure the variant actually exists before inserting.
$check = $pdo->prepare('SELECT id FROM product_variants WHERE id = ?');
$check->execute([$variant_id]);
if (!$check->fetch()) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'That item is no longer available.'];
    header("Location: ../{$return_to}");
    exit;
}

$session_id = cart_session_id();
$user_id = $_SESSION['user_id'] ?? null;

// If this variant is already in the cart, bump the quantity instead of
// creating a duplicate row.
$existing = $pdo->prepare('SELECT id, quantity FROM cart_items WHERE session_id = ? AND product_variant_id = ?');
$existing->execute([$session_id, $variant_id]);
$row = $existing->fetch();

if ($row) {
    $update = $pdo->prepare('UPDATE cart_items SET quantity = quantity + ? WHERE id = ?');
    $update->execute([$quantity, $row['id']]);
} else {
    $insert = $pdo->prepare('INSERT INTO cart_items (session_id, user_id, product_variant_id, quantity) VALUES (?, ?, ?, ?)');
    $insert->execute([$session_id, $user_id, $variant_id, $quantity]);
}

$_SESSION['flash'] = ['type' => 'success', 'message' => 'Added to cart.'];
header("Location: ../{$return_to}");
exit;
