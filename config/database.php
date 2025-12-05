<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'sistema_mentorias');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

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
            die("Erro na conexão com o banco de dados: " . $e->getMessage());
        }
    }
    
    return $conn;
}

function iniciarSessao() {
    if (session_status() === PHP_SESSION_NONE) {
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
