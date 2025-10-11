<?php
require_once 'private/includes/db.php';

// Buscar dados da corretora (exemplo com dados estáticos, pode ser adaptado para BD)
$sobre_nos = [
    'titulo' => 'Sobre a Corretora Claudia Colombo',
    'descricao' => 'Somos uma corretora de imóveis comprometida em oferecer o melhor serviço para nossos clientes. Com anos de experiência no mercado, ajudamos você a encontrar o imóvel perfeito ou a vender seu patrimônio com segurança e tranquilidade.',
    'missao' => 'Proporcionar soluções imobiliárias com excelência, transparência e ética, superando as expectativas de nossos clientes.',
    'visao' => 'Ser reconhecida como a melhor opção em serviços imobiliários, através de um atendimento personalizado e resultados excepcionais.',
    'valores' => ['Ética', 'Transparência', 'Comprometimento', 'Excelência', 'Inovação']
];

// Buscar equipe (exemplo com dados estáticos, pode ser adaptado para BD)

// Buscar estatísticas (pode ser substituído por dados reais do BD)
$estatisticas = [
    'imoveis' => 250,
    'clientes' => 500,
    'anos' => 10,
    'premiacoes' => 5
];

// Metas para o header
$page_title = 'Sobre Nós | Corretora Claudia Colombo';
$meta_description = 'Conheça nossa história, missão, visão, valores e equipe especializada.';

// Incluir o header padrão do site
include 'private/includes/header.php';
?>



    <!-- Page Header com imagem de fundo -->
    <?php
    // Seleção da imagem de fundo, priorizando nome sem espaços
    $__hero_candidates = [
        'public/assets/images/cta-bg.jpg',
        'public/assets/images/contato.jpg',
        'public/assets/images/about.jpg'
    ];
    $__hero_img_url = 'public/assets/images/about.jpg';
    foreach ($__hero_candidates as $__candidate) {
        if (file_exists($__candidate)) { $__hero_img_url = str_replace(' ', '%20', $__candidate); break; }
    }
    ?>
    <section class="page-header" style="
        min-height: 260px;
        background-image: linear-gradient(rgba(0,0,0,0.45), rgba(0,0,0,0.45)), url('<?= $__hero_img_url ?>');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        background-attachment: fixed;
        display: flex; align-items: center;
    ">
        <div class="container">
            <h1 style="color:#fff; margin-bottom:8px;">Sobre Nós</h1>
            <p style="color:#f5f5f5;">Conheça nossa história, missão e equipe especializada</p>
        </div>
    </section>

    <!-- About Section -->
    <section class="about-section">
        <div class="container">
            <div class="about-content">
                <h2 class="section-title"><?= htmlspecialchars($sobre_nos['titulo']) ?></h2>
                <p><?= nl2br(htmlspecialchars($sobre_nos['descricao'])) ?></p>
                
                <div class="mission-vision">
                    <div class="mv-item">
                        <i class="fas fa-bullseye"></i>
                        <h3>Missão</h3>
                        <p><?= htmlspecialchars($sobre_nos['missao']) ?></p>
                    </div>
                    
                    <div class="mv-item">
                        <i class="fas fa-eye"></i>
                        <h3>Visão</h3>
                        <p><?= htmlspecialchars($sobre_nos['visao']) ?></p>
                    </div>
                </div>
            </div>
            
            <div class="about-image">
                <img src="public/assets/images/claudia/claudiaCO.jpeg" alt="Claudia Colombo - Corretora">
            </div>
        </div>
    </section>

    <!-- Values Section -->
    <section class="values-section">
        <div class="container">
            <h2 class="section-title">Nossos Valores</h2>
            <div class="values-grid">
                <?php foreach ($sobre_nos['valores'] as $valor): ?>
                    <div class="value-item">
                        <div class="value-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h3><?= htmlspecialchars($valor) ?></h3>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item">
                    <h3><span class="counter" data-target="<?= $estatisticas['imoveis'] ?>">0</span>+</h3>
                    <p>Imóveis Vendidos</p>
                </div>
                
                <div class="stat-item">
                    <h3><span class="counter" data-target="<?= $estatisticas['clientes'] ?>">0</span>+</h3>
                    <p>Clientes Satisfeitos</p>
                </div>
                
                <div class="stat-item">
                    <h3><span class="counter" data-target="<?= $estatisticas['anos'] ?>">0</span>+</h3>
                    <p>Anos no Mercado</p>
                </div>
                
                <div class="stat-item">
                    <h3><span class="counter" data-target="<?= $estatisticas['premiacoes'] ?>">0</span>+</h3>
                    <p>Prêmios e Reconhecimentos</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    

    <!-- CTA Section -->
    <?php
    // Define imagem do CTA com prioridade para nome sem espaços e fallback seguro
    $__cta_candidates = [
        'public/assets/images/contato.jpg',
        'public/assets/images/contato.jpg',
        'public/assets/images/contato.jpg'
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
            <h2 style="color:#fff;">Pronto para encontrar seu imóvel ideal?</h2>
            <p style="color:#f5f5f5;">Entre em contato conosco e agende uma visita</p>
            <a href="contato.php" class="btn">Fale Conosco</a>
        </div>
    </section>

    <?php   
    include 'private/includes/footer.php';
    ?>

    <script src="public/assets/js/main.js"></script>
    <script>
        // Animação de contagem para estatísticas
        const counters = document.querySelectorAll('.counter');
        const speed = 200;
        
        const animateCounters = () => {
            counters.forEach(counter => {
                const target = +counter.getAttribute('data-target');
                const count = +counter.innerText;
                const increment = target / speed;
                
                if (count < target) {
                    counter.innerText = Math.ceil(count + increment);
                    setTimeout(animateCounters, 1);
                } else {
                    counter.innerText = target + '+';
                }
            });
        };
        
        // Iniciar animação quando a seção estiver visível
        const statsSection = document.querySelector('.stats-section');
        const observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting) {
                animateCounters();
                observer.unobserve(statsSection);
            }
        });
        
        observer.observe(statsSection);
        
        // Slider de depoimentos (simplificado)
        let currentTestimonial = 0;
        const testimonials = document.querySelectorAll('.testimonial');
        
        function showTestimonial(index) {
            testimonials.forEach((testimonial, i) => {
                testimonial.style.display = i === index ? 'block' : 'none';
            });
        }
        
        function nextTestimonial() {
            currentTestimonial = (currentTestimonial + 1) % testimonials.length;
            showTestimonial(currentTestimonial);
        }
        
        // Mostrar primeiro depoimento e iniciar rotacionamento
        showTestimonial(0);
        setInterval(nextTestimonial, 5000);
    </script>
