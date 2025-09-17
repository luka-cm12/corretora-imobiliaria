<?php
// Defina como true em desenvolvimento e false em produção
define('DEV_ENVIRONMENT', true);

// Configurações do banco de dados
define('DB_HOST', '127.0.0.1');     // Endereço do servidor MySQL
define('DB_USER', 'admin'); // Usuário do banco de dados
define('DB_PASS', 'senha_admin');   // Senha do banco de dados
define('DB_NAME', 'corretora_base');    // Nome do banco de dados

// Tentativa de conexão
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Verificar erros na conexão
    if ($conn->connect_error) {
        throw new Exception("Falha na conexão com o banco de dados: " . $conn->connect_error);
    }
    
    // Definir o charset para utf8 (recomendado para aplicações em português)
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    // Registrar o erro em um arquivo de log (recomendado para produção)
    error_log($e->getMessage(), 3, __DIR__ . '/../logs/db_errors.log');
    
    // Exibir mensagem amigável (apenas em ambiente de desenvolvimento)
    if (defined('DEV_ENVIRONMENT') && DEV_ENVIRONMENT === true) {
        die("<h2>Erro de conexão com o banco de dados</h2>
             <p>{$e->getMessage()}</p>
             <p>Por favor, verifique as configurações do banco de dados.</p>");
    } else {
        die("<h2>Erro temporário do sistema</h2>
             <p>Estamos enfrentando problemas técnicos. Por favor, tente novamente mais tarde.</p>");
    }
}

// Funções úteis para o sistema

/**
 * Executa uma consulta SQL segura usando prepared statements
 * 
 * @param string $sql Consulta SQL com placeholders (?)
 * @param array $params Parâmetros para bind (tipos e valores)
 * @return mysqli_result|bool Resultado da consulta ou false em caso de erro
 */
function db_query($sql, $params = []) {
    global $conn; // $conn deve ser um objeto mysqli

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Erro na preparação da query: " . $conn->error);
    }

    if ($params) {
        // Cria a string de tipos automaticamente (tudo como string 's')
        $types = str_repeat('s', count($params));
        // Usa o operador ... para passar os parâmetros
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();

    // Para SELECT, retorna resultado como array associativo
    if (stripos(trim($sql), 'select') === 0) {
        $result = $stmt->get_result();
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();
        return $rows;
    }

    // Para INSERT/UPDATE/DELETE, retorna número de linhas afetadas
    $affected = $stmt->affected_rows;
    $stmt->close();
    return $affected;
}

/**
 * Escapa strings para prevenir SQL Injection
 * 
 * @param string $data Dados a serem escapados
 * @return string Dados escapados
 */
function db_escape($data) {
    global $conn;
    return $conn->real_escape_string($data);
}

/**
 * Obtém o último ID inserido
 * 
 * @return int Último ID inserido
 */
function db_last_id() {
    global $conn;
    return $conn->insert_id;
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
