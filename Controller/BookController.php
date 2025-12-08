<?php

namespace Controller;
use Model\BookModel;
use Exception;

class BookController {
    
    private $BookModel;

    public function __construct() {
        $this->BookModel = new BookModel();
    }

    public function checkBookExists($isbn) {
        return $this->BookModel->existsByISBN($isbn);
    }

    public function addBook($name, $author, $isbn, $qtd, $cover) {

        if ($cover['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        return $this->BookModel->insertBook($name, $author, $isbn, $qtd, $cover);
    }
}