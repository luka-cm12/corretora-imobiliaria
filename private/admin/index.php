<?php
/**
 * Página inicial da área administrativa
 * Redireciona para login se não estiver logado, ou para dashboard se estiver logado
 */

// Incluir autenticação
require_once(__DIR__ . '/../includes/auth.php');

// Se estiver logado, vai para dashboard
if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

// Se não estiver logado, vai para login
header('Location: login.php');
exit;
?>