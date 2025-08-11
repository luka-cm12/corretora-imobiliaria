<?php
/**
 * logout.php
 * Processamento de logout avançado com controle de sessões
 * 
 * @version 2.0
 * @date 2023-11-20
 */

// Inicia a sessão se ainda não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se há um usuário logado
if (isset($_SESSION['usuario_id'])) {
    require_once 'conexao.php';
    require_once 'log_acoes.php';
    
    $usuario_id = $_SESSION['usuario_id'];
    $session_id = session_id();
    
    try {
        // 1. Registra o log de logout
        registrarLog($usuario_id, 'logout', 'Usuário realizou logout no sistema');
        
        // 2. Remove a sessão ativa do banco de dados (se estiver usando controle de sessões)
        $stmt = $conn->prepare("DELETE FROM sessoes_ativas WHERE usuario_id = ? AND session_id = ?");
        $stmt->execute([$usuario_id, $session_id]);
        
        // 3. Limpa os tokens de autenticação (se estiver usando tokens)
        if (isset($_COOKIE['auth_token'])) {
            $token = $_COOKIE['auth_token'];
            $stmt = $conn->prepare("DELETE FROM auth_tokens WHERE token = ?");
            $stmt->execute([$token]);
            
            // Expira o cookie
            setcookie('auth_token', '', time() - 3600, '/', '', true, true);
        }
        
    } catch (PDOException $e) {
        // Em caso de erro, apenas registra no log do sistema
        error_log("Erro durante logout: " . $e->getMessage());
    }
}

// Limpa todos os dados da sessão
$_SESSION = array();

// Configuração para destruição segura do cookie de sessão
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// Destrói a sessão
session_destroy();

// Redireciona para a página de login com mensagem
header("Location: " . BASE_URL . "admin/login.php?logout=success");
exit();
?>