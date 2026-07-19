<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$active_page = 'menu';

$categories = $pdo->query('SELECT * FROM categories ORDER BY display_order')->fetchAll();

$products_stmt = $pdo->prepare('SELECT * FROM products WHERE category_id = ? AND is_active = 1 ORDER BY id');
$variants_stmt = $pdo->prepare('SELECT * FROM product_variants WHERE product_id = ? ORDER BY sort_order');

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Menu — Guido's Pizzeria</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Luckiest+Guy&family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Roboto+Mono:ital,wght@0,100..700;1,100..700&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="assets/css/global.css" />
    <link rel="stylesheet" href="assets/css/landing-page.css" />
    <link rel="stylesheet" href="assets/css/site.css" />
    <link rel="stylesheet" href="assets/css/menu.css" />
  </head>
  <body>
    <main class="page-grid">
      <?php require __DIR__ . '/includes/header.php'; ?>

      <div class="menu-page">
        <div class="menu-page__intro">
          <h2 class="h2">Our Menu</h2>
        </div>

        <?php if ($flash): ?>
          <div class="alert alert--<?= h($flash['type']) ?>"><?= h($flash['message']) ?></div>
        <?php endif; ?>

        <nav class="category-tabs">
          <?php foreach ($categories as $i => $cat): ?>
            <a class="category-tabs__link<?= $i === 0 ? ' is-active' : '' ?>" href="#cat-<?= h($cat['slug']) ?>">
              <?= h($cat['name']) ?>
            </a>
          <?php endforeach; ?>
        </nav>

        <?php foreach ($categories as $cat): ?>
          <?php
            $products_stmt->execute([$cat['id']]);
            $products = $products_stmt->fetchAll();
          ?>
          <section class="category-section" id="cat-<?= h($cat['slug']) ?>">
            <h3 class="category-section__title h3"><?= h($cat['name']) ?></h3>

            <div class="product-grid">
              <?php foreach ($products as $product): ?>
                <?php
                  $variants_stmt->execute([$product['id']]);
                  $variants = $variants_stmt->fetchAll();
                ?>
                <article class="product-card">
                  <img class="product-card__image" src="<?= h($product['image']) ?>" alt="" />

                  <div class="product-card__header">
                    <h4 class="h4"><?= h($product['name']) ?></h4>
                    <?php if ($product['calories']): ?>
                      <span class="caption product-card__calories"><?= (int) $product['calories'] ?> cal</span>
                    <?php endif; ?>
                  </div>

                  <p class="body caption product-card__description"><?= h($product['description']) ?></p>

                  <form class="product-card__form" action="api/cart_add.php" method="post">
                    <input type="hidden" name="return_to" value="menu.php" />

                    <?php if (count($variants) > 1): ?>
                      <select class="product-card__variant" name="product_variant_id">
                        <?php foreach ($variants as $variant): ?>
                          <option value="<?= (int) $variant['id'] ?>">
                            <?= h($variant['name']) ?> — $<?= number_format($variant['price'], 2) ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    <?php else: ?>
                      <input type="hidden" name="product_variant_id" value="<?= (int) $variants[0]['id'] ?>" />
                      <span class="price-badge caption">$<?= number_format($variants[0]['price'], 2) ?></span>
                    <?php endif; ?>

                    <input class="product-card__qty" type="number" name="quantity" value="1" min="1" max="20" />
                    <button type="submit" class="btn btn--small">Add to Cart</button>
                  </form>
                </article>
              <?php endforeach; ?>
            </div>
          </section>
        <?php endforeach; ?>
      </div>

      <?php require __DIR__ . '/includes/footer.php'; ?>
    </main>
  </body>
</html>