<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$active_page = 'home';

// Featured items for the home page (mirrors the original static mockup,
// now sourced from the database so prices/descriptions stay in sync
// with the menu page).
$pizza = $pdo->query("SELECT * FROM products WHERE name = 'Pizza by the Slice' LIMIT 1")->fetch();
$pizza_variants = $pdo->prepare('SELECT * FROM product_variants WHERE product_id = ? ORDER BY sort_order');
$pizza_variants->execute([$pizza['id']]);
$pizza_variants = $pizza_variants->fetchAll();

$sidebar_stmt = $pdo->query("
    SELECT p.*, c.name AS category_name, v.id AS variant_id, v.price
    FROM products p
    JOIN categories c ON c.id = p.category_id
    JOIN product_variants v ON v.product_id = p.id
    WHERE p.is_featured = 1
    ORDER BY c.display_order
");
$sidebar_items = $sidebar_stmt->fetchAll();

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Guido's Pizzeria</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Luckiest+Guy&family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Roboto+Mono:ital,wght@0,100..700;1,100..700&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="assets/css/global.css" />
    <link rel="stylesheet" href="assets/css/landing-page.css" />
    <link rel="stylesheet" href="assets/css/site.css" />
  </head>
  <body>
    <main class="page-grid">
      <?php require __DIR__ . '/includes/header.php'; ?>

      <?php if ($flash): ?>
        <div class="alert alert--<?= h($flash['type']) ?>" style="margin: 1rem 1.5rem;">
          <?= h($flash['message']) ?>
        </div>
      <?php endif; ?>

      <article class="menu">
        <div class="menu-feature">
          <div class="menu-feature__top">
            <div class="menu-feature__copy">
              <h2 class="menu-feature__title h2"><?= h($pizza['name']) ?></h2>
              <p class="menu-feature__text body"><?= h($pizza['description']) ?></p>
            </div>

            <img
              src="<?= h($pizza['image']) ?>"
              alt=""
              class="menu-feature__image"
            />
          </div>

          <form class="menu-feature__portions" action="api/cart_add.php" method="post">
            <input type="hidden" name="return_to" value="index.php" />
            <?php foreach ($pizza_variants as $i => $variant): ?>
              <section class="menu-portion menu-portion--<?= h(strtolower($variant['name'])) ?>">
                <label>
                  <input type="radio" name="product_variant_id" value="<?= (int) $variant['id'] ?>" <?= $i === 0 ? 'checked' : '' ?> />
                  <h3 class="menu-portion__title h3"><?= h($variant['name']) ?></h3>
                </label>
                <p class="menu-portion__description body">
                  Includes a drink and a pickle
                </p>
                <span class="menu-portion__price caption price-badge">$<?= number_format($variant['price'], 2) ?></span>
              </section>
            <?php endforeach; ?>
            <div class="menu-feature__portions-footer">
              <button type="submit" class="btn">Add to Cart</button>
            </div>
          </form>

          <section class="shop-gallery" aria-labelledby="shop-gallery-heading">
            <h3 id="shop-gallery-heading" class="h4 shop-gallery__title">Behind the Scenes at Guido's</h3>
            <div class="shop-gallery__grid">
              <img src="assets/images/pizza-place-outside.jpg" alt="Guido's Pizzeria outer look" class="shop-gallery__photo" />
              <img src="assets/images/pizza-boxes.jpg" alt="Guido's Pizzeria boxes stacked" class="shop-gallery__photo" />
              <img src="assets/images/pizza-place-inside.jpg" alt="Guido's Pizzeria on a busy mid day" class="shop-gallery__photo" />
              <img src="assets/images/packed-pizza.jpeg" alt="Chef putting pizza inside the box" class="shop-gallery__photo" />
            </div>
          </section>
        </div>

        <aside class="menu-sidebar">
          <?php foreach ($sidebar_items as $item): ?>
            <section class="menu-sidebar__item">
              <span class="caption uppercase menu-sidebar__eyebrow">Most Popular &middot; <?= h($item['category_name']) ?></span>

              <h3 class="h4 menu-sidebar__name"><?= h($item['name']) ?></h3>

              <?php if ($item['calories']): ?>
                <span class="caption menu-sidebar__calories"><?= (int) $item['calories'] ?> Calories</span>
              <?php endif; ?>

              <img src="<?= h($item['image']) ?>" alt="" class="menu-sidebar__image" />

              <form class="menu-sidebar__price-row" action="api/cart_add.php" method="post">
                <input type="hidden" name="return_to" value="index.php" />
                <input type="hidden" name="product_variant_id" value="<?= (int) $item['variant_id'] ?>" />
                <span class="menu-sidebar__price">$<?= number_format($item['price'], 2) ?></span>
                <button type="submit" class="menu-sidebar__arrow" aria-label="Add <?= h($item['name']) ?> to cart">&#8599;</button>
              </form>
            </section>
          <?php endforeach; ?>
        </aside>
      </article>

      <div class="menu-cta">
        <a href="menu.php" class="btn btn--outline">View Full Menu</a>
      </div>

      <?php require __DIR__ . '/includes/footer.php'; ?>
    </main>
  </body>
</html>