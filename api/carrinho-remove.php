<?php
header('Content-Type: application/json');
require_once '../config/database.php';
iniciarSessao();

if (!estaLogado()) {
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$mentoriaId = $data['mentoria_id'] ?? 0;
$usuarioId = $_SESSION['usuario_id'];

try {
    $conn = getConnection();
    $stmt = $conn->prepare("DELETE FROM carrinho WHERE usuario_id = ? AND mentoria_id = ?");
    $stmt->execute([$usuarioId, $mentoriaId]);
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao remover']);
}
?>
