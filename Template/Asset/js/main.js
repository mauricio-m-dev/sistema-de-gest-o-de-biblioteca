import { Api } from './api.js';
import { formatDate, formatCurrency, showFeedback, UI } from './utils.js';

// --- Estado Global ---
let state = {
    books: [],
    loans: []
};

// --- Inicialização ---
document.addEventListener("DOMContentLoaded", () => {
    loadData();
    setupEventListeners();
});

function setupEventListeners() {
    // Forms
    document.getElementById("book-form").addEventListener("submit", handleBookSubmit);
    document.getElementById("loan-form").addEventListener("submit", handleLoanSubmit);

    // Validação de ISBN
    const isbnInput = document.getElementById('book-isbn');
    isbnInput.addEventListener('blur', validateISBN);
    isbnInput.addEventListener('input', function () {
        document.getElementById('isbn-feedback').innerText = "";
        this.classList.remove('input-error', 'input-success');
        this.removeAttribute('data-invalid');
    });

    // --- CORREÇÃO DA PESQUISA ---
    const searchInput = document.getElementById('search-book');

    // Usamos o evento 'input' que pega digitação, colar (Ctrl+V) e apagar
    searchInput.addEventListener('input', (e) => {
        const term = e.target.value.toLowerCase().trim();

        // Se o campo estiver vazio, renderiza tudo
        if (term === "") {
            renderBooks(state.books);
            return;
        }

        // Filtra por Nome, ISBN ou Autor
        const filteredBooks = state.books.filter(book =>
            book.name.toLowerCase().includes(term) ||
            book.isbn.includes(term) ||
            book.author.toLowerCase().includes(term)
        );

        // Renderiza apenas os encontrados
        renderBooks(filteredBooks);
    });
}

// --- Lógica de Carregamento ---
async function loadData() {
    try {
        console.log("Carregando dados...");
        const [booksData, loansData] = await Promise.all([
            Api.getBooks(),
            Api.getLoans()
        ]);

        // Garante que sejam arrays
        state.books = Array.isArray(booksData) ? booksData : [];
        state.loans = Array.isArray(loansData) ? loansData : [];

        updateUI();
    } catch (error) {
        console.error("Falha fatal ao carregar dados.", error);
    }
}

function updateUI() {
    // Renderiza a lista completa inicialmente
    renderBooks(state.books);
    renderLoans();
    updateDashboard();
    populateBookSelect();
}

// --- Renderização (View) ---

/**
 * Agora renderBooks aceita um parâmetro opcional 'customList'.
 * Se não passar nada, ele usa state.books (todos).
 */
function renderBooks(customList = null) {
    const tbody = document.getElementById("book-list");
    tbody.innerHTML = "";

    // Usa a lista passada ou a lista completa global
    const booksToRender = customList || state.books;

    if (booksToRender.length === 0) {
        tbody.innerHTML = "<tr><td colspan='6' class='text-center'>Nenhum livro encontrado.</td></tr>";
        return;
    }

    booksToRender.forEach((book) => {
        // Lógica da Imagem
        let imageHtml;
        if (book.cover && book.cover.trim() !== "") {
            imageHtml = `<img src="${book.cover}" alt="Capa" 
                style="width:50px; height:70px; object-fit:cover; border-radius:4px; border:1px solid #ddd;">`;
        } else {
            imageHtml = `<div style="width:50px; height:70px; background:#eee; color:#777; 
                display:flex; align-items:center; justify-content:center; 
                font-weight:bold; font-size:1.2rem; border-radius:4px;">
                ${book.name.charAt(0).toUpperCase()}
            </div>`;
        }

        // Ícone PDF
        let pdfIcon = book.pdf_path
            ? `<a href="${book.pdf_path}" target="_blank" style="color:#d32f2f; margin-right:8px; font-size:1.1rem;" title="Ler PDF"><i class="fa-solid fa-file-pdf"></i></a>`
            : `<span style="color:#ccc; margin-right:8px;"><i class="fa-solid fa-file"></i></span>`;

        const tr = document.createElement("tr");
        tr.innerHTML = `
            <td class="book-cover-cell">${imageHtml}</td>
            <td><span class="badge badge-gray">${book.isbn}</span></td>
            <td>
                <div style="font-weight:600; color:#333;">${book.name}</div>
                <small style="color:#777;">${book.category || 'Geral'}</small>
            </td>
            <td>${book.author}</td>
            <td><span class="badge ${book.qtd > 0 ? 'badge-success' : 'badge-warning'}">${book.qtd} unid.</span></td> 
            <td>
                ${pdfIcon}
                <button class="btn-sm delete-btn" data-id="${book.id}" title="Excluir" style="color:#dc3545; border:none; background:none; cursor:pointer;">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });

    // Reata os eventos de delete nos novos botões criados
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', () => deleteBook(btn.dataset.id));
    });
}

function renderLoans() {
    const tbody = document.getElementById("loan-list");
    tbody.innerHTML = "";

    if (state.loans.length === 0) {
        tbody.innerHTML = "<tr><td colspan='8' class='text-center'>Nenhum empréstimo registrado.</td></tr>";
        return;
    }

    state.loans.forEach((loan) => {
        const tr = document.createElement("tr");

        const fineValue = parseFloat(loan.fine_amount || 0);
        const fineDisplay = fineValue > 0
            ? `<span style="color:#dc3545; font-weight:bold;">R$ ${formatCurrency(fineValue)}</span>`
            : "R$ 0,00";

        let actionBtn = "";
        let statusBadge = "";

        if (loan.status === 'active') {
            actionBtn = `<button class="btn-primary return-btn" data-id="${loan.id}" 
                style="padding:5px 10px; font-size:0.8rem; background-color:#0d6efd; border-color:#0d6efd;">
                Devolver
            </button>`;
            statusBadge = `<span class="badge badge-warning">Emprestado</span>`;
        } else {
            actionBtn = `<span style="color:green; font-size:1.1rem;"><i class="fa-solid fa-circle-check"></i></span>`;
            statusBadge = `<span class="badge badge-success">Devolvido</span>`;
        }

        const cpfDisplay = loan.reader_cpf ? loan.reader_cpf : '-';

        tr.innerHTML = `
            <td><strong>${loan.reader_name}</strong></td>
            <td><small>${cpfDisplay}</small></td>
            <td>${loan.book_name}</td> 
            <td>${formatDate(loan.loan_date)}</td>
            <td>${formatDate(loan.return_date_est)}</td>
            <td>${statusBadge}</td>
            <td>${fineDisplay}</td> 
            <td style="text-align:center;">${actionBtn}</td>
        `;
        tbody.appendChild(tr);
    });

    document.querySelectorAll('.return-btn').forEach(btn => {
        btn.addEventListener('click', () => returnLoan(btn.dataset.id));
    });
}

function updateDashboard() {
    document.getElementById("total-books").innerText = state.books.length;

    const activeLoans = state.loans.filter(l => l.status === 'active');
    document.getElementById("active-loans-count").innerText = activeLoans.length;

    const totalFines = state.loans.reduce((acc, loan) => acc + parseFloat(loan.fine_amount || 0), 0);
    document.getElementById("total-fines").innerText = `R$ ${formatCurrency(totalFines)}`;

    const counts = {};
    state.loans.forEach(l => { counts[l.book_name] = (counts[l.book_name] || 0) + 1; });
    const sorted = Object.entries(counts).sort((a, b) => b[1] - a[1]).slice(0, 3);

    const topList = document.getElementById("top-books-list");
    topList.innerHTML = "";
    sorted.forEach(([name, count]) => {
        const li = document.createElement("li");
        li.innerHTML = `<span>${name}</span> <strong>${count}x</strong>`;
        topList.appendChild(li);
    });
}

function populateBookSelect() {
    const select = document.getElementById("loan-book-select");
    select.innerHTML = '<option value="">Selecione um livro...</option>';
    state.books.forEach((b) => {
        if (b.qtd > 0) {
            const option = document.createElement("option");
            option.value = b.id;
            option.innerText = `${b.name} (ISBN: ${b.isbn})`;
            select.appendChild(option);
        }
    });
}

// --- Handlers de Eventos (Forms) ---

async function validateISBN() {
    const input = document.getElementById('book-isbn');
    const feedback = document.getElementById('isbn-feedback');
    let isbn = input.value.replace(/[^0-9]/g, '');

    if (isbn.length !== 10 && isbn.length !== 13) {
        if (isbn.length > 0) showFeedback(input, feedback, "O ISBN deve ter 10 ou 13 dígitos.", false);
        return false;
    }

    try {
        const data = await Api.checkIsbn(isbn);
        if (data.exists) {
            showFeedback(input, feedback, "Este ISBN já está cadastrado!", false);
            return false;
        } else {
            showFeedback(input, feedback, "ISBN válido e disponível.", true);
            return true;
        }
    } catch (error) {
        console.error(error);
        return false;
    }
}

async function handleBookSubmit(e) {
    e.preventDefault();
    const isbnInput = document.getElementById('book-isbn');

    // Bloqueia se o ISBN estiver inválido
    if (isbnInput.getAttribute('data-invalid') === 'true' || !(await validateISBN())) {
        alert("Corrija o ISBN antes de salvar.");
        isbnInput.focus();
        return;
    }

    const formData = new FormData(e.target);

    try {
        const result = await Api.saveBook(formData);
        if (result.success) {
            alert("Livro cadastrado com sucesso!");
            UI.closeModal('modal-book');
            e.target.reset();
            document.getElementById('isbn-feedback').innerText = '';
            isbnInput.style.border = '';
            loadData();
        } else {
            alert("Erro: " + result.message);
        }
    } catch (error) {
        alert("Erro de comunicação com servidor.");
    }
}

async function handleLoanSubmit(e) {
    e.preventDefault();
    const data = {
        reader: document.getElementById('loan-reader').value,
        cpf: document.getElementById('loan-cpf').value,
        book_id: document.getElementById('loan-book-select').value,
        return_date: document.getElementById('loan-date-return').value
    };

    try {
        const result = await Api.saveLoan(data);
        if (result.success) {
            alert("Empréstimo registrado!");
            UI.closeModal('modal-loan');
            e.target.reset();
            loadData();
        } else {
            alert("Erro: " + (result.message || "Erro desconhecido"));
        }
    } catch (error) {
        alert("Erro de conexão.");
    }
}

async function deleteBook(id) {
    if (!confirm("Excluir este livro permanentemente?")) return;
    const result = await Api.deleteBook(id);
    if (result.success) loadData();
    else alert("Erro ao excluir.");
}

async function returnLoan(id) {
    if (!confirm("Confirmar devolução do livro?")) return;
    const result = await Api.returnLoan(id);
    if (result.success) loadData();
    else alert("Erro na devolução.");
}