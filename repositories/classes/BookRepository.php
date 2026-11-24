<?php

class BookRepository extends BaseRepository
{
    public function getAll()
    {
        $statement = $this->connection->query(
            'SELECT books.id, books.title, books.author, books.description, categories.name AS category,
             books.supplier_id, suppliers.name AS supplier, books.price, books.stock, books.cover_image
             FROM books
             INNER JOIN categories ON categories.id = books.category_id
             LEFT JOIN suppliers ON suppliers.id = books.supplier_id
             ORDER BY books.title'
        );

        return $statement->fetch_all(MYSQLI_ASSOC);
    }

    public function add(array $data): void
    {
        $book = Book::fromArray($data);
        $categoryId = BookRepositorySupport::categoryId($this->connection, $book->category);
        $supplierId = (int) ($data['supplier_id'] ?? 0);
        if ($supplierId < 1) {
            $supplierId = 0;
        }
        $statement = $this->connection->prepare(
            'INSERT INTO books (title, author, category_id, supplier_id, price, stock, cover_image, description)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $parameters = BookRepositorySupport::parameters($book, $categoryId);
        $parameters['supplier_id'] = $supplierId > 0 ? $supplierId : null;
        $statement->bind_param(
            'ssidis',
            $parameters['title'],
            $parameters['author'],
            $parameters['category_id'],
            $parameters['supplier_id'],
            $parameters['price'],
            $parameters['stock'],
            $parameters['cover_image'],
            $parameters['description']
        );
        $statement->execute();
        $statement->close();
    }

    public function update(int $bookId, array $data): bool
    {
        if ($bookId < 1 || !$this->exists($bookId)) {
            return false;
        }
        $book = Book::fromArray($data);
        $categoryId = BookRepositorySupport::categoryId($this->connection, $book->category);
        $parameters = BookRepositorySupport::parameters($book, $categoryId);
        $supplierId = (int) ($data['supplier_id'] ?? 0);
        $parameters['supplier_id'] = $supplierId > 0 ? $supplierId : null;
        $parameters['id'] = $bookId;
        $statement = $this->connection->prepare(
            'UPDATE books SET title = ?, author = ?, category_id = ?,
             supplier_id = ?, price = ?, stock = ?,
             cover_image = ?, description = ?
             WHERE id = ?'
        );
        $statement->bind_param(
            'ssidisisi',
            $parameters['title'],
            $parameters['author'],
            $parameters['category_id'],
            $parameters['supplier_id'],
            $parameters['price'],
            $parameters['stock'],
            $parameters['cover_image'],
            $parameters['description'],
            $parameters['id']
        );
        $statement->execute();
        $statement->close();
        return true;
    }

    public function delete(int $bookId): bool
    {
        if ($bookId < 1 || !$this->exists($bookId)) {
            return false;
        }
        $orders = $this->connection->prepare('SELECT id FROM purchase_requests WHERE book_id = ? LIMIT 1');
        $orders->bind_param('i', $bookId);
        $orders->execute();
        $ordersResult = $orders->get_result();
        if ($ordersResult->fetch_assoc() !== null) {
            $orders->close();
            return false;
        }
        $orders->close();
        $delete = $this->connection->prepare('DELETE FROM books WHERE id = ?');
        $delete->bind_param('i', $bookId);
        $delete->execute();
        $affected = $delete->affected_rows;
        $delete->close();
        return $affected === 1;
    }

    private function exists(int $bookId): bool
    {
        $statement = $this->connection->prepare('SELECT id FROM books WHERE id = ?');
        $statement->bind_param('i', $bookId);
        $statement->execute();
        $result = $statement->get_result();
        $row = $result->fetch_assoc();
        $statement->close();
        return $row !== null;
    }
}
