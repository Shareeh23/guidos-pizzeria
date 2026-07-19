<?php
/**
 * Shared header. Expects $active_page to be set by the including page
 * (one of: 'home', 'menu', 'cart', 'account') so the nav can highlight it.
 * Expects $pdo and auth.php to already be loaded.
 */
$active_page = $active_page ?? '';
$cart_count = cart_item_count($pdo);
?>
<header class="header">
    <div class="header__branding">
        <div class="header__logo header__logo--left">
            <img src="assets/svgs/friendly-pizza-sausage-no-bg.png" alt="" />
        </div>

        <h1 class="header__title title text-center">
            <a href="index.php">Guido's Pizzeria</a>
        </h1>

        <div class="header__logo header__logo--right">
            <img src="assets/svgs/ice-cream-karate-stance-no-bg.png" alt="" />
        </div>
    </div>

    <nav class="header__nav">
        <ul class="header__nav-list">
            <li class="header__nav-item body text-center<?= $active_page === 'home' ? ' is-active' : '' ?>">
                <a href="index.php" class="header__nav-link">Home</a>
            </li>
            <li class="header__nav-item body text-center<?= $active_page === 'menu' ? ' is-active' : '' ?>">
                <a href="menu.php" class="header__nav-link">Menu</a>
            </li>
            <li class="header__nav-item body text-center<?= $active_page === 'cart' ? ' is-active' : '' ?>">
                <a href="cart.php" class="header__nav-link">Cart<?= $cart_count > 0 ? ' (' . $cart_count . ')' : '' ?></a>
            </li>
            <li class="header__nav-item body text-center<?= $active_page === 'account' ? ' is-active' : '' ?>">
                <a href="account.php" class="header__nav-link"><?= is_logged_in() ? 'Account' : 'Sign In' ?></a>
            </li>
        </ul>
    </nav>
</header>
