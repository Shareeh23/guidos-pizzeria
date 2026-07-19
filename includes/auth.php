<?php
/**
 * Session + cart helper functions.
 * Plain functions only — no classes, per project convention.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Returns true if a user is currently logged in. */
function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

/** Returns the logged-in user's row, or null if guest. */
function current_user(PDO $pdo): ?array
{
    if (!is_logged_in()) {
        return null;
    }
    $stmt = $pdo->prepare('SELECT id, name, email, phone, created_at FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    return $user ?: null;
}

/** The cart is always keyed by PHP session id, whether guest or logged in. */
function cart_session_id(): string
{
    return session_id();
}

/** Total number of items (sum of quantities) currently in the cart. */
function cart_item_count(PDO $pdo): int
{
    $stmt = $pdo->prepare('SELECT COALESCE(SUM(quantity), 0) AS total FROM cart_items WHERE session_id = ?');
    $stmt->execute([cart_session_id()]);
    return (int) $stmt->fetch()['total'];
}

/** Simple helper to escape output in templates. */
function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}
