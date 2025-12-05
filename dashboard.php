<?php
$pageTitle = 'Painel - MentorHub';
require_once 'config/database.php';
require_once 'includes/header.php';

if (!estaLogado()) {
    header('Location: index.php#login');
    exit;
}

$conn = getConnection();
$usuarioId = $_SESSION['usuario_id'];

echo '<div class="container"><div style="padding: 2rem; background: var(--bg-primary); border-radius: var(--radius-lg); margin: 2rem 0;"><h1>👋 Bem-vindo ao seu Painel</h1><p>Esta é sua área personalizada.</p></div></div>';

require_once 'includes/footer.php';
?>
