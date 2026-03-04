<?php

namespace App\Application\UseCases;

use App\Infrastructure\Persistence\UserRepository;

class RegisterUseCase
{
    private $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    public function execute(string $nome, string $usuario, string $senha): string
    {
        $userData = $this->userRepository->findByUsername($usuario);

        if (!$userData) {
            return 'not_authorized';
        }

        if (!empty($userData['senha'])) {
            return 'already_exists';
        }

        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        if ($this->userRepository->updatePassword($userData['id'], $nome, $senhaHash)) {
            return 'success';
        }

        return 'error';
    }
}
