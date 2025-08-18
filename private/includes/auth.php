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
        header('Location: private/admin/login.php');
        exit;
    }
}

// Tentativa de login
function attempt_login($username, $password) {
    global $conn;
    
    $sql = "SELECT * FROM admin_users WHERE username = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            return true;
        }
    }
    
    return false;
}

// Logout
function logout() {
    $_SESSION = [];
    session_destroy();
    header('Location: private/admin/login.php');
    exit;
}