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
if (!defined('DB_HOST')) define('DB_HOST', '127.0.0.1');
if (!defined('DB_USER')) define('DB_USER', 'admin');
if (!defined('DB_PASS')) define('DB_PASS', 'senha_admin');
if (!defined('DB_NAME')) define('DB_NAME', 'corretora_base');


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
