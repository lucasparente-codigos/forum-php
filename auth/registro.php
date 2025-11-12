<?php
require_once '../config.php';

// Se já estiver logado, redirecionar para home
if (estaLogado()) {
    redirecionar('/forum/index.php');
}

$erro = '';
$sucesso = '';

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome_usuario = limparInput($_POST['nome_usuario'] ?? '');
    $email = limparInput($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';
    
    // Validações
    if (empty($nome_usuario) || empty($email) || empty($senha)) {
        $erro = "Todos os campos são obrigatórios!";
    } elseif (strlen($nome_usuario) < 3) {
        $erro = "Nome de usuário deve ter no mínimo 3 caracteres!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "Email inválido!";
    } elseif (strlen($senha) < 6) {
        $erro = "Senha deve ter no mínimo 6 caracteres!";
    } elseif ($senha !== $confirmar_senha) {
        $erro = "As senhas não coincidem!";
    } else {
        // Tentar cadastrar
        $conn = conectarDB();
        
        // Verificar se usuário ou email já existem
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE nome_usuario = ? OR email = ?");
        $stmt->bind_param("ss", $nome_usuario, $email);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows > 0) {
            $erro = "Nome de usuário ou email já cadastrados!";
        } else {
            // Criar hash da senha
            $senha_hash = password_hash($senha, PASSWORD_BCRYPT);
            
            // Inserir novo usuário
            $stmt = $conn->prepare("INSERT INTO usuarios (nome_usuario, email, senha_hash) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $nome_usuario, $email, $senha_hash);
            
            if ($stmt->execute()) {
                $sucesso = "Cadastro realizado com sucesso! Redirecionando...";
                echo "<script>
                    setTimeout(function() {
                        window.location.href = '/forum/auth/login.php';
                    }, 2000);
                </script>";
            } else {
                $erro = "Erro ao cadastrar usuário. Tente novamente.";
            }
        }
        
        $stmt->close();
        $conn->close();
    }
}

$page_title = "Cadastro - Fórum";
include '../includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <h1>Criar Conta</h1>
        <p class="subtitle">Junte-se à nossa comunidade</p>
        
        <?php if ($erro): ?>
            <div class="alert alert-error"><?php echo $erro; ?></div>
        <?php endif; ?>
        
        <?php if ($sucesso): ?>
            <div class="alert alert-success"><?php echo $sucesso; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" class="auth-form">
            <div class="form-group">
                <label for="nome_usuario">Nome de Usuário</label>
                <input 
                    type="text" 
                    id="nome_usuario" 
                    name="nome_usuario" 
                    placeholder="Digite seu nome de usuário"
                    value="<?php echo isset($nome_usuario) ? $nome_usuario : ''; ?>"
                    required
                    minlength="3"
                >
            </div>
            
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
                    placeholder="Mínimo 6 caracteres"
                    required
                    minlength="6"
                >
            </div>
            
            <div class="form-group">
                <label for="confirmar_senha">Confirmar Senha</label>
                <input 
                    type="password" 
                    id="confirmar_senha" 
                    name="confirmar_senha" 
                    placeholder="Digite a senha novamente"
                    required
                    minlength="6"
                >
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Cadastrar</button>
        </form>
        
        <div class="auth-footer">
            Já tem uma conta? <a href="/forum/auth/login.php">Faça login</a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>