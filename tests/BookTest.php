<?php

use PHPUnit\Framework\TestCase;
use Model\BookModel;

class BookTest extends TestCase
{
    private $pdo;
    private $bookModel;

    protected function setUp(): void
    {
        // Cria um banco SQLite na memória RAM (super rápido para testes)
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Cria a tabela de livros na memória
        $this->pdo->exec("CREATE TABLE book (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT, author TEXT, category TEXT, isbn TEXT, qtd INTEGER, cover TEXT, pdf_path TEXT
        )");

        // Instancia o Model passando o banco de teste
        $this->bookModel = new BookModel($this->pdo);
    }

    public function testDeveCadastrarLivroCorretamente()
    {
        $resultado = $this->bookModel->insertBook(
            "Clean Code",
            "Uncle Bob",
            "Tech",
            "1234567890",
            5,
            null,
            null
        );

        $this->assertTrue($resultado, "O livro deveria ser inserido com sucesso");

        // Verifica se salvou no banco
        $stmt = $this->pdo->query("SELECT * FROM book WHERE isbn = '1234567890'");
        $livro = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals("Clean Code", $livro['name']);
        $this->assertEquals(5, $livro['qtd']);
    }

    public function testNaoDevePermitirIsbnDuplicado()
    {
        // Insere o primeiro
        $this->bookModel->insertBook("Livro A", "Autor A", "Geral", "111222", 1, null, null);

        // Verifica existência
        $existe = $this->bookModel->existsByISBN("111222");
        $this->assertIsArray($existe, "Deveria retornar array com dados do livro");

        // Verifica não existência
        $naoExiste = $this->bookModel->existsByISBN("999999");
        $this->assertFalse($naoExiste, "Deveria retornar false para ISBN inexistente");
    }

    public function testDeveAtualizarEstoque()
    {
        // Cadastra com 10 unidades
        $this->bookModel->insertBook("Teste Estoque", "Autor", "Geral", "555", 10, null, null);
        $id = $this->pdo->lastInsertId();

        // Simula Empréstimo (Diminui)
        $this->bookModel->updateStock($id, 'decrease');

        $stmt = $this->pdo->query("SELECT qtd FROM book WHERE id = $id");
        $this->assertEquals(9, $stmt->fetchColumn());

        // Simula Devolução (Aumenta)
        $this->bookModel->updateStock($id, 'increase');

        $stmt = $this->pdo->query("SELECT qtd FROM book WHERE id = $id");
        $this->assertEquals(10, $stmt->fetchColumn());
    }
}