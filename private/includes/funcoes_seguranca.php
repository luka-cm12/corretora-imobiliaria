<?php
// Garante sessão ativa para uso de CSRF e outras verificações
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// conexao segura com MySQL usando PDO
function getConnection() {
    $host = "localhost";
    $db   = "corretora_base";
    $user = "admin";
    $pass = "senha_admin";
    $charset = "utf8mb4";

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        return new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        die("Erro de conexão: " . $e->getMessage());
    }
}

/**
 * Consulta segura com parâmetros
 */
function consultaSegura($sql, $params = []) {
    $pdo = getConnection();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Inserção segura (exemplo)
 */
function inserirUsuario($nome, $email, $senha) {
    $pdo = getConnection();
    $sql = "INSERT INTO usuarios (nome, email, senha) VALUES (:nome, :email, :senha)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nome'  => sanitize_input($nome),
        ':email' => sanitize_input($email),
        ':senha' => hash_password($senha)
    ]);
    return $pdo->lastInsertId();
}

/**
 * Sanitiza entrada básica (fallback simples)
 */
if (!function_exists('sanitize_input')) {
    function sanitize_input($data) {
        if (is_string($data)) {
            return trim(filter_var($data, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES));
        }
        return $data;
    }
}

/**
 * Hash de senha usando algoritmo padrão
 */
if (!function_exists('hash_password')) {
    function hash_password($senha) {
        return password_hash((string)$senha, PASSWORD_DEFAULT);
    }
}

/**
 * Gera token CSRF se não existir
 */
if (!function_exists('ensureCsrfToken')) {
    function ensureCsrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

/**
 * Verifica o token CSRF presente em POST/GET
 */
if (!function_exists('verificaCsrfToken')) {
    function verificaCsrfToken() {
        $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? null;
        if (!$token || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            header('Location: acesso-negado.php');
            exit;
        }
    }
}
?>
