<?php
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
?>
