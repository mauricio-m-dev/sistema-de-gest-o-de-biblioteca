<?php
namespace Controller;

use Model\BookModel;
use Exception;

class BookController
{

    private $bookModel;

    // Caminhos relativos à raiz do site (para salvar no banco)
    private $publicUploadDir = 'uploads/';
    private $publicPdfDir = 'uploads/books/';

    public function __construct()
    {
        $this->bookModel = new BookModel();
    }

    public function createBook($data, $files)
    {
        try {
            // 1. Sanitização
            $data = $this->sanitizeData($data);

            // 2. Validação
            if ($this->bookModel->existsByISBN($data['isbn'])) {
                return ['success' => false, 'message' => 'ISBN já cadastrado.'];
            }

            // 3. Uploads
            $coverPath = $this->processUpload($files['book-cover'] ?? null, ['image/jpeg', 'image/png', 'image/webp'], $this->publicUploadDir);
            $pdfPath = $this->processUpload($files['book-pdf'] ?? null, ['application/pdf'], $this->publicPdfDir);

            // 4. Salvar no Banco
            $inserted = $this->bookModel->insertBook(
                $data['name'],
                $data['author'],
                $data['category'],
                $data['isbn'],
                $data['qtd'],
                $coverPath,
                $pdfPath
            );

            if ($inserted) {
                return ['success' => true];
            }

            throw new Exception('Erro ao inserir no banco de dados.');

        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function sanitizeData($data)
    {
        return [
            'name' => htmlspecialchars($data['book-title'] ?? '', ENT_QUOTES, 'UTF-8'),
            'author' => htmlspecialchars($data['book-author'] ?? '', ENT_QUOTES, 'UTF-8'),
            'category' => htmlspecialchars($data['book-category'] ?? 'Geral', ENT_QUOTES, 'UTF-8'),
            'isbn' => preg_replace('/[^0-9]/', '', $data['book-isbn'] ?? ''),
            'qtd' => (int) ($data['book-stock'] ?? 0)
        ];
    }

    /**
     * Processa o upload garantindo o local correto
     * @param array $file O arquivo do $_FILES
     * @param array $allowedTypes Mime types permitidos
     * @param string $publicTargetDir O caminho visual (ex: 'uploads/')
     */
    private function processUpload($file, $allowedTypes, $publicTargetDir)
    {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        // Validação de Segurança (MIME Type)
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);

        if (!in_array($mime, $allowedTypes)) {
            throw new Exception("Tipo de arquivo inválido ($mime).");
        }

        // __DIR__ é a pasta Controller. dirname(__DIR__) é a raiz do projeto.
        $projectRoot = dirname(__DIR__);

        // Ex: C:/xampp/htdocs/seu-site/uploads/
        $physicalDir = $projectRoot . '/' . $publicTargetDir;

        // Cria a pasta se não existir (usando o caminho físico)
        if (!is_dir($physicalDir)) {
            mkdir($physicalDir, 0777, true);
        }

        // Gera nome único
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = uniqid() . '.' . $ext;

        // Move o arquivo para a pasta física correta
        if (move_uploaded_file($file['tmp_name'], $physicalDir . $fileName)) {
            // Retorna o caminho PÚBLICO para salvar no banco (uploads/arquivo.jpg)
            return $publicTargetDir . $fileName;
        }

        throw new Exception("Falha ao mover arquivo para: " . $physicalDir);
    }
}