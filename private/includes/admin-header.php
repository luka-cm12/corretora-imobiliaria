<?php
// Verificar se o usuário está logado
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Definir título da página se não estiver definido
if (!isset($page_title)) {
    $page_title = 'Dashboard';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> | Admin | Corretora Claudia</title>
    <link rel="stylesheet" href="../../public/assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <h1>Corretora<span>Claudia</span></h1>
            </div>
            <nav class="admin-nav">
                <ul>
                    <li><a href="dashboard.php" <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'class="active"' : '' ?>><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="../imoveis/listar.php" <?= basename($_SERVER['PHP_SELF']) == 'listar.php' ? 'class="active"' : '' ?>><i class="fas fa-home"></i> Imóveis</a></li>
                    <li><a href="../imoveis/adicionar.php" <?= basename($_SERVER['PHP_SELF']) == 'adicionar.php' ? 'class="active"' : '' ?>><i class="fas fa-plus-circle"></i> Adicionar Imóvel</a></li>
                    <li><a href="usuarios.php" <?= basename($_SERVER['PHP_SELF']) == 'usuarios.php' ? 'class="active"' : '' ?>><i class="fas fa-users"></i> Usuários</a></li>
                    <li><a href="configuracoes.php" <?= basename($_SERVER['PHP_SELF']) == 'configuracoes.php' ? 'class="active"' : '' ?>><i class="fas fa-cog"></i> Configurações</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
                </ul>
            </nav>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <header class="admin-header">
                <div class="header-left">
                    <h2><?= htmlspecialchars($page_title) ?></h2>
                </div>
                <div class="header-right">
                    <span class="welcome">Bem-vindo, <?= htmlspecialchars($_SESSION['admin_username']) ?></span>
                </div>
            </header>
            
            <div class="content">