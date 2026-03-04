<?php

namespace App\Domain\Entities;

class Infracao
{
    public function __construct(
        public int $id,
        public int $aluno_id,
        public string $data_hora,
        public ?string $descricao = null
    ) {}
}
