-- SQL para criar o banco de dados e tabelas do EPI GUARD
CREATE DATABASE IF NOT EXISTS epi_guard CHARACTER SET utf8mb4;
USE epi_guard;

-- Tabela de Cursos
CREATE TABLE IF NOT EXISTS cursos (
    id INT NOT NULL AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    sigla VARCHAR(10),
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de Usuários (Administradores/Professores)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT NOT NULL AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    cargo ENUM('super_admin', 'supervisor', 'professor') NOT NULL,
    curso_id INT DEFAULT NULL, -- Adicionada a coluna faltante
    turno VARCHAR(50),
    PRIMARY KEY (id),
    FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de Alunos
CREATE TABLE IF NOT EXISTS alunos (
    id INT NOT NULL AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    curso_id INT,
    turno VARCHAR(50),
    foto_referencia VARCHAR(255),
    imagem LONGBLOB,
    PRIMARY KEY (id),
    FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de EPIs
CREATE TABLE IF NOT EXISTS epis (
    id INT NOT NULL AUTO_INCREMENT,
    nome VARCHAR(50) NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de Ocorrências
CREATE TABLE IF NOT EXISTS ocorrencias (
    id INT NOT NULL AUTO_INCREMENT,
    aluno_id INT NOT NULL,
    data_hora DATETIME NOT NULL,
    epi_id INT NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE CASCADE,
    FOREIGN KEY (epi_id) REFERENCES epis(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inserir Dados Iniciais (Opcional - Para teste)
INSERT IGNORE INTO epis (id, nome) VALUES (1, 'Óculos'), (2, 'Capacete');
INSERT IGNORE INTO usuarios (nome, usuario, senha, cargo) VALUES ('Administrador', 'admin@gmail.com', '123', 'super_admin');
