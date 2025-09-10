<?php
require_once(__DIR__ . '/../includes/auth.php'); // Já inclui session_start()
require_once(__DIR__ . '/../includes/db.php');

// Se já estiver logado, redireciona para o dashboard
if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

// Processar formulário de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = 'Por favor, preencha todos os campos';
    } elseif (attempt_login($email, $password)) {
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Credenciais inválidas';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Área Administrativa</title>
    <link rel="stylesheet" href="public/assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="logo">
                <h1>Corretora<span>Base</span></h1>
                <p>Área Administrativa</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form action="" method="post">
                <div class="form-group">
                    <label for="email"><i class="fas fa-user"></i> Email</label>
                    <input type="text" id="email" name="email" required autofocus>
                </div>
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Senha</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn">Entrar</button>
            </form>
        </div>
    </div>
</body>
</html>
