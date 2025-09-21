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
                    <li>
                        <a href="<?php echo BASE_URL; ?>admin/tipos_imoveis.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'tipos_imoveis.php') ? 'active' : ''; ?>">
                            <i class="fas fa-tags"></i> Tipos de Imóvel
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
                    <li>
                        <a href="<?php echo BASE_URL; ?>admin/adicionar_cliente.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'adicionar_cliente.php') ? 'active' : ''; ?>">
                            <i class="fas fa-user-plus"></i> Adicionar Cliente
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>admin/leads.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'leads.php') ? 'active' : ''; ?>">
                            <i class="fas fa-lightbulb"></i> Leads
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

            <!-- Contratos -->
            <li class="menu-item">
                <a href="#submenu-contratos" data-bs-toggle="collapse" class="dropdown-toggle">
                    <i class="fas fa-file-contract"></i>
                    <span>Contratos</span>
                </a>
                <ul class="collapse list-unstyled" id="submenu-contratos">
                    <li>
                        <a href="<?php echo BASE_URL; ?>admin/lista_contratos.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'lista_contratos.php') ? 'active' : ''; ?>">
                            <i class="fas fa-list"></i> Listar Contratos
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>admin/novo_contrato.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'novo_contrato.php') ? 'active' : ''; ?>">
                            <i class="fas fa-file-signature"></i> Novo Contrato
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>admin/modelos_contrato.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'modelos_contrato.php') ? 'active' : ''; ?>">
                            <i class="fas fa-copy"></i> Modelos
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Relatórios (apenas para admin) -->
            <?php if ($perfil_usuario == 'admin'): ?>
            <li>
                <a href="<?php echo BASE_URL; ?>admin/relatorios.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'relatorios.php') ? 'active' : ''; ?>">
                    <i class="fas fa-chart-bar"></i>
                    <span>Relatórios</span>
                </a>
            </li>
            <?php endif; ?>

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
                        <a href="<?php echo BASE_URL; ?>admin/config_sistema.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'config_sistema.php') ? 'active' : ''; ?>">
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