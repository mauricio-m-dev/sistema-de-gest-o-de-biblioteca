async function request(url, options = {}) {
    try {
        const response = await fetch(url, options);
        const text = await response.text();
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error(`Erro ao fazer parse do JSON em ${url}:`, text);
            throw new Error("Resposta inválida do servidor.");
        }
    } catch (error) {
        console.error("Erro na requisição:", error);
        throw error;
    }
}

// --- Métodos Públicos ---

export const Api = {
    getBooks: () => request("api/list_books.php"),

    getLoans: () => request("api/list_loans.php"),

    checkIsbn: async (isbn) => {
        return request(`api/check_isbn.php?isbn=${isbn}`);
    },

    saveBook: (formData) => request("api/save_book.php", {
        method: "POST",
        body: formData
    }),

    deleteBook: (id) => {
        const formData = new FormData();
        formData.append("id", id);
        return request("api/delete_book.php", {
            method: "POST",
            body: formData
        });
    },

    saveLoan: (data) => request("api/save_loan.php", {
        method: "POST",
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    }),

    returnLoan: (id) => request("api/return_loan.php", {
        method: "POST",
        body: JSON.stringify({ id: id })
    })
};