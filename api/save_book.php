<?php
/**
 * Endpoint: Salvar um novo livro (com Upload)
 * Método: POST
 * Payload: FormData
 */
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../Model/Connection.php';
require_once __DIR__ . '/../Model/BookModel.php';
require_once __DIR__ . '/../Controller/BookController.php';

try {
    // 1. Verifica Método HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método inválido. Use POST.', 405);
    }

    // 2. Instancia Controller
    $controller = new \Controller\BookController();

    // 3. Processa
    // O Controller cuida da validação, upload e inserção
    $result = $controller->createBook($_POST, $_FILES);

    // 4. Retorna
    if (!$result['success']) {
        http_response_code(400); // Bad Request se falhou validação
    }

    echo json_encode($result);

} catch (Exception $e) {
    $code = $e->getCode() ?: 500;
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}