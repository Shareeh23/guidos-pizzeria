<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

$name  = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($name === '' || $email === '' || strlen($password) < 6) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Please fill in all fields; password must be at least 6 characters.'];
    header('Location: ../account.php');
    exit;
}

$check = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$check->execute([$email]);
if ($check->fetch()) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'An account with that email already exists.'];
    header('Location: ../account.php');
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);

$insert = $pdo->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
$insert->execute([$name, $email, $hash]);

$user_id = $pdo->lastInsertId();

// Attach any guest cart items from this session to the new account.
$attach = $pdo->prepare('UPDATE cart_items SET user_id = ? WHERE session_id = ?');
$attach->execute([$user_id, cart_session_id()]);

$_SESSION['user_id'] = $user_id;
$_SESSION['flash'] = ['type' => 'success', 'message' => 'Welcome, ' . $name . '!'];
header('Location: ../account.php');
exit;
