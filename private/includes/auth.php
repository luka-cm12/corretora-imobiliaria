<?php
/**
 * Autenticação e controle de acesso usando tabela `usuarios`
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once(__DIR__ . '/db.php');
global $conn;

// Verificar se usuário está logado
function is_logged_in() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

// Redirecionar para login se não estiver autenticado
function require_login() {
    if (!is_logged_in()) {
        // Usa BASE_URL quando disponível para redirecionamento absoluto
        $base = defined('BASE_URL') ? BASE_URL : (isset($_SERVER['HTTP_HOST']) ? ('http://' . $_SERVER['HTTP_HOST'] . '/corretora-imobiliaria/') : '/corretora-imobiliaria/');
        header('Location: ' . rtrim($base, '/') . '/private/admin/login.php');
        exit;
    }
}

// Tentativa de login
function attempt_login($email, $password) {
    global $conn;

    // Aceita e normaliza email; não bloqueia caso formato seja incomum
    $email = trim($email);

    $sql = "SELECT * FROM usuarios WHERE email = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Verifica se usuário está ativo (se a coluna existir). Considera ativo quando status ausente/NULL.
        if (array_key_exists('status', $user)) {
            $statusVal = is_null($user['status']) ? 1 : (int)$user['status'];
            if ($statusVal !== 1) {
                return false;
            }
        }

        $stored = $user['senha'] ?? '';
        $passwordOk = false;

        // 1) Tenta verificar como hash (bcrypt/argon2)
        if (is_string($stored) && $stored !== '' && password_get_info($stored)['algo'] !== 0) {
            if (password_verify($password, $stored)) {
                $passwordOk = true;
                // Rehash se necessário
                if (password_needs_rehash($stored, PASSWORD_DEFAULT)) {
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $rehash = $conn->prepare("UPDATE usuarios SET senha = ?, atualizado_em = NOW() WHERE id = ?");
                    $rehash->execute([$newHash, $user['id']]);
                }
            }
        }

        // 2) Caso não seja hash ou a verificação como hash falhe, aceita senha em texto puro (migração) e re-hash
        if (!$passwordOk && is_string($stored)) {
            if (hash_equals((string)$stored, (string)$password)) {
                $passwordOk = true;
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $rehash = $conn->prepare("UPDATE usuarios SET senha = ?, atualizado_em = NOW() WHERE id = ?");
                $rehash->execute([$newHash, $user['id']]);
            }
        }

        // 3) Migração de hashes legados (md5/sha1) se detectado formato, então re-hash seguro
        if (!$passwordOk && is_string($stored)) {
            // md5 com 32 hex
            if (preg_match('/^[a-f0-9]{32}$/i', $stored) === 1) {
                if (hash_equals(strtolower($stored), md5($password))) {
                    $passwordOk = true;
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $rehash = $conn->prepare("UPDATE usuarios SET senha = ?, atualizado_em = NOW() WHERE id = ?");
                    $rehash->execute([$newHash, $user['id']]);
                }
            }
            // sha1 com 40 hex
            if (!$passwordOk && preg_match('/^[a-f0-9]{40}$/i', $stored) === 1) {
                if (hash_equals(strtolower($stored), sha1($password))) {
                    $passwordOk = true;
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $rehash = $conn->prepare("UPDATE usuarios SET senha = ?, atualizado_em = NOW() WHERE id = ?");
                    $rehash->execute([$newHash, $user['id']]);
                }
            }
        }

        if ($passwordOk) {
            // Regenerar ID da sessão para segurança
            session_regenerate_id(true);

            // Chaves atuais
            $_SESSION['logged_in'] = true;
            $_SESSION['id'] = $user['id'];
            $_SESSION['nome'] = $user['nome'] ?? '';
            $_SESSION['email'] = $user['email'] ?? '';
            $_SESSION['perfil'] = $user['perfil'] ?? '';

            // Chaves legadas para compatibilidade com páginas antigas
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['usuario_nome'] = $user['nome'] ?? '';
            $_SESSION['usuario_email'] = $user['email'] ?? '';
            $_SESSION['usuario_perfil'] = $user['perfil'] ?? '';

            // Gera token CSRF se não existir
            if (empty($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }

            // Atualizar último login
            $update = $conn->prepare("UPDATE usuarios SET ultimo_login = NOW() WHERE id = ?");
            $update->execute([$user['id']]);

            return true;
        }
    }

    // Em desenvolvimento, registra motivo do erro
    if (defined('DEV_ENVIRONMENT') && DEV_ENVIRONMENT) {
        error_log('[AUTH] Falha de login para email: ' . $email);
    }
    return false;
}

// Logout
function logout() {
    // Limpa sessões atuais e legadas
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();

    // Redireciona respeitando BASE_URL
    $base = defined('BASE_URL') ? BASE_URL : (isset($_SERVER['HTTP_HOST']) ? ('http://' . $_SERVER['HTTP_HOST'] . '/corretora-imobiliaria/') : '/corretora-imobiliaria/');
    header('Location: ' . rtrim($base, '/') . '/private/admin/login.php');
    exit;
}
