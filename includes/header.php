<?php
if (!isset($page_title)) {
    $page_title = "Fórum de Discussão";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="/forum/assets/css/style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container">
            <div class="nav-content">
                <a href="/forum/index.php" class="logo">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                    Fórum
                </a>
                
                <div class="nav-links">
                    <a href="/forum/index.php">Início</a>
                    
                    <?php if (estaLogado()): ?>
                        <a href="/forum/topicos/criar.php" class="btn-primary">Novo Tópico</a>
                        <span class="user-info">Olá, <strong><?php echo limparInput(obterNomeUsuario()); ?></strong></span>
                        <a href="/forum/auth/logout.php" class="btn-outline">Sair</a>
                    <?php else: ?>
                        <a href="/forum/auth/login.php" class="btn-outline">Entrar</a>
                        <a href="/forum/auth/registro.php" class="btn-primary">Cadastrar</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Container Principal -->
    <main class="main-container">
        <div class="container"></div>