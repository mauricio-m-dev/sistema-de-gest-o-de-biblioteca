<?php
/**
 * Endpoint: Devolver Livro
 * Método: POST
 * Payload: JSON { id }
 */
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../Model/Connection.php';
require_once __DIR__ . '/../Model/BookModel.php'; // Necessário para atualização de estoque
require_once __DIR__ . '/../Model/LoanModel.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método inválido.', 405);
    }

    $input = file_get_contents("php://input");
    $data = json_decode($input, true);

    $loanId = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);

    if (!$loanId) {
        throw new Exception('ID do empréstimo inválido.');
    }

    $model = new \Model\LoanModel();

    if ($model->returnBook($loanId)) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Erro ao processar devolução.');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}