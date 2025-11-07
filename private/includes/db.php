<?php
// Carrega configuração central (BASE_URL, DB_*, $conn compartilhado)
require_once __DIR__ . '/../config/config.php';

// Define ambiente com base no host, caso não tenha vindo da config
if (!defined('DEV_ENVIRONMENT')) {
    $host = $_SERVER['SERVER_NAME'] ?? 'localhost';
    define('DEV_ENVIRONMENT', in_array($host, ['localhost', '127.0.0.1']));
}

// A conexão $conn já foi criada no config.php

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
        $success = $stmt->execute($params);
        
        if (!$success) {
            throw new PDOException("Execute failed");
        }

        // SELECT retorna array de resultados
        if (stripos(trim($sql), 'select') === 0 || stripos(trim($sql), 'show') === 0 || stripos(trim($sql), 'describe') === 0) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // INSERT retorna o ID do registro inserido
        if (stripos(trim($sql), 'insert') === 0) {
            $insertId = $conn->lastInsertId();
            return $insertId ? (int)$insertId : $stmt->rowCount();
        }

        // UPDATE/DELETE retorna número de linhas afetadas
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
