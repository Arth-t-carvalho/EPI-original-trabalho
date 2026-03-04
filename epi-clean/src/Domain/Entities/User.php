<?php

namespace App\Domain\Entities;

class User
{
    public function __construct(
        public int $id,
        public string $nome,
        public string $usuario,
        public string $senha,
        public string $cargo,
        public ?int $curso_id = null
    ) {}
}
