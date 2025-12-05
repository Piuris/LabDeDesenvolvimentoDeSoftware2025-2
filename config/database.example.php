<?php
/**
 * Arquivo de exemplo de configuração do banco de dados
 * 
 * INSTRUÇÕES:
 * 1. Copie este arquivo para database.php
 * 2. Preencha as credenciais fornecidas pela hospedagem
 * 3. NUNCA commite o arquivo database.php com credenciais reais no Git
 */

// ============================================
// CONFIGURAÇÃO PARA DESENVOLVIMENTO LOCAL (XAMPP)
// ============================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'sistema_mentorias');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ============================================
// CONFIGURAÇÃO PARA PRODUÇÃO (HOSPEDAGEM)
// ============================================
// Descomente e preencha com os dados da sua hospedagem:

// Exemplo InfinityFree:
// define('DB_HOST', 'sqlXXX.epizy.com');
// define('DB_NAME', 'epiz_XXXXX_mentorias');
// define('DB_USER', 'epiz_XXXXX');
// define('DB_PASS', 'sua_senha_aqui');
// define('DB_CHARSET', 'utf8mb4');

// Exemplo 000webhost:
// define('DB_HOST', 'localhost');
// define('DB_NAME', 'idXXXXX_mentorias');
// define('DB_USER', 'idXXXXX');
// define('DB_PASS', 'sua_senha_aqui');
// define('DB_CHARSET', 'utf8mb4');

// ============================================
// FUNÇÕES (NÃO ALTERAR)
// ============================================

function getConnection() {
    static $conn = null;
    
    if ($conn === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Em produção, não exiba detalhes do erro
            // Log o erro em um arquivo de log
            error_log("Erro na conexão com o banco de dados: " . $e->getMessage());
            
            // Mensagem amigável para o usuário
            die("Erro na conexão com o banco de dados. Por favor, tente novamente mais tarde.");
        }
    }
    
    return $conn;
}

function iniciarSessao() {
    if (session_status() === PHP_SESSION_NONE) {
        // Configurações de segurança para sessões em produção
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS'])); // HTTPS apenas se disponível
        
        session_start();
    }
}

function estaLogado() {
    iniciarSessao();
    return isset($_SESSION['usuario_id']);
}

function getUsuarioLogado() {
    iniciarSessao();
    if (estaLogado()) {
        return [
            'id' => $_SESSION['usuario_id'],
            'nome' => $_SESSION['usuario_nome'],
            'email' => $_SESSION['usuario_email'],
            'tipo' => $_SESSION['usuario_tipo']
        ];
    }
    return null;
}

function logout() {
    iniciarSessao();
    session_unset();
    session_destroy();
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
?>

