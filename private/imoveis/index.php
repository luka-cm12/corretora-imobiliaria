<?php
/**
 * Página inicial da gestão de imóveis
 * Redireciona para login se não estiver logado, ou para listar imóveis se estiver logado
 */

// Incluir autenticação
require_once(__DIR__ . '/../includes/auth.php');

// Verificar se está logado
if (!is_logged_in()) {
    // Se não estiver logado, redireciona para login do admin
    header('Location: ../admin/login.php');
    exit;
}

// Se estiver logado, vai para listagem de imóveis
header('Location: listar.php');
exit;
?>