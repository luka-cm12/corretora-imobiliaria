<?php
/**
 * Configurações globais do sistema (prontas para Hostinger)
 */

// Exibir erros: habilita no local, desabilita no servidor
$__serverName = $_SERVER['SERVER_NAME'] ?? 'localhost';
$__isLocal = in_array($__serverName, ['localhost', '127.0.0.1']);
ini_set('display_errors', $__isLocal ? '1' : '0');
error_reporting(E_ALL);

// Fuso horário
date_default_timezone_set('America/Sao_Paulo');

// Inicia a sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Nome da corretora (branding)
if (!defined('NOME_CORRETORA')) define('NOME_CORRETORA', 'Corretora Cláudia Colombo');

// BASE_URL dinâmico (sempre aponta para a raiz do projeto, não para a pasta atual)
$__scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$__host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$__script = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', $_SERVER['SCRIPT_NAME']) : '/';
if (preg_match('#^(.*?)/(?:private|public)/#', $__script, $m)) {
    $__basePath = rtrim($m[1], '/') . '/';
} else {
    $__basePath = rtrim(dirname($__script), '/') . '/';
    if ($__basePath === '//') { $__basePath = '/'; }
}
if (!defined('BASE_URL')) define('BASE_URL', $__scheme . '://' . $__host . $__basePath);

// Permite sobrepor via arquivo local (não versionado) quando presente
$__localOverride = __DIR__ . '/config.local.php';
if (file_exists($__localOverride)) {
    require_once $__localOverride;
}

// Configurações do banco de dados (permite variáveis de ambiente ou override)
if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: ($__isLocal ? '127.0.0.1' : 'localhost'));
if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'corretora_base');
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: ($__isLocal ? 'root' : 'admin'));
if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') ?: ($__isLocal ? '' : 'senha_admin'));

// Única conexão PDO compartilhada
if (!isset($conn) || !($conn instanceof PDO)) {
    try {
        $conn = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    } catch (PDOException $e) {
        die('Erro ao conectar ao banco: ' . $e->getMessage());
    }
}

// Controle de acesso (tempo de sessão: 1h)
if (!defined('SESSION_TIMEOUT')) define('SESSION_TIMEOUT', 3600);
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > SESSION_TIMEOUT)) {
    session_unset();
    session_destroy();
}
$_SESSION['LAST_ACTIVITY'] = time();
