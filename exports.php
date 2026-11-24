<?php

if (isset($_GET['export'])) {
    if (($_SESSION['user_role'] ?? '') !== 'admin') {
        http_response_code(403);
        exit('Administrator access is required to export reports.');
    }

    $export = is_string($_GET['export']) ? $_GET['export'] : '';
    $dateSuffix = date('Y-m-d');
    if ($export === 'sales') {
        $rows = array_map(static function ($sale) {
            return [$sale['created_at'], $sale['book'], (int) $sale['quantity'], (float) $sale['total']];
        }, $_SESSION['sales']);
        sendCsvDownload('bookshop-sales-' . $dateSuffix . '.csv', ['Date', 'Book', 'Quantity', 'Total'], $rows);
    }
    if ($export === 'users') {
        $rows = array_map(static function ($user) {
            return [
                $user['name'],
                $user['username'],
                $user['email'],
                (int) $user['order_count'],
                (int) $user['books_purchased'],
                (float) $user['total_spent'],
                $user['created_at'],
            ];
        }, $customerRepo->getClientAccounts());
        sendCsvDownload(
            'bookshop-users-' . $dateSuffix . '.csv',
            ['Name', 'Username', 'Email', 'Orders', 'Books purchased', 'Total spent', 'Joined'],
            $rows
        );
    }
    if ($export === 'orders') {
        $rows = array_map(static function ($order) {
            return [
                $order['created_at'],
                $order['customer'],
                $order['email'],
                $order['book'],
                (int) $order['quantity'],
                (float) $order['total'],
                ucfirst($order['status']),
            ];
        }, $customerRepo->getClientOrders());
        sendCsvDownload(
            'bookshop-orders-' . $dateSuffix . '.csv',
            ['Date', 'Customer', 'Email', 'Book', 'Quantity', 'Total', 'Status'],
            $rows
        );
    }
    if ($export === 'inventory') {
        $rows = array_map(static function ($book) {
            return [
                $book['title'],
                $book['author'],
                $book['category'],
                (float) $book['price'],
                (int) $book['stock'],
                $book['description'] ?? '',
            ];
        }, $bookRepo->getAll());
        sendCsvDownload(
            'bookshop-inventory-' . $dateSuffix . '.csv',
            ['Book', 'Author', 'Category', 'Price', 'Stock', 'Description'],
            $rows
        );
    }

    http_response_code(400);
    exit('Unknown export type.');
}
