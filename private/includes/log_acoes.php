<?php
/**
 * log_acoes.php
 * Registro centralizado de ações do usuário no sistema
 * 
 * @version 1.0
 * @date 2025-09-10
 */

// Função para registrar logs de forma segura
function registrarLog($conn, $usuario_id, $acao, $descricao) {
    try {
        // Validação simples
        if (empty($usuario_id) || empty($acao)) {
            throw new Exception("ID do usuário ou ação não informados.");
        }

        // Prepara e executa o insert
        $stmt = $conn->prepare("INSERT INTO logs (usuario_id, acao, descricao, data) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$usuario_id, $acao, $descricao]);

    } catch (PDOException $e) {
        // Log de erro no sistema
        error_log("Erro ao registrar log no banco: " . $e->getMessage());
    } catch (Exception $e) {
        error_log("Erro ao registrar log: " . $e->getMessage());
    }
}

/**
 * Função auxiliar para registrar ação de login
 */
function logLogin($conn, $usuario_id) {
    registrarLog($conn, $usuario_id, 'login', 'Usuário realizou login no sistema');
}

/**
 * Função auxiliar para registrar ação de logout
 */
function logLogout($conn, $usuario_id) {
    registrarLog($conn, $usuario_id, 'logout', 'Usuário realizou logout no sistema');
}

/**
 * Função auxiliar para registrar qualquer ação customizada
 */
function logAcaoCustom($conn, $usuario_id, $acao, $descricao) {
    registrarLog($conn, $usuario_id, $acao, $descricao);
}
