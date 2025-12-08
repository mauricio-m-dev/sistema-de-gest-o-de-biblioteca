<?php
// api/list_books.php
header('Content-Type: application/json');
require_once '/Model/Connection.php';

try {
    $conn = \Model\Connection::getInstance();
    $stmt = $conn->query("SELECT * FROM book ORDER BY id DESC");
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($books);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}