<?php
/**
 * admin-sidebar.php
 * Barra lateral da área administrativa
 * 
 * @version 1.2
 * @date 2023-11-20
 */

// Defina o nome da corretora se ainda não estiver definido
if (!defined('NOME_CORRETORA')) {
    define('NOME_CORRETORA', 'Corretora');
}

// Verifica se o usuário está logado
/*if (!isset($_SESSION['usuario_id'])) {
    header("Location: " . BASE_URL . "admin/login.php");
    exit();
}*/

// Obtém o perfil do usuário
$perfil_usuario = $_SESSION['usuario_tipo'] ?? 'corretor';
?>

<!-- Sidebar -->
<aside class="admin-sidebar">
    <!-- Logo e Nome da Corretora -->
    <div class="sidebar-header">
        <a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="d-flex align-items-center">
            <img src="<?php echo BASE_URL; ?>assets/images/logo-admin.png" alt="<?php echo defined('NOME_CORRETORA') ? NOME_CORRETORA : 'Corretora'; ?>" class="sidebar-logo">
            <span class="sidebar-brand"><?php echo (defined('NOME_CORRETORA') && NOME_CORRETORA) ? NOME_CORRETORA : 'Corretora'; ?></span>
        </a>
    </div>

    <!-- Menu de Navegação -->
    <nav class="sidebar-menu">
        <ul class="list-unstyled">
            <!-- Dashboard -->
            <li>
                <a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <!-- Imóveis -->
            <li class="menu-item">
                <a href="#submenu-imoveis" data-bs-toggle="collapse" class="dropdown-toggle">
                    <i class="fas fa-home"></i>
                    <span>Imóveis</span>
                </a>
                <ul class="collapse list-unstyled" id="submenu-imoveis">
                    <li>
                        <a href="<?php echo BASE_URL; ?>admin/listar.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'lista_imoveis.php') ? 'active' : ''; ?>">
                            <i class="fas fa-list"></i> Listar Imóveis
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>admin/adicionar.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'adicionar_imovel.php') ? 'active' : ''; ?>">
                            <i class="fas fa-plus-circle"></i> Adicionar Imóvel
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Clientes -->
            <li class="menu-item">
                <a href="#submenu-clientes" data-bs-toggle="collapse" class="dropdown-toggle">
                    <i class="fas fa-users"></i>
                    <span>Clientes</span>
                </a>
                <ul class="collapse list-unstyled" id="submenu-clientes">
                    <li>
                        <a href="<?php echo BASE_URL; ?>admin/lista_clientes.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'lista_clientes.php') ? 'active' : ''; ?>">
                            <i class="fas fa-list"></i> Listar Clientes
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Visitas -->
            <li>
                <a href="<?php echo BASE_URL; ?>admin/agenda_visitas.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'agenda_visitas.php') ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-check"></i>
                    <span>Agenda de Visitas</span>
                </a>
            </li>

           <!-- Configurações (apenas para admin) -->
            <?php if ($perfil_usuario == 'admin'): ?>
            <li class="menu-item">
                <a href="#submenu-config" data-bs-toggle="collapse" class="dropdown-toggle">
                    <i class="fas fa-cog"></i>
                    <span>Configurações</span>
                </a>
                <ul class="collapse list-unstyled" id="submenu-config">
                    <li>
                        <a href="<?php echo BASE_URL; ?>admin/usuarios.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'usuarios.php') ? 'active' : ''; ?>">
                            <i class="fas fa-user-shield"></i> Usuários
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>admin/configuracoes.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'config_sistema.php') ? 'active' : ''; ?>">
                            <i class="fas fa-sliders-h"></i> Sistema
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>admin/bairros.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'bairros.php') ? 'active' : ''; ?>">
                            <i class="fas fa-map-marked-alt"></i> Bairros
                        </a>
                    </li>
                </ul>
            </li>
            <?php endif; ?>
        </ul>
    </nav>

    <!-- Perfil do Usuário -->
    <div class="sidebar-footer">
        <div class="user-panel">
            <div class="user-avatar">
                <img src="<?php echo BASE_URL; ?>assets/uploads/usuarios/<?php echo $_SESSION['usuario_avatar'] ?? 'default.jpg'; ?>" alt="Foto do Usuário">
            </div>
            <div class="user-info">
                <span class="user-name"><?php echo $_SESSION['usuario_nome'] ?? 'Usuário'; ?></span>
                <span class="user-role"><?php echo ucfirst($perfil_usuario); ?></span>
            </div>
            <a href="<?php echo BASE_URL; ?>admin/logout.php" class="logout-btn" title="Sair">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </div>
</aside>

<!-- Overlay para mobile -->
<div class="sidebar-overlay"></div>