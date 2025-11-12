<?php
require_once '../config.php';

// Se já estiver logado, redirecionar para home
if (estaLogado()) {
    redirecionar('/forum/index.php');
}

$erro = '';

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = limparInput($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    
    // Validações básicas
    if (empty($email) || empty($senha)) {
        $erro = "Email e senha são obrigatórios!";
    } else {
        $conn = conectarDB();
        
        // Buscar usuário por email
        $stmt = $conn->prepare("SELECT id, nome_usuario, senha_hash FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows === 1) {
            $usuario = $resultado->fetch_assoc();
            
            // Verificar senha
            if (password_verify($senha, $usuario['senha_hash'])) {
                // Login bem-sucedido
                $_SESSION['user_id'] = $usuario['id'];
                $_SESSION['nome_usuario'] = $usuario['nome_usuario'];
                
                redirecionar('/forum/index.php');
            } else {
                $erro = "Email ou senha incorretos!";
            }
        } else {
            $erro = "Email ou senha incorretos!";
        }
        
        $stmt->close();
        $conn->close();
    }
}

$page_title = "Login - Fórum";
include '../includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <h1>Entrar</h1>
        <p class="subtitle">Bem-vindo de volta!</p>
        
        <?php if ($erro): ?>
            <div class="alert alert-error"><?php echo $erro; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" class="auth-form">
            <div class="form-group">
                <label for="email">Email</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="seu@email.com"
                    value="<?php echo isset($email) ? $email : ''; ?>"
                    required
                >
            </div>
            
            <div class="form-group">
                <label for="senha">Senha</label>
                <input 
                    type="password" 
                    id="senha" 
                    name="senha" 
                    placeholder="Digite sua senha"
                    required
                >
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Entrar</button>
        </form>
        
        <div class="auth-footer">
            Não tem uma conta? <a href="/forum/auth/registro.php">Cadastre-se</a>
        </div>
        
        <div class="demo-info">
            <hr>
            <p><strong>Conta de teste:</strong></p>
            <p>Email: admin@forum.com</p>
            <p>Senha: 123456</p>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>