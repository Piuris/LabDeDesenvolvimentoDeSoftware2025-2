<?php
$pageTitle = 'MentorHub - Início';
require_once 'config/database.php';
require_once 'includes/header.php';

$loginError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = sanitize($_POST['email']);
    $senha = $_POST['senha'];
    
    if (empty($email) || empty($senha)) {
        $loginError = 'Preencha todos os campos';
    } else {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT id, nome, email, senha, tipo FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();
        
        if ($usuario && password_verify($senha, $usuario['senha'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_email'] = $usuario['email'];
            $_SESSION['usuario_tipo'] = $usuario['tipo'];
            
            header('Location: mentorias.php');
            exit;
        } else {
            $loginError = 'Email ou senha incorretos';
        }
    }
}

$conn = getConnection();
$stats = [
    'mentores' => $conn->query("SELECT COUNT(*) FROM usuarios WHERE tipo = 'mentor'")->fetchColumn(),
    'mentorias' => $conn->query("SELECT COUNT(*) FROM mentorias WHERE ativo = 1")->fetchColumn(),
    'categorias' => $conn->query("SELECT COUNT(*) FROM categorias")->fetchColumn()
];

$stmt = $conn->query("
    SELECT m.*, u.nome as mentor_nome, c.nome as categoria_nome, c.icone as categoria_icone,
           COALESCE(AVG(a.nota), 0) as media_avaliacao
    FROM mentorias m
    JOIN usuarios u ON m.mentor_id = u.id
    JOIN categorias c ON m.categoria_id = c.id
    LEFT JOIN avaliacoes a ON m.id = a.mentoria_id
    WHERE m.ativo = 1
    GROUP BY m.id
    ORDER BY m.visualizacoes DESC
    LIMIT 6
");
$mentoriasDestaque = $stmt->fetchAll();
?>

<div class="container">
    <?php if (!estaLogado()): ?>
        <section class="hero">
            <h1>🎓 Aprenda com os Melhores Mentores</h1>
            <p>Conecte-se com especialistas e impulsione sua carreira</p>
            <div>
                <a href="#login" class="btn btn-primary" style="background: white; color: var(--primary-color); margin-right: 1rem;">Começar Agora</a>
                <a href="mentorias.php" class="btn btn-outline" style="border-color: white; color: white;">Explorar Mentorias</a>
            </div>
        </section>

        <section id="login" class="login-section" style="margin: 3rem 0;">
            <div class="form-container" style="margin: 0;">
                <h2>Entrar</h2>
                <?php if ($loginError): ?>
                    <div class="alert alert-error"><?php echo $loginError; ?></div>
                <?php endif; ?>
                
                <form method="POST" data-validate>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                        <span class="error-message"></span>
                    </div>
                    
                    <div class="form-group">
                        <label for="senha">Senha</label>
                        <input type="password" id="senha" name="senha" class="form-control" required>
                        <span class="error-message"></span>
                    </div>
                    
                    <button type="submit" name="login" class="btn btn-primary" style="width: 100%;">Entrar</button>
                </form>
            </div>
            
            <div class="form-container" style="margin: 0; display: flex; flex-direction: column; justify-content: center;">
                <h2>Novo por aqui?</h2>
                <p style="margin: 1rem 0;">Cadastre-se e comece a aprender com os melhores mentores ou compartilhe seu conhecimento!</p>
                <a href="register.php" class="btn btn-primary">Criar Conta</a>
            </div>
        </section>
    <?php else: ?>
        <section class="hero">
            <h1>Bem-vindo, <?php echo htmlspecialchars($usuarioLogado['nome']); ?>! 👋</h1>
            <p>Explore nossas mentorias e continue aprendendo</p>
            <a href="mentorias.php" class="btn btn-primary" style="background: white; color: var(--primary-color);">Ver Todas as Mentorias</a>
        </section>
    <?php endif; ?>

    <section style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem; margin: 3rem 0;">
        <div class="card" style="text-align: center; padding: 2rem;">
            <div style="font-size: 3rem;">👥</div>
            <h3 style="font-size: 2rem; color: var(--primary-color);"><?php echo $stats['mentores']; ?></h3>
            <p>Mentores Ativos</p>
        </div>
        <div class="card" style="text-align: center; padding: 2rem;">
            <div style="font-size: 3rem;">📚</div>
            <h3 style="font-size: 2rem; color: var(--primary-color);"><?php echo $stats['mentorias']; ?></h3>
            <p>Mentorias Disponíveis</p>
        </div>
        <div class="card" style="text-align: center; padding: 2rem;">
            <div style="font-size: 3rem;">🎯</div>
            <h3 style="font-size: 2rem; color: var(--primary-color);"><?php echo $stats['categorias']; ?></h3>
            <p>Categorias</p>
        </div>
    </section>

    <section>
        <h2 style="text-align: center; margin: 3rem 0 2rem;">🌟 Mentorias em Destaque</h2>
        <div class="cards-grid">
            <?php foreach ($mentoriasDestaque as $mentoria): ?>
                <div class="card">
                    <div class="card-image"><?php echo $mentoria['categoria_icone']; ?></div>
                    <div class="card-content">
                        <h3 class="card-title"><?php echo htmlspecialchars($mentoria['titulo']); ?></h3>
                        <p class="card-description"><?php echo htmlspecialchars(substr($mentoria['descricao'], 0, 100)) . '...'; ?></p>
                        <p style="color: var(--text-secondary); margin: 0.5rem 0;">
                            👤 <?php echo htmlspecialchars($mentoria['mentor_nome']); ?>
                        </p>
                        <div class="card-meta">
                            <span class="card-price">R$ <?php echo number_format($mentoria['preco'], 2, ',', '.'); ?></span>
                            <div class="card-rating">
                                ⭐ <?php echo number_format($mentoria['media_avaliacao'], 1); ?>
                            </div>
                        </div>
                        <a href="mentoria-detalhes.php?id=<?php echo $mentoria['id']; ?>" class="btn btn-primary" style="width: 100%; margin-top: 1rem; text-align: center;">Ver Detalhes</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<?php require_once 'includes/footer.php'; ?>
