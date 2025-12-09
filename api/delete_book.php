<?php
/**
 * Endpoint: Excluir um livro
 * Método: POST
 * Payload: id
 */
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../Model/Connection.php';
require_once __DIR__ . '/../Model/BookModel.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método inválido.', 405);
    }

    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if (!$id) {
        throw new Exception('ID do livro inválido ou não fornecido.');
    }

    $model = new \Model\BookModel();

    if ($model->delete($id)) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Não foi possível excluir o livro.');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}