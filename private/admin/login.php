<?php
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

// Se já estiver logado, redirecionar para o dashboard
if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

// Processar formulário de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($username) || empty($password)) {
        $error = 'Por favor, preencha todos os campos';
    } elseif (attempt_login($username, $password)) {
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
    <link rel="stylesheet" href="../../assets/css/admin.css">
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
                    <label for="username"><i class="fas fa-user"></i> Usuário</label>
                    <input type="text" id="username" name="username" required autofocus>
                </div>
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Senha</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn">Entrar</button>
            </form>

            <a href="<?php echo BASE_URL; ?>admin/logout.php" class="logout-link">
                <i class="fas fa-sign-out-alt"></i> Sair
            </a>

            <?php if (isset($_GET['logout']) && $_GET['logout'] === 'success'): ?>
            <div class="alert alert-success">
                Você foi desconectado com sucesso.
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>