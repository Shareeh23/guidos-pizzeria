# Guido's Pizzeria — Full-Stack Project

Vanilla HTML / CSS / JS + PHP (no frameworks, no OOP — plain functions and
PDO throughout).

## 1. Project structure

```
guidos-pizzeria/
├── database.sql              ← run this once to create + seed the DB
├── index.php                 ← Home page
├── menu.php                  ← Full menu, grouped by category
├── cart.php                  ← View/edit cart, checkout
├── account.php                ← Sign in / register / order history
├── includes/
│   ├── db.php                ← PDO connection ($pdo)
│   ├── auth.php               ← session + cart helper functions
│   ├── header.php              ← shared <header>/nav, included by every page
│   └── footer.php               ← shared footer
├── api/                       ← form targets, no page markup, redirect back
│   ├── cart_add.php
│   ├── cart_update.php
│   ├── cart_remove.php
│   ├── checkout.php
│   ├── login.php
│   ├── register.php
│   └── logout.php
└── assets/
    ├── css/
    │   ├── global.css          (yours, unchanged)
    │   ├── landing-page.css     (yours, unchanged)
    │   ├── site.css             ← buttons, forms, nav states, price badges
    │   ├── menu.css
    │   ├── cart.css
    │   └── account.css
    ├── js/                      ← empty on purpose, see note below
    └── images/                  ← put product photos here (see list below)
```

## 2. Database

1. Open a terminal in this folder and run:
   ```bash
   mysql -u root -p < database.sql
   ```
   This drops/recreates a `guidos_pizzeria` database, creates all tables,
   and seeds it with categories, products, variants.
2. Edit `includes/db.php` if your MySQL user/password/host differ from the
   defaults (`root` / empty password / `localhost`).

### Schema summary

- **categories** — Mains, Sides, Drinks, Desserts
- **products** — belongs to a category
- **product_variants** — every product has ≥1 variant. "Pizza by the Slice"
  has three (Single/Double/Triple, matching your original mockup); everything
  else has a single "Regular" variant. This is what lets a size-based product
  and a flat-price product share the same cart/order logic.
- **users** — `password_hash` via PHP's `password_hash()`/`password_verify()`
- **cart_items** — keyed by PHP `session_id`, with an optional `user_id` once
  someone signs in (guest carts merge into the account automatically on
  login/register)
- **orders** / **order_items** — created on checkout; `order_items` stores a
  snapshot of the product name/variant/price at time of purchase, so later
  menu edits don't rewrite order history

### Seeded products

| Category | Items |
|---|---|
| Mains | Pizza by the Slice (Single/Double/Triple), Chicken & Waffles, Meatball Sub |
| Sides | Garlic Knots, Mozzarella Sticks, Side Salad |
| Drinks | Craft Beer, Soda, Iced Tea |
| Desserts | Dipped Ice Cream Bar, Cannoli, Chocolate Lava Cake |

## 3. Image assets

The pages reference images at `assets/images/...`. Drop files with these
exact names in that folder (or update the `image` column in `products` to
match whatever you use):

```
friendly-pizza-sausage-no-bg.png   (header logo, left)
ice-cream-karate-stance-no-bg.png  (header logo, right)
pizza-slice.jpg
chicken-waffles.jpg
meatball-sub.jpg
garlic-knots.jpg
mozz-sticks.jpg
side-salad.jpg
craft-beer.webp
soda.jpg
iced-tea.jpg
ice-cream-bar.jpg
cannoli.jpg
lava-cake.jpg
```

## 4. Running it locally

Any local PHP setup works, e.g.:

```bash
php -S localhost:8000
```

then visit `http://localhost:8000/index.php`. (If you use MAMP/WAMP/XAMPP/Laravel
Valet instead, just drop this folder in your web root as usual.)

## 5. How the pieces fit together

- **Cart** is session-based, not tied to login — `includes/auth.php`'s
  `cart_session_id()` just returns PHP's session id, so guests can add items
  before creating an account. On login/register, `api/login.php` and
  `api/register.php` attach any existing guest cart rows to the new
  `user_id`.
- **Every page** (`index.php`, `menu.php`, `cart.php`, `account.php`)
  follows the same pattern: connect to the DB, run a couple of queries,
  `require` the shared header/footer, and render with plain `<?= h($x) ?>`
  escaping — no templating engine.
- **`api/*.php`** files never output HTML. They validate `$_POST`, run one
  write query, set a one-time `$_SESSION['flash']` message, and redirect
  back with `header('Location: ...')`. This keeps the forms working even
  with JavaScript off, and is where you'd hook in `fetch()`/AJAX later if
  you want the cart to update without a full page reload.
- `assets/js/` is left empty intentionally — the whole flow above works
  without JS. It's there for you to progressively enhance (e.g. intercept
  the "Add to Cart" forms with `fetch()` and update the header cart badge
  without a reload) whenever you're ready.
