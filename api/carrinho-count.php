<?php
header('Content-Type: application/json');
require_once '../config/database.php';
iniciarSessao();

if (!estaLogado()) {
    echo json_encode(['count' => 0]);
    exit;
}

$conn = getConnection();
$stmt = $conn->prepare("SELECT COUNT(*) FROM carrinho WHERE usuario_id = ?");
$stmt->execute([$_SESSION['usuario_id']]);
$count = $stmt->fetchColumn();

echo json_encode(['count' => (int)$count]);
?>
