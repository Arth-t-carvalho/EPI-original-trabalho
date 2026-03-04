<?php

namespace App\Application\UseCases;

use App\Infrastructure\Persistence\UserRepository;

class LoginUseCase
{
    private $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    public function execute(string $usuario, string $senha): ?array
    {
        $userData = $this->userRepository->findByUsername($usuario);

        if (!$userData) {
            return null;
        }

        // Suporte a texto puro (legado) e hash (novo)
        if ($senha === $userData['senha'] || password_verify($senha, $userData['senha'])) {
            return $userData;
        }

        return null;
    }
}
