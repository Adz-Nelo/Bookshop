<?php

// Restrict management actions to administrators
$adminActions = [
    'add_book',
    'add_category',
    'add_customer',
    'add_supplier',
    'add_sale',
    'review_purchase',
    'mark_payment_received',
    'update_book',
    'delete_book',
];
foreach ($adminActions as $adminAction) {
    if (isset($_POST[$adminAction]) && ($_SESSION['user_role'] ?? '') !== 'admin') {
        header('Location: index.php?page=catalog');
        exit;
    }
}

// Request purchase
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

// Review purchase
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

// Mark payment received
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

// Add book
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

// Update book
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

// Delete book
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

// Add category
if (isset($_POST['add_category'])) {
    $categoryRepo->add($_POST['category'] ?? '');
    header('Location: index.php?page=categories');
    exit;
}

// Add customer
if (isset($_POST['add_customer'])) {
    $customerRepo->add($_POST);
    header('Location: index.php?page=customers');
    exit;
}

// Add supplier
if (isset($_POST['add_supplier'])) {
    $supplierRepo->add($_POST);
    header('Location: index.php?page=suppliers');
    exit;
}

// Add sale
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
