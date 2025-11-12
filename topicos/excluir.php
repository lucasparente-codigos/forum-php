<?php
require_once '../auth/verificar_sessao.php';

// Verificar se ID foi passado
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirecionar('/forum/index.php');
}

$topico_id = intval($_GET['id']);
$conn = conectarDB();

// Buscar tópico e verificar se o usuário é o autor
$stmt = $conn->prepare("SELECT t.*, u.nome_usuario 
                        FROM topicos t 
                        INNER JOIN usuarios u ON t.id_usuario = u.id 
                        WHERE t.id = ?");
$stmt->bind_param("i", $topico_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    $stmt->close();
    $conn->close();
    redirecionar('/forum/index.php');
}

$topico = $resultado->fetch_assoc();
$stmt->close();

// Verificar se o usuário logado é o autor
if (obterUserID() != $topico['id_usuario']) {
    $conn->close();
    $_SESSION['erro'] = "Você não tem permissão para excluir este tópico!";
    redirecionar('/forum/topicos/ver.php?id=' . $topico_id);
}

$erro = '';
$sucesso = '';

// Processar exclusão
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar'])) {
    // Excluir tópico (respostas serão excluídas automaticamente por CASCADE)
    $stmt = $conn->prepare("DELETE FROM topicos WHERE id = ? AND id_usuario = ?");
    $user_id = obterUserID();
    $stmt->bind_param("ii", $topico_id, $user_id);
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        $_SESSION['sucesso'] = "Tópico excluído com sucesso!";
        redirecionar('/forum/index.php');
    } else {
        $erro = "Erro ao excluir tópico. Tente novamente.";
    }
    $stmt->close();
}

$page_title = "Excluir Tópico - Fórum";
include '../includes/header.php';
?>

<div class="page-header">
    <h1>Confirmar Exclusão</h1>
    <a href="/forum/topicos/ver.php?id=<?php echo $topico_id; ?>" class="btn btn-outline">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="19" y1="12" x2="5" y2="12"></line>
            <polyline points="12 19 5 12 12 5"></polyline>
        </svg>
        Cancelar
    </a>
</div>

<?php if ($erro): ?>
    <div class="alert alert-error"><?php echo $erro; ?></div>
<?php endif; ?>

<div class="confirm-delete-card">
    <div class="confirm-icon">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
    </div>
    
    <h2>Tem certeza que deseja excluir este tópico?</h2>
    <p class="warning-text">Esta ação não pode ser desfeita. O tópico e todas as suas respostas serão permanentemente excluídos.</p>
    
    <div class="preview-card">
        <h3><?php echo limparInput($topico['titulo']); ?></h3>
        <p><?php echo strlen($topico['conteudo']) > 200 ? substr(limparInput($topico['conteudo']), 0, 200) . '...' : limparInput($topico['conteudo']); ?></p>
        <div class="preview-meta">
            <span>Criado em: <?php echo date('d/m/Y H:i', strtotime($topico['data_criacao'])); ?></span>
            <span>Por: <?php echo limparInput($topico['nome_usuario']); ?></span>
        </div>
    </div>
    
    <form method="POST" action="" class="confirm-form">
        <input type="hidden" name="confirmar" value="1">
        <div class="confirm-actions">
            <a href="/forum/topicos/ver.php?id=<?php echo $topico_id; ?>" class="btn btn-outline">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
                Não, manter tópico
            </a>
            <button type="submit" class="btn btn-danger">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                </svg>
                Sim, excluir tópico
            </button>
        </div>
    </form>
</div>

<?php
$conn->close();
include '../includes/footer.php';
?>