# Whispering Scriptorium

Whispering Scriptorium is a PHP and MySQL bookshop website. Administrators manage books, stock, readers, sales, orders, suppliers, and reports. Readers browse the catalog, read book details, and place cash-on-delivery order requests.

## First-time setup with XAMPP

### Requirements

- XAMPP with **Apache**, **MySQL**, and PHP
- The project folder located at `C:\xampp\htdocs\Bookshop`

### Start the website

1. Open the **XAMPP Control Panel** and start **Apache** and **MySQL**.
2. In your browser, open `http://localhost/phpmyadmin`.
3. Select **Import**, choose `C:\xampp\htdocs\Bookshop\database\bookshop.sql`, then click **Import**. This creates the `bookshop_db` database, its tables, and sample records.
4. Open `http://localhost/Bookshop/`.
5. Sign in as the administrator using:
   - **Username:** `admin`
   - **Password:** `BookshopAdmin!2026`

These are demo credentials. Do not expose the default account on a public server.

The default database settings in `config/database.php` are host `127.0.0.1`, database `bookshop_db`, user `root`, and an empty password, as in a standard local XAMPP setup. If your MySQL credentials differ, update this file. The application initializes its schema when it starts and adds the book-description field to an existing database if needed.

### Try the reader experience

1. Sign out of the admin account.
2. From the sign-in screen, create a reader account.
3. Browse the catalog, open a book to read its details, and submit an order with delivery information.
4. Reader orders use **cash on delivery only** and appear in **My Account**.
5. Sign back in as the administrator and open **Sales & orders** to approve or reject the request.

## Features

### Reader pages

- **Catalog:** Browse book covers, titles, authors, categories, prices, and stock status.
- **Book details:** View the full cover, description, price, and availability.
- **Checkout:** Submit a quantity, recipient name, phone number, and delivery address. Payment is cash on delivery.
- **My Account:** View account details, order history, order and payment status, and delivery information.

### Administrator workspace

- **Dashboard:** See catalog, stock, reader, and sales summaries, with weekly, monthly, and yearly income charts.
- **Books:** View the collection in a table and add, edit, or delete books. Books with order history are protected from deletion.
- **Categories:** Add and review book categories.
- **Users:** Review reader accounts, order histories, approved books purchased, and total spent. Keep separate records for walk-in customers.
- **Sales & orders:** Record in-store sales, approve or reject reader orders, and mark payments as received. Approving an order reduces stock and records the sale.
- **Suppliers:** Add and review supplier contact records.
- **Reports & exports:** Review inventory and download sales, order, user, and inventory reports as UTF-8 CSV files that open in Excel.
- **Navigation:** Use the admin side panel to move between sections and sign out.

### Book covers

Built-in cover art is available in `assets/books/`. When adding a book, administrators can choose a bundled cover or upload a JPG, PNG, WEBP, GIF, or SVG image. Uploaded covers are stored in `assets/books/uploads/`. The catalog and book details page display the whole cover without cropping.

## Order and payment workflow

Reader orders start as pending requests. Administrators can approve or reject them in **Sales & orders**. Approval reduces available stock and records the sale; rejection does not reduce stock. Customers pay the delivery courier in cash, and an administrator can mark payment as received. The application does not process online payments or store card details.

## Access control

- Readers can register and access the catalog, book details, checkout, and their own account page.
- Inventory, user management, sales, suppliers, reports, and exports are restricted to administrator accounts.

## Code and data structure

- Book models extend the shared `Product` parent class in `classes/`.
- Database operations are handled by repositories in `repositories/classes/`; page markup is grouped in `modules/`.
- The database enforces category-to-book, user-to-order, book-to-order, and book-to-sale relationships with foreign keys. Historical sales retain the book title if a book is removed.
