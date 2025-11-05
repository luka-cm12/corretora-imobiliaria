<?php
require_once 'private/includes/db.php';
require_once 'private/includes/functions.php';

// Buscar imóveis em destaque para o carrossel
$carousel_imoveis = db_query("
    SELECT * FROM imoveis 
    WHERE destaque = 1 
    ORDER BY created_at DESC 
    LIMIT 6
");

// Buscar totais para estatísticas
$total_imoveis = db_query("SELECT COUNT(*) as total FROM imoveis")[0]['total'];
$total_vendas  = db_query("SELECT COUNT(*) as total FROM imoveis WHERE tipo IN ('casa','apartamento','terreno')")[0]['total'];
$total_locacoes = db_query("SELECT COUNT(*) as total FROM imoveis WHERE tipo = 'comercial'")[0]['total'];

$cidades = db_query("SELECT DISTINCT cidade FROM imoveis ORDER BY cidade LIMIT 5");

// Definir variáveis para o header
$page_title = 'Corretora Claudia | Imóveis de Qualidade';
$meta_description = 'Encontre o imóvel dos seus sonhos com a Corretora Claudia. Oferecemos as melhores opções de casas, apartamentos e imóveis comerciais.';
$load_lightbox = true;
$load_slick = true; // Para carregar o Slick Carousel

// Incluir o header
include 'private/includes/header.php';
?>

<?php
// Carrossel: preferir imagens estáticas da pasta /public/assets/images/carrosel
$carousel_folder = 'public/assets/images/carrosel';
$carousel_static_images = [];
if (is_dir($carousel_folder)) {
    $paths = glob($carousel_folder . '/*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}', GLOB_BRACE) ?: [];
    foreach ($paths as $p) {
        if (is_file($p)) {
            // Normaliza separadores para URL e codifica espaços
            $p_url = str_replace('\\', '/', $p);
            $carousel_static_images[] = str_replace(' ', '%20', $p_url);
        }
    }
}
?>

<!-- Carrossel no estilo Attuale -->
<section class="home-slider">
    <div class="container-full">
        <div class="slick-custom-wrapper">
            <div class="slick-main">
                <?php if (!empty($carousel_static_images)): ?>
                    <?php foreach ($carousel_static_images as $imgUrl): ?>
                        <div>
                            <a href="imoveis.php" class="slick-main__banner" style="
                                display:block; min-height:420px;
                                background-image:url('<?= $imgUrl ?>');
                                background-size:cover; background-position:center; background-repeat:no-repeat;">
                                <div class="slick-main__text">
                                    <div class="slick-main__opacity"></div>
                                    <div class="slick-main__container">
                                        <div class="slick-main__flex-group">
                                            <h2 class="slick-main__title">Encontre o imóvel ideal</h2>
                                        </div>
                                        <span class="btn-custom btn-custom--dark">
                                            <div class="btn-custom__grey">
                                                <span class="btn-custom__grey-line btn-custom__grey-line--top-bottom"></span>
                                                <span class="btn-custom__grey-line btn-custom__grey-line--left"></span>
                                                <span class="btn-custom__grey-line btn-custom__grey-line--right"></span>
                                            </div>
                                            <div class="btn-custom__grey btn-custom__grey--green">
                                                <span class="btn-custom__grey-line btn-custom__grey-line--top-bottom"></span>
                                                <span class="btn-custom__grey-line btn-custom__grey-line--left"></span>
                                                <span class="btn-custom__grey-line btn-custom__grey-line--right"></span>
                                            </div>
                                            Ver Imóveis
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php elseif ($carousel_imoveis && count($carousel_imoveis) > 0): ?>
                    <?php foreach ($carousel_imoveis as $imovel): 
                        $imagens = array_values(array_filter(array_map('trim', explode(',', (string)$imovel['imagens']))));
                        $firstImage = !empty($imagens) ? 'public/uploads/' . $imagens[0] : 'public/assets/images/about.jpg';
                    ?>
                        <div>
                            <a href="imovel-detalhes.php?id=<?= $imovel['id'] ?>" class="slick-main__banner" style="
                                display:block; min-height:420px;
                                background-image:url('<?= $firstImage ?>');
                                background-size:cover; background-position:center; background-repeat:no-repeat;">
                                <div class="slick-main__text">
                                    <div class="slick-main__opacity"></div>
                                    <div class="slick-main__container">
                                        <div class="slick-main__flex-group">
                                            <?php if (!empty($imovel['titulo'])): ?>
                                                <h2 class="slick-main__title"><?= htmlspecialchars($imovel['titulo']) ?></h2>
                                            <?php endif; ?>
                                            <?php if (!empty($imovel['bairro'])): ?>
                                                <span class="slick-main__simple-text"><?= htmlspecialchars($imovel['bairro']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <span class="btn-custom btn-custom--dark">
                                            <div class="btn-custom__grey">
                                                <span class="btn-custom__grey-line btn-custom__grey-line--top-bottom"></span>
                                                <span class="btn-custom__grey-line btn-custom__grey-line--left"></span>
                                                <span class="btn-custom__grey-line btn-custom__grey-line--right"></span>
                                            </div>
                                            <div class="btn-custom__grey btn-custom__grey--green">
                                                <span class="btn-custom__grey-line btn-custom__grey-line--top-bottom"></span>
                                                <span class="btn-custom__grey-line btn-custom__grey-line--left"></span>
                                                <span class="btn-custom__grey-line btn-custom__grey-line--right"></span>
                                            </div>
                                            Saiba Mais
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div>
                        <div class="slick-main__banner" style="
                            display:block; min-height:420px;
                            background-image:url('public/assets/images/about.jpg');
                            background-size:cover; background-position:center; background-repeat:no-repeat;">
                            <div class="slick-main__text">
                                <div class="slick-main__container">
                                    <div class="slick-main__flex-group">
                                        <h2 class="slick-main__title">Encontre o imóvel dos seus sonhos</h2>
                                        <span class="slick-main__simple-text">Oferecemos as melhores opções para você e sua família</span>
                                    </div>
                                    <a href="imoveis.php" class="btn-custom btn-custom--dark">
                                        <div class="btn-custom__grey">
                                            <span class="btn-custom__grey-line btn-custom__grey-line--top-bottom"></span>
                                            <span class="btn-custom__grey-line btn-custom__grey-line--left"></span>
                                            <span class="btn-custom__grey-line btn-custom__grey-line--right"></span>
                                        </div>
                                        <div class="btn-custom__grey btn-custom__grey--green">
                                            <span class="btn-custom__grey-line btn-custom__grey-line--top-bottom"></span>
                                            <span class="btn-custom__grey-line btn-custom__grey-line--left"></span>
                                            <span class="btn-custom__grey-line btn-custom__grey-line--right"></span>
                                        </div>
                                        Ver Imóveis
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Botões de navegação (ligados ao Slick via prevArrow/nextArrow) -->
            <button class="custom-arrows custom-arrows--prev" aria-disabled="false">
                <img src="public/assets/images/arrows/seta-esquerda.png" alt="Anterior" class="custom-arrows__img force-img-white">
            </button>
            <button class="custom-arrows custom-arrows--next" aria-disabled="false">
                <img src="public/assets/images/arrows/seta-direita.png" alt="Próximo" class="custom-arrows__img force-img-white">
            </button>
        </div>
    </div>
</section>

<!-- Search Box -->
<section class="search-box">
    <div class="container">
        <form action="busca.php" method="get">
            <div class="form-group">
                <select name="tipo">
                    <option value="">Todos os Tipos</option>
                    <option value="casa">🏠 Casa</option>
                    <option value="casa_condominio">🏘️ Casa em Condomínio</option>
                    <option value="apartamento">🏢 Apartamento</option>
                    <option value="apartamento_mobiliado">🏢🛋️ Apartamento Mobiliado</option>
                    <option value="sobrado">🏘️ Sobrado</option>
                    <option value="chacara">🌾 Chácara</option>
                    <option value="semi_mobiliado">🛋️ Semi Mobiliado</option>
                    <option value="terreno">🌿 Terreno</option>
                    <option value="loft">🏙️ Loft</option>
                    <option value="comercial">🏪 Comercial</option>
                    <option value="pavilhao">🏭 Pavilhão</option>
                    <option value="fazenda">🚜 Fazenda</option>
                    <option value="laja_terrea">🏘️ Laja Térrea</option>
                    <option value="sala_area">📦 Sala Aérea</option>
                    <option value="area_terras">🌍 Área de Terras</option>
                    <option value="loteamento">🗺️ Loteamento</option>
                    <option value="condominio_fechado">🏛️ Condomínio Fechado</option>
                </select>
            </div>
            <div class="form-group">
                <select name="finalidade">
                    <option value="">Venda ou Locação</option>
                    <option value="venda">💰 Venda</option>
                    <option value="locacao">🏠 Locação</option>
                </select>
            </div>
            <div class="form-group">
                <select name="cidade">
                    <option value="">Todas as Cidades</option>
                    <?php foreach ($cidades as $cidade): ?>
                        <option value="<?= htmlspecialchars($cidade['cidade']) ?>"><?= htmlspecialchars($cidade['cidade']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <select name="preco">
                    <option value="">Faixa de Preço</option>
                    <option value="1">Até R$ 200.000</option>
                    <option value="2">R$ 200.000 - R$ 500.000</option>
                    <option value="3">Acima de R$ 500.000</option>
                </select>
            </div>
            <div class="form-group">
                <button type="submit" class="btn-search"><i class="fas fa-search"></i> Buscar</button>
            </div>
        </form>
    </div>
</section>

<!-- Featured Properties -->
<section class="featured-properties">
    <div class="container">
        <h2 class="section-title">Imóveis em Destaque</h2>
        
        <?php 
        // Buscar imóveis em destaque para a seção
        $destaques = db_query("SELECT * FROM imoveis WHERE destaque = 1 ORDER BY created_at DESC LIMIT 3");
        if ($destaques && count($destaques) > 0): 
        ?>
            <div class="properties-grid">
                <?php foreach ($destaques as $imovel): 
                    $imagens = explode(',', $imovel['imagens']);
                    $firstImage = !empty($imagens) ? 'public/uploads/' . $imagens[0] : 'public/assets/images/default-property.jpg';
                    $preco_formatado = formatar_preco($imovel['preco']);
                ?>
                    <div class="property-card">
                        <div class="property-badge">Destaque</div>
                        <a href="imovel-detalhes.php?id=<?= $imovel['id'] ?>">
                            <img src="<?= $firstImage ?>" alt="<?= htmlspecialchars($imovel['titulo']) ?>">
                        </a>
                        <div class="property-info">
                            <h3><a href="imovel-detalhes.php?id=<?= $imovel['id'] ?>"><?= htmlspecialchars($imovel['titulo']) ?></a></h3>
                            <p class="property-address"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($imovel['bairro']) ?>, <?= htmlspecialchars($imovel['cidade']) ?></p>
                            <div class="property-details">
                                <?php if ($imovel['quartos'] > 0): ?>
                                    <span><i class="fas fa-bed"></i> <?= $imovel['quartos'] ?></span>
                                <?php endif; ?>
                                <?php if ($imovel['banheiros'] > 0): ?>
                                    <span><i class="fas fa-bath"></i> <?= $imovel['banheiros'] ?></span>
                                <?php endif; ?>
                                <?php if ($imovel['garagem'] > 0): ?>
                                    <span><i class="fas fa-car"></i> <?= $imovel['garagem'] ?></span>
                                <?php endif; ?>
                                <?php if ($imovel['area'] > 0): ?>
                                    <span><i class="fas fa-vector-square"></i> <?= $imovel['area'] ?>m²</span>
                                <?php endif; ?>
                            </div>
                            <p class="property-price"><?= $preco_formatado ?></p>
                            <a href="imovel-detalhes.php?id=<?= $imovel['id'] ?>" class="btn">Ver Detalhes</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-properties">
                <p>Nenhum imóvel em destaque no momento.</p>
            </div>
        <?php endif; ?>
        
        <div class="view-all">
            <a href="imoveis.php" class="btn">Ver Todos os Imóveis</a>
        </div>
    </div>
</section>

<!-- Últimos Cadastros -->
<section class="latest-properties">
    <div class="container">
        <h2 class="section-title">Últimos Cadastros</h2>

        <?php 
        // Buscar os últimos imóveis cadastrados
        $ultimos = db_query("SELECT * FROM imoveis ORDER BY created_at DESC LIMIT 6");

        if ($ultimos && count($ultimos) > 0): ?>
            <div class="properties-grid">
                <?php foreach ($ultimos as $imovel): 
                    $imagens = explode(',', $imovel['imagens']);
                    $firstImage = !empty($imagens[0]) ? 'public/uploads/' . $imagens[0] : 'public/assets/images/default-property.jpg';
                    $preco_formatado = formatar_preco($imovel['preco']);
                ?>
                    <div class="property-card">
                        <a href="imovel-detalhes.php?id=<?= $imovel['id'] ?>">
                            <img src="<?= $firstImage ?>" alt="<?= htmlspecialchars($imovel['titulo']) ?>">
                        </a>
                        <div class="property-info">
                            <h3>
                                <a href="imovel-detalhes.php?id=<?= $imovel['id'] ?>">
                                    <?= htmlspecialchars($imovel['titulo']) ?>
                                </a>
                            </h3>
                            <p class="property-address">
                                <i class="fas fa-map-marker-alt"></i> 
                                <?= htmlspecialchars($imovel['bairro']) ?>, <?= htmlspecialchars($imovel['cidade']) ?>
                            </p>
                            <div class="property-details">
                                <?php if ($imovel['quartos'] > 0): ?>
                                    <span><i class="fas fa-bed"></i> <?= $imovel['quartos'] ?></span>
                                <?php endif; ?>
                                <?php if ($imovel['banheiros'] > 0): ?>
                                    <span><i class="fas fa-bath"></i> <?= $imovel['banheiros'] ?></span>
                                <?php endif; ?>
                                <?php if ($imovel['garagem'] > 0): ?>
                                    <span><i class="fas fa-car"></i> <?= $imovel['garagem'] ?></span>
                                <?php endif; ?>
                                <?php if ($imovel['area'] > 0): ?>
                                    <span><i class="fas fa-vector-square"></i> <?= $imovel['area'] ?>m²</span>
                                <?php endif; ?>
                            </div>
                            <p class="property-price"><?= $preco_formatado ?></p>
                            <a href="imovel-detalhes.php?id=<?= $imovel['id'] ?>" class="btn">Ver Detalhes</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-properties">
                <p>Nenhum imóvel cadastrado recentemente.</p>
            </div>
        <?php endif; ?>

        <div class="view-all">
            <a href="imoveis.php" class="btn">Ver Todos os Imóveis</a>
        </div>
    </div>
</section>


<!-- About Section -->
<section class="about-section">
    <div class="container">
        <div class="about-content">
            <h2 class="section-title">Sobre a Corretora Claudia</h2>
            <p>Há 20 anos oferecendo consultoria imobiliária para seu melhor negócio. Com anos de experiência no mercado, ajudamos você a vender ou encontrar o imóvel ideal, com segurança, tranquilidade, transparência e comprometimento.</p>
            <p>Buscando entender as necessidades de cada cliente e trabalhar para superar suas expectativas.</p>
            <!--<a href="sobre.php" class="btn">Saiba Mais</a> -->
        </div>
        <div class="about-image">
            <img src="public/assets/images/claudia/claudiaCO.jpeg" alt="Claudia Colombo - Corretora">
        </div>
    </div>
</section>

<!-- Services -->
<section class="services">
    <div class="container">
        <h2 class="section-title">Nossos Serviços</h2>
        <div class="services-grid">
            <div class="service-card">
                <i class="fas fa-home"></i>
                <h3>Compra e Venda</h3>
                <p>Encontre o imóvel perfeito ou venda seu patrimônio com a melhor assessoria.</p>
            </div>
            <div class="service-card">
                <i class="fas fa-file-signature"></i>
                <h3>Locação</h3>
                <p>Alugue imóveis comerciais com toda segurança jurídica.</p>
            </div>
            <div class="service-card">
                <i class="fas fa-hand-holding-usd"></i>
                <h3>Avaliações</h3>
                <p>Avaliação profissional do seu imóvel com metodologia reconhecida.</p>
            </div>
        </div>
    </div>
</section>

<!-- Contact CTA -->
<?php
// Define a imagem da faixa de contato: preferimos um nome sem espaços (cta-bg.jpg).
$__cta_candidates = [
    'public/assets/images/contato.jpg',
    'public/assets/images/contato.jpg',
    'public/assets/images/contato.jpg',
];
$__cta_img_url = 'public/assets/images/contato.jpg';
foreach ($__cta_candidates as $__candidate) {
    if (file_exists($__candidate)) { $__cta_img_url = str_replace(' ', '%20', $__candidate); break; }
}
?>
<section class="contact-cta" style="
    min-height:320px;
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 50%, #2c3e50 100%);
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    position: relative;
">
    <div class="container">
        <h2>Pronto para encontrar seu imóvel ideal?</h2>
        <p>Entre em contato conosco e agende uma visita</p>
        <a href="contato.php" class="btn">Fale Conosco</a>
    </div>
</section>

<?php
// Incluir o footer
include 'private/includes/footer.php';
?>

<!-- Scripts do Slick Carousel -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css"/>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>

<script>
// Inicialização do Slick Carousel
$(document).ready(function(){
    var $slider = $('.slick-main');
    if ($slider.hasClass('slick-initialized')) {
        $slider.slick('unslick');
    }
    $slider.slick({
        dots: true,
        arrows: true,
        prevArrow: $('.custom-arrows--prev'),
        nextArrow: $('.custom-arrows--next'),
        infinite: true,
        speed: 900,
        slidesToShow: 1,
        slidesToScroll: 1,
        autoplay: true,
        autoplaySpeed: 4500,
        fade: true,
        cssEase: 'cubic-bezier(.4,2.3,.3,1)'
    });
});
</script>
