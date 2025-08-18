<?php
// Definir título da página dinamicamente
$page_title = isset($page_title) ? $page_title : 'Corretora Base | Imóveis de Qualidade';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <meta name="description" content="<?= isset($meta_description) ? htmlspecialchars($meta_description) : 'Encontre o imóvel dos seus sonhos com a Corretora Base. Oferecemos as melhores opções para você e sua família.' ?>">
    
    <!-- Favicon -->
    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">
    
    <!-- CSS -->
    <link rel="stylesheet" href="public/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Open Graph / Social Media -->
    <meta property="og:title" content="<?= htmlspecialchars($page_title) ?>">
    <meta property="og:description" content="<?= isset($meta_description) ? htmlspecialchars($meta_description) : 'Encontre o imóvel dos seus sonhos com a Corretora Base' ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ?>">
    <meta property="og:image" content="public/assets/images/og-image.jpg">
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container">
            <div class="contact-info">
                <span><i class="fas fa-phone"></i> (XX) XXXX-XXXX</span>
                <span><i class="fas fa-envelope"></i> contato@corretorabase.com.br</span>
            </div>
            <div class="social-icons">
                <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="#" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
            </div>
        </div>
    </div>

    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="logo">
                <a href="index.php">
                    <h1>Corretora<span>Base</span></h1>
                </a>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="index.php" <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'class="active"' : '' ?>>Home</a></li>
                    <li><a href="sobre.php" <?= basename($_SERVER['PHP_SELF']) == 'sobre.php' ? 'class="active"' : '' ?>>Sobre</a></li>
                    <li><a href="imoveis.php" <?= basename($_SERVER['PHP_SELF']) == 'imoveis.php' ? 'class="active"' : '' ?>>Imóveis</a></li>
                    <li><a href="contato.php" <?= basename($_SERVER['PHP_SELF']) == 'contato.php' ? 'class="active"' : '' ?>>Contato</a></li>
                </ul>
            </nav>
            <div class="mobile-menu">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </header>