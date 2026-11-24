<?php

class Supplier {

    public $name;
    public $contact;

    public function __construct($name, $contact) {
        $this->name = $name;
        $this->contact = $contact;
    }
}
?>