<?php

namespace App\Domain\Entities;

class Aluno
{
    public function __construct(
        public int $id,
        public string $nome,
        public ?int $curso_id = null
    ) {}
}
