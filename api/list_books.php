<?php
/**
 * Endpoint: Listar todos os livros
 * Método: GET
 */
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../Model/Connection.php';
require_once __DIR__ . '/../Model/BookModel.php';

try {
    $bookModel = new \Model\BookModel();
    $books = $bookModel->getAll();

    echo json_encode($books);

} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Erro ao listar livros: ' . $e->getMessage()]);
}