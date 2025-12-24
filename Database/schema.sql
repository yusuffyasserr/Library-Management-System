-- Database/schema.sql
CREATE DATABASE IF NOT EXISTS bookstore_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE bookstore_db;

-- ======================
-- USERS
-- ======================
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  full_name VARCHAR(100) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  role ENUM('customer','admin') NOT NULL DEFAULT 'customer',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ======================
-- PUBLISHERS
-- ======================
CREATE TABLE IF NOT EXISTS publishers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL UNIQUE,
  phone VARCHAR(30),
  address VARCHAR(200)
);

-- ======================
-- BOOKS
-- ======================
CREATE TABLE IF NOT EXISTS books (
  id INT AUTO_INCREMENT PRIMARY KEY,
  isbn VARCHAR(20) NOT NULL UNIQUE,
  title VARCHAR(200) NOT NULL,
  authors VARCHAR(200) NOT NULL,
  category VARCHAR(80) NOT NULL,
  published_year INT,
  price INT NOT NULL,
  stock INT NOT NULL DEFAULT 0,
  publisher_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_books_publisher
    FOREIGN KEY (publisher_id) REFERENCES publishers(id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
);

-- ======================
-- ORDERS
-- ======================
CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  total INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_orders_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
);

-- ======================
-- ORDER ITEMS
-- ======================
CREATE TABLE IF NOT EXISTS order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  book_id INT NOT NULL,
  qty INT NOT NULL,
  unit_price INT NOT NULL,
  subtotal INT NOT NULL,
  CONSTRAINT fk_items_order
    FOREIGN KEY (order_id) REFERENCES orders(id)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT fk_items_book
    FOREIGN KEY (book_id) REFERENCES books(id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
);

-- ======================
-- REPLENISHMENTS
-- ======================
CREATE TABLE IF NOT EXISTS replenishments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  book_id INT NOT NULL,
  qty INT NOT NULL,
  status ENUM('Pending','Confirmed') NOT NULL DEFAULT 'Pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  confirmed_at TIMESTAMP NULL,
  CONSTRAINT fk_repl_book
    FOREIGN KEY (book_id) REFERENCES books(id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
);
