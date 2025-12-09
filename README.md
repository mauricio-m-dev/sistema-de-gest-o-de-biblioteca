# SGB Tech - Sistema de Gestão de Biblioteca

> Um sistema web completo para gerenciamento de acervos físicos e digitais, controle de empréstimos e cálculo automático de multas.

O **SGB Tech** foi desenvolvido para modernizar o processo de empréstimo de livros técnicos de TI. Diferente de sistemas tradicionais baseados em recarregamento de página (SSR), este projeto utiliza uma arquitetura **SPA (Single Page Application)** simulada, onde o Frontend (JavaScript/ES6) consome uma API RESTful (PHP) para garantir uma experiência de usuário fluida e rápida.

## 🚀 Funcionalidades

- **Dashboard Interativo:** Visão geral em tempo real do acervo e empréstimos.
- **Gestão de Livros:** Cadastro completo com validação de ISBN, upload seguro de capa e arquivo PDF (Livro Digital).
- **Controle de Estoque:** Baixa automática ao emprestar e reposição ao devolver.
- **Sistema de Multas:** Cálculo automático de dias de atraso e valor monetário (R$ 2,00/dia) no ato da devolução.
- **Validação de Integridade:** Verificação de duplicidade de ISBN via API antes do envio do formulário.
- **Segurança:** Proteção contra SQL Injection (PDO), XSS (Sanitização) e Uploads Maliciosos (MIME Type check).

## 🛠️ Tecnologias Utilizadas

- **Back-End:** PHP 8.0+ (POO, MVC, PDO).
- **Banco de Dados:** MySQL / MariaDB.
- **Front-End:** HTML5, CSS3, JavaScript (ES6 Modules, Fetch API).
- **Design:** CSS Flexbox/Grid e FontAwesome para ícones.

## 📋 Requisitos do Ambiente

Para rodar o projeto, você precisará de:

1.  **Servidor Web:** Apache ou Nginx.
2.  **PHP:** Versão 8.0 ou superior.
3.  **MySQL:** Versão 5.7 ou superior.
4.  **Permissões:** Acesso de escrita na pasta `uploads/`.

## 📦 Instalação e Configuração

Siga os passos abaixo para rodar o projeto localmente:

### 1. Clonar o Repositório
Baixe os arquivos para o diretório do seu servidor web (ex: `htdocs` ou `www`).

### 2. Configurar o Banco de Dados
Crie um banco de dados chamado `biblioteca` e execute o seguinte script SQL:

```sql
CREATE DATABASE IF NOT EXISTS biblioteca;
USE biblioteca;

CREATE TABLE IF NOT EXISTS book (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    category VARCHAR(100) DEFAULT 'Geral',
    isbn VARCHAR(20) NOT NULL UNIQUE,
    qtd INT NOT NULL DEFAULT 0,
    cover VARCHAR(255) DEFAULT NULL,
    pdf_path VARCHAR(255) DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS loans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    reader_name VARCHAR(255) NOT NULL,
    reader_cpf VARCHAR(14) DEFAULT NULL,
    loan_date DATE NOT NULL,
    return_date_est DATE NOT NULL,
    return_date_real DATE DEFAULT NULL,
    status ENUM('active', 'returned') DEFAULT 'active',
    fine_amount DECIMAL(10,2) DEFAULT 0.00,
    FOREIGN KEY (book_id) REFERENCES book(id) ON DELETE CASCADE
);