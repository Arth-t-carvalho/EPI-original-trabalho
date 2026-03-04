<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Entities\User;

class UserRepository
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function findByUsername(string $username): ?array
    {
        $sql = "SELECT id, nome, usuario, senha, cargo, curso_id FROM usuarios WHERE usuario = ? LIMIT 1";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    }

    public function updatePassword(int $id, string $nome, string $senhaHash): bool
    {
        $sql = "UPDATE usuarios SET nome = ?, senha = ? WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssi", $nome, $senhaHash, $id);
        return mysqli_stmt_execute($stmt);
    }
}
