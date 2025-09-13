<?php
/**
 * Autenticação e controle de acesso usando tabela `usuarios`
 */

session_start();
require_once(__DIR__ . '/db.php');
global $conn;

// Verificar se usuário está logado
function is_logged_in() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

// Redirecionar para login se não estiver autenticado
function require_login() {
    if (!is_logged_in()) {
        header('Location: admin/login.php');
        exit;
    }
}

// Tentativa de login
function attempt_login($email, $password) {
    global $conn;

    // Validar formato de email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $sql = "SELECT * FROM usuarios WHERE email = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verifica se usuário está ativo
        if ($user['status'] != 1) {
            return false;
        }

        if (password_verify($password, $user['senha'])) {
            // Regenerar ID da sessão para segurança
            session_regenerate_id(true);

            $_SESSION['logged_in'] = true;
            $_SESSION['id'] = $user['id'];
            $_SESSION['nome'] = $user['nome'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['perfil'] = $user['perfil'];

            // Atualizar último login
            $update = $conn->prepare("UPDATE usuarios SET ultimo_login = NOW() WHERE id = ?");
            $update->bind_param('i', $user['id']);
            $update->execute();

            return true;
        }
    }

    return false;
}

// Logout
function logout() {
    $_SESSION = [];
    session_destroy();
    header('Location: admin/login.php');
    exit;
}
