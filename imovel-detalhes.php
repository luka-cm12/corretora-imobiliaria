<?php
require_once 'private/includes/db.php';

// Verificar se o ID do imóvel foi passado
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: imoveis.php');
    exit;
}

$imovel_id = intval($_GET['id']);

// Buscar os dados do imóvel
$imovel = db_query(
    "SELECT * FROM imoveis WHERE id = ?", 
    [$imovel_id]
);

if (!$imovel || $imovel->num_rows === 0) {
    header('Location: imoveis.php');
    exit;
}

$imovel = $imovel->fetch_assoc();
$imagens = explode(',', $imovel['imagens']);

// Formatar preço
$preco_formatado = 'R$ ' . number_format($imovel['preco'], 2, ',', '.');

// Buscar imóveis similares (mesmo tipo e cidade)
$similares = db_query(
    "SELECT id, titulo, preco, imagens FROM imoveis 
     WHERE tipo = ? AND cidade = ? AND id != ? 
     ORDER BY RAND() LIMIT 3",
    [$imovel['tipo'], $imovel['cidade'], $imovel_id]
);

// Tipos de imóvel para exibição amigável
$tipos = [
    'casa' => 'Casa',
    'apartamento' => 'Apartamento',
    'terreno' => 'Terreno',
    'comercial' => 'Comercial'
];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($imovel['titulo']) ?> | Corretora Base</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/lightbox.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-whatsapp"></i></a>
            </div>
        </div>
    </div>

    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="logo">
                <h1>Corretora<span>Base</span></h1>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="sobre.php">Sobre</a></li>
                    <li><a href="imoveis.php">Imóveis</a></li>
                    <li><a href="contato.php">Contato</a></li>
                </ul>
            </nav>
            <div class="mobile-menu">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </header>

    <!-- Breadcrumb -->
    <section class="breadcrumb">
        <div class="container">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="imoveis.php">Imóveis</a></li>
                <li><?= htmlspecialchars($imovel['titulo']) ?></li>
            </ul>
        </div>
    </section>

    <!-- Property Details -->
    <section class="property-details">
        <div class="container">
            <div class="property-header">
                <h1><?= htmlspecialchars($imovel['titulo']) ?></h1>
                <p class="property-address">
                    <i class="fas fa-map-marker-alt"></i> 
                    <?= htmlspecialchars($imovel['endereco']) ?>, 
                    <?= htmlspecialchars($imovel['bairro']) ?> - 
                    <?= htmlspecialchars($imovel['cidade']) ?>
                </p>
                <p class="property-price"><?= $preco_formatado ?></p>
                
                <?php if ($imovel['destaque']): ?>
                    <span class="property-badge">Destaque</span>
                <?php endif; ?>
            </div>

            <!-- Gallery -->
            <div class="property-gallery">
                <div class="main-image">
                    <a href="uploads/<?= htmlspecialchars($imagens[0]) ?>" data-lightbox="property-images">
                        <img src="uploads/<?= htmlspecialchars($imagens[0]) ?>" alt="<?= htmlspecialchars($imovel['titulo']) ?>">
                    </a>
                </div>
                
                <?php if (count($imagens) > 1): ?>
                    <div class="thumbnail-grid">
                        <?php foreach (array_slice($imagens, 1) as $imagem): ?>
                            <div class="thumbnail">
                                <a href="uploads/<?= htmlspecialchars($imagem) ?>" data-lightbox="property-images">
                                    <img src="uploads/<?= htmlspecialchars($imagem) ?>" alt="<?= htmlspecialchars($imovel['titulo']) ?>">
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Details and Features -->
            <div class="property-content">
                <div class="details-section">
                    <h2>Detalhes do Imóvel</h2>
                    
                    <div class="details-grid">
                        <div class="detail-item">
                            <i class="fas fa-home"></i>
                            <span>Tipo</span>
                            <strong><?= $tipos[$imovel['tipo']] ?? ucfirst($imovel['tipo']) ?></strong>
                        </div>
                        
                        <?php if ($imovel['area'] > 0): ?>
                        <div class="detail-item">
                            <i class="fas fa-vector-square"></i>
                            <span>Área</span>
                            <strong><?= $imovel['area'] ?> m²</strong>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($imovel['quartos'] > 0): ?>
                        <div class="detail-item">
                            <i class="fas fa-bed"></i>
                            <span>Quartos</span>
                            <strong><?= $imovel['quartos'] ?></strong>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($imovel['banheiros'] > 0): ?>
                        <div class="detail-item">
                            <i class="fas fa-bath"></i>
                            <span>Banheiros</span>
                            <strong><?= $imovel['banheiros'] ?></strong>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($imovel['garagem'] > 0): ?>
                        <div class="detail-item">
                            <i class="fas fa-car"></i>
                            <span>Vagas</span>
                            <strong><?= $imovel['garagem'] ?></strong>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Description -->
                <div class="description-section">
                    <h2>Descrição</h2>
                    <p><?= nl2br(htmlspecialchars($imovel['descricao'])) ?></p>
                </div>

                <!-- Contact Form -->
                <div class="contact-section">
                    <h2>Interessado neste imóvel?</h2>
                    <form action="processa-contato.php" method="post" class="property-contact-form">
                        <input type="hidden" name="imovel_id" value="<?= $imovel_id ?>">
                        <input type="hidden" name="imovel_titulo" value="<?= htmlspecialchars($imovel['titulo']) ?>">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nome">Nome *</label>
                                <input type="text" id="nome" name="nome" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email *</label>
                                <input type="email" id="email" name="email" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="telefone">Telefone *</label>
                                <input type="tel" id="telefone" name="telefone" required>
                            </div>
                            <div class="form-group">
                                <label for="interesse">Interesse</label>
                                <select id="interesse" name="interesse">
                                    <option value="visita">Agendar visita</option>
                                    <option value="informacoes">Mais informações</option>
                                    <option value="financiamento">Financiamento</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="mensagem">Mensagem</label>
                            <textarea id="mensagem" name="mensagem" rows="4"></textarea>
                        </div>
                        
                        <button type="submit" class="btn">Enviar Mensagem</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Similar Properties -->
    <?php if ($similares && $similares->num_rows > 0): ?>
    <section class="similar-properties">
        <div class="container">
            <h2 class="section-title">Imóveis Similares</h2>
            
            <div class="properties-grid">
                <?php while ($similar = $similares->fetch_assoc()): 
                    $similar_imagens = explode(',', $similar['imagens']);
                    $similar_preco = 'R$ ' . number_format($similar['preco'], 2, ',', '.');
                ?>
                    <div class="property-card">
                        <img src="uploads/<?= htmlspecialchars($similar_imagens[0]) ?>" alt="<?= htmlspecialchars($similar['titulo']) ?>">
                        
                        <div class="property-info">
                            <h3><?= htmlspecialchars($similar['titulo']) ?></h3>
                            <p class="property-price"><?= $similar_preco ?></p>
                            <a href="imovel-detalhes.php?id=<?= $similar['id'] ?>" class="btn">Ver Detalhes</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h3>Corretora Base</h3>
                    <p>Oferecendo soluções imobiliárias completas com transparência e profissionalismo.</p>
                    <div class="footer-social">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
                <div class="footer-col">
                    <h3>Links Rápidos</h3>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="sobre.php">Sobre</a></li>
                        <li><a href="imoveis.php">Imóveis</a></li>
                        <li><a href="contato.php">Contato</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h3>Contato</h3>
                    <ul class="contact-info">
                        <li><i class="fas fa-map-marker-alt"></i> Rua Exemplo, 123 - Centro</li>
                        <li><i class="fas fa-phone"></i> (XX) XXXX-XXXX</li>
                        <li><i class="fas fa-envelope"></i> contato@corretorabase.com.br</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <p>&copy; 2023 Corretora Base. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="assets/js/lightbox-plus-jquery.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        // Inicializar lightbox
        lightbox.option({
            'resizeDuration': 200,
            'wrapAround': true,
            'showImageNumberLabel': true
        });

        // Máscara para telefone
        document.getElementById('telefone').addEventListener('input', function (e) {
            var x = e.target.value.replace(/\D/g, '').match(/(\d{0,2})(\d{0,5})(\d{0,4})/);
            e.target.value = !x[2] ? x[1] : '(' + x[1] + ') ' + x[2] + (x[3] ? '-' + x[3] : '');
        });
    </script>
</body>
</html>