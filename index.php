<?php
session_start();

if (isset($_SESSION['logged_in']) && !isset($_SESSION['user_role'])) {
    unset($_SESSION['logged_in']);
}

// 1. Require all models and repositories
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

// 2. Instantiate Repositories
$bookRepo     = new BookRepository();
$categoryRepo = new CategoryRepository();
$customerRepo = new CustomerRepository();
$supplierRepo = new SupplierRepository();
$saleRepo     = new SaleRepository();
$authRepo     = new AuthRepository();

// 3. Load persistent data for the existing screens.
$_SESSION['books'] = $bookRepo->getAll();
$_SESSION['categories'] = $categoryRepo->getAll();
$_SESSION['customers'] = $customerRepo->getAll();
$_SESSION['suppliers'] = $supplierRepo->getAll();
$_SESSION['sales'] = $saleRepo->getAll();

// 4. Authentication
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

// 5. Restrict management actions to administrators.
$adminActions = [
    'add_book', 'add_category', 'add_customer', 'add_supplier', 'add_sale',
    'review_purchase', 'mark_payment_received', 'update_book', 'delete_book',
];
foreach ($adminActions as $adminAction) {
    if (isset($_POST[$adminAction]) && ($_SESSION['user_role'] ?? '') !== 'admin') {
        header('Location: index.php?page=catalog');
        exit;
    }
}

if (isset($_POST['request_purchase'])) {
    if (($_SESSION['user_role'] ?? '') !== 'client') {
        header('Location: index.php?page=login');
        exit;
    }

    $bookId = filter_var($_POST['book_id'] ?? '', FILTER_VALIDATE_INT);
    $quantity = filter_var($_POST['quantity'] ?? '', FILTER_VALIDATE_INT);
    $bookId = $bookId === false ? 0 : (int) $bookId;
    $quantity = $quantity === false ? 0 : (int) $quantity;
    $shippingName = is_string($_POST['shipping_name'] ?? null) ? trim($_POST['shipping_name']) : '';
    $phone = is_string($_POST['phone'] ?? null) ? trim($_POST['phone']) : '';
    $shippingAddress = is_string($_POST['shipping_address'] ?? null) ? trim($_POST['shipping_address']) : '';
    $paymentMethod = is_string($_POST['payment_method'] ?? null) ? $_POST['payment_method'] : '';
    $checkoutDetails = [
        'shipping_name' => $shippingName,
        'phone' => $phone,
        'shipping_address' => $shippingAddress,
        'payment_method' => $paymentMethod,
    ];

    if (
        $shippingName === '' ||
        $phone === '' ||
        $shippingAddress === ''
    ) {
        $_SESSION['purchase_error'] = 'Enter your name, phone number, and delivery address.';
    } elseif (
        strlen($shippingName) > 255 ||
        strlen($phone) > 40 ||
        strlen($shippingAddress) > 1500
    ) {
        $_SESSION['purchase_error'] = 'One or more checkout fields are too long.';
    } elseif ($paymentMethod !== 'cash_on_delivery') {
        $_SESSION['purchase_error'] = 'Cash on delivery is the only supported payment method.';
    } elseif ($saleRepo->createPurchaseRequest(
        (int) $_SESSION['user_id'],
        $bookId,
        $quantity,
        $checkoutDetails
    )) {
        $_SESSION['purchase_notice'] = 'Your purchase request has been sent to the bookshop.';
        header('Location: index.php?page=account');
        exit;
    } else {
        $_SESSION['purchase_error'] = 'That quantity is unavailable. Please check the stock and try again.';
    }
    header('Location: index.php?page=checkout&book_id=' . $bookId);
    exit;
}

if (isset($_POST['review_purchase'])) {
    $requestId = filter_var($_POST['request_id'] ?? '', FILTER_VALIDATE_INT);
    $requestId = $requestId === false ? 0 : (int) $requestId;
    $decision = is_string($_POST['decision'] ?? null) ? $_POST['decision'] : '';
    if ($saleRepo->reviewPurchaseRequest($requestId, $decision)) {
        $_SESSION['purchase_notice'] = $decision === 'approve'
            ? 'Purchase request approved and stock updated.'
            : 'Purchase request rejected.';
    } else {
        $_SESSION['purchase_error'] = 'Could not process the request. It may already be handled or there may not be enough stock.';
    }
    header('Location: index.php?page=sales');
    exit;
}

if (isset($_POST['mark_payment_received'])) {
    $requestId = filter_var($_POST['request_id'] ?? '', FILTER_VALIDATE_INT);
    $requestId = $requestId === false ? 0 : (int) $requestId;
    if ($saleRepo->markPaymentReceived($requestId)) {
        $_SESSION['purchase_notice'] = 'Payment marked as received.';
    } else {
        $_SESSION['purchase_error'] = 'Could not update payment status for that order.';
    }
    header('Location: index.php?page=sales');
    exit;
}

function resolveBookCoverImage(array $postData): string
{
    if (!empty($_FILES['cover_file']['name']) && is_uploaded_file($_FILES['cover_file']['tmp_name'])) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = $finfo ? finfo_file($finfo, $_FILES['cover_file']['tmp_name']) : null;
        if ($finfo) {
            finfo_close($finfo);
        }

        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml'];
        if (!in_array($mimeType, $allowedMimeTypes, true)) {
            throw new InvalidArgumentException('Upload a JPG, PNG, WEBP, GIF, or SVG cover image.');
        }

        $uploadDirectory = __DIR__ . '/assets/books/uploads';
        if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0777, true) && !is_dir($uploadDirectory)) {
            throw new RuntimeException('Unable to create the uploads folder for book covers.');
        }

        $extension = strtolower(pathinfo($_FILES['cover_file']['name'], PATHINFO_EXTENSION));
        if ($extension === '') {
            if ($mimeType === 'image/jpeg') {
                $extension = 'jpg';
            } elseif ($mimeType === 'image/png') {
                $extension = 'png';
            } elseif ($mimeType === 'image/webp') {
                $extension = 'webp';
            } elseif ($mimeType === 'image/gif') {
                $extension = 'gif';
            } elseif ($mimeType === 'image/svg+xml') {
                $extension = 'svg';
            } else {
                $extension = 'png';
            }
        }

        $fileName = 'custom-book-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
        $destination = $uploadDirectory . '/' . $fileName;

        if (!move_uploaded_file($_FILES['cover_file']['tmp_name'], $destination)) {
            throw new RuntimeException('The uploaded cover image could not be saved.');
        }

        return 'assets/books/uploads/' . $fileName;
    }

    $coverImage = trim((string) ($postData['cover_image'] ?? ''));
    $coverImage = str_replace('\\', '/', $coverImage);

    if ($coverImage === '') {
        return 'assets/books/default-book.svg';
    }

    if (strpos($coverImage, '../') !== false || $coverImage[0] === '/' || strpos($coverImage, 'assets/') !== 0) {
        return 'assets/books/default-book.svg';
    }

    return $coverImage;
}

function sendCsvDownload(string $filename, array $headers, array $rows): void
{
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('X-Content-Type-Options: nosniff');

    $output = fopen('php://output', 'wb');
    if ($output === false) {
        throw new RuntimeException('Unable to create the requested export.');
    }

    fwrite($output, "\xEF\xBB\xBF");
    fputcsv($output, $headers, ',', '"', '\\');
    foreach ($rows as $row) {
        $safeRow = array_map(static function ($value) {
            if (is_string($value) && preg_match('/^[\t\r ]*[=+\-@]/', $value)) {
                return "'" . $value;
            }

            return $value;
        }, $row);
        fputcsv($output, $safeRow, ',', '"', '\\');
    }

    fclose($output);
    exit;
}

// 6. Form Actions Using Repositories
if (isset($_POST['add_book'])) {
    try {
        $_POST['cover_image'] = resolveBookCoverImage($_POST);
        $bookRepo->add($_POST);
    } catch (Throwable $exception) {
        $_SESSION['book_error'] = $exception->getMessage();
    }
    header('Location: index.php?page=books');
    exit;
}
if (isset($_POST['update_book'])) {
    $bookId = filter_var($_POST['book_id'] ?? '', FILTER_VALIDATE_INT);
    try {
        if ($bookId === false || !$bookRepo->update((int) $bookId, $_POST)) {
            $_SESSION['book_error'] = 'The book could not be updated. Check that it still exists.';
        } else {
            $_SESSION['book_notice'] = 'Book updated.';
        }
    } catch (Throwable $exception) {
        $_SESSION['book_error'] = $exception->getMessage();
    }
    header('Location: index.php?page=books');
    exit;
}
if (isset($_POST['delete_book'])) {
    $bookId = filter_var($_POST['book_id'] ?? '', FILTER_VALIDATE_INT);
    if ($bookId === false || !$bookRepo->delete((int) $bookId)) {
        $_SESSION['book_error'] = 'This book cannot be deleted while it has order history.';
    } else {
        $_SESSION['book_notice'] = 'Book deleted.';
    }
    header('Location: index.php?page=books');
    exit;
}
if (isset($_POST['add_category'])) {
    $categoryRepo->add($_POST['category'] ?? '');
    header('Location: index.php?page=categories');
    exit;
}
if (isset($_POST['add_customer'])) {
    $customerRepo->add($_POST);
    header('Location: index.php?page=customers');
    exit;
}
if (isset($_POST['add_supplier'])) {
    $supplierRepo->add($_POST);
    header('Location: index.php?page=suppliers');
    exit;
}
if (isset($_POST['add_sale'])) {
    $bookId = (int) ($_POST['book_id'] ?? 0);
    $quantity  = (int) $_POST['quantity'];
    $customerId = (int) ($_POST['customer_id'] ?? 0);

    if (!$saleRepo->add(['book_id' => $bookId, 'quantity' => $quantity, 'customer_id' => $customerId])) {
        $_SESSION['sale_error'] = 'The selected book does not have enough stock.';
    }
    header('Location: index.php?page=sales');
    exit;
}

// 7. Page Routing
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

// Welcome Screen
if ($page === 'welcome') {
    include 'components/head.php';
    ?>
    <div class="original-login-screen">
        <a href="index.php?page=login" class="original-login-button" aria-label="Log In"></a>
        <a href="index.php?page=register" class="welcome-register">New here? Create a reader account</a>
    </div>
    </body>
    </html>
    <?php
    exit;
}

// Login and client registration screens
if ($page === 'login' || $page === 'register') {
    include 'components/head.php';
    ?>
    <div class="original-login-screen">
        <div class="login-card">
            <?php if ($page === 'register'): ?>
                <h1>Join Our Readers</h1>
                <p>Create an account to explore our collection.</p>
            <?php else: ?>
                <h1>Log In</h1>
                <p>Welcome to Whispering Scriptorium</p>
            <?php endif; ?>
            <?php if (isset($_SESSION[$page === 'register' ? 'register_error' : 'login_error'])): ?>
                <div class="login-error">
                    <?= htmlspecialchars($_SESSION[$page === 'register' ? 'register_error' : 'login_error']) ?>
                </div>
                <?php unset($_SESSION[$page === 'register' ? 'register_error' : 'login_error']); ?>
            <?php endif; ?>
            <form method="POST" action="index.php?page=<?= htmlspecialchars($page) ?>">
                <?php if ($page === 'register'): ?>
                    <input type="text" name="name" placeholder="Full name" required autocomplete="name" maxlength="255">
                    <input type="text" name="username" placeholder="Username" required autocomplete="username" maxlength="80">
                    <input type="email" name="email" placeholder="Email address" required autocomplete="email" maxlength="255">
                    <input type="password" name="password" placeholder="Password (8+ characters)" required autocomplete="new-password" minlength="8">
                    <input type="password" name="password_confirmation" placeholder="Confirm password" required autocomplete="new-password" minlength="8">
                    <button type="submit" name="register">Create Client Account</button>
                <?php else: ?>
                    <input type="text" name="username" placeholder="Username or email" required autocomplete="username">
                    <input type="password" name="password" placeholder="Password" required autocomplete="current-password">
                    <button type="submit" name="login">Log In</button>
                <?php endif; ?>
            </form>
            <a class="back-to-welcome" href="index.php?page=<?= $page === 'register' ? 'login' : 'register' ?>">
                <?= $page === 'register' ? 'Already have an account? Log in' : 'New reader? Create an account' ?>
            </a>
        </div>
    </div>
    </body>
    </html>
    <?php
    exit;
}

// 8. Render Modules
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