<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Biblioteca</title>

    <link rel="stylesheet" href="Template/Asset/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="container">

        <nav class="sidebar">
            <h2><i class="fa-solid fa-book-open"></i> SGB Tech </h2>
            <ul>
                <li onclick="showSection('dashboard')">
                    <i class="fa-solid fa-chart-line"></i> Dashboard
                </li>
                <li onclick="showSection('books')">
                    <i class="fa-solid fa-book"></i> Acervo & Estoque
                </li>
                <li onclick="showSection('loans')">
                    <i class="fa-solid fa-hand-holding"></i> Empréstimos
                </li>
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

                    <input type="text" id="search-book" placeholder="Buscar por título, autor ou ISBN...">
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
                    <button class="btn-primary" onclick="openModal('modal-loan')">+ Novo Emprestimo</button>
                    <input type="text" id="search-book" placeholder="Buscar por título ou ISBN...">
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Leitor</th>
                            <th>CPF</th>
                            <th>Livro</th>
                            <th>Data Saída</th>
                            <th>Data Prevista</th>
                            <th>Status</th>
                            <th>Multa</th>
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
                <div class="form-group">
                    <label for="book-isbn">ISBN:</label>
                    <input type="number" name="book-isbn" id="book-isbn" placeholder="Somente números (10 ou 13)"
                        required>
                    <small id="isbn-feedback"></small>
                </div>

                <div class="form-group">
                    <label for="book-title">Título:</label>
                    <input type="text" name="book-title" id="book-title" placeholder="Ex: Código Limpo" required>
                </div>

                <div class="form-group">
                    <label for="book-author">Autor:</label>
                    <input type="text" name="book-author" id="book-author" placeholder="Nome do Autor" required>
                </div>

                <div class="form-group">
                    <label for="book-category">Categoria:</label>
                    <input type="text" name="book-category" id="book-category" placeholder="Ex: Tecnologia" required>
                </div>

                <div class="form-group">
                    <label for="book-stock">Estoque:</label>
                    <input type="number" name="book-stock" id="book-stock" placeholder="Qtd" min="1" required>
                </div>

                <div class="form-group">
                    <label for="book-cover">Capa (Imagem):</label>
                    <input type="file" name="book-cover" id="book-cover" accept="image/*">
                </div>

                <div class="form-group">
                    <label for="book-pdf">Livro Digital (PDF):</label>
                    <input type="file" name="book-pdf" id="book-pdf" accept="application/pdf">
                </div>

                <button type="submit" class="btn-primary">Salvar</button>
            </form>
        </div>
    </div>

    <div id="modal-loan" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('modal-loan')">&times;</span>
            <h2>Novo Empréstimo</h2>

            <form id="loan-form">
                <div class="form-group">
                    <label for="loan-reader">Leitor:</label>
                    <input type="text" id="loan-reader" placeholder="Nome Completo" required>
                </div>

                <div class="form-group">
                    <label for="loan-cpf">CPF:</label>
                    <input type="text" id="loan-cpf" placeholder="000.000.000-00" required maxlength="14"
                        oninput="maskCPF(this)">
                </div>

                <div class="form-group">
                    <label for="loan-book-select">Livro:</label>
                    <select id="loan-book-select" required>
                        <option value="">Carregando livros...</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="loan-date-return">Devolução Prevista:</label>
                    <input type="date" id="loan-date-return" required>
                </div>

                <button type="submit" class="btn-primary">Confirmar Empréstimo</button>
            </form>
        </div>
    </div>

    <script type="module" src="Template/Asset/js/main.js"></script>
</body>

</html>