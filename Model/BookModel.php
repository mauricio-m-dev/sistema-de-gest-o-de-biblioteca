<?php

namespace Model;

use Model\Connection;
use PDO;
use PDOException;
use Exception;

class BookModel
{

    private $db;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    public function existsByISBN($isbn)
    {

        try {
            $sql = ("SELECT * FROM book WHERE isbn = :isbn LIMIT 1");

            $stmt = $this->db->prepare($sql);

            $stmt->bindparam(':isbn', $isbn);

            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $error) {
            echo "Erro ao verificar ISBN: " . $error->getMessage();
            return false;
        }
    }

    public function insertBook($name, $author, $isbn, $qtd, $cover)
    {
        try {
            if (is_array($cover['name'])) {
                throw new Exception("Multiple files detected. Please upload a single cover image.");
            }

            $extension = pathinfo($cover['name'], PATHINFO_EXTENSION);
            $newFileName = uniqid() . '.' . $extension; 
            $coverPath = 'uploads/' . $newFileName;

            if (!move_uploaded_file($cover['tmp_name'], $coverPath)) {
                throw new Exception("Erro ao fazer upload da capa do livro.");
            }

            $sql = "INSERT INTO book (name, author, isbn, qtd, cover) VALUES (:name, :author, :isbn, :qtd, :cover_path)";

            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':author', $author);
            $stmt->bindParam(':isbn', $isbn);
            $stmt->bindParam(':qtd', $qtd);
            $stmt->bindParam(':cover_path', $coverPath);

            return $stmt->execute();

        } catch (Exception $error) {
            echo "Erro ao inserir livro: " . $error->getMessage();
            return false;
        }
    }
}