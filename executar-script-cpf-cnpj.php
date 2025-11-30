<?php
// Script para executar o SQL de criação das colunas CPF/CNPJ
require_once(__DIR__ . '/private/includes/db.php');

echo "<!DOCTYPE html><html><head><title>Executar Script CPF/CNPJ</title><style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; }
.success { color: green; background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0; }
.error { color: red; background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0; }
.info { color: #0c5460; background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0; }
table { border-collapse: collapse; width: 100%; margin: 15px 0; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background: #f2f2f2; }
.btn { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; margin: 5px; }
.btn:hover { background: #0056b3; }
</style></head><body>";

echo "<h1>🔧 Executar Script de CPF/CNPJ</h1>";

try {
    // 1. Verificar estrutura atual
    echo "<h2>1️⃣ Estrutura Atual</h2>";
    $colunas = db_query("SHOW COLUMNS FROM proprietarios");
    $campos_existentes = array_column($colunas, 'Field');
    
    echo "<p><strong>Campos existentes:</strong> " . implode(', ', $campos_existentes) . "</p>";
    
    $tem_tipo_documento = in_array('tipo_documento', $campos_existentes);
    $tem_cnpj = in_array('cnpj', $campos_existentes);
    
    echo "<div class='info'>";
    echo "<p>✅ <strong>tipo_documento:</strong> " . ($tem_tipo_documento ? "JÁ EXISTE" : "PRECISA CRIAR") . "</p>";
    echo "<p>✅ <strong>cnpj:</strong> " . ($tem_cnpj ? "JÁ EXISTE" : "PRECISA CRIAR") . "</p>";
    echo "</div>";
    
    // 2. Executar alterações se necessário
    if (!$tem_tipo_documento || !$tem_cnpj) {
        echo "<h2>2️⃣ Executando Alterações</h2>";
        
        if (!$tem_tipo_documento) {
            echo "<p>➕ Adicionando coluna tipo_documento...</p>";
            try {
                db_query("ALTER TABLE proprietarios ADD tipo_documento ENUM('cpf', 'cnpj') DEFAULT 'cpf' COMMENT 'Tipo do documento: cpf para pessoa física, cnpj para pessoa jurídica'");
                echo "<div class='success'>✅ Coluna tipo_documento criada com sucesso!</div>";
            } catch (Exception $e) {
                echo "<div class='error'>❌ Erro ao criar tipo_documento: " . $e->getMessage() . "</div>";
            }
        }
        
        if (!$tem_cnpj) {
            echo "<p>➕ Adicionando coluna cnpj...</p>";
            try {
                db_query("ALTER TABLE proprietarios ADD cnpj VARCHAR(18) NULL COMMENT 'CNPJ do proprietário pessoa jurídica (formato: 00.000.000/0000-00)'");
                echo "<div class='success'>✅ Coluna cnpj criada com sucesso!</div>";
            } catch (Exception $e) {
                echo "<div class='error'>❌ Erro ao criar cnpj: " . $e->getMessage() . "</div>";
            }
        }
        
        // Verificar novamente
        $colunas_novas = db_query("SHOW COLUMNS FROM proprietarios");
        $campos_novos = array_column($colunas_novas, 'Field');
        echo "<p><strong>Novos campos:</strong> " . implode(', ', $campos_novos) . "</p>";
        
    } else {
        echo "<div class='success'>";
        echo "<h2>✅ Estrutura Já Está Correta!</h2>";
        echo "<p>As colunas necessárias já existem no banco.</p>";
        echo "</div>";
    }
    
    // 3. Mostrar detalhes das colunas
    echo "<h2>3️⃣ Detalhes das Colunas</h2>";
    $detalhes = db_query("
        SELECT 
            COLUMN_NAME as 'Nome da Coluna',
            COLUMN_TYPE as 'Tipo',
            IS_NULLABLE as 'Permite NULL',
            COLUMN_DEFAULT as 'Valor Padrão',
            COLUMN_COMMENT as 'Comentário'
        FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'proprietarios' 
            AND COLUMN_NAME IN ('tipo_documento', 'cnpj')
    ");
    
    if (!empty($detalhes)) {
        echo "<table>";
        echo "<tr><th>Coluna</th><th>Tipo</th><th>NULL</th><th>Padrão</th><th>Comentário</th></tr>";
        foreach ($detalhes as $col) {
            echo "<tr>";
            echo "<td><strong>{$col['Nome da Coluna']}</strong></td>";
            echo "<td>{$col['Tipo']}</td>";
            echo "<td>{$col['Permite NULL']}</td>";
            echo "<td>" . ($col['Valor Padrão'] ?: 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($col['Comentário']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 4. Teste de inserção
    echo "<h2>4️⃣ Teste de Funcionamento</h2>";
    
    // Teste CPF
    echo "<h3>Teste CPF:</h3>";
    try {
        $nome_cpf = 'TESTE CPF ' . date('H:i:s');
        $result_cpf = db_query("INSERT INTO proprietarios (nome, tipo_documento, cpf) VALUES (?, ?, ?)", 
                              [$nome_cpf, 'cpf', '123.456.789-00']);
        echo "<div class='success'>✅ CPF inserido com sucesso! ID: {$result_cpf}</div>";
        
        // Limpar teste
        db_query("DELETE FROM proprietarios WHERE id_proprietario = ?", [$result_cpf]);
    } catch (Exception $e) {
        echo "<div class='error'>❌ Erro no teste CPF: " . $e->getMessage() . "</div>";
    }
    
    // Teste CNPJ
    echo "<h3>Teste CNPJ:</h3>";
    try {
        $nome_cnpj = 'TESTE CNPJ ' . date('H:i:s');
        $result_cnpj = db_query("INSERT INTO proprietarios (nome, tipo_documento, cnpj) VALUES (?, ?, ?)", 
                               [$nome_cnpj, 'cnpj', '12.345.678/0001-90']);
        echo "<div class='success'>✅ CNPJ inserido com sucesso! ID: {$result_cnpj}</div>";
        
        // Limpar teste
        db_query("DELETE FROM proprietarios WHERE id_proprietario = ?", [$result_cnpj]);
    } catch (Exception $e) {
        echo "<div class='error'>❌ Erro no teste CNPJ: " . $e->getMessage() . "</div>";
    }
    
    // 5. Resultado final
    echo "<div class='success'>";
    echo "<h2>🎉 Estrutura Configurada!</h2>";
    echo "<p>O banco está pronto para cadastrar CPF e CNPJ separadamente.</p>";
    echo "<p><a href='private/imoveis/proprietario-cadastrar.php' class='btn'>📝 Testar Cadastro</a>";
    echo "<a href='private/imoveis/proprietarios-listar.php' class='btn'>📋 Ver Lista</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h2>❌ Erro Crítico</h2>";
    echo "<p>Erro: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "</body></html>";
?>