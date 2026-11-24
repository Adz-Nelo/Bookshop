<?php

// Login
if (isset($_POST['login'])) {
    $login = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $account = $login !== '' ? $authRepo->findByLogin($login) : null;

    if ($account && password_verify($password, $account['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['logged_in'] = true;
        $_SESSION['user_id'] = (int) $account['id'];
        $_SESSION['username'] = $account['username'];
        $_SESSION['user_name'] = $account['name'];
        $_SESSION['user_email'] = $account['email'];
        $_SESSION['user_role'] = $account['role'];
        $destination = $account['role'] === 'admin' ? 'dashboard' : 'catalog';
        header('Location: index.php?page=' . $destination);
        exit;
    } else {
        $_SESSION['login_error'] = 'The username/email or password is incorrect.';
        header('Location: index.php?page=login');
        exit;
    }
}

// Register
if (isset($_POST['register'])) {
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirmation = $_POST['password_confirmation'] ?? '';

    if ($name === '' || $username === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['register_error'] = 'Enter your name, a username, and a valid email address.';
    } elseif (strlen($password) < 8) {
        $_SESSION['register_error'] = 'Your password must be at least 8 characters long.';
    } elseif ($password !== $passwordConfirmation) {
        $_SESSION['register_error'] = 'The passwords do not match.';
    } else {
        $newUserId = $authRepo->registerClient($name, $username, $email, $password);

        if ($newUserId === false) {
            $_SESSION['register_error'] = 'That username or email is already registered.';
        } else {
            session_regenerate_id(true);
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $newUserId;
            $_SESSION['username'] = $username;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role'] = 'client';
            header('Location: index.php?page=catalog');
            exit;
        }
    }

    header('Location: index.php?page=register');
    exit;
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}
