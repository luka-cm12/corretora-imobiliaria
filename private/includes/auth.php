<?php
/**
 * Autenticação e controle de acesso
 */

session_start();

// Verificar se usuário está logado (para área administrativa)
function is_logged_in() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
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
    require_once(__DIR__ . '/db.php');
    global $conn;

    $sql = "SELECT * FROM usuarios WHERE email = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['senha'])) {
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nome'] = $user['nome'];
            $_SESSION['user_perfil'] = $user['perfil'];
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