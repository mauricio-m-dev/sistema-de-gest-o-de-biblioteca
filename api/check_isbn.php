<?php
/**
 * Endpoint: Verificar duplicidade de ISBN
 * Método: GET
 * Parâmetro: isbn
 */
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../Model/Connection.php';
require_once __DIR__ . '/../Model/BookModel.php';

try {
    $isbn = $_GET['isbn'] ?? '';

    // Sanitização simples: remove tudo que não for número
    $isbnClean = preg_replace('/[^0-9]/', '', $isbn);

    if (empty($isbnClean)) {
        http_response_code(400); // Bad Request
        echo json_encode(['exists' => false, 'message' => 'ISBN inválido']);
        exit;
    }

    $model = new \Model\BookModel();
    $book = $model->existsByISBN($isbnClean);

    echo json_encode(['exists' => ($book !== false)]);

} catch (Exception $e) {
    // Em validações, preferimos não quebrar o fluxo, mas logar o erro seria ideal
    echo json_encode(['exists' => false, 'error' => $e->getMessage()]);
}