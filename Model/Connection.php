<?php

namespace Model;

use PDO;
use PDOException;

require_once __DIR__ . '/../Config/Configuration.php';

class Connection
{
    private static $stmt;

    // Função para obter conexão com o banco de dados
    public static function getInstance(): PDO
    {
        if (empty(self::$stmt)) {
            try {
                self::$stmt = new PDO('mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . '', DB_USER, DB_PASSWORD);

            } catch (PDOException $error) {
                echo "Erro de conexão com o banco de dados: " . $error->getMessage();
            }
        }
        return self::$stmt;
    }
}