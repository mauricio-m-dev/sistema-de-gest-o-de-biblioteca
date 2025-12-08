<?php

require_once __DIR__ . "/vendor/autoload.php";

use Controller\BookController;

$book = new BookController();

$success_message = htmlspecialchars('');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['book-title'], $_POST['book-author'], $_POST['book-isbn'], $_POST['book-stock']) && isset($_FILES['book-cover'])) {

        $name = htmlspecialchars($_POST['book-title']);
        $author = htmlspecialchars($_POST['book-author']);
        $isbn = intval($_POST['book-isbn']);
        $qtd = intval($_POST['book-stock']);
        $cover = $_FILES['book-cover'];

        if ($book->checkBookExists($isbn)) {
            $success_message = "O livro com ISBN $isbn já está cadastrado no sistema.";
        } else {
            if ($book->addBook($name, $author, $isbn, $qtd, $cover)) {
                $success_message = "Livro adicionado com sucesso!";
            } else {
                $success_message = "Erro ao adicionar o livro. Verifique os dados e tente novamente.";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGB Tech - Gestão de Biblioteca</title>
    <link rel="stylesheet" href="Template/Asset/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>

    <div class="container">
        <nav class="sidebar">
            <h2><i class="fa-solid fa-book-open"></i> Biblio Tech</h2>
            <ul>
                <li class="active" onclick="showSection('dashboard')"><i class="fa-solid fa-chart-line"></i> Dashboard
                </li>
                <li onclick="showSection('books')"><i class="fa-solid fa-book"></i> Acervo & Estoque</li>
                <li onclick="showSection('loans')"><i class="fa-solid fa-hand-holding"></i> Empréstimos</li>
            </ul>
        </nav>

        <main class="content">

            <section id="dashboard" class="section active-section">
                <h1>Painel de Controle</h1>
                <div class="cards-container">
                    <div class="card">
                        <h3>Total de Livros</h3>
                        <p id="total-books">0</p>
                    </div>
                    <div class="card">
                        <h3>Empréstimos Ativos</h3>
                        <p id="active-loans-count">0</p>
                    </div>
                    <div class="card warning">
                        <h3>Multas Pendentes</h3>
                        <p id="total-fines">R$ 0,00</p>
                    </div>
                </div>

                <div class="report-box">
                    <h3><i class="fa-solid fa-trophy"></i> Livros Mais Emprestados (Top 3)</h3>
                    <ul id="top-books-list">
                    </ul>
                </div>
            </section>

            <section id="books" class="section">
                <h1>Gerenciar Acervo</h1>
                <div class="actions">
                    <button class="btn-primary" onclick="openModal('modal-book')">+ Novo Livro</button>
                    <input type="text" id="search-book" placeholder="Buscar por título ou ISBN..."
                        onkeyup="filterBooks()">
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Capa</th>
                            <th>ISBN</th>
                            <th>Título</th>
                            <th>Autor</th>
                            <th>Estoque</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="book-list">
                    </tbody>
                </table>
            </section>

            <section id="loans" class="section">
                <h1>Controle de Empréstimos</h1>
                <div class="actions">
                    <button class="btn-primary" onclick="openModal('modal-loan')">Registrar Saída</button>
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Leitor</th>
                            <th>Livro</th>
                            <th>Data Saída</th>
                            <th>Data Prevista</th>
                            <th>Status</th>
                            <th>Multa (Simulação)</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody id="loan-list">
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <div id="modal-book" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('modal-book')">&times;</span>
            <h2>Cadastrar Novo Livro</h2>
            <form id="book-form" method="POST" enctype="multipart/form-data">
                <input type="text" name="book-isbn" id="book-isbn" placeholder="ISBN" required>

                <input type="text" name="book-title" id="book-title" placeholder="Título do Livro" required>

                <input type="text" name="book-author" id="book-author" placeholder="Autor" required>

                <input type="number" name="book-stock" id="book-stock" placeholder="Quantidade em Estoque" min="1"
                    required>

                <label for="book-cover" style="display:block; margin-bottom: 5px; color: var(--text-muted);">Capa do
                    Livro:</label>
                <input type="file" name="book-cover" id="book-cover" accept="image/*"
                    style="padding: 5px 15px; border: 1px dashed var(--border-color);">

                <button type="submit" class="btn-primary">Salvar</button>
            </form>
            <p> <?php echo $success_message; ?> </p>
        </div>
    </div>

    <div id="modal-loan" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('modal-loan')">&times;</span>
            <h2>Novo Empréstimo</h2>
            <form id="loan-form">
                <input type="text" id="loan-reader" placeholder="Nome do Leitor" required>
                <select id="loan-book-select" required>
                    <option value="">Selecione um livro...</option>
                </select>
                <label>Data de Devolução Prevista:</label>
                <input type="date" id="loan-date-return" required>
                <button type="submit" class="btn-primary">Confirmar Empréstimo</button>
            </form>
        </div>
    </div>

    <script src="Template/Asset/js/script.js"></script>
</body>

</html>