<?php

class BookRepositorySupport
{
    public static function categoryId(mysqli $connection, string $name): int
    {
        $statement = $connection->prepare('SELECT id FROM categories WHERE name = ?');
        $statement->bind_param('s', $name);
        $statement->execute();
        $result = $statement->get_result();
        $row = $result->fetch_row();
        $statement->close();
        if ($row === null) {
            throw new RuntimeException('Select a category that exists before saving a book.');
        }
        return (int) $row[0];
    }

    public static function parameters(Book $book, int $categoryId): array
    {
        return [
            'title' => $book->title, 'author' => $book->author, 'category_id' => $categoryId,
            'price' => $book->price, 'stock' => $book->stock, 'cover_image' => $book->coverImage,
            'description' => $book->description !== '' ? $book->description : null,
        ];
    }
}
