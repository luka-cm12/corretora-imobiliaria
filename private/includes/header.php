<?php
// Inicia a sessão e prepara um token CSRF global para formulários
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    try {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } catch (Exception $e) {
        $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
    }
}

// Carrega BASE_URL e demais configs
require_once __DIR__ . '/../config/config.php';

// Definir título da página dinamicamente
$page_title = isset($page_title) ? $page_title : 'Corretora Claudia Colombo | Imóveis de Qualidade';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <meta name="description" content="<?= isset($meta_description) ? htmlspecialchars($meta_description) : 'Encontre o imóvel dos seus sonhos com a Corretora Base. Oferecemos as melhores opções para você e sua família.' ?>">
    
    <!-- BASE_URL vindo da config (dinâmico) -->
    <?php $BASE_PATH = rtrim(BASE_URL, '/') . '/'; ?>

    <!-- Favicon -->
    <link rel="icon" href="<?= $BASE_PATH ?>public/assets/images/logo/logo.png" type="image/png">
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?= $BASE_PATH ?>public/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Open Graph / Social Media -->
    <meta property="og:title" content="<?= htmlspecialchars($page_title) ?>">
    <meta property="og:description" content="<?= isset($meta_description) ? htmlspecialchars($meta_description) : 'Encontre o imóvel dos seus sonhos com a Corretora Claudia Colombo' ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ?>">
    <meta property="og:image" content="<?= $BASE_PATH ?>public/assets/images/logo/logo.png">
</head>
<body>

    <!-- Header -->
    <header class="header">
        <div class="container">
            <div id="logo" class="catalogo-logo">
                <div class="logo__bg"></div>
                <a href="<?= $BASE_PATH ?>index.php" title="Home">
                    <img class="logo-dark" src="<?= $BASE_PATH ?>public/assets/images/logo/logo-dark.png" alt="Corretora Claudia Colombo">
                </a>
            </div>
            
            <nav class="main-nav">
                <ul>
                    <li><a href="<?= $BASE_PATH ?>index.php" <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'class="active"' : '' ?>>Home</a></li>
                   <!-- <li><a href="<?= $BASE_PATH ?>sobre.php" <?= basename($_SERVER['PHP_SELF']) == 'sobre.php' ? 'class="active"' : '' ?>>Sobre</a></li> -->
                    <li><a href="<?= $BASE_PATH ?>imoveis.php" <?= basename($_SERVER['PHP_SELF']) == 'imoveis.php' ? 'class="active"' : '' ?>>Imóveis</a></li>
                    <li><a href="<?= $BASE_PATH ?>contato.php" <?= basename($_SERVER['PHP_SELF']) == 'contato.php' ? 'class="active"' : '' ?>>Contato</a></li>
                </ul>
            </nav>
            <div class="mobile-menu">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </header>