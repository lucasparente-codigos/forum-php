<?php
require_once '../config.php';

// Verificar se ID foi passado
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirecionar('/forum/index.php');
}

$usuario_id = intval($_GET['id']);
$conn = conectarDB();

// Buscar dados do usuário
$stmt = $conn->prepare("SELECT id, nome_usuario, email, data_cadastro FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    $stmt->close();
    $conn->close();
    redirecionar('/forum/index.php');
}

$usuario = $resultado->fetch_assoc();
$stmt->close();

// Verificar se é o próprio perfil
$eh_proprio_perfil = estaLogado() && obterUserID() == $usuario_id;

// Contar tópicos criados
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM topicos WHERE id_usuario = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$total_topicos = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Contar respostas
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM respostas WHERE id_usuario = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$total_respostas = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Buscar tópicos recentes do usuário
$stmt = $conn->prepare("SELECT t.id, t.titulo, t.data_criacao, t.visualizacoes,
                        (SELECT COUNT(*) FROM respostas WHERE id_topico = t.id) as total_respostas
                        FROM topicos t
                        WHERE t.id_usuario = ?
                        ORDER BY t.data_criacao DESC
                        LIMIT 5");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$topicos_recentes = $stmt->get_result();
$stmt->close();

// Buscar respostas recentes
$stmt = $conn->prepare("SELECT r.id, r.conteudo, r.data_criacao, t.id as topico_id, t.titulo as topico_titulo
                        FROM respostas r
                        INNER JOIN topicos t ON r.id_topico = t.id
                        WHERE r.id_usuario = ?
                        ORDER BY r.data_criacao DESC
                        LIMIT 5");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$respostas_recentes = $stmt->get_result();
$stmt->close();

// Função para gerar iniciais do avatar
function gerarIniciais($nome) {
    $palavras = explode(' ', $nome);
    if (count($palavras) >= 2) {
        return strtoupper(substr($palavras[0], 0, 1) . substr($palavras[1], 0, 1));
    }
    return strtoupper(substr($nome, 0, 2));
}

$page_title = "Perfil de " . limparInput($usuario['nome_usuario']) . " - Fórum";
include '../includes/header.php';
?>

<div class="perfil-container">
    <!-- Card do Perfil -->
    <div class="perfil-card">
        <div class="perfil-header">
            <div class="avatar-large">
                <?php echo gerarIniciais($usuario['nome_usuario']); ?>
            </div>
            <div class="perfil-info">
                <h1><?php echo limparInput($usuario['nome_usuario']); ?></h1>
                <?php if ($eh_proprio_perfil): ?>
                    <p class="email"><?php echo limparInput($usuario['email']); ?></p>
                <?php endif; ?>
                <p class="membro-desde">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    Membro desde <?php echo date('d/m/Y', strtotime($usuario['data_cadastro'])); ?>
                </p>
            </div>
        </div>
        
        <div class="perfil-stats">
            <div class="stat-item">
                <div class="stat-value"><?php echo $total_topicos; ?></div>
                <div class="stat-label">Tópicos</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?php echo $total_respostas; ?></div>
                <div class="stat-label">Respostas</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?php echo $total_topicos + $total_respostas; ?></div>
                <div class="stat-label">Total Posts</div>
            </div>
        </div>
    </div>
    
    <!-- Tópicos Recentes -->
    <div class="atividades-section">
        <h2>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
            Tópicos Criados
        </h2>
        
        <?php if ($topicos_recentes->num_rows > 0): ?>
            <div class="atividades-lista">
                <?php while ($topico = $topicos_recentes->fetch_assoc()): ?>
                    <div class="atividade-item">
                        <h3>
                            <a href="/forum/topicos/ver.php?id=<?php echo $topico['id']; ?>">
                                <?php echo limparInput($topico['titulo']); ?>
                            </a>
                        </h3>
                        <div class="atividade-meta">
                            <span>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                </svg>
                                <?php echo date('d/m/Y', strtotime($topico['data_criacao'])); ?>
                            </span>
                            <span>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                </svg>
                                <?php echo $topico['total_respostas']; ?> respostas
                            </span>
                            <span>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <?php echo $topico['visualizacoes']; ?> views
                            </span>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="sem-atividades">Nenhum tópico criado ainda.</p>
        <?php endif; ?>
    </div>
    
    <!-- Respostas Recentes -->
    <div class="atividades-section">
        <h2>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="9 11 12 14 22 4"></polyline>
                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
            </svg>
            Respostas Recentes
        </h2>
        
        <?php if ($respostas_recentes->num_rows > 0): ?>
            <div class="atividades-lista">
                <?php while ($resposta = $respostas_recentes->fetch_assoc()): ?>
                    <div class="atividade-item">
                        <p class="resposta-preview">
                            <?php 
                            $preview = limparInput($resposta['conteudo']);
                            echo strlen($preview) > 100 ? substr($preview, 0, 100) . '...' : $preview;
                            ?>
                        </p>
                        <div class="atividade-meta">
                            <span>Em resposta a:</span>
                            <a href="/forum/topicos/ver.php?id=<?php echo $resposta['topico_id']; ?>">
                                <?php echo limparInput($resposta['topico_titulo']); ?>
                            </a>
                            <span class="data-resposta">
                                • <?php echo date('d/m/Y', strtotime($resposta['data_criacao'])); ?>
                            </span>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="sem-atividades">Nenhuma resposta ainda.</p>
        <?php endif; ?>
    </div>
</div>

<?php
$conn->close();
include '../includes/footer.php';
?>