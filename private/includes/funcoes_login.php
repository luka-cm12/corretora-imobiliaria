<?php
session_start();

/**
 * Faz login do usuário.
 * @param string $email
 * @param string $senha
 * @param PDO $pdo - Conexão com o banco de dados
 * @return bool - true se login OK, false caso contrário
 */
function login_usuario($email, $senha, $pdo) {
    $stmt = $pdo->prepare("SELECT id, nome, senha, permissoes FROM usuarios WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($senha, $usuario['senha'])) {
        // Armazena informações na sessão
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];

        // Converte permissões string para array
        $_SESSION['usuario_permissoes'] = !empty($usuario['permissoes']) ? explode(',', $usuario['permissoes']) : [];
        return true;
    }
    return false;
}

/**
 * Faz logout do usuário.
 */
function logout_usuario() {
    session_unset();
    session_destroy();
}

/**
 * Verifica se o usuário está logado.
 * @return bool
 */
function usuario_logado() {
    return isset($_SESSION['usuario_id']);
}

/**
 * Cria hash seguro para a senha.
 * @param string $senha
 * @return string
 */
function criar_hash_senha($senha) {
    return password_hash($senha, PASSWORD_DEFAULT);
}

/**
 * Redireciona usuário se não estiver logado
 * @param string $urlLogin
 */
function proteger_pagina($urlLogin = 'login.php') {
    if (!usuario_logado()) {
        header("Location: $urlLogin");
        exit();
    }
}
?>
