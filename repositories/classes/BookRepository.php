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

        return $statement->fetchAll();
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
             VALUES (:title, :author, :category_id, :supplier_id, :price, :stock, :cover_image, :description)'
        );
        $parameters = BookRepositorySupport::parameters($book, $categoryId);
        $parameters['supplier_id'] = $supplierId > 0 ? $supplierId : null;
        $statement->execute($parameters);
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
            'UPDATE books SET title = :title, author = :author, category_id = :category_id,
             supplier_id = :supplier_id, price = :price, stock = :stock,
             cover_image = :cover_image, description = :description
             WHERE id = :id'
        );
        $statement->execute($parameters);
        return true;
    }

    public function delete(int $bookId): bool
    {
        if ($bookId < 1 || !$this->exists($bookId)) {
            return false;
        }
        $orders = $this->connection->prepare('SELECT id FROM purchase_requests WHERE book_id = :id LIMIT 1');
        $orders->execute(['id' => $bookId]);
        if ($orders->fetch() !== false) {
            return false;
        }
        $delete = $this->connection->prepare('DELETE FROM books WHERE id = :id');
        $delete->execute(['id' => $bookId]);
        return $delete->rowCount() === 1;
    }

    private function exists(int $bookId): bool
    {
        $statement = $this->connection->prepare('SELECT id FROM books WHERE id = :id');
        $statement->execute(['id' => $bookId]);
        return $statement->fetch() !== false;
    }

}