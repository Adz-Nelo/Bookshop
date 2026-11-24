<?php

class CustomerRepository extends BaseRepository
{
    public function getClientAccounts(): array
    {
        return $this->connection->query(
            'SELECT users.id, users.name, users.username, users.email, users.created_at,
                    COUNT(purchase_requests.id) AS order_count,
                    COALESCE(SUM(
                        CASE WHEN purchase_requests.status = "approved"
                             THEN purchase_requests.quantity ELSE 0 END
                    ), 0) AS books_purchased,
                    COALESCE(SUM(
                        CASE WHEN purchase_requests.status = "approved"
                             THEN purchase_requests.total ELSE 0 END
                    ), 0) AS total_spent
             FROM users
             LEFT JOIN purchase_requests ON purchase_requests.user_id = users.id
             WHERE users.role = "client"
             GROUP BY users.id, users.name, users.username, users.email, users.created_at
             ORDER BY users.name'
        )->fetchAll();
    }

    public function getClientOrders(): array
    {
        return $this->connection->query(
            'SELECT users.id AS user_id, users.name AS customer, users.email,
                    books.title AS book,
                    purchase_requests.quantity, purchase_requests.total,
                    purchase_requests.status, purchase_requests.created_at
             FROM purchase_requests
             INNER JOIN users ON users.id = purchase_requests.user_id
             INNER JOIN books ON books.id = purchase_requests.book_id
             WHERE users.role = "client"
             ORDER BY purchase_requests.created_at DESC'
        )->fetchAll();
    }

    public function getAll()
    {
        return $this->connection->query(
            'SELECT id, user_id, name, email FROM customers ORDER BY name'
        )->fetchAll();
    }

    public function add($data)
    {
        $statement = $this->connection->prepare(
            'INSERT INTO customers (name, email) VALUES (:name, :email)'
        );
        $statement->execute([
            'name' => trim($data['name']),
            'email' => trim($data['email']),
        ]);
    }
}
