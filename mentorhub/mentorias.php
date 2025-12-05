<?php
$pageTitle = 'Mentorias - MentorHub';
require_once 'config/database.php';
require_once 'includes/header.php';

$conn = getConnection();

$categorias = $conn->query("SELECT * FROM categorias ORDER BY nome")->fetchAll();

$categoriaFiltro = $_GET['categoria'] ?? '';
$buscaFiltro = $_GET['busca'] ?? '';
$ordenacao = $_GET['ordem'] ?? 'recentes';

$sql = "
    SELECT m.*, u.nome as mentor_nome, c.nome as categoria_nome, c.icone as categoria_icone,
           COALESCE(AVG(a.nota), 0) as media_avaliacao,
           COUNT(DISTINCT a.id) as total_avaliacoes
    FROM mentorias m
    JOIN usuarios u ON m.mentor_id = u.id
    JOIN categorias c ON m.categoria_id = c.id
    LEFT JOIN avaliacoes a ON m.id = a.mentoria_id
    WHERE m.ativo = 1
";

$params = [];

if ($categoriaFiltro) {
    $sql .= " AND m.categoria_id = ?";
    $params[] = $categoriaFiltro;
}

if ($buscaFiltro) {
    $sql .= " AND (m.titulo LIKE ? OR m.descricao LIKE ?)";
    $params[] = "%$buscaFiltro%";
    $params[] = "%$buscaFiltro%";
}

$sql .= " GROUP BY m.id";

switch ($ordenacao) {
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

$mentoriasComTags = [];
foreach ($mentorias as $mentoria) {
    $stmtTags = $conn->prepare("
        SELECT t.nome 
        FROM tags t
        JOIN mentorias_tags mt ON t.id = mt.tag_id
        WHERE mt.mentoria_id = ?
        LIMIT 3
    ");
    $stmtTags->execute([$mentoria['id']]);
    $mentoria['tags'] = $stmtTags->fetchAll(PDO::FETCH_COLUMN);
    $mentoriasComTags[] = $mentoria;
}
$mentorias = $mentoriasComTags;
?>

<div class="container">
    <h1 style="margin: 2rem 0;">📚 Todas as Mentorias</h1>
    
    <div class="filters">
        <form id="ajax-search-form" style="display: contents;">
            <div class="filter-item">
                <label for="busca">🔍 Buscar</label>
                <input type="text" id="busca" name="busca" class="form-control" 
                       placeholder="Digite para buscar..." 
                       value="<?php echo htmlspecialchars($buscaFiltro); ?>">
            </div>
            
            <div class="filter-item">
                <label for="categoria">📁 Categoria</label>
                <select id="categoria" name="categoria" class="form-control">
                    <option value="">Todas</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" 
                                <?php echo $categoriaFiltro == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo $cat['icone'] . ' ' . htmlspecialchars($cat['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-item">
                <label for="ordem">🔄 Ordenar por</label>
                <select id="ordem" name="ordem" class="form-control">
                    <option value="recentes" <?php echo $ordenacao === 'recentes' ? 'selected' : ''; ?>>Mais Recentes</option>
                    <option value="populares" <?php echo $ordenacao === 'populares' ? 'selected' : ''; ?>>Mais Populares</option>
                    <option value="avaliacao" <?php echo $ordenacao === 'avaliacao' ? 'selected' : ''; ?>>Melhor Avaliadas</option>
                    <option value="preco_menor" <?php echo $ordenacao === 'preco_menor' ? 'selected' : ''; ?>>Menor Preço</option>
                    <option value="preco_maior" <?php echo $ordenacao === 'preco_maior' ? 'selected' : ''; ?>>Maior Preço</option>
                </select>
            </div>
            
            <div class="filter-item" style="display: flex; align-items: flex-end;">
                <button type="submit" class="btn btn-primary" style="width: 100%;">Filtrar</button>
            </div>
        </form>
    </div>
    
    <div id="search-results">
        <?php if (count($mentorias) === 0): ?>
            <p style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                😔 Nenhuma mentoria encontrada com os filtros aplicados.
            </p>
        <?php else: ?>
            <div class="cards-grid">
                <?php foreach ($mentorias as $mentoria): ?>
                    <div class="card">
                        <div class="card-image"><?php echo $mentoria['categoria_icone']; ?></div>
                        <div class="card-content">
                            <span class="tag"><?php echo htmlspecialchars($mentoria['categoria_nome']); ?></span>
                            <h3 class="card-title"><?php echo htmlspecialchars($mentoria['titulo']); ?></h3>
                            <p class="card-description">
                                <?php echo htmlspecialchars(substr($mentoria['descricao'], 0, 100)) . '...'; ?>
                            </p>
                            
                            <?php if (!empty($mentoria['tags'])): ?>
                                <div class="tags" style="margin: 0.75rem 0; display: flex; flex-wrap: wrap; gap: 0.5rem;">
                                    <?php foreach ($mentoria['tags'] as $tag): ?>
                                        <span class="tag" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                                            <?php echo htmlspecialchars($tag); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
                            <p style="color: var(--text-secondary); margin: 0.5rem 0;">
                                👤 <?php echo htmlspecialchars($mentoria['mentor_nome']); ?>
                            </p>
                            <p style="color: var(--text-secondary); font-size: 0.875rem;">
                                ⏱️ <?php echo $mentoria['duracao']; ?> minutos
                            </p>
                            <div class="card-meta">
                                <span class="card-price">R$ <?php echo number_format($mentoria['preco'], 2, ',', '.'); ?></span>
                                <div class="card-rating">
                                    ⭐ <?php echo number_format($mentoria['media_avaliacao'], 1); ?>
                                    <span style="font-size: 0.875rem; color: var(--text-secondary);">
                                        (<?php echo $mentoria['total_avaliacoes']; ?>)
                                    </span>
                                </div>
                            </div>
                            <a href="mentoria-detalhes.php?id=<?php echo $mentoria['id']; ?>" 
                               class="btn btn-primary" style="width: 100%; margin-top: 1rem; text-align: center;">
                                Ver Detalhes
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
