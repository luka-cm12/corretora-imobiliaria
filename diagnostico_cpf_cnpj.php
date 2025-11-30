<?php
require_once 'private/includes/db.php';

echo "🔍 ANÁLISE DOS DADOS DA TABELA PROPRIETÁRIOS\n\n";

try {
    // 1. Verificar estrutura atual
    echo "1️⃣ Verificando estrutura da tabela:\n";
    $columns = db_query("SHOW COLUMNS FROM proprietarios");
    
    foreach ($columns as $col) {
        echo "   - {$col['Field']} ({$col['Type']}) - {$col['Null']} - {$col['Key']}\n";
    }
    
    // 2. Verificar dados problemáticos
    echo "\n2️⃣ Analisando dados problemáticos:\n";
    
    $proprietarios = db_query("SELECT * FROM proprietarios ORDER BY id_proprietario");
    
    $problemas = [];
    
    foreach ($proprietarios as $prop) {
        $id = $prop['id_proprietario'];
        $nome = $prop['nome'];
        $cpf = $prop['cpf'];
        $cnpj = $prop['cnpj'] ?? '';
        $tipo = $prop['tipo_documento'] ?? 'cpf';
        
        // Verificar CPF
        if (!empty($cpf)) {
            $cpf_limpo = preg_replace('/\D/', '', $cpf);
            if (strlen($cpf_limpo) !== 11) {
                $problemas[] = "ID {$id} ({$nome}): CPF inválido '{$cpf}' -> {$cpf_limpo} (" . strlen($cpf_limpo) . " dígitos)";
            }
        }
        
        // Verificar CNPJ
        if (!empty($cnpj)) {
            $cnpj_limpo = preg_replace('/\D/', '', $cnpj);
            if (strlen($cnpj_limpo) !== 14) {
                $problemas[] = "ID {$id} ({$nome}): CNPJ inválido '{$cnpj}' -> {$cnpj_limpo} (" . strlen($cnpj_limpo) . " dígitos)";
            }
        }
        
        // Verificar consistência de tipo
        if ($tipo === 'cpf' && empty($cpf)) {
            $problemas[] = "ID {$id} ({$nome}): Tipo CPF mas campo CPF vazio";
        }
        
        if ($tipo === 'cnpj' && empty($cnpj)) {
            $problemas[] = "ID {$id} ({$nome}): Tipo CNPJ mas campo CNPJ vazio";
        }
    }
    
    if (empty($problemas)) {
        echo "✅ Todos os dados estão consistentes!\n";
    } else {
        echo "❌ Problemas encontrados:\n";
        foreach ($problemas as $problema) {
            echo "   - {$problema}\n";
        }
    }
    
    // 3. Mostrar dados atuais
    echo "\n3️⃣ Dados atuais (primeiros 10 registros):\n";
    echo str_pad("ID", 4) . " | " . str_pad("Nome", 25) . " | " . str_pad("CPF", 12) . " | " . str_pad("CNPJ", 15) . " | Tipo\n";
    echo str_repeat("-", 70) . "\n";
    
    $amostra = array_slice($proprietarios, 0, 10);
    foreach ($amostra as $prop) {
        $id = str_pad($prop['id_proprietario'], 4);
        $nome = str_pad(substr($prop['nome'], 0, 25), 25);
        $cpf = str_pad($prop['cpf'] ?: 'VAZIO', 12);
        $cnpj = str_pad($prop['cnpj'] ?: 'VAZIO', 15);
        $tipo = $prop['tipo_documento'] ?? 'cpf';
        
        echo "{$id} | {$nome} | {$cpf} | {$cnpj} | {$tipo}\n";
    }
    
    // 4. Sugerir correções
    echo "\n4️⃣ Script de correção SQL:\n";
    echo "-- Execute este SQL para corrigir os dados:\n\n";
    
    foreach ($proprietarios as $prop) {
        $id = $prop['id_proprietario'];
        $cpf = $prop['cpf'];
        $cnpj = $prop['cnpj'] ?? '';
        
        if (!empty($cpf)) {
            $cpf_limpo = preg_replace('/\D/', '', $cpf);
            if (strlen($cpf_limpo) === 11 && $cpf_limpo !== $cpf) {
                echo "UPDATE proprietarios SET cpf = '{$cpf_limpo}' WHERE id_proprietario = {$id};\n";
            }
        }
        
        if (!empty($cnpj)) {
            $cnpj_limpo = preg_replace('/\D/', '', $cnpj);
            if (strlen($cnpj_limpo) === 14 && $cnpj_limpo !== $cnpj) {
                echo "UPDATE proprietarios SET cnpj = '{$cnpj_limpo}' WHERE id_proprietario = {$id};\n";
            }
        }
    }
    
    echo "\n5️⃣ Teste de validação:\n";
    
    // Testar função de validação
    $teste_cpfs = ['12345678901', '123.456.789-01', '000.000.000-00', '12345'];
    $teste_cnpjs = ['12345678000195', '12.345.678/0001-95', '00000000000000', '123456'];
    
    echo "CPFs de teste:\n";
    foreach ($teste_cpfs as $cpf_teste) {
        $limpo = preg_replace('/\D/', '', $cpf_teste);
        $valido = (strlen($limpo) === 11 && !preg_match('/^(\d)\1{10}$/', $limpo));
        echo "   - '{$cpf_teste}' -> '{$limpo}' -> " . ($valido ? "✅ VÁLIDO" : "❌ INVÁLIDO") . "\n";
    }
    
    echo "\nCNPJs de teste:\n";
    foreach ($teste_cnpjs as $cnpj_teste) {
        $limpo = preg_replace('/\D/', '', $cnpj_teste);
        $valido = (strlen($limpo) === 14 && !preg_match('/^(\d)\1{13}$/', $limpo));
        echo "   - '{$cnpj_teste}' -> '{$limpo}' -> " . ($valido ? "✅ VÁLIDO" : "❌ INVÁLIDO") . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
}
?>