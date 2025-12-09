<?php
/**
 * Endpoint: Registrar Empréstimo
 * Método: POST
 * Payload: JSON { reader, cpf, book_id, return_date }
 */
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../Model/Connection.php';
require_once __DIR__ . '/../Model/BookModel.php';
require_once __DIR__ . '/../Model/LoanModel.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método inválido.', 405);
    }

    // Lê o JSON enviado pelo JavaScript
    $input = file_get_contents("php://input");
    $data = json_decode($input, true);

    // Validação de Campos Obrigatórios
    if (empty($data['reader']) || empty($data['book_id']) || empty($data['return_date'])) {
        throw new Exception('Dados incompletos. Preencha leitor, livro e data.');
    }

    $model = new \Model\LoanModel();
    $cpf = $data['cpf'] ?? ''; // CPF pode ser opcional ou vazio dependendo da regra

    $success = $model->create(
        (int) $data['book_id'],
        $data['reader'],
        $cpf,
        $data['return_date']
    );

    if ($success) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Erro ao registrar empréstimo no banco.');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}