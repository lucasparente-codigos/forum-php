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
                <button id="theme-switcher" class="btn-icon" title="Mudar tema">
                    <svg id="theme-icon-light" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="5"></circle>
                        <line x1="12" y1="1" x2="12" y2="3"></line>
                        <line x1="12" y1="21" x2="12" y2="23"></line>
                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                        <line x1="1" y1="12" x2="3" y2="12"></line>
                        <line x1="21" y1="12" x2="23" y2="12"></line>
                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                    </svg>
                    <svg id="theme-icon-dark" class="hidden" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                    </svg>
                </button>
                
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