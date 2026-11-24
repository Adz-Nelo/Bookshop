<?php

class SaleRepository extends BaseRepository
{
    public function getAll()
    {
        $result = $this->connection->query(
            'SELECT sales.book_title AS book, sales.quantity, sales.total, sales.created_at,
                    customers.name AS customer
             FROM sales
             LEFT JOIN customers ON customers.id = sales.customer_id
             ORDER BY sales.id DESC'
        );

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function add($data)
    {
        $bookId = (int) $data['book_id'];
        $quantity = (int) $data['quantity'];
        $customerId = (int) ($data['customer_id'] ?? 0);

        if ($bookId < 1 || $quantity < 1) {
            return false;
        }

        $this->connection->autocommit(FALSE);

        try {
            $update = $this->connection->prepare(
                'UPDATE books SET stock = stock - ?
                 WHERE id = ? AND stock >= ?'
            );
            $update->bind_param('iii', $quantity, $bookId, $quantity);
            $update->execute();
            $affected = $update->affected_rows;
            $update->close();

            if ($affected !== 1) {
                $this->connection->rollback();
                $this->connection->autocommit(TRUE);
                return false;
            }

            $sale = $this->connection->prepare(
                'INSERT INTO sales (book_id, customer_id, book_title, quantity, total)
                 SELECT id, ?, title, ?, price * ?
                 FROM books WHERE id = ?'
            );
            $nullableCustomerId = $customerId > 0 ? $customerId : null;
            $sale->bind_param('iiii', $nullableCustomerId, $quantity, $quantity, $bookId);
            $sale->execute();
            $sale->close();

            $this->connection->commit();
            $this->connection->autocommit(TRUE);
            return true;
        } catch (Throwable $exception) {
            $this->connection->rollback();
            $this->connection->autocommit(TRUE);

            throw $exception;
        }
    }

    public function createPurchaseRequest(int $userId, int $bookId, int $quantity, array $checkoutDetails): bool
    {
        if ($userId < 1 || $bookId < 1 || $quantity < 1) {
            return false;
        }

        $shippingName = trim((string) ($checkoutDetails['shipping_name'] ?? ''));
        $phone = trim((string) ($checkoutDetails['phone'] ?? ''));
        $shippingAddress = trim((string) ($checkoutDetails['shipping_address'] ?? ''));
        $paymentMethod = (string) ($checkoutDetails['payment_method'] ?? '');

        if (
            $shippingName === '' ||
            strlen($shippingName) > 255 ||
            $phone === '' ||
            strlen($phone) > 40 ||
            $shippingAddress === '' ||
            strlen($shippingAddress) > 1500 ||
            $paymentMethod !== 'cash_on_delivery'
        ) {
            return false;
        }

        $book = $this->connection->prepare(
            'SELECT price, stock FROM books WHERE id = ?'
        );
        $book->bind_param('i', $bookId);
        $book->execute();
        $bookResult = $book->get_result();
        $bookDetails = $bookResult->fetch_assoc();
        $book->close();

        if (!$bookDetails || $quantity > (int) $bookDetails['stock']) {
            return false;
        }

        $request = $this->connection->prepare(
            'INSERT INTO purchase_requests
                (user_id, book_id, quantity, total, shipping_name, phone,
                 shipping_address, payment_method, payment_reference, payment_status)
             VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $total = (float) $bookDetails['price'] * $quantity;
        $nullRef = null;
        $request->bind_param('iidsssssss', $userId, $bookId, $quantity, $total,
            $shippingName, $phone, $shippingAddress, $paymentMethod, $nullRef, 'not_paid');
        $request->execute();
        $request->close();

        return true;
    }

    public function getManageableRequests(): array
    {
        $result = $this->connection->query(
            'SELECT purchase_requests.id, users.name AS customer, users.email,
                    books.title AS book, purchase_requests.quantity,
                    purchase_requests.total, purchase_requests.status,
                    purchase_requests.shipping_name, purchase_requests.phone,
                    purchase_requests.shipping_address, purchase_requests.payment_method,
                    purchase_requests.payment_reference, purchase_requests.payment_status,
                    purchase_requests.created_at
             FROM purchase_requests
             INNER JOIN users ON users.id = purchase_requests.user_id
             INNER JOIN books ON books.id = purchase_requests.book_id
             WHERE purchase_requests.status IN ("pending", "approved")
             ORDER BY purchase_requests.created_at'
        );

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getRequestsForUser(int $userId): array
    {
        $statement = $this->connection->prepare(
            'SELECT books.title AS book, purchase_requests.quantity,
                    purchase_requests.total, purchase_requests.status,
                    purchase_requests.shipping_name, purchase_requests.phone,
                    purchase_requests.shipping_address, purchase_requests.payment_method,
                    purchase_requests.payment_reference, purchase_requests.payment_status,
                    purchase_requests.created_at
             FROM purchase_requests
             INNER JOIN books ON books.id = purchase_requests.book_id
             WHERE purchase_requests.user_id = ?
             ORDER BY purchase_requests.created_at DESC'
        );
        $statement->bind_param('i', $userId);
        $statement->execute();
        $result = $statement->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $statement->close();

        return $rows;
    }

    public function markPaymentReceived(int $requestId): bool
    {
        if ($requestId < 1) {
            return false;
        }

        $statement = $this->connection->prepare(
            'UPDATE purchase_requests
             SET payment_status = "paid"
             WHERE id = ? AND status != "rejected" AND payment_status != "paid"'
        );
        $statement->bind_param('i', $requestId);
        $statement->execute();
        $affected = $statement->affected_rows;
        $statement->close();

        return $affected === 1;
    }

    public function reviewPurchaseRequest(int $requestId, string $decision): bool
    {
        if ($requestId < 1 || !in_array($decision, ['approve', 'reject'], true)) {
            return false;
        }

        $this->connection->autocommit(FALSE);

        try {
            $request = $this->connection->prepare(
                'SELECT purchase_requests.book_id, purchase_requests.quantity,
                        purchase_requests.total, purchase_requests.status,
                        purchase_requests.user_id, books.title
                 FROM purchase_requests
                 INNER JOIN books ON books.id = purchase_requests.book_id
                 WHERE purchase_requests.id = ?
                 FOR UPDATE'
            );
            $request->bind_param('i', $requestId);
            $request->execute();
            $result = $request->get_result();
            $purchase = $result->fetch_assoc();
            $request->close();

            if (!$purchase || $purchase['status'] !== 'pending') {
                $this->connection->rollback();
                $this->connection->autocommit(TRUE);
                return false;
            }

            if ($decision === 'approve') {
                $updateStock = $this->connection->prepare(
                    'UPDATE books SET stock = stock - ?
                     WHERE id = ? AND stock >= ?'
                );
                $updateStock->bind_param('iii', $purchase['quantity'], $purchase['book_id'], $purchase['quantity']);
                $updateStock->execute();
                $stockAffected = $updateStock->affected_rows;
                $updateStock->close();

                if ($stockAffected !== 1) {
                    $this->connection->rollback();
                    $this->connection->autocommit(TRUE);
                    return false;
                }

                $sale = $this->connection->prepare(
                    'INSERT INTO sales (book_id, customer_id, book_title, quantity, total)
                     VALUES (
                         ?,
                         (SELECT id FROM customers WHERE user_id = ? LIMIT 1),
                         ?,
                         ?,
                         ?
                     )'
                );
                $sale->bind_param('iisid', $purchase['book_id'], $purchase['user_id'],
                    $purchase['title'], $purchase['quantity'], $purchase['total']);
                $sale->execute();
                $sale->close();
            }

            $updateRequest = $this->connection->prepare(
                'UPDATE purchase_requests SET status = ? WHERE id = ?'
            );
            $status = $decision === 'approve' ? 'approved' : 'rejected';
            $updateRequest->bind_param('si', $status, $requestId);
            $updateRequest->execute();
            $updateRequest->close();

            $this->connection->commit();
            $this->connection->autocommit(TRUE);
            return true;
        } catch (Throwable $exception) {
            $this->connection->rollback();
            $this->connection->autocommit(TRUE);

            throw $exception;
        }
    }
}
