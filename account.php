<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$active_page = 'account';
$user = current_user($pdo);

$orders = [];
if ($user) {
    $orders_stmt = $pdo->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC');
    $orders_stmt->execute([$user['id']]);
    $orders = $orders_stmt->fetchAll();

    $order_items_stmt = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ?');
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Account — Guido's Pizzeria</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Luckiest+Guy&family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Roboto+Mono:ital,wght@0,100..700;1,100..700&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="assets/css/global.css" />
    <link rel="stylesheet" href="assets/css/landing-page.css" />
    <link rel="stylesheet" href="assets/css/site.css" />
    <link rel="stylesheet" href="assets/css/account.css" />
  </head>
  <body>
    <main class="page-grid">
      <?php require __DIR__ . '/includes/header.php'; ?>

      <div class="account-page">
        <?php if ($flash): ?>
          <div class="alert alert--<?= h($flash['type']) ?>"><?= h($flash['message']) ?></div>
        <?php endif; ?>

        <?php if (!$user): ?>
          <h2 class="h2">Account</h2>
          <div class="auth-grid">
            <div class="auth-panel">
              <h3 class="h3">Sign In</h3>
              <form class="form" action="api/login.php" method="post">
                <div class="form__field">
                  <label for="login_email" class="caption">Email</label>
                  <input type="email" id="login_email" name="email" required />
                </div>
                <div class="form__field">
                  <label for="login_password" class="caption">Password</label>
                  <input type="password" id="login_password" name="password" required />
                </div>
                <button type="submit" class="btn">Sign In</button>
              </form>
            </div>

            <div class="auth-panel">
              <h3 class="h3">Create Account</h3>
              <form class="form" action="api/register.php" method="post">
                <div class="form__field">
                  <label for="register_name" class="caption">Name</label>
                  <input type="text" id="register_name" name="name" required />
                </div>
                <div class="form__field">
                  <label for="register_email" class="caption">Email</label>
                  <input type="email" id="register_email" name="email" required />
                </div>
                <div class="form__field">
                  <label for="register_password" class="caption">Password</label>
                  <input type="password" id="register_password" name="password" minlength="6" required />
                </div>
                <button type="submit" class="btn">Create Account</button>
              </form>
            </div>
          </div>
        <?php else: ?>
          <div class="account-summary">
            <div>
              <h2 class="h2"><?= h($user['name']) ?></h2>
              <p class="body caption"><?= h($user['email']) ?></p>
            </div>
            <form action="api/logout.php" method="post">
              <button type="submit" class="btn btn--outline">Sign Out</button>
            </form>
          </div>

          <div>
            <h3 class="h3" style="margin-bottom: 1rem;">Order History</h3>

            <?php if (empty($orders)): ?>
              <div class="empty-state">
                <p class="body">No orders yet.</p>
                <a href="menu.php" class="btn">Browse the Menu</a>
              </div>
            <?php else: ?>
              <div class="order-history">
                <?php foreach ($orders as $order): ?>
                  <?php
                    $order_items_stmt->execute([$order['id']]);
                    $order_items = $order_items_stmt->fetchAll();
                    $item_summary = implode(', ', array_map(
                        fn($oi) => "{$oi['quantity']}× {$oi['product_name']} ({$oi['variant_name']})",
                        $order_items
                    ));
                  ?>
                  <div class="order-row">
                    <span class="caption">#<?= (int) $order['id'] ?> &middot; <?= h(date('M j, Y', strtotime($order['created_at']))) ?></span>
                    <span class="body caption"><?= h($item_summary) ?></span>
                    <span class="order-status caption"><?= h(str_replace('_', ' ', $order['status'])) ?></span>
                    <span class="body"><strong>$<?= number_format($order['total_amount'], 2) ?></strong></span>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>

      <?php require __DIR__ . '/includes/footer.php'; ?>
    </main>
  </body>
</html>