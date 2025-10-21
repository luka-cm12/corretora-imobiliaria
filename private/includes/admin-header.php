<?php
// Verificar se o usuário está logado
/*if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}*/

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
    <?php require_once __DIR__ . '/../config/config.php'; ?>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Admin custom CSS -->
    <link rel="stylesheet" href="<?= rtrim(BASE_URL,'/') ?>/public/assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <a href="<?= BASE_URL ?>private/admin/dashboard.php" class="sidebar-logo-link" title="Dashboard">
                    <img src="<?= BASE_URL ?>public/assets/images/logo/logo-dark.png" alt="Claudia Colombo - Corretora" class="sidebar-logo-img">
                </a>
                <div class="sidebar-logo-creci">CRECI 61839F</div>
            </div>
            <nav class="admin-nav">
                <ul>
                    <li><a href="<?= rtrim(BASE_URL,'/') ?>/private/admin/dashboard.php" <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'class="active"' : '' ?>><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="<?= rtrim(BASE_URL,'/') ?>/private/imoveis/listar.php" <?= basename($_SERVER['PHP_SELF']) == 'listar.php' ? 'class="active"' : '' ?>><i class="fas fa-home"></i> Imóveis</a></li>
                    <li><a href="<?= rtrim(BASE_URL,'/') ?>/private/imoveis/adicionar.php" <?= basename($_SERVER['PHP_SELF']) == 'adicionar.php' ? 'class="active"' : '' ?>><i class="fas fa-plus-circle"></i> Adicionar Imóvel</a></li>
                    <li><a href="<?= rtrim(BASE_URL,'/') ?>/private/admin/lista_clientes.php" <?= basename($_SERVER['PHP_SELF']) == 'lista_clientes.php' ? 'class="active"' : '' ?>><i class="fas fa-users"></i> Leads (Clientes)</a></li>
                    <li><a href="<?= rtrim(BASE_URL,'/') ?>/private/imoveis/proprietario-cadastrar.php" <?= in_array(basename($_SERVER['PHP_SELF']), ['proprietario-cadastrar.php','proprietarios.php']) ? 'class="active"' : '' ?>><i class="fas fa-user-tie"></i> Proprietários</a></li>
                    <li><a href="<?= rtrim(BASE_URL,'/') ?>/private/admin/usuarios.php" <?= basename($_SERVER['PHP_SELF']) == 'usuarios.php' ? 'class="active"' : '' ?>><i class="fas fa-users"></i> Usuários</a></li>
                    <li><a href="<?= rtrim(BASE_URL,'/') ?>/private/admin/configuracoes.php" <?= basename($_SERVER['PHP_SELF']) == 'configuracoes.php' ? 'class="active"' : '' ?>><i class="fas fa-cog"></i> Configurações</a></li>
                    <li><a href="<?= rtrim(BASE_URL,'/') ?>/private/admin/logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
                </ul>
            </nav>
        </div>
        
        <!-- Mobile sidebar overlay -->
        <div class="mobile-sidebar-overlay d-lg-none" id="mobileSidebarOverlay"></div>
        
        <!-- Main Content -->
        <div class="main-content">
            <header class="admin-header">
                <div class="header-left">
                    <button class="mobile-sidebar-toggle d-lg-none" id="mobileSidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h2><?= htmlspecialchars($page_title) ?></h2>
                </div>
                <div class="header-right">
                    <span class="welcome">Bem-vindo, <?= htmlspecialchars($_SESSION['nome'] ?? $_SESSION['email'] ?? 'Administrador') ?></span>
                </div>
            </header>
            
            <div class="content">