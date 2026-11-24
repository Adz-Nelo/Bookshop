<?php

class BookRepositorySupport
{
    public static function categoryId(PDO $connection, string $name): int
    {
        $statement = $connection->prepare('SELECT id FROM categories WHERE name = :name');
        $statement->execute(['name' => $name]);
        $categoryId = $statement->fetchColumn();
        if ($categoryId === false) {
            throw new RuntimeException('Select a category that exists before saving a book.');
        }
        return (int) $categoryId;
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
