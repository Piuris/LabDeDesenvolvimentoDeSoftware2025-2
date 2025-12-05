<?php
require_once __DIR__ . '/../config/database.php';
iniciarSessao();
$usuarioLogado = getUsuarioLogado();
$paginaAtual = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="plagiarism" content="Este conteúdo é original e protegido por direitos autorais. Qualquer uso não autorizado está sujeito a medidas legais.">
    <meta name="description" content="Plataforma de mentorias online - Conecte-se com mentores especializados">
    <title><?php echo $pageTitle ?? 'MentorHub - Plataforma de Mentorias'; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <nav class="navbar">
                <a href="index.php" class="logo">
                    <span class="logo-icon">🎓</span>
                    <span class="logo-text">MentorHub</span>
                </a>
                
                <ul class="nav-menu">
                    <li><a href="index.php" class="<?php echo $paginaAtual === 'index.php' ? 'active' : ''; ?>">Início</a></li>
                    <li><a href="mentorias.php" class="<?php echo $paginaAtual === 'mentorias.php' ? 'active' : ''; ?>">Mentorias</a></li>
                    <li><a href="contato.php" class="<?php echo $paginaAtual === 'contato.php' ? 'active' : ''; ?>">Contato</a></li>
                    <li><a href="termos.php" class="<?php echo $paginaAtual === 'termos.php' ? 'active' : ''; ?>">Termos</a></li>
                </ul>
                
                <div class="nav-actions">
                    <?php if ($usuarioLogado): ?>
                        <a href="carrinho.php" class="btn-icon" title="Carrinho">
                            🛒 <span class="badge" id="cart-count">0</span>
                        </a>
                        <div class="user-dropdown">
                            <button class="btn-user">
                                <span>👤 <?php echo htmlspecialchars($usuarioLogado['nome']); ?></span>
                            </button>
                            <div class="dropdown-content">
                                <a href="dashboard.php">Meu Painel</a>
                                <?php if ($usuarioLogado['tipo'] === 'mentor'): ?>
                                    <a href="perfil-mentor.php">Minhas Mentorias</a>
                                    <a href="admin-categorias.php">Gerenciar Categorias</a>
                                <?php endif; ?>
                                <a href="logout.php">Sair</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="index.php#login" class="btn btn-outline">Entrar</a>
                        <a href="register.php" class="btn btn-primary">Cadastrar</a>
                    <?php endif; ?>
                </div>
                
                <button class="mobile-menu-toggle" aria-label="Menu">
                    ☰
                </button>
            </nav>
        </div>
    </header>
    
    <main class="main-content">

</main>
</body>
</html>
