<?php
/**
 * Autenticação e controle de acesso
 */

session_start();
require_once(__DIR__ . '/db.php');
global $conn;

// Verificar se usuário está logado (para área administrativa)
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

    $sql = "SELECT * FROM admin_users WHERE email = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nome'] = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];
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
