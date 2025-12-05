<?php
$pageTitle = 'Cadastro - MentorHub';
require_once 'config/database.php';

$success = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = sanitize($_POST['nome']);
    $email = sanitize($_POST['email']);
    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];
    $tipo = sanitize($_POST['tipo']);
    $telefone = sanitize($_POST['telefone'] ?? '');
    $biografia = sanitize($_POST['biografia'] ?? '');
    
    if (empty($nome)) $errors[] = 'Nome é obrigatório';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email inválido';
    if (empty($senha) || strlen($senha) < 6) $errors[] = 'Senha deve ter no mínimo 6 caracteres';
    if ($senha !== $confirmar_senha) $errors[] = 'Senhas não coincidem';
    if (!in_array($tipo, ['aluno', 'mentor'])) $errors[] = 'Tipo de usuário inválido';
    
    if (empty($errors)) {
        $conn = getConnection();
        
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Este email já está cadastrado';
        } else {
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("
                INSERT INTO usuarios (nome, email, senha, tipo, telefone, biografia) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            if ($stmt->execute([$nome, $email, $senhaHash, $tipo, $telefone, $biografia])) {
                $success = true;
            } else {
                $errors[] = 'Erro ao cadastrar usuário';
            }
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container">
    <div class="form-container" style="max-width: 600px;">
        <h1>📝 Criar Conta</h1>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                ✅ Cadastro realizado com sucesso! <a href="index.php#login">Fazer login</a>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $error): ?>
                    <p>❌ <?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" data-validate>
            <div class="form-group">
                <label for="nome">Nome Completo *</label>
                <input type="text" id="nome" name="nome" class="form-control" required 
                       value="<?php echo htmlspecialchars($_POST['nome'] ?? ''); ?>">
                <span class="error-message"></span>
            </div>
            
            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" class="form-control" required
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                <span class="error-message"></span>
            </div>
            
            <div class="form-group">
                <label for="telefone">Telefone</label>
                <input type="tel" id="telefone" name="telefone" class="form-control" 
                       placeholder="(11) 99999-9999"
                       value="<?php echo htmlspecialchars($_POST['telefone'] ?? ''); ?>">
                <span class="error-message"></span>
            </div>
            
            <div class="form-group">
                <label for="tipo">Tipo de Conta *</label>
                <select id="tipo" name="tipo" class="form-control" required>
                    <option value="aluno" <?php echo ($_POST['tipo'] ?? '') === 'aluno' ? 'selected' : ''; ?>>Aluno - Quero aprender</option>
                    <option value="mentor" <?php echo ($_POST['tipo'] ?? '') === 'mentor' ? 'selected' : ''; ?>>Mentor - Quero ensinar</option>
                </select>
                <span class="error-message"></span>
            </div>
            
            <div class="form-group">
                <label for="biografia">Biografia</label>
                <textarea id="biografia" name="biografia" class="form-control" rows="3" 
                          placeholder="Conte um pouco sobre você..."><?php echo htmlspecialchars($_POST['biografia'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="senha">Senha *</label>
                <input type="password" id="senha" name="senha" class="form-control" required 
                       minlength="6">
                <span class="error-message"></span>
            </div>
            
            <div class="form-group">
                <label for="confirmar_senha">Confirmar Senha *</label>
                <input type="password" id="confirmar_senha" name="confirmar_senha" class="form-control" required>
                <span class="error-message"></span>
            </div>
            
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" required>
                    Aceito os <a href="termos.php" target="_blank">Termos de Serviço</a>
                </label>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">Cadastrar</button>
            
            <p style="text-align: center; margin-top: 1rem;">
                Já tem uma conta? <a href="index.php#login">Fazer login</a>
            </p>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
