<?php
require_once '../config/database.php';

header('Content-Type: application/json');

iniciarSessao();

if (!estaLogado()) {
    echo json_encode([
        'success' => false,
        'message' => 'Você precisa estar logado para avaliar'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido'
    ]);
    exit;
}

$mentoria_id = filter_input(INPUT_POST, 'mentoria_id', FILTER_VALIDATE_INT);
$nota = filter_input(INPUT_POST, 'nota', FILTER_VALIDATE_INT);
$comentario = trim($_POST['comentario'] ?? '');

$erros = [];

if (!$mentoria_id) {
    $erros[] = 'ID da mentoria inválido';
}

if (!$nota || $nota < 1 || $nota > 5) {
    $erros[] = 'A nota deve ser entre 1 e 5';
}

if (strlen($comentario) < 10) {
    $erros[] = 'O comentário deve ter no mínimo 10 caracteres';
}

if (strlen($comentario) > 500) {
    $erros[] = 'O comentário deve ter no máximo 500 caracteres';
}

if (!empty($erros)) {
    echo json_encode([
        'success' => false,
        'message' => implode('. ', $erros)
    ]);
    exit;
}

try {
    $conn = getConnection();
    $usuario_id = $_SESSION['usuario_id'];
    
    $stmt = $conn->prepare("SELECT id FROM mentorias WHERE id = ? AND ativo = 1");
    $stmt->execute([$mentoria_id]);
    if (!$stmt->fetch()) {
        throw new Exception('Mentoria não encontrada');
    }
    
    $stmt = $conn->prepare("SELECT id FROM avaliacoes WHERE mentoria_id = ? AND usuario_id = ?");
    $stmt->execute([$mentoria_id, $usuario_id]);
    if ($stmt->fetch()) {
        throw new Exception('Você já avaliou esta mentoria');
    }
    $stmt = $conn->prepare("
        INSERT INTO avaliacoes (mentoria_id, usuario_id, nota, comentario)
        VALUES (?, ?, ?, ?)
    ");
    
    $stmt->execute([$mentoria_id, $usuario_id, $nota, $comentario]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Avaliação enviada com sucesso!'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
