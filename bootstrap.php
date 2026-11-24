<?php

session_start();

if (isset($_SESSION['logged_in']) && !isset($_SESSION['user_role'])) {
    unset($_SESSION['logged_in']);
}

// Require all models and repositories
require_once 'classes/Product.php';
require_once 'classes/Book.php';
require_once 'classes/Category.php';
require_once 'classes/Customer.php';
require_once 'classes/Sale.php';
require_once 'classes/Supplier.php';
require_once 'classes/Page.php';

require_once __DIR__ . '/repositories/classes/BaseRepository.php';
require_once __DIR__ . '/repositories/classes/BookRepositorySupport.php';
require_once __DIR__ . '/repositories/classes/BookRepository.php';
require_once __DIR__ . '/repositories/classes/CategoryRepository.php';
require_once __DIR__ . '/repositories/classes/CustomerRepository.php';
require_once __DIR__ . '/repositories/classes/SupplierRepository.php';
require_once __DIR__ . '/repositories/classes/SaleRepository.php';
require_once __DIR__ . '/repositories/classes/AuthRepository.php';
require_once __DIR__ . '/config/database.php';

// Instantiate Repositories
$bookRepo     = new BookRepository();
$categoryRepo = new CategoryRepository();
$customerRepo = new CustomerRepository();
$supplierRepo = new SupplierRepository();
$saleRepo     = new SaleRepository();
$authRepo     = new AuthRepository();

// Load persistent data for the existing screens
$_SESSION['books'] = $bookRepo->getAll();
$_SESSION['categories'] = $categoryRepo->getAll();
$_SESSION['customers'] = $customerRepo->getAll();
$_SESSION['suppliers'] = $supplierRepo->getAll();
$_SESSION['sales'] = $saleRepo->getAll();
