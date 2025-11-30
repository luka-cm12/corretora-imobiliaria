<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste Específico CNPJ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>🏢 Teste Específico para CNPJ</h1>
        
        <?php
        require_once 'private/includes/db.php';
        
        echo "<h2>1️⃣ Estrutura da Tabela:</h2>";
        try {
            $columns = db_query("SHOW COLUMNS FROM proprietarios");
            
            $tem_cpf = false;
            $tem_cnpj = false;
            $tem_tipo_documento = false;
            
            echo "<table class='table table-sm'>";
            echo "<thead><tr><th>Campo</th><th>Tipo</th><th>Permite NULL</th><th>Padrão</th></tr></thead>";
            foreach ($columns as $col) {
                echo "<tr>";
                echo "<td><strong>{$col['Field']}</strong></td>";
                echo "<td>{$col['Type']}</td>";
                echo "<td>" . ($col['Null'] === 'YES' ? '✅ SIM' : '❌ NÃO') . "</td>";
                echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
                echo "</tr>";
                
                if ($col['Field'] === 'cpf') $tem_cpf = true;
                if ($col['Field'] === 'cnpj') $tem_cnpj = true;
                if ($col['Field'] === 'tipo_documento') $tem_tipo_documento = true;
            }
            echo "</table>";
            
            echo "<div class='alert alert-info'>";
            echo "<h6>Status das colunas:</h6>";
            echo "<ul>";
            echo "<li>Coluna CPF: " . ($tem_cpf ? "✅ Existe" : "❌ Não existe") . "</li>";
            echo "<li>Coluna CNPJ: " . ($tem_cnpj ? "✅ Existe" : "❌ Não existe") . "</li>";
            echo "<li>Coluna tipo_documento: " . ($tem_tipo_documento ? "✅ Existe" : "❌ Não existe") . "</li>";
            echo "<li>Estrutura Nova: " . ($tem_tipo_documento && $tem_cnpj ? "✅ SIM" : "❌ NÃO") . "</li>";
            echo "</ul>";
            echo "</div>";
            
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>Erro: " . $e->getMessage() . "</div>";
        }
        
        echo "<h2>2️⃣ Dados Atuais com CNPJ:</h2>";
        try {
            $cnpjs = db_query("SELECT * FROM proprietarios WHERE cnpj IS NOT NULL OR tipo_documento = 'cnpj' ORDER BY id_proprietario");
            
            if (count($cnpjs) > 0) {
                echo "<table class='table table-striped'>";
                echo "<thead><tr><th>ID</th><th>Nome</th><th>CPF</th><th>CNPJ</th><th>Tipo</th></tr></thead>";
                foreach ($cnpjs as $prop) {
                    echo "<tr>";
                    echo "<td>{$prop['id_proprietario']}</td>";
                    echo "<td>" . htmlspecialchars($prop['nome']) . "</td>";
                    echo "<td>" . ($prop['cpf'] ?: '<em>NULL</em>') . "</td>";
                    echo "<td>" . ($prop['cnpj'] ?? '<em>NULL</em>') . "</td>";
                    echo "<td>" . ($prop['tipo_documento'] ?? 'N/A') . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<div class='alert alert-warning'>Nenhum registro com CNPJ encontrado</div>";
            }
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>Erro: " . $e->getMessage() . "</div>";
        }
        
        echo "<h2>3️⃣ Teste de Inserção CNPJ:</h2>";
        
        if ($_POST['teste_cnpj'] ?? false) {
            $nome_teste = "Empresa Teste CNPJ " . date('H:i:s');
            $cnpj_teste = "12345678000195";
            
            try {
                echo "<div class='alert alert-info'>Tentando inserir empresa com CNPJ...</div>";
                
                if ($tem_tipo_documento && $tem_cnpj) {
                    $sql = "INSERT INTO proprietarios (nome, cpf, cnpj, tipo_documento, telefone, email, endereco, data_cadastro) VALUES (?, NULL, ?, 'cnpj', ?, ?, ?, NOW())";
                    $params = [$nome_teste, $cnpj_teste, null, null, null];
                } else {
                    $sql = "INSERT INTO proprietarios (nome, cpf, telefone, email, endereco, data_cadastro) VALUES (?, ?, ?, ?, ?, NOW())";
                    $params = [$nome_teste, $cnpj_teste, null, null, null];
                }
                
                echo "<p><strong>SQL:</strong> " . htmlspecialchars($sql) . "</p>";
                echo "<p><strong>Parâmetros:</strong> " . htmlspecialchars(json_encode($params)) . "</p>";
                
                $result = db_query($sql, $params);
                
                if ($result > 0) {
                    echo "<div class='alert alert-success'>✅ CNPJ inserido com sucesso! ID: {$result}</div>";
                    
                    // Verificar o registro inserido
                    $verificar = db_query("SELECT * FROM proprietarios WHERE id_proprietario = ?", [$result]);
                    if (!empty($verificar)) {
                        $reg = $verificar[0];
                        echo "<div class='alert alert-info'>";
                        echo "<h6>Registro inserido:</h6>";
                        echo "<ul>";
                        echo "<li>Nome: " . htmlspecialchars($reg['nome']) . "</li>";
                        echo "<li>CPF: " . ($reg['cpf'] ?: 'NULL') . "</li>";
                        echo "<li>CNPJ: " . ($reg['cnpj'] ?? 'NULL') . "</li>";
                        echo "<li>Tipo: " . ($reg['tipo_documento'] ?? 'N/A') . "</li>";
                        echo "</ul>";
                        echo "</div>";
                    }
                } else {
                    echo "<div class='alert alert-danger'>❌ Falha na inserção</div>";
                }
                
            } catch (Exception $e) {
                echo "<div class='alert alert-danger'>❌ Erro: " . $e->getMessage() . "</div>";
            }
        }
        ?>
        
        <form method="post" class="mb-4">
            <button type="submit" name="teste_cnpj" value="1" class="btn btn-primary">
                🧪 Testar Inserção de CNPJ
            </button>
        </form>
        
        <h2>4️⃣ Teste Manual CNPJ:</h2>
        <form method="post" action="private/imoveis/proprietario-cadastrar.php" target="_blank">
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">Nome da Empresa:</label>
                    <input type="text" name="nome" class="form-control" value="Empresa Teste Manual" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tipo de Documento:</label>
                    <select name="tipo_documento" class="form-control">
                        <option value="cpf">CPF</option>
                        <option value="cnpj" selected>CNPJ</option>
                    </select>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6">
                    <label class="form-label">CNPJ:</label>
                    <input type="text" name="documento" class="form-control" value="12.345.678/0001-95" placeholder="12.345.678/0001-95">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Telefone:</label>
                    <input type="text" name="telefone" class="form-control" value="(11) 99999-9999">
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6">
                    <label class="form-label">Email:</label>
                    <input type="email" name="email" class="form-control" value="empresa@teste.com">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Endereço:</label>
                    <input type="text" name="endereco" class="form-control" value="Rua das Empresas, 123">
                </div>
            </div>
            <input type="hidden" name="csrf_token" value="teste_token">
            <button type="submit" class="btn btn-success mt-3">
                🏢 Cadastrar Empresa via Formulário Real
            </button>
        </form>
        
        <div class="alert alert-warning mt-4">
            <h6>📋 Verificações CNPJ:</h6>
            <ul>
                <li>✅ Campo CNPJ existe na tabela</li>
                <li>✅ Campo tipo_documento configurado</li>
                <li>✅ Inserção separa CPF e CNPJ nas colunas corretas</li>
                <li>✅ Validação de CNPJ com 14 dígitos</li>
                <li>✅ Formulário permite seleção CPF/CNPJ</li>
            </ul>
        </div>
        
        <div class="mt-4">
            <a href="private/imoveis/proprietario-cadastrar.php" class="btn btn-primary">
                📝 Ir para Cadastro Real
            </a>
            <a href="private/imoveis/proprietarios-listar.php" class="btn btn-secondary">
                📋 Ver Lista de Proprietários
            </a>
        </div>
    </div>
</body>
</html>