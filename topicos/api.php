<?php
require_once '../config.php';

// Definir header para JSON
header('Content-Type: application/json');

// Verificar se está logado
if (!estaLogado()) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

// Verificar método da requisição
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

$acao = $_POST['acao'] ?? '';
$conn = conectarDB();

// ================================================
// EDITAR TÓPICO
// ================================================
if ($acao === 'editar') {
    $topico_id = intval($_POST['id'] ?? 0);
    $novo_titulo = limparInput($_POST['titulo'] ?? '');
    $novo_conteudo = limparInput($_POST['conteudo'] ?? '');
    $user_id = obterUserID();
    
    // Validações
    if (empty($novo_titulo) || empty($novo_conteudo)) {
        echo json_encode(['success' => false, 'message' => 'Título e conteúdo são obrigatórios']);
        exit;
    }
    
    if (strlen($novo_titulo) < 5) {
        echo json_encode(['success' => false, 'message' => 'Título deve ter no mínimo 5 caracteres']);
        exit;
    }
    
    if (strlen($novo_conteudo) < 10) {
        echo json_encode(['success' => false, 'message' => 'Conteúdo deve ter no mínimo 10 caracteres']);
        exit;
    }
    
    // Verificar se o tópico pertence ao usuário
    $stmt = $conn->prepare("SELECT id_usuario FROM topicos WHERE id = ?");
    $stmt->bind_param("i", $topico_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Tópico não encontrado']);
        exit;
    }
    
    $topico = $resultado->fetch_assoc();
    $stmt->close();
    
    if ($topico['id_usuario'] != $user_id) {
        echo json_encode(['success' => false, 'message' => 'Você não tem permissão para editar este tópico']);
        exit;
    }
    
    // Atualizar tópico
    $stmt = $conn->prepare("UPDATE topicos SET titulo = ?, conteudo = ? WHERE id = ? AND id_usuario = ?");
    $stmt->bind_param("ssii", $novo_titulo, $novo_conteudo, $topico_id, $user_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Tópico atualizado com sucesso!',
            'titulo' => $novo_titulo,
            'conteudo' => nl2br($novo_conteudo)
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar tópico']);
    }
    
    $stmt->close();
}

// ================================================
// BUSCAR DADOS DO TÓPICO (para edição)
// ================================================
elseif ($acao === 'buscar') {
    $topico_id = intval($_POST['id'] ?? 0);
    $user_id = obterUserID();
    
    $stmt = $conn->prepare("SELECT titulo, conteudo FROM topicos WHERE id = ? AND id_usuario = ?");
    $stmt->bind_param("ii", $topico_id, $user_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows > 0) {
        $topico = $resultado->fetch_assoc();
        echo json_encode([
            'success' => true,
            'titulo' => $topico['titulo'],
            'conteudo' => $topico['conteudo']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Tópico não encontrado ou sem permissão']);
    }
    
    $stmt->close();
}

else {
    echo json_encode(['success' => false, 'message' => 'Ação inválida']);
}

$conn->close();
?>