<?php
require_once 'config/database.php';
iniciarSessao();

$id = $_GET['id'] ?? 0;

if (!$id || !is_numeric($id)) {
    header('Location: mentorias.php');
    exit;
}

$conn = getConnection();

$stmt = $conn->prepare("UPDATE mentorias SET visualizacoes = visualizacoes + 1 WHERE id = ?");
$stmt->execute([$id]);

$stmt = $conn->prepare("
    SELECT m.*, u.nome as mentor_nome, u.biografia as mentor_bio, u.foto_perfil,
           c.nome as categoria_nome, c.icone as categoria_icone,
           COALESCE(AVG(a.nota), 0) as media_avaliacao,
           COUNT(DISTINCT a.id) as total_avaliacoes
    FROM mentorias m
    JOIN usuarios u ON m.mentor_id = u.id
    JOIN categorias c ON m.categoria_id = c.id
    LEFT JOIN avaliacoes a ON m.id = a.mentoria_id
    WHERE m.id = ? AND m.ativo = 1
    GROUP BY m.id
");
$stmt->execute([$id]);
$mentoria = $stmt->fetch();

if (!$mentoria) {
    header('Location: mentorias.php');
    exit;
}

$stmt = $conn->prepare("
    SELECT t.nome 
    FROM tags t
    JOIN mentorias_tags mt ON t.id = mt.tag_id
    WHERE mt.mentoria_id = ?
");
$stmt->execute([$id]);
$tags = $stmt->fetchAll(PDO::FETCH_COLUMN);

$stmt = $conn->prepare("
    SELECT a.*, u.nome as usuario_nome
    FROM avaliacoes a
    JOIN usuarios u ON a.usuario_id = u.id
    WHERE a.mentoria_id = ?
    ORDER BY a.criado_em DESC
    LIMIT 10
");
$stmt->execute([$id]);
$avaliacoes = $stmt->fetchAll();

$podeAvaliar = false;
$jaAvaliou = false;
if (estaLogado()) {
    $podeAvaliar = true;
    
    $stmt = $conn->prepare("SELECT id FROM avaliacoes WHERE mentoria_id = ? AND usuario_id = ?");
    $stmt->execute([$id, $_SESSION['usuario_id']]);
    $jaAvaliou = $stmt->fetch() !== false;
    
    if ($jaAvaliou) {
        $podeAvaliar = false;
    }
}

$pageTitle = htmlspecialchars($mentoria['titulo']) . ' - MentorHub';
require_once 'includes/header.php';
?>

<div class="container">
    <div class="product-details">
        <div>
            <div class="card-image product-image" style="font-size: 8rem; height: 400px;">
                <?php echo $mentoria['categoria_icone']; ?>
            </div>
        </div>
        
        <div class="product-info">
            <span class="tag"><?php echo htmlspecialchars($mentoria['categoria_nome']); ?></span>
            <h1><?php echo htmlspecialchars($mentoria['titulo']); ?></h1>
            
            <div style="display: flex; gap: 2rem; margin: 1rem 0;">
                <div class="card-rating" style="font-size: 1.2rem;">
                    ⭐ <?php echo number_format($mentoria['media_avaliacao'], 1); ?>
                    <span style="color: var(--text-secondary);">
                        (<?php echo $mentoria['total_avaliacoes']; ?> avaliações)
                    </span>
                </div>
                <div style="color: var(--text-secondary);">
                    👁️ <?php echo $mentoria['visualizacoes']; ?> visualizações
                </div>
            </div>
            
            <p class="product-price">R$ <?php echo number_format($mentoria['preco'], 2, ',', '.'); ?></p>
            
            <div style="background: var(--bg-secondary); padding: 1rem; border-radius: var(--radius-md); margin: 1rem 0;">
                <p><strong>⏱️ Duração:</strong> <?php echo $mentoria['duracao']; ?> minutos</p>
                <p><strong>👤 Mentor:</strong> <?php echo htmlspecialchars($mentoria['mentor_nome']); ?></p>
            </div>
            
            <?php if (!empty($tags)): ?>
                <div class="tags">
                    <?php foreach ($tags as $tag): ?>
                        <span class="tag"><?php echo htmlspecialchars($tag); ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <div class="product-actions">
                <?php if (estaLogado()): ?>
                    <button 
                        class="btn btn-primary add-to-cart-btn" 
                        data-mentoria-id="<?php echo $id; ?>" 
                        style="flex: 1;">
                        🛒 Adicionar ao Carrinho
                    </button>
                    <a href="carrinho.php" class="btn btn-success" style="flex: 1;">Ir para o Carrinho</a>
                <?php else: ?>
                    <a href="index.php#login" class="btn btn-primary" style="flex: 1;">Faça login para comprar</a>
                <?php endif; ?>
            </div>
            
            <div style="margin-top: 2rem;">
                <h2>📝 Sobre esta Mentoria</h2>
                <p style="line-height: 1.8; color: var(--text-secondary); margin-top: 1rem;">
                    <?php echo nl2br(htmlspecialchars($mentoria['descricao'])); ?>
                </p>
            </div>
            
            <div style="margin-top: 2rem; padding: 1.5rem; background: var(--bg-secondary); border-radius: var(--radius-md);">
                <h3>👨‍🏫 Sobre o Mentor</h3>
                <p style="margin-top: 1rem; color: var(--text-secondary);">
                    <?php echo htmlspecialchars($mentoria['mentor_bio'] ?: 'Mentor experiente'); ?>
                </p>
            </div>
        </div>
    </div>
    
    <section style="margin-top: 3rem;">
        <h2>⭐ Avaliações</h2>
        
        <?php if ($podeAvaliar): ?>
            <div style="background: var(--bg-secondary); padding: 2rem; border-radius: var(--radius-lg); margin: 2rem 0;">
                <h3 style="margin-bottom: 1.5rem;">Deixe sua avaliação</h3>
                <form id="form-avaliacao" method="POST" action="api/avaliacoes-add.php">
                    <input type="hidden" name="mentoria_id" value="<?php echo $id; ?>">
                    
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Nota</label>
                        <div class="rating-input" style="display: flex; gap: 0.5rem; font-size: 2rem;">
                            <input type="radio" name="nota" value="1" id="star1" required style="display: none;">
                            <label for="star1" class="star-label" data-value="1">⭐</label>
                            
                            <input type="radio" name="nota" value="2" id="star2" required style="display: none;">
                            <label for="star2" class="star-label" data-value="2">⭐</label>
                            
                            <input type="radio" name="nota" value="3" id="star3" required style="display: none;">
                            <label for="star3" class="star-label" data-value="3">⭐</label>
                            
                            <input type="radio" name="nota" value="4" id="star4" required style="display: none;">
                            <label for="star4" class="star-label" data-value="4">⭐</label>
                            
                            <input type="radio" name="nota" value="5" id="star5" required style="display: none;">
                            <label for="star5" class="star-label" data-value="5">⭐</label>
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <label for="comentario" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Comentário</label>
                        <textarea 
                            id="comentario" 
                            name="comentario" 
                            rows="4" 
                            required
                            minlength="10"
                            maxlength="500"
                            placeholder="Compartilhe sua experiência com esta mentoria..."
                            style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-family: inherit; resize: vertical;"
                        ></textarea>
                        <small style="color: var(--text-secondary);">Mínimo 10 caracteres</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Enviar Avaliação</button>
                </form>
            </div>
        <?php elseif (estaLogado() && $jaAvaliou): ?>
            <div style="background: var(--bg-secondary); padding: 1.5rem; border-radius: var(--radius-md); margin: 2rem 0; text-align: center; color: var(--text-secondary);">
                ✓ Você já avaliou esta mentoria
            </div>
        <?php elseif (!estaLogado()): ?>
            <div style="background: var(--bg-secondary); padding: 1.5rem; border-radius: var(--radius-md); margin: 2rem 0; text-align: center;">
                <p style="color: var(--text-secondary);">
                    <a href="index.php#login" class="btn btn-primary">Faça login</a> para avaliar esta mentoria
                </p>
            </div>
        <?php endif; ?>
        
        <?php if (count($avaliacoes) === 0): ?>
            <p style="color: var(--text-secondary); margin: 2rem 0;">Ainda não há avaliações para esta mentoria.</p>
        <?php else: ?>
            <div style="margin-top: 2rem;">
                <?php foreach ($avaliacoes as $avaliacao): ?>
                    <div style="background: var(--bg-primary); padding: 1.5rem; border-radius: var(--radius-md); margin-bottom: 1rem; border-left: 4px solid var(--primary-color);">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                            <strong style="font-size: 1.1rem;">👤 <?php echo htmlspecialchars($avaliacao['usuario_nome']); ?></strong>
                            <div class="card-rating" style="font-size: 1.2rem;">
                                <?php for($i = 0; $i < $avaliacao['nota']; $i++) echo '⭐'; ?>
                            </div>
                        </div>
                        <p style="color: var(--text-secondary); line-height: 1.6; margin: 1rem 0;">
                            <?php echo nl2br(htmlspecialchars($avaliacao['comentario'])); ?>
                        </p>
                        <small style="color: var(--text-secondary); font-size: 0.9rem;">
                            📅 <?php echo date('d/m/Y H:i', strtotime($avaliacao['criado_em'])); ?>
                        </small>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php require_once 'includes/footer.php'; ?>

<script>
console.log('[v0] Inline cart script loaded');

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        background: ${type === 'success' ? '#10b981' : '#ef4444'};
        color: white;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        z-index: 9999;
        animation: slideIn 0.3s ease-out;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease-in';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

function updateCartCount() {
    fetch('api/carrinho-count.php')
        .then(response => response.json())
        .then(data => {
            const badge = document.getElementById('cart-count');
            if (badge && data.count !== undefined) {
                badge.textContent = data.count;
                badge.style.display = data.count > 0 ? 'flex' : 'none';
            }
        })
        .catch(error => console.error('[v0] Erro ao atualizar contador:', error));
}

const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
`;
document.head.appendChild(style);

document.addEventListener('DOMContentLoaded', function() {
    console.log('[v0] DOM ready, initializing cart button...');
    
    const cartButton = document.querySelector('.add-to-cart-btn');
    
    if (cartButton) {
        console.log('[v0] Cart button found!');
        
        cartButton.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('[v0] Cart button clicked!');
            
            const mentoriaId = this.getAttribute('data-mentoria-id');
            console.log('[v0] Mentoria ID:', mentoriaId);
            
            if (!mentoriaId) {
                console.error('[v0] No mentoria ID found');
                showNotification('Erro: ID da mentoria não encontrado', 'error');
                return;
            }
            
            this.disabled = true;
            const originalText = this.textContent;
            this.textContent = 'Adicionando...';
            
            console.log('[v0] Sending request to API...');
            
            fetch('api/carrinho-add.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ mentoria_id: mentoriaId })
            })
            .then(response => {
                console.log('[v0] Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('[v0] Response data:', data);
                
                if (data.success) {
                    showNotification('Adicionado ao carrinho!', 'success');
                    updateCartCount();
                    this.textContent = '✓ Adicionado';
                    
                    setTimeout(() => {
                        this.textContent = originalText;
                        this.disabled = false;
                    }, 2000);
                } else {
                    showNotification(data.message || 'Erro ao adicionar', 'error');
                    this.textContent = originalText;
                    this.disabled = false;
                }
            })
            .catch(error => {
                console.error('[v0] Error:', error);
                showNotification('Erro ao adicionar ao carrinho', 'error');
                this.textContent = originalText;
                this.disabled = false;
            });
        });
        
        console.log('[v0] Event listener attached successfully');
    } else {
        console.error('[v0] Cart button NOT found!');
    }
});

console.log('[v0] Script setup complete');
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('.star-label');
    const ratingInput = document.querySelector('.rating-input');
    
    if (stars.length > 0) {
        let selectedRating = 0;
        
        stars.forEach(star => {
            star.style.cursor = 'pointer';
            star.style.opacity = '0.3';
            star.style.transition = 'all 0.2s ease';
            
            star.addEventListener('mouseenter', function() {
                const value = parseInt(this.getAttribute('data-value'));
                highlightStars(value);
            });
            
            star.addEventListener('click', function() {
                const value = parseInt(this.getAttribute('data-value'));
                selectedRating = value;
                document.getElementById('star' + value).checked = true;
                highlightStars(value);
            });
        });
        
        ratingInput.addEventListener('mouseleave', function() {
            highlightStars(selectedRating);
        });
        
        function highlightStars(count) {
            stars.forEach(star => {
                const value = parseInt(star.getAttribute('data-value'));
                if (value <= count) {
                    star.style.opacity = '1';
                    star.style.transform = 'scale(1.1)';
                } else {
                    star.style.opacity = '0.3';
                    star.style.transform = 'scale(1)';
                }
            });
        }
    }
    
    const formAvaliacao = document.getElementById('form-avaliacao');
    if (formAvaliacao) {
        formAvaliacao.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const btn = this.querySelector('button[type="submit"]');
            const originalText = btn.textContent;
            
            btn.disabled = true;
            btn.textContent = 'Enviando...';
            
            fetch('api/avaliacoes-add.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Avaliação enviada com sucesso!', 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showNotification(data.message || 'Erro ao enviar avaliação', 'error');
                    btn.disabled = false;
                    btn.textContent = originalText;
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                showNotification('Erro ao enviar avaliação', 'error');
                btn.disabled = false;
                btn.textContent = originalText;
            });
        });
    }
});
</script>
