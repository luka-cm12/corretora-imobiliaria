<?php
require_once(__DIR__ . '/private/includes/db.php');

$page_title = 'Teste - Cadastro Proprietário (CPF e Email Opcionais)';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <style>
        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            margin: 40px;
            background: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { 
            color: #2c3e50; 
            text-align: center;
            margin-bottom: 30px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
        }
        .info {
            background: #cce7ff;
            color: #0066cc;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #007bff;
        }
        .changes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .change-card {
            background: #f8f9fa;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
        }
        .change-card.before {
            border-color: #dc3545;
            background: #f8d7da;
        }
        .change-card.after {
            border-color: #28a745;
            background: #d4edda;
        }
        .code-block {
            background: #2c3e50;
            color: #ecf0f1;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            overflow-x: auto;
            margin: 10px 0;
        }
        .test-scenarios {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .test-link {
            display: inline-block;
            background: #007bff;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 10px 10px 0;
            transition: background-color 0.3s;
        }
        .test-link:hover {
            background: #0056b3;
            text-decoration: none;
            color: white;
        }
        ul {
            padding-left: 20px;
        }
        li {
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>✅ Atualização: CPF e Email Opcionais no Cadastro de Proprietário</h1>

        <div class="success">
            <strong>🎉 Alteração Implementada com Sucesso!</strong><br>
            O CPF e email agora são campos opcionais no cadastro de proprietários.
        </div>

        <div class="changes-grid">
            <div class="change-card before">
                <h3>❌ ANTES (Obrigatório)</h3>
                <ul>
                    <li><strong>CPF:</strong> Campo obrigatório (*)</li>
                    <li><strong>Email:</strong> Sem indicação, mas tratado como obrigatório</li>
                    <li><strong>Validação:</strong> Erro se CPF estiver vazio</li>
                    <li><strong>Duplicidade:</strong> Sempre verificava CPF</li>
                </ul>
            </div>
            
            <div class="change-card after">
                <h3>✅ AGORA (Opcional)</h3>
                <ul>
                    <li><strong>CPF:</strong> Campo opcional (sem *)</li>
                    <li><strong>Email:</strong> Permanece opcional</li>
                    <li><strong>Validação:</strong> Só valida CPF se informado</li>
                    <li><strong>Duplicidade:</strong> Só verifica CPF se preenchido</li>
                </ul>
            </div>
        </div>

        <div class="info">
            <h3>🔧 Alterações Técnicas Realizadas</h3>
            
            <h4>1. Validação do CPF:</h4>
            <div class="code-block">
// ANTES:
if ($cpf === '') {
    $errors[] = 'O CPF é obrigatório.';
} else {
    if (!cpf_valido($cpf)) {
        $errors[] = 'Informe um CPF com 11 dígitos.';
    }
}

// DEPOIS:
if ($cpf !== '' && !cpf_valido($cpf)) {
    $errors[] = 'Informe um CPF com 11 dígitos.';
}
            </div>

            <h4>2. Verificação de Duplicidade:</h4>
            <div class="code-block">
// ANTES:
$cpf_norm = normalizar_cpf($cpf);
$cpf_dup = db_query(...);

// DEPOIS:
if ($cpf !== '') {
    $cpf_norm = normalizar_cpf($cpf);
    $cpf_dup = db_query(...);
}
            </div>

            <h4>3. Campo HTML:</h4>
            <div class="code-block">
// ANTES:
&lt;label&gt;CPF *&lt;/label&gt;
&lt;input type="text" name="cpf" required ... &gt;

// DEPOIS:
&lt;label&gt;CPF&lt;/label&gt;
&lt;input type="text" name="cpf" ... &gt;
            </div>
        </div>

        <div class="test-scenarios">
            <h3>🧪 Cenários de Teste</h3>
            <p><strong>Agora você pode testar os seguintes cenários:</strong></p>
            
            <ol>
                <li><strong>Cadastro apenas com nome:</strong> Deve funcionar normalmente</li>
                <li><strong>Cadastro com CPF inválido:</strong> Deve mostrar erro de validação</li>
                <li><strong>Cadastro com CPF válido:</strong> Deve aceitar e verificar duplicidade</li>
                <li><strong>Cadastro sem email:</strong> Deve funcionar normalmente</li>
                <li><strong>Cadastro com email inválido:</strong> Deve mostrar erro de validação</li>
            </ol>
        </div>

        <div class="info">
            <h3>📋 Resumo das Mudanças</h3>
            <ul>
                <li>✅ <strong>CPF não é mais obrigatório</strong> - pode ficar em branco</li>
                <li>✅ <strong>Email permanece opcional</strong> - como já era</li>
                <li>✅ <strong>Validação inteligente</strong> - só valida se preenchido</li>
                <li>✅ <strong>Duplicidade otimizada</strong> - só verifica CPF se informado</li>
                <li>✅ <strong>Interface atualizada</strong> - remoção do asterisco (*) no CPF</li>
                <li>✅ <strong>Texto explicativo</strong> - indica que CPF é opcional</li>
            </ul>
        </div>

        <h3>🔗 Testar Funcionalidade</h3>
        <a href="private/imoveis/proprietario-cadastrar.php" class="test-link" target="_blank">
            👥 Cadastrar Proprietário
        </a>
        
        <a href="private/imoveis/adicionar.php" class="test-link" target="_blank">
            🏠 Cadastrar Imóvel
        </a>

        <div class="success">
            <strong>✨ Benefícios da Alteração:</strong><br>
            • <strong>Flexibilidade:</strong> Permite cadastrar proprietários mesmo sem CPF<br>
            • <strong>Praticidade:</strong> Reduz campos obrigatórios no formulário<br>
            • <strong>Usabilidade:</strong> Menos barreiras no cadastro<br>
            • <strong>Compatibilidade:</strong> Mantém validação quando CPF é informado
        </div>

        <?php
        // Verificar se existem proprietários cadastrados
        try {
            $count = $conn->prepare("SELECT COUNT(*) as total FROM proprietarios");
            $count->execute();
            $total = $count->fetch()['total'];
            
            echo "<div class='info'>";
            echo "<h3>📊 Status Atual do Sistema</h3>";
            echo "<p><strong>Proprietários cadastrados:</strong> {$total}</p>";
            
            if ($total > 0) {
                // Contar proprietários com e sem CPF
                $com_cpf = $conn->prepare("SELECT COUNT(*) as total FROM proprietarios WHERE cpf IS NOT NULL AND cpf != ''");
                $com_cpf->execute();
                $count_com_cpf = $com_cpf->fetch()['total'];
                
                $sem_cpf = $total - $count_com_cpf;
                
                echo "<p><strong>Com CPF:</strong> {$count_com_cpf}</p>";
                echo "<p><strong>Sem CPF:</strong> {$sem_cpf}</p>";
            }
            echo "</div>";
            
        } catch (Exception $e) {
            echo "<div class='info'>📊 <strong>Status:</strong> Não foi possível verificar os dados do banco.</div>";
        }
        ?>
    </div>
</body>
</html>