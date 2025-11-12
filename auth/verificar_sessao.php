<?php
// ================================================
// MIDDLEWARE DE VERIFICAÇÃO DE SESSÃO
// Incluir este arquivo no topo de páginas protegidas
// ================================================

require_once __DIR__ . '/../config.php';

// Verificar se o usuário está logado
if (!estaLogado()) {
    // Salvar URL atual para redirecionar após login (opcional)
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    
    // Redirecionar para login
    redirecionar('/forum/auth/login.php');
}
?>