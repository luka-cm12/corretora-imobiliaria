<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste Cadastro Opcional - Proprietários</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .test-case { 
            margin: 20px 0; 
            padding: 15px; 
            border: 2px solid #ddd; 
            border-radius: 8px;
        }
        .success { border-color: #28a745; background: #d4edda; }
        .error { border-color: #dc3545; background: #f8d7da; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1>🧪 Teste de Cadastro com Campos Opcionais</h1>
        
        <?php
        require_once 'private/includes/db.php';
        
        // Incluir as mesmas funções do cadastro
        function normalizar_documento(string $valor): string {
            return preg_replace('/\D+/', '', $valor);
        }

        function cpf_valido(string $cpf): bool {
            if (empty(trim($cpf))) return false;
            $cpf = normalizar_documento($cpf);
            if (strlen($cpf) !== 11) return false;
            if (preg_match('/^(\d)\1{10}$/', $cpf)) return false;
            if (!ctype_digit($cpf)) return false;
            return true;
        }

        function cnpj_valido(string $cnpj): bool {
            if (empty(trim($cnpj))) return false;
            $cnpj = normalizar_documento($cnpj);
            if (strlen($cnpj) !== 14) return false;
            if (preg_match('/^(\d)\1{13}$/', $cnpj)) return false;
            if (!ctype_digit($cnpj)) return false;
            return true;
        }
        
        // Casos de teste
        $testes = [
            [
                'nome' => 'João Silva (Só Nome)',
                'tipo_documento' => 'cpf',
                'documento' => '',
                'telefone' => '',
                'email' => '',
                'endereco' => '',
                'esperado' => 'SUCESSO'
            ],
            [
                'nome' => 'Maria Santos (Com CPF)',
                'tipo_documento' => 'cpf',
                'documento' => '12345678901',
                'telefone' => '11999887766',
                'email' => '',
                'endereco' => '',
                'esperado' => 'SUCESSO'
            ],
            [
                'nome' => 'Empresa ABC (Com CNPJ)',
                'tipo_documento' => 'cnpj',
                'documento' => '12345678000195',
                'telefone' => '',
                'email' => 'empresa@abc.com',
                'endereco' => 'Rua das Empresas, 123',
                'esperado' => 'SUCESSO'
            ],
            [
                'nome' => 'Teste CPF Inválido',
                'tipo_documento' => 'cpf',
                'documento' => '123456',
                'telefone' => '',
                'email' => '',
                'endereco' => '',
                'esperado' => 'ERRO'
            ],
            [
                'nome' => '',
                'tipo_documento' => 'cpf',
                'documento' => '',
                'telefone' => '',
                'email' => '',
                'endereco' => '',
                'esperado' => 'ERRO'
            ]
        ];
        
        echo "<h2>Estrutura da Tabela:</h2>";
        try {
            $columns = db_query("SHOW COLUMNS FROM proprietarios");
            echo "<table class='table table-sm'>";
            echo "<thead><tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Padrão</th></tr></thead>";
            foreach ($columns as $col) {
                echo "<tr>";
                echo "<td>{$col['Field']}</td>";
                echo "<td>{$col['Type']}</td>";
                echo "<td>{$col['Null']}</td>";
                echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            $tem_tipo_documento = false;
            $tem_cnpj = false;
            foreach ($columns as $col) {
                if ($col['Field'] === 'tipo_documento') $tem_tipo_documento = true;
                if ($col['Field'] === 'cnpj') $tem_cnpj = true;
            }
            
            echo "<p><strong>Estrutura nova:</strong> " . ($tem_tipo_documento && $tem_cnpj ? "✅ SIM" : "❌ NÃO") . "</p>";
        } catch (Exception $e) {
            echo "<p class='text-danger'>Erro ao verificar estrutura: " . $e->getMessage() . "</p>";
        }
        
        echo "<h2>Casos de Teste:</h2>";
        
        foreach ($testes as $index => $teste) {
            $numero = $index + 1;
            echo "<div class='test-case'>";
            echo "<h5>Teste {$numero}: {$teste['nome']}</h5>";
            
            $errors = [];
            
            // Simular validação
            $nome = trim($teste['nome']);
            $tipo_documento = $teste['tipo_documento'];
            $documento = trim($teste['documento']);
            $telefone = trim($teste['telefone']);
            $email = trim($teste['email']);
            $endereco = trim($teste['endereco']);
            
            if ($nome === '') {
                $errors[] = 'O nome é obrigatório.';
            }
            
            // Validação do documento (opcional)
            if ($documento !== '') {
                if ($tipo_documento === 'cpf' && !cpf_valido($documento)) {
                    $errors[] = 'Informe um CPF válido com 11 dígitos.';
                } elseif ($tipo_documento === 'cnpj' && !cnpj_valido($documento)) {
                    $errors[] = 'Informe um CNPJ válido com 14 dígitos.';
                }
            }
            
            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Informe um e-mail válido.';
            }
            
            echo "<p><strong>Dados:</strong></p>";
            echo "<ul>";
            echo "<li>Nome: " . ($nome ?: '<em>vazio</em>') . "</li>";
            echo "<li>Tipo: {$tipo_documento}</li>";
            echo "<li>Documento: " . ($documento ?: '<em>vazio</em>') . "</li>";
            echo "<li>Telefone: " . ($telefone ?: '<em>vazio</em>') . "</li>";
            echo "<li>Email: " . ($email ?: '<em>vazio</em>') . "</li>";
            echo "</ul>";
            
            if (empty($errors)) {
                echo "<p class='text-success'><strong>✅ Validação passou!</strong></p>";
                if ($teste['esperado'] === 'SUCESSO') {
                    echo "<div class='alert alert-success'>✅ Resultado conforme esperado</div>";
                } else {
                    echo "<div class='alert alert-warning'>⚠️ Esperava erro mas passou na validação</div>";
                }
            } else {
                echo "<p class='text-danger'><strong>❌ Erros encontrados:</strong></p>";
                echo "<ul>";
                foreach ($errors as $error) {
                    echo "<li class='text-danger'>{$error}</li>";
                }
                echo "</ul>";
                
                if ($teste['esperado'] === 'ERRO') {
                    echo "<div class='alert alert-success'>✅ Resultado conforme esperado (erro capturado)</div>";
                } else {
                    echo "<div class='alert alert-danger'>❌ Não era para dar erro</div>";
                }
            }
            
            echo "</div>";
        }
        
        echo "<h2>Links úteis:</h2>";
        echo "<ul>";
        echo "<li><a href='private/imoveis/proprietario-cadastrar.php' class='btn btn-primary'>Ir para Cadastro Real</a></li>";
        echo "<li><a href='private/imoveis/proprietarios-listar.php' class='btn btn-secondary'>Ver Lista de Proprietários</a></li>";
        echo "</ul>";
        ?>
        
        <div class="alert alert-info mt-4">
            <h6>📋 Resumo das Alterações:</h6>
            <ul>
                <li>✅ CPF/CNPJ agora são <strong>opcionais</strong></li>
                <li>✅ Email e telefone são <strong>opcionais</strong></li>
                <li>✅ Apenas o <strong>nome é obrigatório</strong></li>
                <li>✅ Sistema detecta automaticamente estrutura da tabela</li>
                <li>✅ Validação só ocorre se documento for preenchido</li>
            </ul>
        </div>
    </div>
</body>
</html>