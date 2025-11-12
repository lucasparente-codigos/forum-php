<?php
// ================================================
// CONFIGURAÇÃO DO BANCO DE DADOS
// ================================================

// Definir constantes de conexão
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'projeto_forum');

// Configurações de sessão
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);

// Iniciar sessão se ainda não foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Função para conectar ao banco de dados
function conectarDB() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        // Verificar conexão
        if ($conn->connect_error) {
            throw new Exception("Erro de conexão: " . $conn->connect_error);
        }
        
        // Definir charset para UTF-8
        $conn->set_charset("utf8mb4");
        
        return $conn;
    } catch (Exception $e) {
        die("Erro ao conectar ao banco de dados: " . $e->getMessage());
    }
}

// Função para escapar strings (prevenir XSS)
function limparInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Função para verificar se usuário está logado
function estaLogado() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Função para obter ID do usuário logado
function obterUserID() {
    return $_SESSION['user_id'] ?? null;
}

// Função para obter nome do usuário logado
function obterNomeUsuario() {
    return $_SESSION['nome_usuario'] ?? null;
}

// Função para redirecionar
function redirecionar($url) {
    header("Location: $url");
    exit();
}

// Definir timezone
date_default_timezone_set('America/Sao_Paulo');

// Habilitar exibição de erros apenas em desenvolvimento
// COMENTAR ESSA LINHA EM PRODUÇÃO
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>