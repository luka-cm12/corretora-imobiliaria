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

<!-- Carrossel no estilo Attuale -->
<section class="home-slider">
    <div class="container-full">
        <div class="slick-custom-wrapper">
            <div class="slick-main slick-initialized slick-slider slick-dotted">
                <div class="slick-list draggable">
                    <div class="slick-track">
                        <?php if ($carousel_imoveis && count($carousel_imoveis) > 0): ?>
                            <?php $slide_index = 0; ?>
                            <?php foreach ($carousel_imoveis as $imovel): 
                                $imagens = explode(',', $imovel['imagens']);
                                $firstImage = !empty($imagens) ? 'public/uploads/' . $imagens[0] : 'public/assets/images/68c74fbd67525.jpg';
                            ?>
                                <div class="slick-slide STARTED slick-animate-in" data-slick-index="<?= $slide_index ?>" aria-hidden="true" tabindex="-1" role="tabpanel">
                                    <a href="imovel-detalhes.php?id=<?= $imovel['id'] ?>" class="slick-main__banner" style="background-image: url('<?= $firstImage ?>');">
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
                                <?php $slide_index++; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Slide padrão caso não haja imóveis -->
                            <div class="slick-slide STARTED slick-animate-in slick-current slick-active" data-slick-index="0" aria-hidden="false" tabindex="-1" role="tabpanel">
                                <div class="slick-main__banner" style="background-image: url('public/assets/images/68c74fbd67525.jpg');">
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
                </div>

                <!-- Indicadores do carrossel -->
                <ul class="slick-dots" role="tablist">
                    <?php 
                    $totalSlides = is_array($carousel_imoveis) ? count($carousel_imoveis) : ($carousel_imoveis && $carousel_imoveis->num_rows ? $carousel_imoveis->num_rows : 1);
                    for ($i = 0; $i < $totalSlides; $i++): ?>
                        <li role="presentation" class="<?= $i === 0 ? 'slick-active' : '' ?>">
                            <button type="button" role="tab" aria-controls="slick-slide-control<?= $i ?>" aria-label="<?= $i + 1 ?> of <?= $totalSlides ?>" tabindex="<?= $i === 0 ? '0' : '-1' ?>">
                                <?= $i + 1 ?>
                            </button>
                        </li>
                    <?php endfor; ?>
                </ul>
            </div>
            
            <!-- Botões de navegação -->
            <button class="custom-arrows custom-arrows--prev slick-arrow" aria-disabled="false">
                <img src="public/assets/images/arrows/seta-esquerda.png" alt="Anterior" class="custom-arrows__img force-img-white">
            </button>
            <button class="custom-arrows custom-arrows--next slick-arrow" aria-disabled="false">
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
                    <option value="casa">Casa</option>
                    <option value="apartamento">Apartamento</option>
                    <option value="terreno">Terreno</option>
                    <option value="comercial">Comercial</option>
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
            <p>Somos uma corretora de imóveis comprometida em oferecer o melhor serviço para nossos clientes. Com anos de experiência no mercado, ajudamos você a encontrar o imóvel perfeito ou a vender seu patrimônio com segurança e tranquilidade.</p>
            <p>Nossa equipe é formada por profissionais qualificados que entendem as necessidades de cada cliente e trabalham para superar expectativas.</p>
            <a href="sobre.php" class="btn">Saiba Mais</a>
        </div>
        <div class="about-image">
            <img src="public/assets/images/about.jpg" alt="Sobre nós">
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
                <p>Alugue imóveis residenciais ou comerciais com toda segurança jurídica.</p>
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
<section class="contact-cta">
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
    $('.slick-main').slick({
        dots: true,
        arrows: true,
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
