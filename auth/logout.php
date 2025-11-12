<?php
require_once '../config.php';

// Destruir todas as variáveis de sessão
$_SESSION = array();

// Destruir o cookie de sessão
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Destruir a sessão
session_destroy();

// Redirecionar para página de login
redirecionar('/forum/auth/login.php');
?>