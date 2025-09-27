<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Verifica se o usuário está logado.
 * @return bool
 */
function usuario_logado() {
    return isset($_SESSION['usuario_id']);
}

/**
 * Verifica se o usuário tem uma permissão específica.
 * @param string $permissao
 * @return bool
 */
function usuario_tem_permissao($permissao) {
    if (!usuario_logado()) {
        return false;
    }

    // Supondo que as permissões do usuário estão em $_SESSION['usuario_permissoes'] como array
    if (!isset($_SESSION['usuario_permissoes']) || !is_array($_SESSION['usuario_permissoes'])) {
        return false;
    }

    return in_array($permissao, $_SESSION['usuario_permissoes']);
}

/**
 * Redireciona o usuário se ele não estiver logado.
 * @param string $urlLogin
 */
function proteger_pagina($urlLogin = 'login.php') {
    if (!usuario_logado()) {
        header("Location: $urlLogin");
        exit();
    }
}

/**
 * Redireciona se o usuário não tiver a permissão necessária.
 * @param string $permissao
 * @param string $urlNegado
 */
function permitir_somente($permissao, $urlNegado = 'acesso_negado.php') {
    if (!usuario_tem_permissao($permissao)) {
        header("Location: $urlNegado");
        exit();
    }
}
?>
