<?php
/**
 * Endpoint: Listar todos os empréstimos
 * Método: GET
 */
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../Model/Connection.php';
require_once __DIR__ . '/../Model/LoanModel.php';
require_once __DIR__ . '/../Model/BookModel.php'; // Necessário se houver dependência

try {
    $loanModel = new \Model\LoanModel();
    $loans = $loanModel->getAll();

    echo json_encode($loans);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao listar empréstimos: ' . $e->getMessage()]);
}