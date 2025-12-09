<?php
namespace Model;

use PDO;
use DateTime;

class LoanModel
{
    private $db;

    // Configuração de Regra de Negócio
    const DAILY_FINE_VALUE = 2.00;

    public function __construct($db = null)
    {
        $this->db = $db ?? Connection::getInstance();
    }

    /**
     * Lista todos os empréstimos com dados do livro associado.
     */
    public function getAll(): array
    {
        $sql = "SELECT l.*, b.name as book_name 
                FROM loans l 
                JOIN book b ON l.book_id = b.id 
                ORDER BY l.id DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cria um novo empréstimo e baixa o estoque.
     */
    public function create($bookId, $reader, $cpf, $dateReturn): bool
    {
        $this->db->beginTransaction(); // Inicia transação para segurança

        try {
            // 1. Registra o empréstimo
            $sql = "INSERT INTO loans (book_id, reader_name, reader_cpf, loan_date, return_date_est, status) 
                    VALUES (:book_id, :reader, :cpf, CURDATE(), :return_date, 'active')";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':book_id', $bookId);
            $stmt->bindValue(':reader', $reader);
            $stmt->bindValue(':cpf', $cpf);
            $stmt->bindValue(':return_date', $dateReturn);
            $stmt->execute();

            // 2. Atualiza estoque do livro
            $bookModel = new BookModel();
            $bookModel->updateStock($bookId, 'decrease');

            $this->db->commit();
            return true;

        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Realiza a devolução, calcula multa e repõe estoque.
     */
    public function returnBook($loanId): bool
    {
        // 1. Busca informações do empréstimo
        $stmt = $this->db->prepare("SELECT book_id, return_date_est FROM loans WHERE id = :id");
        $stmt->bindValue(':id', $loanId);
        $stmt->execute();
        $loan = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$loan)
            return false;

        // 2. Cálculo da Multa
        $today = new DateTime();
        $dueDate = new DateTime($loan['return_date_est']);
        $fine = 0.00;

        if ($today > $dueDate) {
            $daysLate = $today->diff($dueDate)->days;
            $fine = $daysLate * self::DAILY_FINE_VALUE;
        }

        // 3. Atualiza devolução e multa com Transação
        $this->db->beginTransaction();

        try {
            $sql = "UPDATE loans 
                    SET status = 'returned', 
                        return_date_real = CURDATE(), 
                        fine_amount = :fine 
                    WHERE id = :id";

            $update = $this->db->prepare($sql);
            $update->bindValue(':id', $loanId);
            $update->bindValue(':fine', $fine);
            $update->execute();

            // 4. Repõe o estoque
            $bookModel = new BookModel();
            $bookModel->updateStock($loan['book_id'], 'increase');

            $this->db->commit();
            return true;

        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}