<?php
// Defina como true em desenvolvimento e false em produção
define('DEV_ENVIRONMENT', true);

// Configurações do banco de dados
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'admin');
define('DB_PASS', 'senha_admin');
define('DB_NAME', 'corretora_base');

try {
    // Conexão PDO
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
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


// Exemplo de uso da função db_query para inserção
$titulo = $_POST['titulo'] ?? '';
$cidade = $_POST['cidade'] ?? '';
$sql = "INSERT INTO imoveis (titulo, cidade) VALUES (?, ?)";
db_query($sql, [$titulo, $cidade]);

$imoveis = db_query("SELECT * FROM imoveis WHERE cidade = ?", [$cidade]);
foreach ($imoveis as $imovel) {
    // ...
}
