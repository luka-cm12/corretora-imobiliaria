<?php
// Carrega configuração central (BASE_URL, DB_*, $conn compartilhado)
require_once __DIR__ . '/../config/config.php';

// Define ambiente com base no host, caso não tenha vindo da config
if (!defined('DEV_ENVIRONMENT')) {
    $host = $_SERVER['SERVER_NAME'] ?? 'localhost';
    define('DEV_ENVIRONMENT', in_array($host, ['localhost', '127.0.0.1']));
}

// Configurações do banco de dados (apenas se não estiverem definidas pela config)
if (!defined('DB_HOST')) define('DB_HOST', '127.0.0.1');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');
if (!defined('DB_NAME')) define('DB_NAME', 'corretora_base');

if (!isset($conn) || !($conn instanceof PDO)) {
    try {
        // Conexão PDO
        $conn = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Lança exceções em erros
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Retorna array associativo por padrão
                PDO::ATTR_EMULATE_PREPARES => false, // Usa prepared statements nativos
            ]
        );
    } catch (PDOException $e) {
        if (DEV_ENVIRONMENT) {
            die("<h2>Erro de conexão com o banco de dados</h2><p>{$e->getMessage()}</p>");
        } else {
            die("<h2>Erro temporário do sistema</h2><p>Estamos enfrentando problemas técnicos. Por favor, tente novamente mais tarde.</p>");
        }
    }
}

/**
 * Executa uma consulta SQL usando PDO
 * @param string $sql SQL com placeholders (?)
 * @param array $params Parâmetros a serem bindados
 * @return array|int Resultado da query ou número de linhas afetadas
 */
function db_query($sql, $params = []) {
    global $conn;
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        // SELECT retorna array de resultados
        if (stripos(trim($sql), 'select') === 0) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // INSERT/UPDATE/DELETE retorna número de linhas afetadas
        return $stmt->rowCount();

    } catch (PDOException $e) {
        if (DEV_ENVIRONMENT) {
            die("<h2>Erro na consulta SQL</h2><p>{$e->getMessage()}</p>");
        } else {
            return false;
        }
    }
}

/**
 * Escapa strings (não é estritamente necessário com PDO, mas útil)
 */
function db_escape($data) {
    global $conn;
    return htmlspecialchars($data, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Obtém último ID inserido
 */
function db_last_id() {
    global $conn;
    return $conn->lastInsertId();
}

// Importante: este arquivo NÃO deve executar consultas automaticamente.
// Qualquer exemplo de uso deve permanecer apenas em documentação/comentários.
