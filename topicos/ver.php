<?php
require_once '../config.php';

// Verificar se ID foi passado
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirecionar('/forum/index.php');
}

$topico_id = intval($_GET['id']);
$conn = conectarDB();

// Buscar dados do tópico
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

// Buscar respostas do tópico
$stmt = $conn->prepare("SELECT r.*, u.nome_usuario 
                        FROM respostas r 
                        INNER JOIN usuarios u ON r.id_usuario = u.id 
                        WHERE r.id_topico = ? 
                        ORDER BY r.data_criacao ASC");
$stmt->bind_param("i", $topico_id);
$stmt->execute();
$respostas = $stmt->get_result();
$stmt->close();

// Processar nova resposta
$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && estaLogado()) {
    $conteudo_resposta = limparInput($_POST['conteudo_resposta'] ?? '');
    $id_usuario = obterUserID();
    
    if (empty($conteudo_resposta)) {
        $erro = "A resposta não pode estar vazia!";
    } elseif (strlen($conteudo_resposta) < 5) {
        $erro = "Resposta deve ter no mínimo 5 caracteres!";
    } else {
        $stmt = $conn->prepare("INSERT INTO respostas (id_topico, id_usuario, conteudo) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $topico_id, $id_usuario, $conteudo_resposta);
        
        if ($stmt->execute()) {
            $sucesso = "Resposta publicada com sucesso!";
            // Recarregar respostas
            $stmt->close();
            $stmt = $conn->prepare("SELECT r.*, u.nome_usuario 
                                    FROM respostas r 
                                    INNER JOIN usuarios u ON r.id_usuario = u.id 
                                    WHERE r.id_topico = ? 
                                    ORDER BY r.data_criacao ASC");
            $stmt->bind_param("i", $topico_id);
            $stmt->execute();
            $respostas = $stmt->get_result();
        } else {
            $erro = "Erro ao publicar resposta. Tente novamente.";
        }
        $stmt->close();
    }
}

$page_title = limparInput($topico['titulo']) . " - Fórum";
include '../includes/header.php';
?>

<div class="page-header">
    <h1>Detalhes do Tópico</h1>
    <a href="/forum/index.php" class="btn btn-outline">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="19" y1="12" x2="5" y2="12"></line>
            <polyline points="12 19 5 12 12 5"></polyline>
        </svg>
        Voltar
    </a>
</div>

<!-- Tópico Principal -->
<div class="topico-detalhe">
    <div class="topico-header">
        <h2><?php echo limparInput($topico['titulo']); ?></h2>
        <div class="topico-meta">
            <span class="autor">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <?php echo limparInput($topico['nome_usuario']); ?>
            </span>
            <span class="data">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                <?php 
                $data = new DateTime($topico['data_criacao']);
                echo $data->format('d/m/Y H:i');
                ?>
            </span>
        </div>
    </div>
    <div class="topico-conteudo">
        <?php echo nl2br(limparInput($topico['conteudo'])); ?>
    </div>
</div>

<!-- Respostas -->
<div class="respostas-secao">
    <h3>
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
        </svg>
        Respostas (<?php echo $respostas->num_rows; ?>)
    </h3>
    
    <?php if ($respostas->num_rows > 0): ?>
        <div class="respostas-lista">
            <?php while ($resposta = $respostas->fetch_assoc()): ?>
                <div class="resposta-card">
                    <div class="resposta-header">
                        <span class="autor"><?php echo limparInput($resposta['nome_usuario']); ?></span>
                        <span class="data">
                            <?php 
                            $data_resp = new DateTime($resposta['data_criacao']);
                            echo $data_resp->format('d/m/Y H:i');
                            ?>
                        </span>
                    </div>
                    <div class="resposta-conteudo">
                        <?php echo nl2br(limparInput($resposta['conteudo'])); ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p class="sem-respostas">Nenhuma resposta ainda. Seja o primeiro a responder!</p>
    <?php endif; ?>
</div>

<!-- Formulário de Resposta -->
<?php if (estaLogado()): ?>
    <div class="responder-secao">
        <h3>Deixe sua resposta</h3>
        
        <?php if ($erro): ?>
            <div class="alert alert-error"><?php echo $erro; ?></div>
        <?php endif; ?>
        
        <?php if ($sucesso): ?>
            <div class="alert alert-success"><?php echo $sucesso; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" class="resposta-form">
            <div class="form-group">
                <textarea 
                    id="conteudo_resposta" 
                    name="conteudo_resposta" 
                    rows="6"
                    placeholder="Digite sua resposta..."
                    required
                    minlength="5"
                ></textarea>
            </div>
            <button type="submit" class="btn btn-primary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="22" y1="2" x2="11" y2="13"></line>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                </svg>
                Publicar Resposta
            </button>
        </form>
    </div>
<?php else: ?>
    <div class="alert alert-info">
        <strong>Quer participar da discussão?</strong> 
        <a href="/forum/auth/login.php">Faça login</a> ou 
        <a href="/forum/auth/registro.php">cadastre-se</a> para responder.
    </div>
<?php endif; ?>

<?php
$conn->close();
include '../includes/footer.php';
?>