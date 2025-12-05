<?php
header('Content-Type: application/json');
require_once '../config/database.php';
iniciarSessao();

if (!estaLogado()) {
    echo json_encode([
        'success' => false, 
        'message' => 'Faça login para adicionar itens ao carrinho'
    ]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$mentoriaId = isset($data['mentoria_id']) ? (int)$data['mentoria_id'] : 0;
$usuarioId = $_SESSION['usuario_id'];

if (!$mentoriaId || $mentoriaId <= 0) {
    echo json_encode([
        'success' => false, 
        'message' => 'ID da mentoria inválido'
    ]);
    exit;
}

try {
    $conn = getConnection();
    
    $stmt = $conn->prepare("SELECT id FROM mentorias WHERE id = ? AND ativo = 1");
    $stmt->execute([$mentoriaId]);
    if (!$stmt->fetch()) {
        echo json_encode([
            'success' => false, 
            'message' => 'Mentoria não encontrada ou indisponível'
        ]);
        exit;
    }
    
    $stmt = $conn->prepare("SELECT id FROM carrinho WHERE usuario_id = ? AND mentoria_id = ?");
    $stmt->execute([$usuarioId, $mentoriaId]);
    
    if ($stmt->fetch()) {
        echo json_encode([
            'success' => false, 
            'message' => 'Esta mentoria já está no seu carrinho'
        ]);
        exit;
    }
    
    $stmt = $conn->prepare("INSERT INTO carrinho (usuario_id, mentoria_id) VALUES (?, ?)");
    $stmt->execute([$usuarioId, $mentoriaId]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Mentoria adicionada ao carrinho com sucesso!'
    ]);
    
} catch (PDOException $e) {
    error_log("Erro ao adicionar ao carrinho: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Erro ao adicionar ao carrinho. Tente novamente.'
    ]);
} catch (Exception $e) {
    error_log("Erro geral ao adicionar ao carrinho: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Erro inesperado. Tente novamente.'
    ]);
}
?>
