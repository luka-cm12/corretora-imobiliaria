<?php
// Script EMERGENCIAL para corrigir estrutura do banco
require_once(__DIR__ . '/private/includes/db.php');

echo "<h1>🚑 Correção EMERGENCIAL - Banco de Dados</h1>";

try {
    // 1. Verificar estrutura atual
    echo "<h2>1️⃣ Verificando estrutura atual</h2>";
    $colunas = db_query("SHOW COLUMNS FROM proprietarios");
    $campos = array_column($colunas, 'Field');
    
    echo "<p>Campos existentes: <code>" . implode(', ', $campos) . "</code></p>";
    
    // 2. Adicionar colunas se necessário
    $tem_tipo_documento = in_array('tipo_documento', $campos);
    $tem_cnpj = in_array('cnpj', $campos);
    
    if (!$tem_tipo_documento) {
        echo "<h3>➕ Adicionando coluna tipo_documento</h3>";
        try {
            db_query("ALTER TABLE proprietarios ADD COLUMN tipo_documento VARCHAR(10) DEFAULT 'cpf' AFTER nome");
            echo "<p style='color: green;'>✅ Coluna tipo_documento adicionada</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>✅ Coluna tipo_documento já existe</p>";
    }
    
    if (!$tem_cnpj) {
        echo "<h3>➕ Adicionando coluna cnpj</h3>";
        try {
            db_query("ALTER TABLE proprietarios ADD COLUMN cnpj VARCHAR(20) NULL AFTER cpf");
            echo "<p style='color: green;'>✅ Coluna cnpj adicionada</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>✅ Coluna cnpj já existe</p>";
    }
    
    // 3. Testar inserção
    echo "<h2>2️⃣ Teste de inserção</h2>";
    
    $test_id = db_query("INSERT INTO proprietarios (nome, tipo_documento, cpf) VALUES (?, ?, ?)", 
                       ['TESTE FUNCIONAMENTO', 'cpf', '12345678901']);
    
    if ($test_id) {
        echo "<p style='color: green;'>✅ Inserção funcionou - ID: {$test_id}</p>";
        
        // Verificar se foi inserido
        $teste = db_query("SELECT * FROM proprietarios WHERE id_proprietario = ?", [$test_id]);
        if (!empty($teste)) {
            echo "<p>✅ Registro encontrado após inserção</p>";
        }
        
        // Remover teste
        db_query("DELETE FROM proprietarios WHERE id_proprietario = ?", [$test_id]);
        echo "<p>🗑️ Registro de teste removido</p>";
    } else {
        echo "<p style='color: red;'>❌ Inserção falhou</p>";
    }
    
    // 4. Contar proprietários reais
    echo "<h2>3️⃣ Contagem atual</h2>";
    $count = db_query("SELECT COUNT(*) as total FROM proprietarios WHERE nome != 'TESTE FUNCIONAMENTO'")[0];
    echo "<p>Total de proprietários: <strong>{$count['total']}</strong></p>";
    
    if ($count['total'] > 0) {
        echo "<h3>Últimos proprietários:</h3>";
        $ultimos = db_query("SELECT id_proprietario, nome, cpf, cnpj, tipo_documento FROM proprietarios WHERE nome != 'TESTE FUNCIONAMENTO' ORDER BY id_proprietario DESC LIMIT 3");
        echo "<ul>";
        foreach ($ultimos as $p) {
            echo "<li>ID: {$p['id_proprietario']} - " . htmlspecialchars($p['nome']) . " (CPF: " . ($p['cpf'] ?? 'NULL') . ", CNPJ: " . ($p['cnpj'] ?? 'NULL') . ")</li>";
        }
        echo "</ul>";
    }
    
    echo "<div style='background: #d4edda; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
    echo "<h3>✅ Correção Concluída!</h3>";
    echo "<p>O banco foi corrigido. Agora teste:</p>";
    echo "<ul>";
    echo "<li><a href='private/imoveis/proprietarios-listar.php'>📋 Ver lista de proprietários</a></li>";
    echo "<li><a href='private/imoveis/proprietario-cadastrar.php'>➕ Cadastrar novo proprietário</a></li>";
    echo "<li><a href='cadastro-proprietario-fix.php'>🚑 Cadastro emergencial</a></li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
    echo "<h3 style='color: red;'>❌ Erro Geral</h3>";
    echo "<p>Erro: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>