<?php

namespace App\Infrastructure\Persistence;

class Database
{
    private static $instance = null;
    private $connection;

    private function __construct()
    {
        $config = require __DIR__ . '/../../../config/database.php';

        $host = $config['host'] ?? 'localhost';
        $user = $config['username'] ?? 'root';
        $pass = $config['password'] ?? '';
        $db = $config['database'] ?? 'epi_guard';
        $port = $config['port'] ?? 3308;

        $this->connection = mysqli_connect($host, $user, $pass, $db, $port);

        if (!$this->connection) {
            error_log("Falha na conexão: " . mysqli_connect_error());
            throw new \Exception("Erro interno de conexão com o banco de dados.");
        }

        mysqli_set_charset($this->connection, "utf8mb4");
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->connection;
    }
}
