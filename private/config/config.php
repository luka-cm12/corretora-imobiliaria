<?php
/**
 * Configurações globais do sistema
 */

// Exibir erros (recomendo desativar em produção)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Fuso horário
date_default_timezone_set('America/Sao_Paulo');

// Inicia a sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Configuração do banco de dados
 */
if (!defined('NOME_CORRETORA')) define('NOME_CORRETORA', 'Corretora Cláudia Colombo');

// Detecta ambiente automaticamente
$serverName = $_SERVER['SERVER_NAME'] ?? 'localhost';

if ($serverName === 'localhost' || $serverName === '127.0.0.1') {
    // Ambiente de desenvolvimento (local)
    if (!defined('DB_HOST')) define('DB_HOST', '127.0.0.1');
    if (!defined('DB_USER')) define('DB_USER', 'root');
    if (!defined('DB_PASS')) define('DB_PASS', '');
    if (!defined('DB_NAME')) define('DB_NAME', 'corretora_base');
} else {
    // Ambiente de produção (servidor real)
    if (!defined('DB_HOST')) define('DB_HOST', '127.0.0.1');
    if (!defined('DB_USER')) define('DB_USER', 'admin');
    if (!defined('DB_PASS')) define('DB_PASS', 'senha_admin');
    if (!defined('DB_NAME')) define('DB_NAME', 'corretora_base');
}

/**
 * Configuração do banco de dados
 */
if (!defined('NOME_CORRETORA')) define('NOME_CORRETORA', 'Corretora Cláudia Colombo');

try {
    // Tenta primeiro com root (sem senha) - comum em PCs locais
    $conn = new PDO("mysql:host=127.0.0.1;dbname=corretora_base", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e1) {
    try {
        // Se falhar, tenta com admin/senha_admin
        $conn = new PDO("mysql:host=127.0.0.1;dbname=corretora_base", "admin", "senha_admin", [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    } catch (PDOException $e2) {
        die("Erro ao conectar ao banco: verifique usuário/senha. <br>" . $e2->getMessage());
    }
}


// Tenta conectar
try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Erro ao conectar ao banco: " . $e->getMessage());
}

/**
 * Configurações de segurança
 */
define('APP_NAME', 'CorretoraClaudiaColombo');
define('BASE_URL', 'http://localhost/corretora-imobiliaria/'); // ajuste conforme sua pasta

// Controle de acesso (tempo de sessão: 1h)
define('SESSION_TIMEOUT', 3600);

// Verifica expiração de sessão
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > SESSION_TIMEOUT)) {
    session_unset();
    session_destroy();
}
$_SESSION['LAST_ACTIVITY'] = time();
