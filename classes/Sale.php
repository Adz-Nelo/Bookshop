<?php

class Sale {

    public $book;
    public $quantity;
    public $total;

    public function __construct($book, $quantity, $total) {
        $this->book = $book;
        $this->quantity = $quantity;
        $this->total = $total;
    }
}
?>