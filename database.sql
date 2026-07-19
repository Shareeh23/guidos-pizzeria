-- =====================================================================
-- Guido's Pizzeria — Database Schema + Seed Data
-- MySQL 8.0+
-- Copy/paste this entire file into your MySQL client (or run:
--   mysql -u root -p < database.sql
-- ) to create and populate the database from scratch.
-- =====================================================================

DROP DATABASE IF EXISTS guidos_pizzeria;
CREATE DATABASE guidos_pizzeria CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE guidos_pizzeria;

-- ---------------------------------------------------------------------
-- categories
-- ---------------------------------------------------------------------
CREATE TABLE categories (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(50)  NOT NULL,
    slug          VARCHAR(50)  NOT NULL UNIQUE,
    display_order INT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- products
-- ---------------------------------------------------------------------
CREATE TABLE products (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NOT NULL,
    name        VARCHAR(100) NOT NULL,
    description TEXT,
    image       VARCHAR(255) DEFAULT NULL,
    calories    INT UNSIGNED DEFAULT NULL,
    is_active   TINYINT(1) NOT NULL DEFAULT 1,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- product_variants
-- Every product has at least one variant (e.g. "Regular"). Products
-- that come in sizes (like pizza slices) get multiple rows here.
-- ---------------------------------------------------------------------
CREATE TABLE product_variants (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    name       VARCHAR(50)   NOT NULL DEFAULT 'Regular',
    price      DECIMAL(6,2)  NOT NULL,
    sort_order INT UNSIGNED  NOT NULL DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- users
-- ---------------------------------------------------------------------
CREATE TABLE users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100) NOT NULL,
    email         VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone         VARCHAR(30)  DEFAULT NULL,
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- cart_items
-- Guest carts are tracked by PHP session_id. When a user logs in,
-- user_id is attached so the cart can also be looked up by account.
-- ---------------------------------------------------------------------
CREATE TABLE cart_items (
    id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id         VARCHAR(191) NOT NULL,
    user_id            INT UNSIGNED DEFAULT NULL,
    product_variant_id INT UNSIGNED NOT NULL,
    quantity           INT UNSIGNED NOT NULL DEFAULT 1,
    created_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_variant_id) REFERENCES product_variants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_session (session_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- orders
-- ---------------------------------------------------------------------
CREATE TABLE orders (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id      INT UNSIGNED DEFAULT NULL,
    guest_name   VARCHAR(100) DEFAULT NULL,
    guest_email  VARCHAR(150) DEFAULT NULL,
    total_amount DECIMAL(8,2) NOT NULL,
    status       ENUM('pending','preparing','out_for_delivery','completed','cancelled') NOT NULL DEFAULT 'pending',
    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- order_items
-- ---------------------------------------------------------------------
CREATE TABLE order_items (
    id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id           INT UNSIGNED NOT NULL,
    product_variant_id INT UNSIGNED NOT NULL,
    product_name       VARCHAR(150) NOT NULL, -- snapshot, in case product changes later
    variant_name       VARCHAR(50)  NOT NULL,
    quantity           INT UNSIGNED NOT NULL,
    price               DECIMAL(6,2) NOT NULL, -- snapshot price at time of order
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_variant_id) REFERENCES product_variants(id)
) ENGINE=InnoDB;

-- =====================================================================
-- SEED DATA
-- =====================================================================

INSERT INTO categories (name, slug, display_order) VALUES
    ('Mains',   'mains',   1),
    ('Sides',   'sides',   2),
    ('Drinks',  'drinks',  3),
    ('Desserts','desserts',4);

-- Mains ----------------------------------------------------------------
INSERT INTO products (category_id, name, description, image, calories) VALUES
    (1, 'Pizza by the Slice',
        'Pizza is sold fresh or frozen, and whole or in portion-size slices. Methods have been developed to overcome challenges such as preventing the sauce from combining with the dough, and producing a crust that can be frozen and reheated without becoming rigid. Includes a drink and a pickle.',
        'assets/images/pizza-slice.jpg', NULL),
    (1, 'Chicken & Waffles',
        'Crispy fried chicken over a buttermilk waffle, finished with hot honey.',
        'assets/images/chicken-waffles.png', 950),
    (1, 'Meatball Sub',
        'Slow-simmered meatballs, marinara, and melted mozzarella on a toasted hoagie roll.',
        'assets/images/meatball-sub.png', 820);

-- Sides ------------------------------------------------------------------
INSERT INTO products (category_id, name, description, image, calories) VALUES
    (2, 'Garlic Knots', 'Hand-tied dough knots brushed with garlic butter and herbs.', 'assets/images/garlic-knots.png', 340),
    (2, 'Mozzarella Sticks', 'Breaded mozzarella, fried golden, served with marinara.', 'assets/images/mozz-sticks.png', 460),
    (2, 'Side Salad', 'Mixed greens, tomato, red onion, and house Italian dressing.', 'assets/images/side-salad.png', 180);

-- Drinks -------------------------------------------------------------------
INSERT INTO products (category_id, name, description, image, calories) VALUES
    (3, 'Craft Beer', 'Rotating local draft — ask your server what is on tap.', 'assets/images/craft-beer.png', 210),
    (3, 'Soda', 'Classic fountain soda, free refills.', 'assets/images/soda.png', 150),
    (3, 'Iced Tea', 'Fresh-brewed, unsweetened or sweet.', 'assets/images/iced-tea.png', 90);

-- Desserts -----------------------------------------------------------------
INSERT INTO products (category_id, name, description, image, calories) VALUES
    (4, 'Dipped Ice Cream Bar', 'Vanilla ice cream dipped in chocolate shell on a stick.', 'assets/images/ice-cream-bar.png', 950),
    (4, 'Cannoli', 'Crisp shell filled with sweet ricotta and chocolate chips.', 'assets/images/cannoli.png', 300),
    (4, 'Chocolate Lava Cake', 'Warm chocolate cake with a molten center, dusted with powdered sugar.', 'assets/images/lava-cake.png', 480);

-- Variants ------------------------------------------------------------------
-- Pizza by the Slice has three portion sizes (matches existing menu UI)
INSERT INTO product_variants (product_id, name, price, sort_order) VALUES
    (1, 'Single', 4.99, 1),
    (1, 'Double', 7.99, 2),
    (1, 'Triple', 10.99, 3);

-- All other products get a single "Regular" variant
INSERT INTO product_variants (product_id, name, price, sort_order) VALUES
    (2, 'Regular', 12.99, 1),  -- Chicken & Waffles
    (3, 'Regular', 9.99,  1),  -- Meatball Sub
    (4, 'Regular', 5.99,  1),  -- Garlic Knots
    (5, 'Regular', 6.99,  1),  -- Mozzarella Sticks
    (6, 'Regular', 4.99,  1),  -- Side Salad
    (7, 'Regular', 6.00,  1),  -- Craft Beer
    (8, 'Regular', 2.50,  1),  -- Soda
    (9, 'Regular', 2.50,  1),  -- Iced Tea
    (10,'Regular', 12.99, 1),  -- Dipped Ice Cream Bar
    (11,'Regular', 5.99,  1),  -- Cannoli
    (12,'Regular', 6.99,  1);  -- Chocolate Lava Cake
