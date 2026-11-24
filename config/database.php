<?php

function initializeBookshopSchema(PDO $connection): void
{
    $connection->exec(
        'CREATE TABLE IF NOT EXISTS categories (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY uq_categories_name (name)
        ) ENGINE=InnoDB'
    );

    $connection->exec(
        'CREATE TABLE IF NOT EXISTS suppliers (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            contact VARCHAR(100) NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB'
    );

    $connection->exec(
        'CREATE TABLE IF NOT EXISTS books (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            title VARCHAR(255) NOT NULL,
            author VARCHAR(255) NOT NULL,
            category_id INT UNSIGNED NOT NULL,
            supplier_id INT UNSIGNED NULL,
            price DECIMAL(10, 2) NOT NULL,
            stock INT UNSIGNED NOT NULL DEFAULT 0,
            cover_image VARCHAR(255) NOT NULL DEFAULT "assets/books/default-book.svg",
            description TEXT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_books_category (category_id),
            KEY idx_books_supplier (supplier_id),
            CONSTRAINT fk_books_category FOREIGN KEY (category_id) REFERENCES categories (id),
            CONSTRAINT fk_books_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers (id) ON DELETE SET NULL
        ) ENGINE=InnoDB'
    );

    $coverImageColumn = $connection->query("SHOW COLUMNS FROM books LIKE 'cover_image'");
    if ($coverImageColumn->fetch() === false) {
        $connection->exec(
            "ALTER TABLE books ADD COLUMN cover_image VARCHAR(255) NOT NULL DEFAULT 'assets/books/default-book.svg'"
        );
    }

    $descriptionColumn = $connection->query("SHOW COLUMNS FROM books LIKE 'description'");
    if ($descriptionColumn->fetch() === false) {
        $connection->exec('ALTER TABLE books ADD COLUMN description TEXT NULL');
    }

    $supplierColumn = $connection->query("SHOW COLUMNS FROM books LIKE 'supplier_id'");
    if ($supplierColumn->fetch() === false) {
        $connection->exec(
            'ALTER TABLE books ADD COLUMN supplier_id INT UNSIGNED NULL,
             ADD KEY idx_books_supplier (supplier_id),
             ADD CONSTRAINT fk_books_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers (id) ON DELETE SET NULL'
        );
    }

    $connection->exec(
        'CREATE TABLE IF NOT EXISTS customers (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id INT UNSIGNED NULL,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_customers_email (email),
            UNIQUE KEY uq_customers_user (user_id)
        ) ENGINE=InnoDB'
    );

    $connection->exec(
        'CREATE TABLE IF NOT EXISTS users (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            username VARCHAR(80) NOT NULL,
            email VARCHAR(255) NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            role ENUM("admin", "client") NOT NULL DEFAULT "client",
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_users_username (username),
            UNIQUE KEY uq_users_email (email)
        ) ENGINE=InnoDB'
    );

    $customerUserColumn = $connection->query("SHOW COLUMNS FROM customers LIKE 'user_id'");
    if ($customerUserColumn->fetch() === false) {
        $connection->exec(
            'ALTER TABLE customers ADD COLUMN user_id INT UNSIGNED NULL,
             ADD UNIQUE KEY uq_customers_user (user_id),
             ADD CONSTRAINT fk_customers_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL'
        );
    } else {
        $customerUserConstraint = $connection->query(
            "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'customers'
             AND CONSTRAINT_NAME = 'fk_customers_user'"
        );
        if ($customerUserConstraint->fetch() === false) {
            $connection->exec(
                'ALTER TABLE customers ADD CONSTRAINT fk_customers_user
                 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL'
            );
        }
    }

    $connection->exec(
        'UPDATE customers
         INNER JOIN users ON users.email = customers.email
         SET customers.user_id = users.id
         WHERE customers.user_id IS NULL'
    );

    $connection->exec(
        'CREATE TABLE IF NOT EXISTS sales (
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
            CONSTRAINT fk_sales_book FOREIGN KEY (book_id) REFERENCES books (id) ON DELETE SET NULL,
            CONSTRAINT fk_sales_customer FOREIGN KEY (customer_id) REFERENCES customers (id) ON DELETE SET NULL
        ) ENGINE=InnoDB'
    );

    $salesBookColumn = $connection->query("SHOW COLUMNS FROM sales LIKE 'book_id'");
    if ($salesBookColumn->fetch() === false) {
        $connection->exec(
            'ALTER TABLE sales ADD COLUMN book_id INT UNSIGNED NULL,
             ADD KEY idx_sales_book (book_id),
             ADD CONSTRAINT fk_sales_book FOREIGN KEY (book_id) REFERENCES books (id) ON DELETE SET NULL'
        );
    }

    $salesCustomerColumn = $connection->query("SHOW COLUMNS FROM sales LIKE 'customer_id'");
    if ($salesCustomerColumn->fetch() === false) {
        $connection->exec(
            'ALTER TABLE sales ADD COLUMN customer_id INT UNSIGNED NULL,
             ADD KEY idx_sales_customer (customer_id),
             ADD CONSTRAINT fk_sales_customer FOREIGN KEY (customer_id) REFERENCES customers (id) ON DELETE SET NULL'
        );
    }
    $connection->exec(
        'UPDATE sales INNER JOIN books ON books.title = sales.book_title
         SET sales.book_id = books.id WHERE sales.book_id IS NULL'
    );

    $connection->exec(
        'CREATE TABLE IF NOT EXISTS purchase_requests (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id INT UNSIGNED NOT NULL,
            book_id INT UNSIGNED NOT NULL,
            quantity INT UNSIGNED NOT NULL,
            total DECIMAL(10, 2) NOT NULL,
            status ENUM("pending", "approved", "rejected") NOT NULL DEFAULT "pending",
            shipping_name VARCHAR(255) NOT NULL,
            phone VARCHAR(40) NOT NULL,
            shipping_address TEXT NULL,
            payment_method ENUM("cash_on_delivery") NOT NULL,
            payment_reference VARCHAR(120) NULL,
            payment_status ENUM("not_paid", "verification_pending", "paid") NOT NULL DEFAULT "not_paid",
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_purchase_requests_status (status),
            KEY idx_purchase_requests_user (user_id),
            CONSTRAINT fk_purchase_requests_user
                FOREIGN KEY (user_id) REFERENCES users (id),
            CONSTRAINT fk_purchase_requests_book
                FOREIGN KEY (book_id) REFERENCES books (id)
        ) ENGINE=InnoDB'
    );

    $purchaseRequestColumns = [
        'shipping_name' => "VARCHAR(255) NOT NULL DEFAULT ''",
        'phone' => "VARCHAR(40) NOT NULL DEFAULT ''",
        'shipping_address' => 'TEXT NULL',
        'payment_method' => "ENUM('cash_on_delivery') NOT NULL DEFAULT 'cash_on_delivery'",
        'payment_reference' => 'VARCHAR(120) NULL',
        'payment_status' => "ENUM('not_paid', 'verification_pending', 'paid') NOT NULL DEFAULT 'not_paid'",
    ];
    foreach ($purchaseRequestColumns as $column => $definition) {
        $existingColumn = $connection->query(
            'SHOW COLUMNS FROM purchase_requests LIKE ' . $connection->quote($column)
        );
        if ($existingColumn->fetch() === false) {
            $connection->exec(
                'ALTER TABLE purchase_requests ADD COLUMN ' . $column . ' ' . $definition
            );
        }
    }

    $connection->exec(
        'INSERT INTO categories (name) VALUES ("Fiction"), ("Self-help"), ("Classic"), ("Mystery"), ("Romance")
         ON DUPLICATE KEY UPDATE name = VALUES(name)'
    );

    $connection->exec(
        'INSERT INTO books (title, author, category_id, price, stock, cover_image)
         SELECT "The Midnight Library", "Matt Haig", categories.id, 399.00, 12, "assets/books/midnight-library.svg"
         FROM categories
         WHERE categories.name = "Fiction"
         AND NOT EXISTS (SELECT 1 FROM books WHERE title = "The Midnight Library")'
    );

    $connection->exec(
        'INSERT INTO books (title, author, category_id, price, stock, cover_image)
         SELECT "Atomic Habits", "James Clear", categories.id, 520.00, 8, "assets/books/atomic-habits.svg"
         FROM categories
         WHERE categories.name = "Self-help"
         AND NOT EXISTS (SELECT 1 FROM books WHERE title = "Atomic Habits")'
    );

    $connection->exec(
        'INSERT INTO books (title, author, category_id, price, stock, cover_image)
         SELECT "Little Women", "Louisa May Alcott", categories.id, 280.00, 4, "assets/books/little-women.svg"
         FROM categories
         WHERE categories.name = "Classic"
         AND NOT EXISTS (SELECT 1 FROM books WHERE title = "Little Women")'
    );

    $connection->exec(
        'INSERT INTO users (name, username, email, password_hash, role)
         SELECT "Bookshop Administrator", "admin", "admin@bookshop.local", "$2y$12$BHbLzFBKI/gY/IICCyHcVej/ZNQrGdjlgUsFlKDS69pzK6DZ/Ebu.", "admin"
         WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = "admin")'
    );

    $connection->exec(
        'INSERT INTO suppliers (name, contact)
         SELECT "Classic Book Distributors", "09123456789"
         WHERE NOT EXISTS (SELECT 1 FROM suppliers WHERE name = "Classic Book Distributors")'
    );

    $connection->exec(
        'INSERT INTO suppliers (name, contact)
         SELECT "Literary House Publishing", "09987654321"
         WHERE NOT EXISTS (SELECT 1 FROM suppliers WHERE name = "Literary House Publishing")'
    );

    $connection->exec(
        'INSERT INTO sales (book_id, book_title, quantity, total)
         SELECT books.id, books.title, 2, 798.00 FROM books
         WHERE books.title = "The Midnight Library"
         AND NOT EXISTS (SELECT 1 FROM sales WHERE book_title = "The Midnight Library")'
    );

    $connection->exec(
        'INSERT INTO sales (book_id, book_title, quantity, total)
         SELECT books.id, books.title, 1, 280.00 FROM books
         WHERE books.title = "Little Women"
         AND NOT EXISTS (SELECT 1 FROM sales WHERE book_title = "Little Women")'
    );
}

function getDatabaseConnection()
{
    static $connection = null;

    if ($connection instanceof PDO) {
        return $connection;
    }

    $host = '127.0.0.1';
    $database = 'bookshop_db';
    $username = 'root';
    $password = '';

    try {
        $serverConnection = new PDO("mysql:host={$host};charset=utf8mb4", $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        $serverConnection->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        $connection = new PDO("mysql:host={$host};dbname={$database};charset=utf8mb4", $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        initializeBookshopSchema($connection);
    } catch (PDOException $exception) {
        http_response_code(500);
        exit(
            'Unable to connect to MySQL. Start MySQL in XAMPP and ensure the root user is available. '
            . 'Original error: ' . htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8')
        );
    }

    return $connection;
}
