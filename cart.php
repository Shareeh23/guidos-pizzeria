<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$active_page = 'cart';
$session_id = cart_session_id();

$items_stmt = $pdo->prepare('
    SELECT ci.id AS cart_item_id, ci.quantity, v.id AS variant_id, v.name AS variant_name,
           v.price, p.name AS product_name, p.image
    FROM cart_items ci
    JOIN product_variants v ON v.id = ci.product_variant_id
    JOIN products p ON p.id = v.product_id
    WHERE ci.session_id = ?
    ORDER BY ci.id
');
$items_stmt->execute([$session_id]);
$items = $items_stmt->fetchAll();

$subtotal = 0;
foreach ($items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$tax = round($subtotal * 0.08, 2);
$total = round($subtotal + $tax, 2);

$user = current_user($pdo);

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Cart — Guido's Pizzeria</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Luckiest+Guy&family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Roboto+Mono:ital,wght@0,100..700;1,100..700&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="assets/css/global.css" />
    <link rel="stylesheet" href="assets/css/landing-page.css" />
    <link rel="stylesheet" href="assets/css/site.css" />
    <link rel="stylesheet" href="assets/css/cart.css" />
  </head>
  <body>
    <main class="page-grid">
      <?php require __DIR__ . '/includes/header.php'; ?>

      <div class="cart-page">
        <h2 class="h2">Your Cart</h2>

        <?php if ($flash): ?>
          <div class="alert alert--<?= h($flash['type']) ?>"><?= h($flash['message']) ?></div>
        <?php endif; ?>

        <?php if (empty($items)): ?>
          <div class="empty-state">
            <p class="body">Your cart is empty.</p>
            <a href="menu.php" class="btn">Browse the Menu</a>
          </div>
        <?php else: ?>
          <div class="cart-list">
            <?php foreach ($items as $item): ?>
              <div class="cart-row">
                <img class="cart-row__image" src="<?= h($item['image']) ?>" alt="" />

                <div class="cart-row__name">
                  <span class="body"><?= h($item['product_name']) ?></span>
                  <span class="caption"><?= h($item['variant_name']) ?> &middot; $<?= number_format($item['price'], 2) ?> each</span>
                </div>

                <form class="cart-row__qty-form" action="api/cart_update.php" method="post">
                  <input type="hidden" name="cart_item_id" value="<?= (int) $item['cart_item_id'] ?>" />
                  <input class="cart-row__qty-input" type="number" name="quantity" value="<?= (int) $item['quantity'] ?>" min="0" max="20" />
                  <button type="submit" class="btn btn--small btn--outline">Update</button>
                </form>

                <span class="body"><strong>$<?= number_format($item['price'] * $item['quantity'], 2) ?></strong></span>

                <form action="api/cart_remove.php" method="post">
                  <input type="hidden" name="cart_item_id" value="<?= (int) $item['cart_item_id'] ?>" />
                  <button type="submit" class="btn btn--small btn--outline">Remove</button>
                </form>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="cart-summary">
            <div class="cart-summary__row"><span>Subtotal</span><span>$<?= number_format($subtotal, 2) ?></span></div>
            <div class="cart-summary__row"><span>Tax (8%)</span><span>$<?= number_format($tax, 2) ?></span></div>
            <div class="cart-summary__row cart-summary__row--total"><span>Total</span><span>$<?= number_format($total, 2) ?></span></div>

            <form class="form" action="api/checkout.php" method="post">
              <?php if (!$user): ?>
                <div class="form__field">
                  <label for="guest_name" class="caption">Name</label>
                  <input type="text" id="guest_name" name="guest_name" required />
                </div>
                <div class="form__field">
                  <label for="guest_email" class="caption">Email</label>
                  <input type="email" id="guest_email" name="guest_email" required />
                </div>
                <p class="caption">Have an account? <a href="account.php"><strong>Sign in</strong></a> to skip this.</p>
              <?php endif; ?>
              <button type="submit" class="btn">Place Order</button>
            </form>
          </div>
        <?php endif; ?>
      </div>

      <?php require __DIR__ . '/includes/footer.php'; ?>
    </main>
  </body>
</html>