<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$busca = $_POST['busca'] ?? '';
$categoria = $_POST['categoria'] ?? '';
$ordem = $_POST['ordem'] ?? 'recentes';

$conn = getConnection();

$sql = "
    SELECT m.*, u.nome as mentor_nome, c.nome as categoria_nome, c.icone as categoria_icone,
           COALESCE(AVG(a.nota), 0) as media_avaliacao
    FROM mentorias m
    JOIN usuarios u ON m.mentor_id = u.id
    JOIN categorias c ON m.categoria_id = c.id
    LEFT JOIN avaliacoes a ON m.id = a.mentoria_id
    WHERE m.ativo = 1
";

$params = [];

if ($categoria) {
    $sql .= " AND m.categoria_id = ?";
    $params[] = $categoria;
}

if ($busca) {
    $sql .= " AND (m.titulo LIKE ? OR m.descricao LIKE ?)";
    $params[] = "%$busca%";
    $params[] = "%$busca%";
}

$sql .= " GROUP BY m.id";

switch ($ordem) {
    case 'preco_menor':
        $sql .= " ORDER BY m.preco ASC";
        break;
    case 'preco_maior':
        $sql .= " ORDER BY m.preco DESC";
        break;
    case 'avaliacao':
        $sql .= " ORDER BY media_avaliacao DESC";
        break;
    case 'populares':
        $sql .= " ORDER BY m.visualizacoes DESC";
        break;
    default:
        $sql .= " ORDER BY m.criado_em DESC";
}

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$mentorias = $stmt->fetchAll();

echo json_encode([
    'success' => true,
    'mentorias' => $mentorias
]);
?>
