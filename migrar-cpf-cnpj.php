<?php
/**
 * Script para migrar estrutura do banco - CPF/CNPJ separados
 * Execute uma única vez para atualizar a estrutura
 */

require_once(__DIR__ . '/private/includes/db.php');

echo "<!DOCTYPE html><html><head><title>Migração CPF/CNPJ</title><style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; }
.success { color: green; } .error { color: red; } .info { color: blue; }
pre { background: #f5f5f5; padding: 10px; border-left: 4px solid #ddd; overflow-x: auto; }
.step { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
</style></head><body>";

echo "<h1>🔄 Migração: CPF/CNPJ Separados</h1>";

try {
    // Verificar estrutura atual
    echo "<div class='step'>";
    echo "<h3>1️⃣ Verificando estrutura atual</h3>";
    
    $colunas = db_query("SHOW COLUMNS FROM proprietarios");
    $colunas_existentes = array_column($colunas, 'Field');
    
    echo "<p>Colunas existentes: <code>" . implode(', ', $colunas_existentes) . "</code></p>";
    
    $tem_tipo_documento = in_array('tipo_documento', $colunas_existentes);
    $tem_cnpj = in_array('cnpj', $colunas_existentes);
    
    echo "<p>✅ tipo_documento: " . ($tem_tipo_documento ? "JÁ EXISTE" : "PRECISA CRIAR") . "</p>";
    echo "<p>✅ cnpj: " . ($tem_cnpj ? "JÁ EXISTE" : "PRECISA CRIAR") . "</p>";
    echo "</div>";

    // Adicionar colunas se necessário
    if (!$tem_tipo_documento) {
        echo "<div class='step'>";
        echo "<h3>2️⃣ Adicionando coluna tipo_documento</h3>";
        
        try {
            db_query("ALTER TABLE proprietarios ADD COLUMN tipo_documento ENUM('cpf', 'cnpj') DEFAULT 'cpf' AFTER nome");
            echo "<p class='success'>✅ Coluna tipo_documento adicionada com sucesso!</p>";
        } catch (Exception $e) {
            echo "<p class='error'>❌ Erro ao adicionar tipo_documento: " . $e->getMessage() . "</p>";
        }
        echo "</div>";
    }

    if (!$tem_cnpj) {
        echo "<div class='step'>";
        echo "<h3>3️⃣ Adicionando coluna cnpj</h3>";
        
        try {
            db_query("ALTER TABLE proprietarios ADD COLUMN cnpj VARCHAR(20) NULL AFTER cpf");
            echo "<p class='success'>✅ Coluna cnpj adicionada com sucesso!</p>";
        } catch (Exception $e) {
            echo "<p class='error'>❌ Erro ao adicionar cnpj: " . $e->getMessage() . "</p>";
        }
        echo "</div>";
    }

    // Migrar dados existentes
    echo "<div class='step'>";
    echo "<h3>4️⃣ Migrando dados existentes</h3>";
    
    // Buscar proprietários com CPF preenchido
    $proprietarios = db_query("SELECT id_proprietario, nome, cpf FROM proprietarios WHERE cpf IS NOT NULL AND cpf != ''");
    
    echo "<p>Encontrados <strong>" . count($proprietarios) . "</strong> proprietários com documento</p>";
    
    $migrados_cpf = 0;
    $migrados_cnpj = 0;
    
    foreach ($proprietarios as $prop) {
        $doc_limpo = preg_replace('/\D/', '', $prop['cpf']);
        
        if (strlen($doc_limpo) >= 14) {
            // É CNPJ - mover para coluna cnpj
            try {
                db_query("UPDATE proprietarios SET tipo_documento = 'cnpj', cnpj = ?, cpf = NULL WHERE id_proprietario = ?", 
                         [$prop['cpf'], $prop['id_proprietario']]);
                $migrados_cnpj++;
                echo "<p>📋 CNPJ: " . htmlspecialchars($prop['nome']) . " -> " . htmlspecialchars($prop['cpf']) . "</p>";
            } catch (Exception $e) {
                echo "<p class='error'>❌ Erro ao migrar CNPJ de {$prop['nome']}: " . $e->getMessage() . "</p>";
            }
        } elseif (strlen($doc_limpo) == 11) {
            // É CPF - definir tipo correto
            try {
                db_query("UPDATE proprietarios SET tipo_documento = 'cpf' WHERE id_proprietario = ?", 
                         [$prop['id_proprietario']]);
                $migrados_cpf++;
                echo "<p>👤 CPF: " . htmlspecialchars($prop['nome']) . " -> " . htmlspecialchars($prop['cpf']) . "</p>";
            } catch (Exception $e) {
                echo "<p class='error'>❌ Erro ao migrar CPF de {$prop['nome']}: " . $e->getMessage() . "</p>";
            }
        }
    }
    
    echo "<p class='success'>✅ Migração concluída:</p>";
    echo "<p>• <strong>{$migrados_cpf}</strong> CPFs mantidos</p>";
    echo "<p>• <strong>{$migrados_cnpj}</strong> CNPJs migrados</p>";
    echo "</div>";

    // Verificar resultado final
    echo "<div class='step'>";
    echo "<h3>5️⃣ Verificação Final</h3>";
    
    $stats = db_query("
        SELECT 
            tipo_documento,
            COUNT(*) as total,
            SUM(CASE WHEN cpf IS NOT NULL AND cpf != '' THEN 1 ELSE 0 END) as com_cpf,
            SUM(CASE WHEN cnpj IS NOT NULL AND cnpj != '' THEN 1 ELSE 0 END) as com_cnpj
        FROM proprietarios 
        GROUP BY tipo_documento
        ORDER BY tipo_documento
    ");
    
    if (!empty($stats)) {
        echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse;'>";
        echo "<tr><th>Tipo</th><th>Total</th><th>Com CPF</th><th>Com CNPJ</th></tr>";
        foreach ($stats as $stat) {
            echo "<tr>";
            echo "<td>" . strtoupper($stat['tipo_documento']) . "</td>";
            echo "<td>{$stat['total']}</td>";
            echo "<td>{$stat['com_cpf']}</td>";
            echo "<td>{$stat['com_cnpj']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    // Mostrar alguns exemplos
    $exemplos = db_query("
        SELECT id_proprietario, nome, tipo_documento, cpf, cnpj 
        FROM proprietarios 
        WHERE (cpf IS NOT NULL AND cpf != '') OR (cnpj IS NOT NULL AND cnpj != '')
        ORDER BY id_proprietario DESC 
        LIMIT 5
    ");
    
    if (!empty($exemplos)) {
        echo "<h4>Últimos proprietários:</h4>";
        echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Nome</th><th>Tipo</th><th>CPF</th><th>CNPJ</th></tr>";
        foreach ($exemplos as $ex) {
            echo "<tr>";
            echo "<td>{$ex['id_proprietario']}</td>";
            echo "<td>" . htmlspecialchars($ex['nome']) . "</td>";
            echo "<td>" . strtoupper($ex['tipo_documento']) . "</td>";
            echo "<td>" . htmlspecialchars($ex['cpf'] ?: '-') . "</td>";
            echo "<td>" . htmlspecialchars($ex['cnpj'] ?: '-') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    echo "</div>";

    echo "<div class='step' style='background: #d4edda; border-color: #c3e6cb;'>";
    echo "<h3>🎉 Migração Concluída com Sucesso!</h3>";
    echo "<p>As colunas foram adicionadas e os dados foram migrados corretamente.</p>";
    echo "<p><strong>Próximos passos:</strong></p>";
    echo "<ul>";
    echo "<li>✅ Testar cadastro de novos proprietários</li>";
    echo "<li>✅ Testar edição de proprietários existentes</li>";
    echo "<li>✅ Verificar listagem de proprietários</li>";
    echo "</ul>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div class='step' style='background: #f8d7da; border-color: #f5c6cb;'>";
    echo "<h3>❌ Erro na Migração</h3>";
    echo "<p class='error'>Erro: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p><a href='private/imoveis/proprietarios-listar.php'>📋 Ver Lista de Proprietários</a> | ";
echo "<a href='cadastro-proprietario-fix.php'>➕ Testar Cadastro</a></p>";

echo "</body></html>";
?>