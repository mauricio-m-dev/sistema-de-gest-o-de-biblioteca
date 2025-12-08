// --- Variáveis Globais ---
let books = [];
let loans = [];

// --- Inicialização ---
document.addEventListener("DOMContentLoaded", () => {
  fetchDataFromBackend();
});

// --- Integração com Backend (API PHP) ---
async function fetchDataFromBackend() {
  try {
    // 1. Buscando Livros
    // Crie um arquivo PHP (ex: api/list_books.php) que dê um "echo json_encode($listaDeLivros)"
    const response = await fetch("api/list_books.php");

    if (!response.ok) throw new Error("Erro ao conectar com API de livros");

    const data = await response.json();

    // Garante que é um array, mesmo se vier vazio
    books = Array.isArray(data) ? data : [];

    // 2. Buscando Empréstimos (Opcional por enquanto)
    // const loansReq = await fetch('api/list_loans.php');
    // loans = await loansReq.json();

    // 3. Atualizar a Tela
    renderBooks();
    renderLoans();
    updateDashboard();
    populateBookSelect();
  } catch (error) {
    console.error("Erro ao buscar dados:", error);
    document.getElementById(
      "book-list"
    ).innerHTML = `<tr><td colspan="7" class="text-center text-danger">Erro ao carregar dados: ${error.message}</td></tr>`;
  }
}

// --- Funções de Livros ---
function renderBooks() {
  const tbody = document.getElementById("book-list");
  tbody.innerHTML = "";

  if (books.length === 0) {
    tbody.innerHTML =
      "<tr><td colspan='7' style='text-align:center'>Nenhum livro encontrado.</td></tr>";
    return;
  }

  books.forEach((book) => {
    const tr = document.createElement("tr");

    // Lógica da Imagem: Se tiver 'cover', mostra a imagem. Se não, mostra o avatar padrão.
    // O caminho vem do banco como 'uploads/nome.png'.
    let imageHtml = "";
    if (book.cover && book.cover !== "") {
      imageHtml = `<img src="${book.cover}" alt="Capa" style="width:40px; height:40px; object-fit:cover; border-radius:4px;">`;
    } else {
      imageHtml = `<div class="mini-avatar" style="background:#eee;">${book.name
        .charAt(0)
        .toUpperCase()}</div>`;
    }

    // Mapeamento: book.name (DB) vs book.title (Antigo JS)
    // Mapeamento: book.qtd (DB) vs book.stock (Antigo JS)
    tr.innerHTML = `
            <td><input type="checkbox" value="${book.id}"></td>
            <td>
                <div class="cell-avatar">
                    ${imageHtml}
                    <div>
                        <div style="font-weight:600">${book.name}</div>
                        <div style="font-size:0.75rem; color:#888">${book.author}</div>
                    </div>
                </div>
            </td>
            <td><span class="badge badge-gray">${book.isbn}</span></td>
            <td>${book.qtd}</td> 
            <td class="text-right">
                <button class="btn-sm" onclick="deleteBook(${book.id})" title="Excluir">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </td>
        `;
    tbody.appendChild(tr);
  });
}

async function deleteBook(id) {
  if (!confirm("Tem certeza que deseja remover este livro permanentemente?"))
    return;

  try {
    const formData = new FormData();
    formData.append("id", id);

    const response = await fetch("api/delete_book.php", {
      method: "POST", // Usar POST ou DELETE dependendo do seu PHP
      body: formData,
    });

    const result = await response.json();

    if (result.success) {
      alert("Livro removido com sucesso!");
      fetchDataFromBackend(); // Recarrega a lista
    } else {
      alert("Erro ao remover: " + (result.message || "Erro desconhecido"));
    }
  } catch (error) {
    console.error("Erro na exclusão:", error);
    alert("Erro de conexão com o servidor.");
  }
}

// Filtro de pesquisa visual
function filterBooks() {
  const term = document.getElementById("search-book").value.toLowerCase();
  const rows = document.querySelectorAll("#book-list tr");

  rows.forEach((row) => {
    // Pega todo o texto da linha para comparar
    const text = row.innerText.toLowerCase();
    row.style.display = text.includes(term) ? "" : "none";
  });
}

// --- Funções de Empréstimos (Mantido estrutura, aguardando Backend) ---
function renderLoans() {
  const tbody = document.getElementById("loan-list");
  tbody.innerHTML = "";

  if (loans.length === 0) {
    tbody.innerHTML =
      "<tr><td colspan='7' style='text-align:center'>Nenhum empréstimo ativo.</td></tr>";
    return;
  }

  loans.forEach((loan) => {
    const tr = document.createElement("tr");
    tr.innerHTML = `
            <td><input type="checkbox"></td>
            <td>${loan.reader_name}</td>
            <td>${loan.book_name}</td> 
            <td>${formatDate(loan.return_date)}</td>
            <td><span class="badge badge-gray">${loan.status}</span></td>
            <td class="text-right">
                <button class="btn-sm" onclick="returnBook(${
                  loan.id
                })">Devolver</button>
            </td>
        `;
    tbody.appendChild(tr);
  });
}

// --- Dashboard e Utilitários ---
function updateDashboard() {
  // Atualiza contadores baseados nos arrays carregados
  const totalBooksEl = document.getElementById("total-books");
  if (totalBooksEl) totalBooksEl.innerText = books.length;
}

function populateBookSelect() {
  const select = document.getElementById("loan-book-select");
  if (!select) return;

  select.innerHTML = '<option value="">Selecione um livro...</option>';
  books.forEach((b) => {
    // Verifica se tem estoque antes de adicionar na lista de empréstimo
    if (b.qtd > 0) {
      const option = document.createElement("option");
      option.value = b.id;
      option.innerText = `${b.name} (ISBN: ${b.isbn})`;
      select.appendChild(option);
    }
  });
}

// --- Navegação e UI ---
function showSection(sectionId) {
  document
    .querySelectorAll(".section")
    .forEach((sec) => sec.classList.remove("active-section"));

  const target = document.getElementById(sectionId);
  if (target) target.classList.add("active-section");

  // Atualiza menu lateral (opcional)
  document
    .querySelectorAll(".sidebar li")
    .forEach((li) => li.classList.remove("active"));
  // A lógica de adicionar 'active' no li clicado depende de como você chama essa função no HTML
}

function openModal(id) {
  const modal = document.getElementById(id);
  if (modal) modal.style.display = "block";
}

function closeModal(id) {
  const modal = document.getElementById(id);
  if (modal) modal.style.display = "none";
}

function formatDate(dateStr) {
  if (!dateStr) return "-";
  // Tenta converter se for formato SQL (YYYY-MM-DD)
  try {
    const [y, m, d] = dateStr.split("-");
    if (y && m && d) return `${d}/${m}/${y}`;
  } catch (e) {
    return dateStr;
  }
  return dateStr;
}
