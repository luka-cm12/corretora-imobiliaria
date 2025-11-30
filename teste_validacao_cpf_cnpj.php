<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de Validação CPF/CNPJ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .valid { color: #28a745; }
        .invalid { color: #dc3545; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1>🧪 Teste de Validação CPF/CNPJ</h1>
        
        <?php
        // Incluir as funções de validação
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
        
        // Testes de CPF
        $testes_cpf = [
            '123.456.789-01' => 'Formato com pontuação',
            '12345678901' => 'Somente números',
            '000.000.000-00' => 'Sequência de zeros',
            '111.111.111-11' => 'Sequência de uns', 
            '12345' => 'Muito pequeno',
            '123456789012345' => 'Muito grande',
            '' => 'Vazio',
            '   ' => 'Só espaços',
            'abc.def.ghi-jk' => 'Com letras'
        ];
        
        echo '<div class="card mb-4">';
        echo '<div class="card-header"><h5>Teste de CPF</h5></div>';
        echo '<div class="card-body">';
        echo '<table class="table table-striped">';
        echo '<thead><tr><th>CPF</th><th>Descrição</th><th>Normalizado</th><th>Resultado</th></tr></thead>';
        
        foreach ($testes_cpf as $cpf => $desc) {
            $normalizado = normalizar_documento($cpf);
            $valido = cpf_valido($cpf);
            $classe = $valido ? 'valid' : 'invalid';
            $resultado = $valido ? '✅ VÁLIDO' : '❌ INVÁLIDO';
            
            echo '<tr>';
            echo "<td><code>{$cpf}</code></td>";
            echo "<td>{$desc}</td>";
            echo "<td><code>{$normalizado}</code> (" . strlen($normalizado) . " dígitos)</td>";
            echo "<td class='{$classe}'><strong>{$resultado}</strong></td>";
            echo '</tr>';
        }
        
        echo '</table>';
        echo '</div>';
        echo '</div>';
        
        // Testes de CNPJ  
        $testes_cnpj = [
            '12.345.678/0001-95' => 'Formato com pontuação',
            '12345678000195' => 'Somente números',
            '00.000.000/0000-00' => 'Sequência de zeros',
            '11.111.111/1111-11' => 'Sequência de uns',
            '12345' => 'Muito pequeno',
            '123456789012345678' => 'Muito grande', 
            '' => 'Vazio',
            '   ' => 'Só espaços'
        ];
        
        echo '<div class="card mb-4">';
        echo '<div class="card-header"><h5>Teste de CNPJ</h5></div>';
        echo '<div class="card-body">';
        echo '<table class="table table-striped">';
        echo '<thead><tr><th>CNPJ</th><th>Descrição</th><th>Normalizado</th><th>Resultado</th></tr></thead>';
        
        foreach ($testes_cnpj as $cnpj => $desc) {
            $normalizado = normalizar_documento($cnpj);
            $valido = cnpj_valido($cnpj);
            $classe = $valido ? 'valid' : 'invalid';
            $resultado = $valido ? '✅ VÁLIDO' : '❌ INVÁLIDO';
            
            echo '<tr>';
            echo "<td><code>{$cnpj}</code></td>";
            echo "<td>{$desc}</td>";
            echo "<td><code>{$normalizado}</code> (" . strlen($normalizado) . " dígitos)</td>";
            echo "<td class='{$classe}'><strong>{$resultado}</strong></td>";
            echo '</tr>';
        }
        
        echo '</table>';
        echo '</div>';
        echo '</div>';
        
        echo '<div class="alert alert-info">';
        echo '<h6>Regras de Validação:</h6>';
        echo '<ul>';
        echo '<li><strong>CPF:</strong> Deve ter exatamente 11 dígitos numéricos</li>';
        echo '<li><strong>CNPJ:</strong> Deve ter exatamente 14 dígitos numéricos</li>';
        echo '<li><strong>Ambos:</strong> Não aceita sequências iguais (000...000, 111...111)</li>';
        echo '<li><strong>Ambos:</strong> Remove automaticamente pontuação durante validação</li>';
        echo '</ul>';
        echo '</div>';
        ?>
        
        <div class="mt-4">
            <a href="private/imoveis/proprietario-cadastrar.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Ir para Cadastro
            </a>
            <a href="private/imoveis/proprietarios-listar.php" class="btn btn-secondary">
                <i class="fas fa-list"></i> Lista de Proprietários
            </a>
        </div>
    </div>
</body>
</html>