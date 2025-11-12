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
// EDITAR RESPOSTA
// ================================================
if ($acao === 'editar') {
    $resposta_id = intval($_POST['id'] ?? 0);
    $novo_conteudo = limparInput($_POST['conteudo'] ?? '');
    $user_id = obterUserID();
    
    // Validações
    if (empty($novo_conteudo)) {
        echo json_encode(['success' => false, 'message' => 'O conteúdo não pode estar vazio']);
        exit;
    }
    
    if (strlen($novo_conteudo) < 5) {
        echo json_encode(['success' => false, 'message' => 'Conteúdo deve ter no mínimo 5 caracteres']);
        exit;
    }
    
    // Verificar se a resposta pertence ao usuário
    $stmt = $conn->prepare("SELECT id_usuario FROM respostas WHERE id = ?");
    $stmt->bind_param("i", $resposta_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Resposta não encontrada']);
        exit;
    }
    
    $resposta = $resultado->fetch_assoc();
    $stmt->close();
    
    if ($resposta['id_usuario'] != $user_id) {
        echo json_encode(['success' => false, 'message' => 'Você não tem permissão para editar esta resposta']);
        exit;
    }
    
    // Atualizar resposta
    $stmt = $conn->prepare("UPDATE respostas SET conteudo = ? WHERE id = ? AND id_usuario = ?");
    $stmt->bind_param("sii", $novo_conteudo, $resposta_id, $user_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Resposta atualizada com sucesso!',
            'conteudo' => nl2br($novo_conteudo)
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar resposta']);
    }
    
    $stmt->close();
}

// ================================================
// BUSCAR DADOS DA RESPOSTA (para edição)
// ================================================
elseif ($acao === 'buscar') {
    $resposta_id = intval($_POST['id'] ?? 0);
    $user_id = obterUserID();
    
    $stmt = $conn->prepare("SELECT conteudo FROM respostas WHERE id = ? AND id_usuario = ?");
    $stmt->bind_param("ii", $resposta_id, $user_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows > 0) {
        $resposta = $resultado->fetch_assoc();
        echo json_encode([
            'success' => true,
            'conteudo' => $resposta['conteudo']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Resposta não encontrada ou sem permissão']);
    }
    
    $stmt->close();
}

else {
    echo json_encode(['success' => false, 'message' => 'Ação inválida']);
}

$conn->close();
?>