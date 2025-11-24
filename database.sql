-- ================================================
-- BANCO DE DADOS: projeto_forum
-- Sistema de Fórum com Autenticação
-- ================================================

-- Criar banco de dados
CREATE DATABASE IF NOT EXISTS projeto_forum CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE projeto_forum;

-- ================================================
-- TABELA 1: usuarios
-- ================================================
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_usuario VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha_hash VARCHAR(255) NOT NULL,
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_nome_usuario (nome_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- TABELA 2: topicos
-- ================================================
CREATE TABLE topicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    conteudo TEXT NOT NULL,
    visualizacoes INT DEFAULT 0,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_data_criacao (data_criacao DESC),
    INDEX idx_usuario (id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- TABELA 3: respostas
-- ================================================
CREATE TABLE respostas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_topico INT NOT NULL,
    id_usuario INT NOT NULL,
    conteudo TEXT NOT NULL,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_topico) REFERENCES topicos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_topico (id_topico),
    INDEX idx_data_criacao (data_criacao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- DADOS DE TESTE (Opcional - pode remover depois)
-- ================================================

-- Inserir usuário de teste
-- Senha: 123456
INSERT INTO usuarios (nome_usuario, email, senha_hash) VALUES 
('admin', 'admin@forum.com', '$2y$10$pYjuofxJe8YzqrDNGv6SO.MmEx/LRmz4dWHiZbL6Korq61An8uScu'),
('usuario_teste', 'teste@forum.com', '$2y$10$pYjuofxJe8YzqrDNGv6SO.MmEx/LRmz4dWHiZbL6Korq61An8uScu');

-- Inserir tópicos de teste
INSERT INTO topicos (id_usuario, titulo, conteudo) VALUES
(1, 'Bem-vindo ao Fórum!', 'Este é o primeiro tópico do nosso fórum. Sinta-se à vontade para criar novos tópicos e participar das discussões!'),
(1, 'Como criar um novo tópico?', 'Para criar um novo tópico, clique no botão "Novo Tópico" na página inicial. Você precisa estar logado para criar tópicos.'),
(2, 'Apresentação', 'Olá pessoal! Sou novo por aqui e estou adorando o fórum.');

-- Inserir respostas de teste
INSERT INTO respostas (id_topico, id_usuario, conteudo) VALUES
(1, 2, 'Obrigado pela boas-vindas! Muito feliz em fazer parte desta comunidade.'),
(2, 2, 'Muito útil! Obrigado pela explicação.'),
(3, 1, 'Seja bem-vindo! Esperamos que aproveite o fórum.');

-- ================================================
-- FIM DO SCRIPT
-- ================================================