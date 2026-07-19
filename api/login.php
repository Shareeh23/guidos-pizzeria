<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

$stmt = $pdo->prepare('SELECT id, name, password_hash FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Incorrect email or password.'];
    header('Location: ../account.php');
    exit;
}

// Attach any guest cart items from this session to the account being logged into.
$attach = $pdo->prepare('UPDATE cart_items SET user_id = ? WHERE session_id = ?');
$attach->execute([$user['id'], cart_session_id()]);

$_SESSION['user_id'] = $user['id'];
$_SESSION['flash'] = ['type' => 'success', 'message' => 'Welcome back, ' . $user['name'] . '!'];
header('Location: ../account.php');
exit;
