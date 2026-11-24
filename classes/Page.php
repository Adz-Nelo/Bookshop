<?php

class Page {

    public static function display($page) {

        $pages = [
            'dashboard' => 'dashboard_display.php',
            'books' => 'books_display.php',
            'categories' => 'categories_display.php',
            'customers' => 'customer_display.php',
            'sales' => 'sales_display.php',
            'suppliers' => 'suppliers_display.php',
            'reports' => 'reports_display.php'
        ];

        if (isset($pages[$page])) {
            include 'modules/' . $pages[$page];
        } else {
            include 'modules/dashboard_display.php';
        }
    }
}
?>