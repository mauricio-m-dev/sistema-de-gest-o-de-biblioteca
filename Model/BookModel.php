<?php
namespace Model;

use PDO;
use PDOException;

class BookModel
{
    private $db;

    public function __construct($db = null)
    {
        $this->db = $db ?? Connection::getInstance();
    }

    /**
     * Retorna todos os livros ordenados pelo mais recente.
     */
    public function getAll(): array
    {
        $sql = "SELECT * FROM book ORDER BY id DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Verifica se um ISBN já existe.
     */
    public function existsByISBN(string $isbn)
    {
        $sql = "SELECT id FROM book WHERE isbn = :isbn LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':isbn', $isbn);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Insere um novo livro no banco.
     */
    public function insertBook($name, $author, $category, $isbn, $qtd, $coverPath, $pdfPath): bool
    {
        $sql = "INSERT INTO book (name, author, category, isbn, qtd, cover, pdf_path) 
                VALUES (:name, :author, :category, :isbn, :qtd, :cover, :pdf)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':name', $name);
        $stmt->bindValue(':author', $author);
        $stmt->bindValue(':category', $category);
        $stmt->bindValue(':isbn', $isbn);
        $stmt->bindValue(':qtd', $qtd);
        $stmt->bindValue(':cover', $coverPath);
        $stmt->bindValue(':pdf', $pdfPath);

        return $stmt->execute();
    }

    /**
     * Remove um livro pelo ID.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM book WHERE id = :id");
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }

    /**
     * Atualiza o estoque (+1 ou -1).
     * @param int $bookId
     * @param string $action 'increase' ou 'decrease'
     */
    public function updateStock(int $bookId, string $action): bool
    {
        $operator = ($action === 'increase') ? '+' : '-';
        $sql = "UPDATE book SET qtd = qtd $operator 1 WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $bookId);
        return $stmt->execute();
    }
}