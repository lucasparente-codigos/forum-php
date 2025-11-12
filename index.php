<?php
require_once 'config.php';

// Configuração de paginação
$topicos_por_pagina = 10;
$pagina_atual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($pagina_atual - 1) * $topicos_por_pagina;

// Busca
$busca = isset($_GET['busca']) ? limparInput($_GET['busca']) : '';
$where = '';
$params = [];
$types = '';

if (!empty($busca)) {
    $where = "WHERE t.titulo LIKE ?";
    $busca_param = "%$busca%";
    $params[] = $busca_param;
    $types = 's';
}

$conn = conectarDB();

// Contar total de tópicos
$sql_count = "SELECT COUNT(*) as total FROM topicos t $where";
if (!empty($params)) {
    $stmt_count = $conn->prepare($sql_count);
    $stmt_count->bind_param($types, ...$params);
    $stmt_count->execute();
    $total_topicos = $stmt_count->get_result()->fetch_assoc()['total'];
    $stmt_count->close();
} else {
    $total_topicos = $conn->query($sql_count)->fetch_assoc()['total'];
}

$total_paginas = ceil($total_topicos / $topicos_por_pagina);

// Buscar tópicos com paginação
$sql = "SELECT t.id, t.titulo, t.conteudo, t.data_criacao, t.visualizacoes,
               u.nome_usuario, u.id as id_autor,
               (SELECT COUNT(*) FROM respostas WHERE id_topico = t.id) as total_respostas
        FROM topicos t
        INNER JOIN usuarios u ON t.id_usuario = u.id
        $where
        ORDER BY t.data_criacao DESC
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $params[] = $topicos_por_pagina;
    $params[] = $offset;
    $types .= 'ii';
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param("ii", $topicos_por_pagina, $offset);
}

$stmt->execute();
$resultado = $stmt->get_result();

$page_title = "Fórum de Discussão - Início";
include 'includes/header.php';
?>

<div class="page-header">
    <h1>Discussões Recentes</h1>
    <?php if (estaLogado()): ?>
        <a href="/forum/topicos/criar.php" class="btn btn-primary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Novo Tópico
        </a>
    <?php endif; ?>
</div>

<?php if (!estaLogado()): ?>
    <div class="alert alert-info">
        <strong>Seja bem-vindo!</strong> Faça login ou cadastre-se para criar tópicos e participar das discussões.
    </div>
<?php endif; ?>

<!-- Barra de Busca -->
<div class="search-bar">
    <form method="GET" action="" class="search-form">
        <div class="search-input-group">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
            </svg>
            <input 
                type="text" 
                name="busca" 
                placeholder="Buscar tópicos..."
                value="<?php echo htmlspecialchars($busca); ?>"
            >
            <?php if (!empty($busca)): ?>
                <a href="/forum/index.php" class="clear-search" title="Limpar busca">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </a>
            <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary">Buscar</button>
    </form>
</div>

<?php if (!empty($busca)): ?>
    <div class="search-results-info">
        <p>Encontrados <strong><?php echo $total_topicos; ?></strong> resultado(s) para "<strong><?php echo htmlspecialchars($busca); ?></strong>"</p>
    </div>
<?php endif; ?>

<div class="topicos-lista">
    <?php if ($resultado && $resultado->num_rows > 0): ?>
        <?php while ($topico = $resultado->fetch_assoc()): ?>
            <div class="topico-card">
                <div class="topico-info">
                    <h2 class="topico-titulo">
                        <a href="/forum/topicos/ver.php?id=<?php echo $topico['id']; ?>">
                            <?php echo limparInput($topico['titulo']); ?>
                        </a>
                    </h2>
                    <p class="topico-preview">
                        <?php 
                        $preview = limparInput($topico['conteudo']);
                        echo strlen($preview) > 150 ? substr($preview, 0, 150) . '...' : $preview;
                        ?>
                    </p>
                    <div class="topico-meta">
                        <span class="autor">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            <a href="/forum/usuario/perfil.php?id=<?php echo $topico['id_autor']; ?>">
                                <?php echo limparInput($topico['nome_usuario']); ?>
                            </a>
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
                <div class="topico-stats">
                    <div class="stat">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                        <span><?php echo $topico['total_respostas']; ?></span>
                    </div>
                    <div class="stat">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <span><?php echo $topico['visualizacoes']; ?></span>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty-state">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
            <h3><?php echo !empty($busca) ? 'Nenhum resultado encontrado' : 'Nenhum tópico ainda'; ?></h3>
            <p><?php echo !empty($busca) ? 'Tente buscar com outros termos' : 'Seja o primeiro a criar uma discussão!'; ?></p>
            <?php if (estaLogado() && empty($busca)): ?>
                <a href="/forum/topicos/criar.php" class="btn btn-primary">Criar Primeiro Tópico</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Paginação -->
<?php if ($total_paginas > 1): ?>
    <div class="pagination">
        <?php if ($pagina_atual > 1): ?>
            <a href="?pagina=<?php echo ($pagina_atual - 1); ?><?php echo !empty($busca) ? '&busca=' . urlencode($busca) : ''; ?>" class="pagination-btn">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
                Anterior
            </a>
        <?php endif; ?>
        
        <div class="pagination-numbers">
            <?php
            $inicio = max(1, $pagina_atual - 2);
            $fim = min($total_paginas, $pagina_atual + 2);
            
            if ($inicio > 1) {
                echo '<a href="?pagina=1' . (!empty($busca) ? '&busca=' . urlencode($busca) : '') . '" class="pagination-number">1</a>';
                if ($inicio > 2) echo '<span class="pagination-dots">...</span>';
            }
            
            for ($i = $inicio; $i <= $fim; $i++) {
                $active = $i === $pagina_atual ? 'active' : '';
                echo '<a href="?pagina=' . $i . (!empty($busca) ? '&busca=' . urlencode($busca) : '') . '" class="pagination-number ' . $active . '">' . $i . '</a>';
            }
            
            if ($fim < $total_paginas) {
                if ($fim < $total_paginas - 1) echo '<span class="pagination-dots">...</span>';
                echo '<a href="?pagina=' . $total_paginas . (!empty($busca) ? '&busca=' . urlencode($busca) : '') . '" class="pagination-number">' . $total_paginas . '</a>';
            }
            ?>
        </div>
        
        <?php if ($pagina_atual < $total_paginas): ?>
            <a href="?pagina=<?php echo ($pagina_atual + 1); ?><?php echo !empty($busca) ? '&busca=' . urlencode($busca) : ''; ?>" class="pagination-btn">
                Próxima
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php
$stmt->close();
$conn->close();
include 'includes/footer.php';
?>