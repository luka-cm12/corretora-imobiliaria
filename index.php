<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Corretora Claudia | Imóveis de Qualidade</title>
    <link rel="stylesheet" href="public/assets/css/style.css">
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
                <img src="public/assets/images/" alt="Claudia Colombo - Corretora de Imóveis" class="logo-img">
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

    <!-- Hero Banner -->
    <section class="hero">
        <div class="hero-content">
            <h2>Encontre o imóvel dos seus sonhos</h2>
            <p>Oferecemos as melhores opções para você e sua família</p>
            <a href="imoveis.php" class="btn">Ver Imóveis</a>
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
                        <option value="cidade1">Cidade 1</option>
                        <option value="cidade2">Cidade 2</option>
                    </select>
                </div>
                <div class="form-group">
                    <select name="bairro">
                        <option value="">Todos os Bairros</option>
                        <option value="bairro1">Bairro 1</option>
                        <option value="bairro2">Bairro 2</option>
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
            <div class="properties-grid">
                <!-- PHP would loop through featured properties here -->
                <div class="property-card">
                    <div class="property-badge">Destaque</div>
                    <img src="assets/images/property1.jpg" alt="Imóvel 1">
                    <div class="property-info">
                        <h3>Apartamento Luxo</h3>
                        <p class="property-address"><i class="fas fa-map-marker-alt"></i> Bairro Nobre, Cidade</p>
                        <div class="property-details">
                            <span><i class="fas fa-bed"></i> 3</span>
                            <span><i class="fas fa-bath"></i> 2</span>
                            <span><i class="fas fa-car"></i> 2</span>
                            <span><i class="fas fa-vector-square"></i> 120m²</span>
                        </div>
                        <p class="property-price">R$ 850.000</p>
                        <a href="imovel-detalhes.php?id=1" class="btn">Ver Detalhes</a>
                    </div>
                </div>
                
                <!-- More property cards would go here -->
            </div>
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
                <img src="assets/images/about.jpg" alt="Sobre nós">
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
                    <h3>Locacao</h3>
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

    <!-- Testimonials -->
    <section class="testimonials">
        <div class="container">
            <h2 class="section-title">O que dizem nossos clientes</h2>
            <div class="testimonials-slider">
                <!-- Testimonial slides would go here -->
                <div class="testimonial">
                    <p>"A Corretora Claudia foi fundamental para eu encontrar meu apartamento dos sonhos. Profissionais extremamente competentes e atenciosos."</p>
                    <div class="client-info">
                        <h4>João Silva</h4>
                        <p>Comprador</p>
                    </div>
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

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h3>Corretora Claudia</h3>
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
                <p>&copy; 2023 Corretora Claudia. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- No final do <body> -->
    <script src="public/assets/js/main.js"></script>
    <script src="public/assets/js/lightbox.js"></script>
    <script src="public/assets/js/form-validation.js"></script>
    <script src="public/assets/js/mobile-menu.js"></script>
</body>
</html>