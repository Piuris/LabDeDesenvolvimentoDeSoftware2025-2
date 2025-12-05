<?php
$pageTitle = 'Minhas Mentorias - MentorHub';
require_once 'config/database.php';
require_once 'includes/header.php';

if (!estaLogado() || $_SESSION['usuario_tipo'] !== 'mentor') {
    header('Location: index.php');
    exit;
}

$conn = getConnection();
$mentorId = $_SESSION['usuario_id'];
$success = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'cadastrar') {
    $titulo = trim($_POST['titulo'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $categoria_id = intval($_POST['categoria_id'] ?? 0);
    $preco = floatval($_POST['preco'] ?? 0);
    $duracao = intval($_POST['duracao'] ?? 0);
    $tags = $_POST['tags'] ?? [];
    $novas_tags = $_POST['novas_tags'] ?? [];
    
    if (empty($titulo)) $errors[] = 'Título é obrigatório';
    if (strlen($titulo) < 10) $errors[] = 'Título deve ter no mínimo 10 caracteres';
    if (empty($descricao)) $errors[] = 'Descrição é obrigatória';
    if (strlen($descricao) < 50) $errors[] = 'Descrição deve ter no mínimo 50 caracteres';
    if ($categoria_id <= 0) $errors[] = 'Selecione uma categoria';
    if ($preco <= 0) $errors[] = 'Preço deve ser maior que zero';
    if ($duracao <= 0) $errors[] = 'Duração deve ser maior que zero';
    
    if (empty($errors)) {
        try {
            $conn->beginTransaction();
            
            $stmt = $conn->prepare("
                INSERT INTO mentorias (mentor_id, categoria_id, titulo, descricao, preco, duracao)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$mentorId, $categoria_id, $titulo, $descricao, $preco, $duracao]);
            $mentoriaId = $conn->lastInsertId();
            
            $todasTagsIds = [];
            
            if (!empty($novas_tags)) {
                $stmtInsertTag = $conn->prepare("INSERT INTO tags (nome) VALUES (?) ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)");
                $stmtGetTagId = $conn->prepare("SELECT id FROM tags WHERE nome = ?");
                
                foreach ($novas_tags as $novaTag) {
                    $novaTag = trim($novaTag);
                    if (!empty($novaTag) && strlen($novaTag) >= 2) {
                        $stmtInsertTag->execute([$novaTag]);
                        $stmtGetTagId->execute([$novaTag]);
                        $tagId = $stmtGetTagId->fetchColumn();
                        if ($tagId) {
                            $todasTagsIds[] = $tagId;
                        }
                    }
                }
            }
            
            foreach ($tags as $tagId) {
                if (intval($tagId) > 0) {
                    $todasTagsIds[] = intval($tagId);
                }
            }
            
            if (!empty($todasTagsIds)) {
                $stmtTag = $conn->prepare("
                    INSERT INTO mentorias_tags (mentoria_id, tag_id)
                    VALUES (?, ?)
                ");
                foreach (array_unique($todasTagsIds) as $tagId) {
                    $stmtTag->execute([$mentoriaId, $tagId]);
                }
            }
            
            $conn->commit();
            $success = 'Mentoria cadastrada com sucesso!';
        } catch (Exception $e) {
            $conn->rollBack();
            $errors[] = 'Erro ao cadastrar mentoria: ' . $e->getMessage();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'excluir') {
    $mentoriaId = intval($_POST['mentoria_id'] ?? 0);
    
    if ($mentoriaId > 0) {
        try {
            $stmt = $conn->prepare("SELECT id FROM mentorias WHERE id = ? AND mentor_id = ?");
            $stmt->execute([$mentoriaId, $mentorId]);
            
            if ($stmt->fetch()) {
                $stmt = $conn->prepare("DELETE FROM mentorias WHERE id = ?");
                $stmt->execute([$mentoriaId]);
                $success = 'Mentoria excluída com sucesso!';
            } else {
                $errors[] = 'Mentoria não encontrada ou você não tem permissão.';
            }
        } catch (Exception $e) {
            $errors[] = 'Erro ao excluir mentoria: ' . $e->getMessage();
        }
    }
}

$categorias = $conn->query("SELECT * FROM categorias ORDER BY nome")->fetchAll();

$todasTags = $conn->query("SELECT * FROM tags ORDER BY nome")->fetchAll();

$stmt = $conn->prepare("
    SELECT m.*, c.nome as categoria_nome, c.icone as categoria_icone,
           COUNT(DISTINCT a.id) as total_avaliacoes,
           COALESCE(AVG(a.nota), 0) as media_avaliacao
    FROM mentorias m
    JOIN categorias c ON m.categoria_id = c.id
    LEFT JOIN avaliacoes a ON m.id = a.mentoria_id
    WHERE m.mentor_id = ?
    GROUP BY m.id
    ORDER BY m.criado_em DESC
");
$stmt->execute([$mentorId]);
$minhasMentorias = $stmt->fetchAll();
?>

<style>
.mentor-dashboard {
    padding: 2rem 0;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin: 2rem 0;
}

.stat-card {
    background: var(--bg-primary);
    padding: 1.5rem;
    border-radius: var(--radius-lg);
    text-align: center;
    box-shadow: var(--shadow-sm);
}

.stat-card h3 {
    font-size: 2rem;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.form-section {
    background: var(--bg-primary);
    padding: 2rem;
    border-radius: var(--radius-lg);
    margin: 2rem 0;
    box-shadow: var(--shadow-sm);
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: var(--text-primary);
}

.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    font-size: 1rem;
    font-family: inherit;
    background: var(--bg-secondary);
    color: var(--text-primary);
}

.form-group textarea {
    min-height: 120px;
    resize: vertical;
}

.tags-group {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.tag-checkbox {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: var(--bg-secondary);
    border: 2px solid var(--border-color);
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: all 0.2s;
}

.tag-checkbox:hover {
    border-color: var(--primary-color);
}

.tag-checkbox input[type="checkbox"]:checked + label {
    color: var(--primary-color);
    font-weight: 600;
}

.tag-checkbox input[type="checkbox"] {
    width: auto;
}

.mentoria-item {
    background: var(--bg-primary);
    padding: 1.5rem;
    border-radius: var(--radius-lg);
    margin-bottom: 1rem;
    box-shadow: var(--shadow-sm);
    display: flex;
    justify-content: space-between;
    align-items: start;
    gap: 1rem;
}

.mentoria-info h3 {
    margin-bottom: 0.5rem;
    color: var(--text-primary);
}

.mentoria-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    color: var(--text-secondary);
    font-size: 0.9rem;
    margin-top: 0.5rem;
}

.mentoria-actions {
    display: flex;
    gap: 0.5rem;
    flex-shrink: 0;
}

.btn-small {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
}

.success-message {
    background: #d4edda;
    color: #155724;
    padding: 1rem;
    border-radius: var(--radius-md);
    margin: 1rem 0;
    border: 1px solid #c3e6cb;
}

.error-message {
    background: #f8d7da;
    color: #721c24;
    padding: 1rem;
    border-radius: var(--radius-md);
    margin: 1rem 0;
    border: 1px solid #f5c6cb;
}
</style>

<div class="container mentor-dashboard">
    <h1>📚 Minhas Mentorias</h1>
    <p style="color: var(--text-secondary);">Gerencie e cadastre suas mentorias</p>
    
    <?php if ($success): ?>
        <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <?php if (!empty($errors)): ?>
        <div class="error-message">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <div class="stats-grid">
        <div class="stat-card">
            <h3><?php echo count($minhasMentorias); ?></h3>
            <p>Mentorias Publicadas</p>
        </div>
        <div class="stat-card">
            <h3><?php echo array_sum(array_column($minhasMentorias, 'visualizacoes')); ?></h3>
            <p>Visualizações Totais</p>
        </div>
        <div class="stat-card">
            <h3><?php echo array_sum(array_column($minhasMentorias, 'total_avaliacoes')); ?></h3>
            <p>Avaliações Recebidas</p>
        </div>
    </div>
    
    <div class="form-section">
        <h2 style="margin-bottom: 1.5rem;">➕ Cadastrar Nova Mentoria</h2>
        
        <form method="POST" action="" id="formCadastroMentoria">
            <input type="hidden" name="acao" value="cadastrar">
            
            <div class="form-group">
                <label for="titulo">Título da Mentoria *</label>
                <input 
                    type="text" 
                    id="titulo" 
                    name="titulo" 
                    required 
                    minlength="10"
                    maxlength="200"
                    placeholder="Ex: Desenvolvimento Web Completo com JavaScript"
                    value="<?php echo htmlspecialchars($_POST['titulo'] ?? ''); ?>"
                >
                <small style="color: var(--text-secondary);">Mínimo 10 caracteres</small>
            </div>
            
            <div class="form-group">
                <label for="categoria_id">Categoria *</label>
                <select id="categoria_id" name="categoria_id" required>
                    <option value="">Selecione uma categoria</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo (($_POST['categoria_id'] ?? '') == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo $cat['icone'] . ' ' . htmlspecialchars($cat['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="descricao">Descrição Detalhada *</label>
                <textarea 
                    id="descricao" 
                    name="descricao" 
                    required 
                    minlength="50"
                    placeholder="Descreva em detalhes o que será abordado na mentoria, os objetivos de aprendizado e o que o aluno poderá esperar..."
                ><?php echo htmlspecialchars($_POST['descricao'] ?? ''); ?></textarea>
                <small style="color: var(--text-secondary);">Mínimo 50 caracteres</small>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="preco">Preço (R$) *</label>
                    <input 
                        type="number" 
                        id="preco" 
                        name="preco" 
                        step="0.01" 
                        min="0.01" 
                        required
                        placeholder="99.90"
                        value="<?php echo htmlspecialchars($_POST['preco'] ?? ''); ?>"
                    >
                </div>
                
                <div class="form-group">
                    <label for="duracao">Duração (minutos) *</label>
                    <input 
                        type="number" 
                        id="duracao" 
                        name="duracao" 
                        min="15" 
                        step="15" 
                        required
                        placeholder="60"
                        value="<?php echo htmlspecialchars($_POST['duracao'] ?? ''); ?>"
                    >
                </div>
            </div>
            
            <div class="form-group">
                <label>Tags (opcional)</label>
                <div class="tags-group">
                    <?php foreach ($todasTags as $tag): ?>
                        <label class="tag-checkbox">
                            <input 
                                type="checkbox" 
                                name="tags[]" 
                                value="<?php echo $tag['id']; ?>"
                                <?php echo (in_array($tag['id'], $_POST['tags'] ?? [])) ? 'checked' : ''; ?>
                            >
                            <span><?php echo htmlspecialchars($tag['nome']); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <div style="margin-top: 1rem; display: flex; gap: 0.5rem;">
                    <input 
                        type="text" 
                        id="novaTag" 
                        placeholder="Criar nova tag..." 
                        style="flex: 1;"
                        maxlength="50"
                    >
                    <button type="button" class="btn btn-outline btn-small" onclick="adicionarNovaTag()">
                        ➕ Adicionar Tag
                    </button>
                </div>
                <div id="novasTagsContainer" style="margin-top: 0.75rem; display: flex; flex-wrap: wrap; gap: 0.75rem;"></div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">
                ✅ Cadastrar Mentoria
            </button>
        </form>
    </div>
    
    <div style="margin-top: 3rem;">
        <h2 style="margin-bottom: 1.5rem;">📋 Suas Mentorias (<?php echo count($minhasMentorias); ?>)</h2>
        
        <?php if (empty($minhasMentorias)): ?>
            <div style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                <p style="font-size: 3rem;">📝</p>
                <p>Você ainda não cadastrou nenhuma mentoria.</p>
                <p>Comece cadastrando sua primeira mentoria acima!</p>
            </div>
        <?php else: ?>
            <?php foreach ($minhasMentorias as $mentoria): ?>
                <div class="mentoria-item">
                    <div class="mentoria-info" style="flex: 1;">
                        <h3>
                            <?php echo $mentoria['categoria_icone']; ?>
                            <?php echo htmlspecialchars($mentoria['titulo']); ?>
                        </h3>
                        <p style="color: var(--text-secondary); margin: 0.5rem 0;">
                            <?php echo htmlspecialchars(substr($mentoria['descricao'], 0, 150)) . '...'; ?>
                        </p>
                        <div class="mentoria-meta">
                            <span>💰 R$ <?php echo number_format($mentoria['preco'], 2, ',', '.'); ?></span>
                            <span>⏱️ <?php echo $mentoria['duracao']; ?> min</span>
                            <span>👁️ <?php echo $mentoria['visualizacoes']; ?> visualizações</span>
                            <span>⭐ <?php echo number_format($mentoria['media_avaliacao'], 1); ?> (<?php echo $mentoria['total_avaliacoes']; ?>)</span>
                            <span><?php echo $mentoria['ativo'] ? '✅ Ativo' : '🔒 Inativo'; ?></span>
                        </div>
                    </div>
                    <div class="mentoria-actions">
                        <a href="mentoria-detalhes.php?id=<?php echo $mentoria['id']; ?>" 
                           class="btn btn-outline btn-small" 
                           target="_blank">
                            👁️ Ver
                        </a>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Tem certeza que deseja excluir esta mentoria?');">
                            <input type="hidden" name="acao" value="excluir">
                            <input type="hidden" name="mentoria_id" value="<?php echo $mentoria['id']; ?>">
                            <button type="submit" class="btn btn-outline btn-small" style="background: #dc3545; color: white; border-color: #dc3545;">
                                🗑️ Excluir
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
document.getElementById('formCadastroMentoria').addEventListener('submit', function(e) {
    const titulo = document.getElementById('titulo').value.trim();
    const descricao = document.getElementById('descricao').value.trim();
    const preco = parseFloat(document.getElementById('preco').value);
    const duracao = parseInt(document.getElementById('duracao').value);
    
    if (titulo.length < 10) {
        e.preventDefault();
        alert('O título deve ter no mínimo 10 caracteres');
        return false;
    }
    
    if (descricao.length < 50) {
        e.preventDefault();
        alert('A descrição deve ter no mínimo 50 caracteres');
        return false;
    }
    
    if (preco <= 0) {
        e.preventDefault();
        alert('O preço deve ser maior que zero');
        return false;
    }
    
    if (duracao <= 0) {
        e.preventDefault();
        alert('A duração deve ser maior que zero');
        return false;
    }
});

let novasTagsCount = 0;

function adicionarNovaTag() {
    const input = document.getElementById('novaTag');
    const tagNome = input.value.trim();
    
    if (!tagNome) {
        alert('Digite o nome da tag');
        return;
    }
    
    if (tagNome.length < 2) {
        alert('A tag deve ter no mínimo 2 caracteres');
        return;
    }
    
    if (tagNome.length > 50) {
        alert('A tag deve ter no máximo 50 caracteres');
        return;
    }
    
    const tagsExistentes = Array.from(document.querySelectorAll('.tag-checkbox span'))
        .map(span => span.textContent.toLowerCase());
    
    if (tagsExistentes.includes(tagNome.toLowerCase())) {
        alert('Esta tag já existe. Selecione-a na lista acima.');
        return;
    }
    
    const container = document.getElementById('novasTagsContainer');
    const tagId = 'nova_' + novasTagsCount++;
    
    const tagElement = document.createElement('label');
    tagElement.className = 'tag-checkbox';
    tagElement.style.background = 'var(--primary-color)';
    tagElement.style.color = 'white';
    tagElement.style.borderColor = 'var(--primary-color)';
    
    tagElement.innerHTML = `
        <input 
            type="checkbox" 
            name="novas_tags[]" 
            value="${tagNome}"
            checked
            style="accent-color: white;"
        >
        <span>${tagNome}</span>
        <button type="button" onclick="removerNovaTag(this)" style="background: none; border: none; color: white; cursor: pointer; padding: 0 0 0 0.5rem;">✕</button>
    `;
    
    container.appendChild(tagElement);
    input.value = '';
    input.focus();
}

function removerNovaTag(button) {
    button.closest('.tag-checkbox').remove();
}

document.getElementById('novaTag').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        adicionarNovaTag();
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
