<?php
require_once '../auth/verificar_sessao.php';

$erro = '';
$sucesso = '';

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = limparInput($_POST['titulo'] ?? '');
    $conteudo = limparInput($_POST['conteudo'] ?? '');
    $id_usuario = obterUserID();
    
    // Validações
    if (empty($titulo) || empty($conteudo)) {
        $erro = "Título e conteúdo são obrigatórios!";
    } elseif (strlen($titulo) < 5) {
        $erro = "Título deve ter no mínimo 5 caracteres!";
    } elseif (strlen($conteudo) < 10) {
        $erro = "Conteúdo deve ter no mínimo 10 caracteres!";
    } else {
        $conn = conectarDB();
        
        $stmt = $conn->prepare("INSERT INTO topicos (id_usuario, titulo, conteudo) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $id_usuario, $titulo, $conteudo);
        
        if ($stmt->execute()) {
            $topico_id = $conn->insert_id;
            $sucesso = "Tópico criado com sucesso! Redirecionando...";
            echo "<script>
                setTimeout(function() {
                    window.location.href = '/forum/topicos/ver.php?id=$topico_id';
                }, 1500);
            </script>";
        } else {
            $erro = "Erro ao criar tópico. Tente novamente.";
        }
        
        $stmt->close();
        $conn->close();
    }
}

$page_title = "Criar Novo Tópico - Fórum";
include '../includes/header.php';
?>

<div class="page-header">
    <h1>Criar Novo Tópico</h1>
    <a href="/forum/index.php" class="btn btn-outline">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="19" y1="12" x2="5" y2="12"></line>
            <polyline points="12 19 5 12 12 5"></polyline>
        </svg>
        Voltar
    </a>
</div>

<?php if ($erro): ?>
    <div class="alert alert-error"><?php echo $erro; ?></div>
<?php endif; ?>

<?php if ($sucesso): ?>
    <div class="alert alert-success"><?php echo $sucesso; ?></div>
<?php endif; ?>

<div class="form-card">
    <form method="POST" action="" class="topico-form">
        <div class="form-group">
            <label for="titulo">Título do Tópico</label>
            <input 
                type="text" 
                id="titulo" 
                name="titulo" 
                placeholder="Digite um título claro e descritivo"
                value="<?php echo isset($titulo) ? $titulo : ''; ?>"
                required
                minlength="5"
                maxlength="200"
            >
            <small>Mínimo 5 caracteres, máximo 200</small>
        </div>
        
        <div class="form-group">
            <label for="conteudo">Conteúdo</label>
            <textarea 
                id="conteudo" 
                name="conteudo" 
                rows="10"
                placeholder="Escreva o conteúdo do seu tópico aqui..."
                required
                minlength="10"
            ><?php echo isset($conteudo) ? $conteudo : ''; ?></textarea>
            <small>Mínimo 10 caracteres</small>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                    <polyline points="7 3 7 8 15 8"></polyline>
                </svg>
                Publicar Tópico
            </button>
            <a href="/forum/index.php" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>