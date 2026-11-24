<?php

class SaleRepository extends BaseRepository
{
    public function getAll()
    {
        return $this->connection->query(
            'SELECT sales.book_title AS book, sales.quantity, sales.total, sales.created_at,
                    customers.name AS customer
             FROM sales
             LEFT JOIN customers ON customers.id = sales.customer_id
             ORDER BY sales.id DESC'
        )->fetchAll();
    }

    public function add($data)
    {
        $bookId = (int) $data['book_id'];
        $quantity = (int) $data['quantity'];
        $customerId = (int) ($data['customer_id'] ?? 0);

        if ($bookId < 1 || $quantity < 1) {
            return false;
        }

        $this->connection->beginTransaction();

        try {
            $update = $this->connection->prepare(
                'UPDATE books SET stock = stock - :quantity
                 WHERE id = :id AND stock >= :available'
            );
            $update->execute([
                'quantity' => $quantity,
                'id' => $bookId,
                'available' => $quantity,
            ]);

            if ($update->rowCount() !== 1) {
                $this->connection->rollBack();
                return false;
            }

            $sale = $this->connection->prepare(
                'INSERT INTO sales (book_id, customer_id, book_title, quantity, total)
                 SELECT id, :customer_id, title, :quantity, price * :total_quantity
                 FROM books WHERE id = :id'
            );
            $sale->execute([
                'customer_id' => $customerId > 0 ? $customerId : null,
                'quantity' => $quantity,
                'total_quantity' => $quantity,
                'id' => $bookId,
            ]);

            $this->connection->commit();
            return true;
        } catch (Throwable $exception) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }

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
            'SELECT price, stock FROM books WHERE id = :id'
        );
        $book->execute(['id' => $bookId]);
        $bookDetails = $book->fetch();

        if (!$bookDetails || $quantity > (int) $bookDetails['stock']) {
            return false;
        }

        $request = $this->connection->prepare(
            'INSERT INTO purchase_requests
                (user_id, book_id, quantity, total, shipping_name, phone,
                 shipping_address, payment_method, payment_reference, payment_status)
             VALUES
                (:user_id, :book_id, :quantity, :total, :shipping_name, :phone,
                 :shipping_address, :payment_method, :payment_reference, :payment_status)'
        );
        $request->execute([
            'user_id' => $userId,
            'book_id' => $bookId,
            'quantity' => $quantity,
            'total' => (float) $bookDetails['price'] * $quantity,
            'shipping_name' => $shippingName,
            'phone' => $phone,
            'shipping_address' => $shippingAddress,
            'payment_method' => $paymentMethod,
            'payment_reference' => null,
            'payment_status' => 'not_paid',
        ]);

        return true;
    }

    public function getManageableRequests(): array
    {
        return $this->connection->query(
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
        )->fetchAll();
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
             WHERE purchase_requests.user_id = :user_id
             ORDER BY purchase_requests.created_at DESC'
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll();
    }

    public function markPaymentReceived(int $requestId): bool
    {
        if ($requestId < 1) {
            return false;
        }

        $statement = $this->connection->prepare(
            'UPDATE purchase_requests
             SET payment_status = "paid"
             WHERE id = :id AND status != "rejected" AND payment_status != "paid"'
        );
        $statement->execute(['id' => $requestId]);

        return $statement->rowCount() === 1;
    }

    public function reviewPurchaseRequest(int $requestId, string $decision): bool
    {
        if ($requestId < 1 || !in_array($decision, ['approve', 'reject'], true)) {
            return false;
        }

        $this->connection->beginTransaction();

        try {
            $request = $this->connection->prepare(
                'SELECT purchase_requests.book_id, purchase_requests.quantity,
                        purchase_requests.total, purchase_requests.status,
                        purchase_requests.user_id, books.title
                 FROM purchase_requests
                 INNER JOIN books ON books.id = purchase_requests.book_id
                 WHERE purchase_requests.id = :id
                 FOR UPDATE'
            );
            $request->execute(['id' => $requestId]);
            $purchase = $request->fetch();

            if (!$purchase || $purchase['status'] !== 'pending') {
                $this->connection->rollBack();
                return false;
            }

            if ($decision === 'approve') {
                $updateStock = $this->connection->prepare(
                    'UPDATE books SET stock = stock - :quantity
                     WHERE id = :id AND stock >= :available'
                );
                $updateStock->execute([
                    'quantity' => $purchase['quantity'],
                    'id' => $purchase['book_id'],
                    'available' => $purchase['quantity'],
                ]);

                if ($updateStock->rowCount() !== 1) {
                    $this->connection->rollBack();
                    return false;
                }

                $sale = $this->connection->prepare(
                    'INSERT INTO sales (book_id, customer_id, book_title, quantity, total)
                     VALUES (
                        :book_id,
                        (SELECT id FROM customers WHERE user_id = :user_id LIMIT 1),
                        :book_title,
                        :quantity,
                        :total
                     )'
                );
                $sale->execute([
                    'book_id' => $purchase['book_id'],
                    'user_id' => $purchase['user_id'],
                    'book_title' => $purchase['title'],
                    'quantity' => $purchase['quantity'],
                    'total' => $purchase['total'],
                ]);
            }

            $updateRequest = $this->connection->prepare(
                'UPDATE purchase_requests SET status = :status WHERE id = :id'
            );
            $updateRequest->execute([
                'status' => $decision === 'approve' ? 'approved' : 'rejected',
                'id' => $requestId,
            ]);

            $this->connection->commit();
            return true;
        } catch (Throwable $exception) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }

            throw $exception;
        }
    }
}
