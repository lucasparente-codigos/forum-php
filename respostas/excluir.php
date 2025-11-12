<?php
require_once '../auth/verificar_sessao.php';

// Verificar se ID foi passado
if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['topico'])) {
    redirecionar('/forum/index.php');
}

$resposta_id = intval($_GET['id']);
$topico_id = intval($_GET['topico']);
$conn = conectarDB();

// Buscar resposta e verificar se o usuário é o autor
$stmt = $conn->prepare("SELECT r.*, u.nome_usuario, t.titulo as topico_titulo
                        FROM respostas r 
                        INNER JOIN usuarios u ON r.id_usuario = u.id 
                        INNER JOIN topicos t ON r.id_topico = t.id
                        WHERE r.id = ?");
$stmt->bind_param("i", $resposta_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    $stmt->close();
    $conn->close();
    redirecionar('/forum/index.php');
}

$resposta = $resultado->fetch_assoc();
$stmt->close();

// Verificar se o usuário logado é o autor
if (obterUserID() != $resposta['id_usuario']) {
    $conn->close();
    $_SESSION['erro'] = "Você não tem permissão para excluir esta resposta!";
    redirecionar('/forum/topicos/ver.php?id=' . $topico_id);
}

$erro = '';

// Processar exclusão
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar'])) {
    $stmt = $conn->prepare("DELETE FROM respostas WHERE id = ? AND id_usuario = ?");
    $user_id = obterUserID();
    $stmt->bind_param("ii", $resposta_id, $user_id);
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        $_SESSION['sucesso'] = "Resposta excluída com sucesso!";
        redirecionar('/forum/topicos/ver.php?id=' . $topico_id);
    } else {
        $erro = "Erro ao excluir resposta. Tente novamente.";
    }
    $stmt->close();
}

$page_title = "Excluir Resposta - Fórum";
include '../includes/header.php';
?>

<div class="page-header">
    <h1>Confirmar Exclusão de Resposta</h1>
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
    
    <h2>Tem certeza que deseja excluir esta resposta?</h2>
    <p class="warning-text">Esta ação não pode ser desfeita. A resposta será permanentemente excluída.</p>
    
    <div class="preview-card">
        <h4>Tópico: <?php echo limparInput($resposta['topico_titulo']); ?></h4>
        <p class="resposta-preview"><?php echo strlen($resposta['conteudo']) > 200 ? substr(limparInput($resposta['conteudo']), 0, 200) . '...' : limparInput($resposta['conteudo']); ?></p>
        <div class="preview-meta">
            <span>Postada em: <?php echo date('d/m/Y H:i', strtotime($resposta['data_criacao'])); ?></span>
            <span>Por: <?php echo limparInput($resposta['nome_usuario']); ?></span>
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
                Não, manter resposta
            </a>
            <button type="submit" class="btn btn-danger">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                </svg>
                Sim, excluir resposta
            </button>
        </div>
    </form>
</div>

<?php
$conn->close();
include '../includes/footer.php';
?>