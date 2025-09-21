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
$equipe = [
    [
        'nome' => 'João Silva',
        'cargo' => 'Corretor Associado',
        'foto' => 'assets/images/team1.jpg',
        'telefone' => '(XX) XXXX-XXXX',
        'email' => 'joao@corretorabase.com.br'
    ],
    [
        'nome' => 'Maria Santos',
        'cargo' => 'Corretora Sênior',
        'foto' => 'assets/images/team2.jpg',
        'telefone' => '(XX) XXXX-XXXX',
        'email' => 'maria@corretorabase.com.br'
    ],
    [
        'nome' => 'Carlos Oliveira',
        'cargo' => 'Gerente Comercial',
        'foto' => 'assets/images/team3.jpg',
        'telefone' => '(XX) XXXX-XXXX',
        'email' => 'carlos@corretorabase.com.br'
    ]
];

// Buscar estatísticas (pode ser substituído por dados reais do BD)
$estatisticas = [
    'imoveis' => 250,
    'clientes' => 500,
    'anos' => 10,
    'premiacoes' => 5
];

// Incluir o header
include 'private/includes/header.php';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sobre Nós | Corretora Claudia Colombo</title>
    <link rel="stylesheet" href="public/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>



    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1>Sobre Nós</h1>
            <p>Conheça nossa história, missão e equipe especializada</p>
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
                <img src="public/assets/images/about.jpg" alt="Sobre a Corretora Base">
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
    <section class="team-section">
        <div class="container">
            <h2 class="section-title">Nossa Equipe</h2>
            <p class="section-subtitle">Profissionais qualificados para te atender</p>
            
            <div class="team-grid">
                <?php foreach ($equipe as $membro): ?>
                    <div class="team-member">
                        <div class="member-image">
                            <img src="<?= $membro['foto'] ?>" alt="<?= htmlspecialchars($membro['nome']) ?>">
                            <div class="member-social">
                                <a href="tel:<?= $membro['telefone'] ?>"><i class="fas fa-phone"></i></a>
                                <a href="mailto:<?= $membro['email'] ?>"><i class="fas fa-envelope"></i></a>
                                <a href="#"><i class="fab fa-whatsapp"></i></a>
                            </div>
                        </div>
                        <div class="member-info">
                            <h3><?= htmlspecialchars($membro['nome']) ?></h3>
                            <p><?= htmlspecialchars($membro['cargo']) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials-section">
        <div class="container">
            <h2 class="section-title">O Que Nossos Clientes Dizem</h2>
            
            <div class="testimonials-slider">
                <div class="testimonial">
                    <div class="testimonial-content">
                        <i class="fas fa-quote-left"></i>
                        <p>A Corretora Claudia Colombo foi fundamental para eu encontrar meu apartamento dos sonhos. Profissionais extremamente competentes e atenciosos.</p>
                    </div>
                    <div class="client-info">
                        <h4>Ana Paula Mendes</h4>
                        <p>Compradora de Apartamento</p>
                    </div>
                </div>
                
                <div class="testimonial">
                    <div class="testimonial-content">
                        <i class="fas fa-quote-left"></i>
                        <p>Vendi meu imóvel em tempo recorde e com ótimo valor de mercado. Recomendo a todos que buscam seriedade e resultados.</p>
                    </div>
                    <div class="client-info">
                        <h4>Roberto Almeida</h4>
                        <p>Vendedor de Casa</p>
                    </div>
                </div>
                
                <div class="testimonial">
                    <div class="testimonial-content">
                        <i class="fas fa-quote-left"></i>
                        <p>Atendimento personalizado e focado nas minhas necessidades. Encontraram exatamente o que eu procurava dentro do meu orçamento.</p>
                    </div>
                    <div class="client-info">
                        <h4>Fernanda Costa</h4>
                        <p>Locatária</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2>Pronto para encontrar seu imóvel ideal?</h2>
            <p>Entre em contato conosco e agende uma visita</p>
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
</body>
</html>