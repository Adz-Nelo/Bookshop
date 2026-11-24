<?php

$page = $_GET['page'] ?? 'welcome';

if (empty($_SESSION['logged_in'])) {
    if (!in_array($page, ['login', 'register'], true)) {
        $page = 'welcome';
    }
} elseif ($_SESSION['user_role'] === 'admin') {
    if (in_array($page, ['welcome', 'login', 'register'], true)) {
        $page = 'dashboard';
    } elseif (!in_array($page, ['dashboard', 'books', 'categories', 'customers', 'suppliers', 'sales', 'reports'], true)) {
        $page = 'dashboard';
    }
} else {
    if (!in_array($page, ['catalog', 'account', 'checkout', 'book'], true)) {
        $page = 'catalog';
    }
}
