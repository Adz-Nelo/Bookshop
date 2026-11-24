<?php

class Book extends Product
{
    public $author;
    public $category;
    public $stock;
    public $coverImage;
    public $description;

    public function __construct($title, $author, $category, $price, $stock, $coverImage, $description)
    {
        parent::__construct($title, $price);
        $this->author = $author;
        $this->category = $category;
        $this->stock = $stock;
        $this->coverImage = $coverImage;
        $this->description = $description;
    }

    public static function fromArray(array $data): self
    {
        $title = trim((string) ($data['title'] ?? ''));
        $author = trim((string) ($data['author'] ?? ''));
        $category = trim((string) ($data['category'] ?? ''));
        $price = filter_var($data['price'] ?? null, FILTER_VALIDATE_FLOAT);
        $stock = filter_var($data['stock'] ?? null, FILTER_VALIDATE_INT);
        $description = trim((string) ($data['description'] ?? ''));
        $descriptionLength = preg_match_all('/./us', $description, $characters);

        if ($title === '' || $author === '' || $category === '' || $price === false || $price < 0 ||
            $stock === false || $stock < 0 || $descriptionLength === false || $descriptionLength > 3000) {
            throw new InvalidArgumentException('Enter valid book details and a description of at most 3,000 characters.');
        }

        $coverImage = str_replace('\\', '/', trim((string) ($data['cover_image'] ?? '')));
        if ($coverImage === '' || strpos($coverImage, '../') !== false ||
            $coverImage[0] === '/' || strpos($coverImage, 'assets/') !== 0) {
            $coverImage = 'assets/books/default-book.svg';
        }

        return new self($title, $author, $category, $price, $stock, $coverImage, $description);
    }
}