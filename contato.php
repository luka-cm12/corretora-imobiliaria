<?php
require_once 'private/includes/db.php';

// Processar formulário de contato
$mensagem_enviada = false;
$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar e sanitizar os dados
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $assunto = trim($_POST['assunto'] ?? '');
    $mensagem = trim($_POST['mensagem'] ?? '');
    $imovel_interesse = trim($_POST['imovel_interesse'] ?? '');

    // Validações
    if (empty($nome)) {
        $erros['nome'] = 'Por favor, informe seu nome';
    }

    if (empty($email)) {
        $erros['email'] = 'Por favor, informe seu email';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros['email'] = 'Por favor, informe um email válido';
    }

    if (empty($mensagem)) {
        $erros['mensagem'] = 'Por favor, escreva sua mensagem';
    }

    // Se não houver erros, processar o formulário
    if (empty($erros)) {
        // Inserir no banco de dados
        $result = db_query(
            "INSERT INTO contatos (nome, email, telefone, assunto, mensagem, imovel_interesse) 
             VALUES (?, ?, ?, ?, ?, ?)",
            [$nome, $email, $telefone, $assunto, $mensagem, $imovel_interesse]
        );

        if ($result) {
            $mensagem_enviada = true;
            
            // Enviar email (opcional)
            $para = 'contato@corretorabase.com.br';
            $assunto_email = "Novo contato do site: $assunto";
            $mensagem_email = "
                <h2>Novo contato recebido</h2>
                <p><strong>Nome:</strong> $nome</p>
                <p><strong>Email:</strong> $email</p>
                <p><strong>Telefone:</strong> $telefone</p>
                <p><strong>Assunto:</strong> $assunto</p>
                <p><strong>Interesse no imóvel:</strong> $imovel_interesse</p>
                <p><strong>Mensagem:</strong></p>
                <p>$mensagem</p>
            ";
            
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=utf-8\r\n";
            $headers .= "From: $nome <$email>\r\n";
            
            @mail($para, $assunto_email, $mensagem_email, $headers);
        }
    }
}

// Se vier de um link de imóvel, preencher automaticamente
$imovel_interesse = '';
if (isset($_GET['imovel'])) {
    $imovel_interesse = urldecode($_GET['imovel']);
}

// Incluir o header
include 'private/includes/header.php';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contato | Corretora Base</title>
    <link rel="stylesheet" href="public/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>


    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1>Fale Conosco</h1>
            <p>Estamos aqui para ajudar você a encontrar o imóvel dos seus sonhos</p>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact-section">
        <div class="container">
            <div class="contact-grid">
                <div class="contact-info">
                    <h2>Informações de Contato</h2>
                    
                    <div class="info-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <h3>Endereço</h3>
                            <p>Rua Exemplo, 123 - Centro<br>Cidade - Estado</p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <i class="fas fa-phone"></i>
                        <div>
                            <h3>Telefone</h3>
                            <p>(XX) XXXX-XXXX</p>
                            <p>(XX) XXXX-XXXX</p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <h3>Email</h3>
                            <p>contato@corretorabase.com.br</p>
                            <p>vendas@corretorabase.com.br</p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <i class="fas fa-clock"></i>
                        <div>
                            <h3>Horário de Atendimento</h3>
                            <p>Segunda a Sexta: 08:00 - 18:00</p>
                            <p>Sábado: 09:00 - 13:00</p>
                        </div>
                    </div>
                </div>
                
                <div class="contact-form">
                    <?php if ($mensagem_enviada): ?>
                        <div class="alert success">
                            <i class="fas fa-check-circle"></i>
                            <h3>Mensagem enviada com sucesso!</h3>
                            <p>Entraremos em contato em breve. Obrigado!</p>
                        </div>
                    <?php else: ?>
                        <h2>Envie sua Mensagem</h2>
                        <p>Tire suas dúvidas ou agende uma visita</p>
                        
                        <form action="contato.php" method="post">
                            <div class="form-row">
                                <div class="form-group <?= isset($erros['nome']) ? 'has-error' : '' ?>">
                                    <label for="nome">Nome *</label>
                                    <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($nome) ?>" required>
                                    <?php if (isset($erros['nome'])): ?>
                                        <span class="error-message"><?= $erros['nome'] ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="form-group <?= isset($erros['email']) ? 'has-error' : '' ?>">
                                    <label for="email">Email *</label>
                                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
                                    <?php if (isset($erros['email'])): ?>
                                        <span class="error-message"><?= $erros['email'] ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="telefone">Telefone</label>
                                    <input type="tel" id="telefone" name="telefone" value="<?= htmlspecialchars($telefone) ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="assunto">Assunto</label>
                                    <select id="assunto" name="assunto">
                                        <option value="Informações" <?= ($assunto === 'Informações') ? 'selected' : '' ?>>Informações</option>
                                        <option value="Visita" <?= ($assunto === 'Visita') ? 'selected' : '' ?>>Agendar Visita</option>
                                        <option value="Dúvida" <?= ($assunto === 'Dúvida') ? 'selected' : '' ?>>Dúvida</option>
                                        <option value="Orçamento" <?= ($assunto === 'Orçamento') ? 'selected' : '' ?>>Orçamento</option>
                                        <option value="Outro" <?= ($assunto === 'Outro') ? 'selected' : '' ?>>Outro</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="imovel_interesse">Imóvel de Interesse (opcional)</label>
                                <input type="text" id="imovel_interesse" name="imovel_interesse" value="<?= htmlspecialchars($imovel_interesse) ?>">
                            </div>
                            
                            <div class="form-group <?= isset($erros['mensagem']) ? 'has-error' : '' ?>">
                                <label for="mensagem">Mensagem *</label>
                                <textarea id="mensagem" name="mensagem" rows="5" required><?= htmlspecialchars($mensagem) ?></textarea>
                                <?php if (isset($erros['mensagem'])): ?>
                                    <span class="error-message"><?= $erros['mensagem'] ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <button type="submit" class="btn">Enviar Mensagem</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="map-section">
        <div class="container">
            <h2>Onde Estamos</h2>
            <div class="map-container">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d12345.678901234567!2d-46.12345678901234!3d-23.123456789012345!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMjPCsDA3JzI0LjQiUyA0NsKwMDcnMjQuNCJX!5e0!3m2!1spt-BR!2sbr!4v1234567890123!5m2!1spt-BR!2sbr" 
                        width="100%" 
                        height="450" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy">
                </iframe>
            </div>
        </div>
    </section>

    <?php
        // Incluir o footer
        include 'private/includes/footer.php';
    ?>
    <script src="public/assets/js/main.js"></script>
    <script>
        // Máscara para telefone
        document.getElementById('telefone').addEventListener('input', function (e) {
            var x = e.target.value.replace(/\D/g, '').match(/(\d{0,2})(\d{0,5})(\d{0,4})/);
            e.target.value = !x[2] ? x[1] : '(' + x[1] + ') ' + x[2] + (x[3] ? '-' + x[3] : '');
        });
    </script>
</body>
</html>