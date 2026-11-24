<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Whispering Scriptorium | Bookshop</title>
    <link rel="stylesheet" href="style.css?v=<?= filemtime(__DIR__ . '/../style.css') ?>">
</head>
<body class="<?= ($_SESSION['user_role'] ?? '') === 'admin' && !empty($_SESSION['logged_in']) ? 'admin-body' : '' ?>">
