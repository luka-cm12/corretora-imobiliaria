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

$imovel_result = db_query("SELECT * FROM imoveis WHERE id = ?", [$imovel_id]);

// Verifica se encontrou algum resultado
if (!is_array($imovel_result) || count($imovel_result) === 0) {
    header('Location: imoveis.php');
    exit;
}

// Pega o primeiro resultado
$imovel = $imovel_result[0];
$imagens = array_values(array_filter(explode(',', $imovel['imagens'])));
// Base de imagens para a galeria (uploads por padrão, fallback para imagem padrão)
if (empty($imagens)) {
    $imagens = ['default-property.jpg'];
    $gallery_base = 'public/assets/images/';
} else {
    $gallery_base = 'public/uploads/';
}


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
    'casa_condominio' => 'Casa em Condomínio',
    'apartamento' => 'Apartamento',
    'apartamento_mobiliado' => 'Apartamento Mobiliado',
    'sobrado' => 'Sobrado',
    'chacara' => 'Chácara',
    'semi_mobiliado' => 'Semi Mobiliado',
    'terreno' => 'Terreno',
    'loft' => 'Loft',
    'comercial' => 'Comercial'
];

// Características principais: mapa de chave => [rótulo, ícone FontAwesome]
$caracteristicas_lista = [
    // Quartos e Suítes
    'suite'             => ['Suíte', 'fa-bed'],
    'closet'            => ['Closet', 'fa-tshirt'],
    'ar_condicionado'   => ['Ar condicionado', 'fa-snowflake'],
    'armarios_embutidos'=> ['Armários embutidos', 'fa-warehouse'],
    'suite_master'      => ['Suíte master', 'fa-crown'],
    'varanda_suite'     => ['Varanda na suíte', 'fa-door-open'],
    
    // Banheiros e Bem-estar
    'hidromassagem'     => ['Hidromassagem', 'fa-spa'],
    'agua_aquecida'     => ['Água aquecida', 'fa-thermometer-half'],
    'gas_central'       => ['Gás central', 'fa-fire-burner'],
    'banheira'          => ['Banheira', 'fa-bath'],
    'box_blindex'       => ['Box blindex', 'fa-shower'],
    'sauna'             => ['Sauna', 'fa-hot-tub'],
    
    // Áreas Sociais
    'sala_de_estar'     => ['Sala de estar', 'fa-couch'],
    'varanda'           => ['Varanda', 'fa-building'],
    'sacada'            => ['Sacada', 'fa-stairs'],
    'sacada_gourmet'    => ['Sacada gourmet', 'fa-utensils'],
    'area_gourmet'      => ['Área gourmet', 'fa-utensils'],
    'churrasqueira'     => ['Churrasqueira', 'fa-fire'],
    'salao_de_festas'   => ['Salão de festas', 'fa-glass-cheers'],
    'quiosque'          => ['Quiosque', 'fa-umbrella-beach'],
    'jardim'            => ['Jardim', 'fa-seedling'],
    'terraço'           => ['Terraço', 'fa-building-columns'],
    
    // Lazer e Recreação
    'piscina'           => ['Piscina', 'fa-water-ladder'],
    'academia'          => ['Academia', 'fa-dumbbell'],
    'quintal'           => ['Quintal', 'fa-tree'],
    'playground'        => ['Playground', 'fa-child'],
    'quadra_esportiva'  => ['Quadra esportiva', 'fa-futbol'],
    'sala_jogos'        => ['Sala de jogos', 'fa-gamepad'],
    
    // Funcionalidades
    'elevador'          => ['Elevador', 'fa-elevator'],
    'portaria_24h'      => ['Portaria', 'fa-shield-halved'],
    'mobiliado'         => ['Mobiliado', 'fa-couch'],
    'pet_friendly'      => ['Pet friendly', 'fa-paw'],
    'lavanderia'        => ['Lavanderia', 'fa-soap'],
    'lareira'           => ['Lareira', 'fa-fire-flame-curved'],
    'interfone'         => ['Interfone', 'fa-phone'],
    'alarme'            => ['Sistema de alarme', 'fa-bell'],
    'garagem_coberta'   => ['Garagem coberta', 'fa-car-garage'],
];

// Decodifica características salvas (JSON) se existirem
$caracteristicas_selecionadas = [];
if (isset($imovel['caracteristicas']) && $imovel['caracteristicas'] !== null && $imovel['caracteristicas'] !== '') {
    $raw = $imovel['caracteristicas'];
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        $caracteristicas_selecionadas = $decoded;
    } else if (is_string($decoded) && strlen($decoded) > 0) {
        // JSON duplamente codificado (string contendo JSON)
        $decoded2 = json_decode($decoded, true);
        if (is_array($decoded2)) {
            $caracteristicas_selecionadas = $decoded2;
        }
    }
    if (empty($caracteristicas_selecionadas)) {
        // Fallback: tratar como lista separada por vírgulas
        $parts = array_filter(array_map('trim', explode(',', (string)$raw)));
        $caracteristicas_selecionadas = $parts;
    }
}

// Incluir o header
include 'private/includes/header.php';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($imovel['titulo']) ?> | Corretora Base</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="public/assets/css/lightbox.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>


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
                    <?= htmlspecialchars($imovel['endereco']) ?><?= !empty($imovel['endereco']) ? ',' : '' ?> 
                    <?= htmlspecialchars($imovel['bairro']) ?> - 
                    <?= htmlspecialchars($imovel['cidade']) ?>
                    <?php if (!empty($imovel['cep'])): ?>
                        <span style="margin-left:6px; color:#666;">CEP: <?= htmlspecialchars(preg_replace('/(\d{5})(\d{3})/','$1-$2', preg_replace('/\D+/','',$imovel['cep']))) ?></span>
                    <?php endif; ?>
                </p>
                <p class="property-price"><?= $preco_formatado ?></p>
                
                <?php if ($imovel['destaque']): ?>
                    <span class="property-badge">Destaque</span>
                <?php endif; ?>
            </div>

            <!-- Gallery -->
            <div class="property-gallery">
                <div class="main-image">
                    <?php if (count($imagens) > 1): ?>
                    <button class="gallery-nav prev" type="button" aria-label="Imagem anterior">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <?php endif; ?>

                    <a id="mainLightboxLink" href="<?= $gallery_base . htmlspecialchars($imagens[0]) ?>" data-lightbox="property-images">
                        <img id="mainImage" src="<?= $gallery_base . htmlspecialchars($imagens[0]) ?>" alt="<?= htmlspecialchars($imovel['titulo']) ?>">
                    </a>
                    <?php if (count($imagens) > 1): ?>
                    <button class="gallery-nav next" type="button" aria-label="Próxima imagem">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                    <?php endif; ?>
                </div>
                
                <?php if (count($imagens) > 1): ?>
                    <div class="thumbnail-grid">
                        <?php foreach ($imagens as $idx => $imagem): ?>
                            <div class="thumbnail <?= $idx === 0 ? 'active' : '' ?>" data-index="<?= $idx ?>">
                                <a href="<?= $gallery_base . htmlspecialchars($imagem) ?>" data-lightbox="property-images">
                                    <img src="<?= $gallery_base . htmlspecialchars($imagem) ?>" alt="<?= htmlspecialchars($imovel['titulo']) ?>">
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
                        
                        <?php if (!empty($imovel['posicao_solar'])): ?>
                        <div class="detail-item">
                            <?php 
                            $icones_posicao = [
                                'norte' => 'fa-compass',
                                'sul' => 'fa-compass', 
                                'leste' => 'fa-sun',
                                'oeste' => 'fa-moon'
                            ];
                            $posicao_nome = [
                                'norte' => 'Norte',
                                'sul' => 'Sul',
                                'leste' => 'Leste', 
                                'oeste' => 'Oeste'
                            ];
                            ?>
                            <i class="fas <?= $icones_posicao[$imovel['posicao_solar']] ?? 'fa-sun' ?>"></i>
                            <span>Posição Solar</span>
                            <strong><?= $posicao_nome[$imovel['posicao_solar']] ?? ucfirst($imovel['posicao_solar']) ?></strong>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Description -->
                <div class="description-section">
                    <h2>Descrição</h2>
                    <p><?= nl2br(htmlspecialchars($imovel['descricao'])) ?></p>
                </div>

                <?php if (!empty($caracteristicas_selecionadas)): ?>
                <div class="features-section">
                    <h2>Características</h2>
                    <ul class="features-list">
                        <?php foreach ($caracteristicas_selecionadas as $key): 
                            $keyStr = (string)$key;
                            $info = $caracteristicas_lista[$keyStr] ?? null;
                            $label = $info[0] ?? ucfirst(str_replace(['_', '-'], ' ', $keyStr));
                            $icon  = $info[1] ?? 'fa-circle-check';
                        ?>
                            <li><i class="fas <?= htmlspecialchars($icon) ?>"></i> <?= htmlspecialchars($label) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <!-- Contact Form -->
                <div class="contact-section">
                    <h2>Interessado neste imóvel?</h2>
                    <form action="processa-contato.php" method="post" class="property-contact-form">
                        <input type="hidden" name="imovel_id" value="<?= $imovel_id ?>">
                        <input type="hidden" name="imovel_titulo" value="<?= htmlspecialchars($imovel['titulo']) ?>">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <!-- Honeypot: campo invisível para bots -->
                        <div style="position:absolute;left:-9999px;">
                            <label for="website">Não preencha este campo</label>
                            <input type="text" id="website" name="website" autocomplete="off">
                        </div>
                        
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
                                <label for="assunto">Assunto</label>
                                <select id="assunto" name="assunto">
                                    <option value="Visita">Agendar visita</option>
                                    <option value="Informações">Mais informações</option>
                                    <option value="Financiamento">Financiamento</option>
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
    <?php if ($similares && count($similares) > 0): ?>
    <section class="similar-properties">
        <div class="container">
            <h2 class="section-title">Imóveis Similares</h2>
            
            <div class="properties-grid">
                <?php foreach ($similares as $similar): 
                    $similar_imagens = explode(',', $similar['imagens']);
                    $similar_preco = 'R$ ' . number_format($similar['preco'], 2, ',', '.');
                ?>
                    <div class="property-card">
                        <?php
                            $similar_imagens = array_values(array_filter($similar_imagens));
                            $similar_base = !empty($similar_imagens) ? 'public/uploads/' : 'public/assets/images/';
                            $similar_first = !empty($similar_imagens) ? $similar_imagens[0] : 'default-property.jpg';
                        ?>
                        <img src="<?= $similar_base . htmlspecialchars($similar_first) ?>" alt="<?= htmlspecialchars($similar['titulo']) ?>">
                        
                        <div class="property-info">
                            <h3><?= htmlspecialchars($similar['titulo']) ?></h3>
                            <p class="property-price"><?= $similar_preco ?></p>
                            <a href="imovel-detalhes.php?id=<?= $similar['id'] ?>" class="btn">Ver Detalhes</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

<?php
// Incluir o footer
include 'private/includes/footer.php';
?>

    <!-- Scripts -->
    <script src="public/assets/js/lightbox-plus-jquery.min.js"></script>
    <script src="public/assets/js/main.js"></script>
    <style>
        .property-gallery { position: relative; }
        .property-gallery .main-image { position: relative; }
        .gallery-nav { position:absolute; top:50%; transform:translateY(-50%); background:rgba(0,0,0,.5); color:#fff; border:none; width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; z-index:2; }
        .gallery-nav.prev { left:10px; }
        .gallery-nav.next { right:10px; }
        .thumbnail-grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(80px, 1fr)); gap:10px; margin-top:12px; }
        .thumbnail { border:2px solid transparent; border-radius:6px; overflow:hidden; cursor:pointer; }
        .thumbnail.active { border-color:#0aa; }
        .thumbnail img { width:100%; height:70px; object-fit:cover; display:block; }
        .features-section { margin-top: 24px; }
        .features-list { list-style: none; padding: 0; margin: 8px 0 0; display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 8px 16px; }
        .features-list li { display: flex; align-items: center; gap: 8px; color: #444; }
        .features-list i { color: #0aa; width: 18px; text-align: center; }
    </style>
    <script>
        // Inicializar lightbox (opcional)
        if (window.lightbox && typeof lightbox.option === 'function') {
            lightbox.option({
                'resizeDuration': 200,
                'wrapAround': true,
                'showImageNumberLabel': true
            });
        }

        // Máscara para telefone
        document.getElementById('telefone').addEventListener('input', function (e) {
            var x = e.target.value.replace(/\D/g, '').match(/(\d{0,2})(\d{0,5})(\d{0,4})/);
            e.target.value = !x[2] ? x[1] : '(' + x[1] + ') ' + x[2] + (x[3] ? '-' + x[3] : '');
        });

        // Carousel simples com setas, cliques em thumbnails, teclado e swipe
        (function(){
            const images = <?= json_encode($imagens) ?>;
            const base = <?= json_encode($gallery_base) ?>;
            if (!images || images.length === 0) return;

            let current = 0;
            const mainImg = document.getElementById('mainImage');
            const mainLink = document.getElementById('mainLightboxLink');
            const thumbs = Array.from(document.querySelectorAll('.thumbnail'));
            const prevBtn = document.querySelector('.gallery-nav.prev');
            const nextBtn = document.querySelector('.gallery-nav.next');

            function setActive(index){
                current = (index + images.length) % images.length;
                const src = base + images[current];
                mainImg.src = src;
                mainLink.href = src;
                thumbs.forEach((t,i)=> t.classList.toggle('active', i===current));
            }

            thumbs.forEach(t => {
                t.addEventListener('click', (e) => {
                    e.preventDefault();
                    const idx = parseInt(t.getAttribute('data-index'), 10);
                    setActive(idx);
                });
            });

            if (prevBtn) prevBtn.addEventListener('click', ()=> setActive(current-1));
            if (nextBtn) nextBtn.addEventListener('click', ()=> setActive(current+1));

            // Teclado
            document.addEventListener('keydown', (e)=>{
                if (e.key === 'ArrowLeft') setActive(current-1);
                if (e.key === 'ArrowRight') setActive(current+1);
            });

            // Swipe (touch)
            let startX = 0;
            mainImg.addEventListener('touchstart', (e)=>{ startX = e.changedTouches[0].clientX; }, {passive:true});
            mainImg.addEventListener('touchend', (e)=>{
                const dx = e.changedTouches[0].clientX - startX;
                if (Math.abs(dx) > 30) {
                    if (dx > 0) setActive(current-1); else setActive(current+1);
                }
            }, {passive:true});
        })();
    
        // Inicializa estado
        // setActive(0) já é estado inicial via markup
    </script>
</body>
</html>