<?php

require_once 'bootstrap.php';
require_once 'helpers.php';
require_once 'auth.php';
require_once 'actions.php';
require_once 'routing.php';
require_once 'exports.php';

if ($page === 'welcome') {
    include 'views/welcome.php';
    exit;
}

if ($page === 'login' || $page === 'register') {
    include 'views/auth.php';
    exit;
}

include 'components/head.php';
include 'components/header.php';
if ($_SESSION['user_role'] === 'admin') {
    include 'components/sidebar.php';
}

echo '<main class="main-content">';

switch ($page) {
    case 'dashboard':
        include 'modules/dashboard_display.php';
        break;
    case 'books':
        include 'modules/books_display.php';
        break;
    case 'categories':
        include 'modules/categories_display.php';
        break;
    case 'customers':
        include 'modules/customers_display.php';
        break;
    case 'suppliers':
        include 'modules/suppliers_display.php';
        break;
    case 'sales':
        include 'modules/sales_display.php';
        break;
    case 'reports':
        include 'modules/reports_display.php';
        break;
    case 'catalog':
        include 'modules/client_catalog_display.php';
        break;
    case 'book':
        include 'modules/client_book_display.php';
        break;
    case 'account':
        include 'modules/client_account_display.php';
        break;
    case 'checkout':
        include 'modules/checkout_display.php';
        break;
    default:
        echo '<h2>Page not found</h2>';
        break;
}

echo '</main>';
?>
</body>

</html>