CREATE DATABASE IF NOT EXISTS bookshop_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE bookshop_db;

CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_categories_name (name)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS suppliers (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    contact VARCHAR(100) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS books (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    supplier_id INT UNSIGNED NULL,
    price DECIMAL(10, 2) NOT NULL,
    stock INT UNSIGNED NOT NULL DEFAULT 0,
    cover_image VARCHAR(255) NOT NULL DEFAULT 'assets/books/default-book.svg',
    description TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_books_category (category_id),
    KEY idx_books_supplier (supplier_id),
    CONSTRAINT fk_books_category
        FOREIGN KEY (category_id) REFERENCES categories (id),
    CONSTRAINT fk_books_supplier
        FOREIGN KEY (supplier_id) REFERENCES suppliers (id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    username VARCHAR(80) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'client') NOT NULL DEFAULT 'client',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_username (username),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS customers (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_customers_email (email),
    UNIQUE KEY uq_customers_user (user_id),
    CONSTRAINT fk_customers_user
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS sales (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    book_id INT UNSIGNED NULL,
    customer_id INT UNSIGNED NULL,
    book_title VARCHAR(255) NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_sales_book (book_id),
    KEY idx_sales_customer (customer_id),
    CONSTRAINT fk_sales_book
        FOREIGN KEY (book_id) REFERENCES books (id) ON DELETE SET NULL,
    CONSTRAINT fk_sales_customer
        FOREIGN KEY (customer_id) REFERENCES customers (id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS purchase_requests (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    book_id INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    shipping_name VARCHAR(255) NOT NULL,
    phone VARCHAR(40) NOT NULL,
    shipping_address TEXT NULL,
    payment_method ENUM('cash_on_delivery') NOT NULL,
    payment_reference VARCHAR(120) NULL,
    payment_status ENUM('not_paid', 'verification_pending', 'paid') NOT NULL DEFAULT 'not_paid',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_purchase_requests_status (status),
    KEY idx_purchase_requests_user (user_id),
    CONSTRAINT fk_purchase_requests_user
        FOREIGN KEY (user_id) REFERENCES users (id),
    CONSTRAINT fk_purchase_requests_book
        FOREIGN KEY (book_id) REFERENCES books (id)
) ENGINE=InnoDB;

INSERT IGNORE INTO categories (name) VALUES
    ('Fiction'),
    ('Self-help'),
    ('Classic'),
    ('Mystery'),
    ('Romance');

INSERT INTO books (title, author, category_id, price, stock, cover_image)
SELECT 'The Midnight Library', 'Matt Haig', categories.id, 399.00, 12, 'assets/books/midnight-library.svg'
FROM categories
WHERE categories.name = 'Fiction'
  AND NOT EXISTS (SELECT 1 FROM books WHERE title = 'The Midnight Library');

INSERT INTO books (title, author, category_id, price, stock, cover_image)
SELECT 'Atomic Habits', 'James Clear', categories.id, 520.00, 8, 'assets/books/atomic-habits.svg'
FROM categories
WHERE categories.name = 'Self-help'
  AND NOT EXISTS (SELECT 1 FROM books WHERE title = 'Atomic Habits');

INSERT INTO books (title, author, category_id, price, stock, cover_image)
SELECT 'Little Women', 'Louisa May Alcott', categories.id, 280.00, 4, 'assets/books/little-women.svg'
FROM categories
WHERE categories.name = 'Classic'
  AND NOT EXISTS (SELECT 1 FROM books WHERE title = 'Little Women');

INSERT IGNORE INTO customers (name, email) VALUES
    ('Russel Panes', 'panesrussel@gmail.com'),
    ('Katrina Mae Mati-ong', 'mationgkatrina@gmail.com');

INSERT INTO users (name, username, email, password_hash, role)
SELECT 'Bookshop Administrator', 'admin', 'admin@bookshop.local',
       '$2y$12$BHbLzFBKI/gY/IICCyHcVej/ZNQrGdjlgUsFlKDS69pzK6DZ/Ebu.',
       'admin'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'admin');

INSERT INTO suppliers (name, contact)
SELECT 'Classic Book Distributors', '09123456789'
WHERE NOT EXISTS (SELECT 1 FROM suppliers WHERE name = 'Classic Book Distributors');

INSERT INTO suppliers (name, contact)
SELECT 'Literary House Publishing', '09987654321'
WHERE NOT EXISTS (SELECT 1 FROM suppliers WHERE name = 'Literary House Publishing');

INSERT INTO sales (book_id, book_title, quantity, total)
SELECT books.id, books.title, 2, 798.00
FROM books
WHERE books.title = 'The Midnight Library'
  AND NOT EXISTS (SELECT 1 FROM sales WHERE book_title = 'The Midnight Library');

INSERT INTO sales (book_id, book_title, quantity, total)
SELECT books.id, books.title, 1, 280.00
FROM books
WHERE books.title = 'Little Women'
  AND NOT EXISTS (SELECT 1 FROM sales WHERE book_title = 'Little Women');
