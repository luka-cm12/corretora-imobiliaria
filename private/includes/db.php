<?php
// Configurações do banco de dados
define('DB_HOST', 'localhost');     // Endereço do servidor MySQL
define('DB_USER', 'usuario_corretora'); // Usuário do banco de dados
define('DB_PASS', 'SenhaSegura123!');   // Senha do banco de dados
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
    global $conn;
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Erro ao preparar query: " . $conn->error);
        return false;
    }
    
    if (!empty($params)) {
        $types = '';
        $values = [];
        
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i'; // integer
            } elseif (is_float($param)) {
                $types .= 'd'; // double
            } elseif (is_string($param)) {
                $types .= 's'; // string
            } else {
                $types .= 'b'; // blob
            }
            
            $values[] = $param;
        }
        
        $stmt->bind_param($types, ...$values);
    }
    
    if (!$stmt->execute()) {
        error_log("Erro ao executar query: " . $stmt->error);
        return false;
    }
    
    $result = $stmt->get_result();
    
    // Para INSERT/UPDATE/DELETE, retornar número de linhas afetadas
    if ($result === false) {
        return $stmt->affected_rows;
    }
    
    return $result;
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