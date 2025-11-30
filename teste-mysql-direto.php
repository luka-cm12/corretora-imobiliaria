<?php
require_once(__DIR__ . '/private/includes/db.php');

echo "<h1>🔧 Teste de Exclusão Direto no MySQL</h1>";

try {
    // 1. Verificar constraints de foreign key
    echo "<h2>1. Verificando Foreign Keys</h2>";
    $foreign_keys = db_query("
        SELECT 
            CONSTRAINT_NAME,
            TABLE_NAME,
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM information_schema.KEY_COLUMN_USAGE 
        WHERE REFERENCED_TABLE_NAME = 'proprietarios'
    ");
    
    if (empty($foreign_keys)) {
        echo "<p style='color: green;'>✅ Nenhuma foreign key encontrada referenciando proprietarios</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Foreign keys encontradas:</p>";
        echo "<ul>";
        foreach ($foreign_keys as $fk) {
            echo "<li>{$fk['TABLE_NAME']}.{$fk['COLUMN_NAME']} → {$fk['REFERENCED_TABLE_NAME']}.{$fk['REFERENCED_COLUMN_NAME']}</li>";
        }
        echo "</ul>";
    }
    
    // 2. Verificar triggers
    echo "<h2>2. Verificando Triggers</h2>";
    $triggers = db_query("SHOW TRIGGERS LIKE 'proprietarios'");
    
    if (empty($triggers)) {
        echo "<p style='color: green;'>✅ Nenhum trigger encontrado na tabela proprietarios</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Triggers encontrados:</p>";
        echo "<ul>";
        foreach ($triggers as $trigger) {
            echo "<li>{$trigger['Trigger']} - {$trigger['Event']} {$trigger['Timing']}</li>";
        }
        echo "</ul>";
    }
    
    // 3. Criar proprietário de teste e tentar excluir
    echo "<h2>3. Teste de Criação e Exclusão</h2>";
    
    $nome_teste = "TESTE_EXCLUSAO_" . date('YmdHis');
    $cpf_teste = "00000000" . rand(100, 999); // CPF fictício único
    
    echo "<p>📝 Criando proprietário de teste...</p>";
    echo "<p><strong>Nome:</strong> {$nome_teste}</p>";
    echo "<p><strong>CPF:</strong> {$cpf_teste}</p>";
    
    // Inserir
    $insert_sql = "INSERT INTO proprietarios (nome, cpf, data_cadastro) VALUES (?, ?, NOW())";
    $insert_id = db_query($insert_sql, [$nome_teste, $cpf_teste]);
    
    echo "<p style='color: green;'>✅ Proprietário criado com ID: {$insert_id}</p>";
    
    // Verificar se foi inserido
    $verificar_insercao = db_query("SELECT * FROM proprietarios WHERE id_proprietario = ?", [$insert_id]);
    if (empty($verificar_insercao)) {
        echo "<p style='color: red;'>❌ ERRO: Proprietário não foi encontrado após inserção!</p>";
    } else {
        echo "<p style='color: green;'>✅ Proprietário confirmado no banco</p>";
        
        // Tentar excluir
        echo "<p>🗑️ Tentando excluir...</p>";
        
        $delete_sql = "DELETE FROM proprietarios WHERE id_proprietario = ?";
        echo "<p><strong>SQL:</strong> {$delete_sql}</p>";
        echo "<p><strong>Parâmetros:</strong> [{$insert_id}]</p>";
        
        $linhas_afetadas = db_query($delete_sql, [$insert_id]);
        
        echo "<p><strong>Resultado:</strong> {$linhas_afetadas} linha(s) afetada(s)</p>";
        
        if ($linhas_afetadas > 0) {
            echo "<p style='color: green; font-weight: bold;'>✅ SUCESSO! Exclusão funcionou!</p>";
            
            // Verificar se realmente foi excluído
            $verificar_exclusao = db_query("SELECT COUNT(*) as count FROM proprietarios WHERE id_proprietario = ?", [$insert_id])[0]['count'];
            
            if ($verificar_exclusao == 0) {
                echo "<p style='color: green;'>✅ CONFIRMADO: Proprietário foi removido do banco</p>";
            } else {
                echo "<p style='color: red;'>❌ ERRO: Proprietário ainda existe após exclusão!</p>";
            }
        } else {
            echo "<p style='color: red; font-weight: bold;'>❌ FALHA! Nenhuma linha foi afetada</p>";
            
            // Investigar por que não foi excluído
            echo "<p>🔍 Investigando...</p>";
            
            // Verificar se ainda existe
            $ainda_existe = db_query("SELECT * FROM proprietarios WHERE id_proprietario = ?", [$insert_id]);
            if (!empty($ainda_existe)) {
                echo "<p>Proprietário ainda existe no banco:</p>";
                echo "<pre>" . print_r($ainda_existe[0], true) . "</pre>";
            }
        }
    }
    
    // 4. Testar com proprietário real sem imóveis
    echo "<h2>4. Testando com Proprietário Real</h2>";
    
    // Buscar um proprietário sem imóveis
    $proprietario_sem_imoveis = db_query("
        SELECT p.* 
        FROM proprietarios p 
        WHERE NOT EXISTS (
            SELECT 1 FROM imoveis WHERE id_proprietario = p.id_proprietario
        ) 
        LIMIT 1
    ");
    
    if (empty($proprietario_sem_imoveis)) {
        echo "<p style='color: orange;'>⚠️ Nenhum proprietário sem imóveis encontrado para teste</p>";
    } else {
        $prop = $proprietario_sem_imoveis[0];
        echo "<p>👤 Proprietário encontrado: ID {$prop['id_proprietario']} - " . htmlspecialchars($prop['nome']) . "</p>";
        
        echo "<form method='POST' style='margin: 10px 0;'>";
        echo "<input type='hidden' name='testar_real' value='{$prop['id_proprietario']}'>";
        echo "<button type='submit' style='background: #dc3545; color: white; padding: 10px 15px; border: none; border-radius: 5px;' ";
        echo "onclick='return confirm(\"ATENÇÃO: Isso excluirá o proprietário REAL: {$prop['nome']}\\n\\nTem certeza?\");'>";
        echo "🗑️ Testar Exclusão do Proprietário Real";
        echo "</button>";
        echo "</form>";
        
        // Processar teste real
        if (isset($_POST['testar_real'])) {
            $id_real = (int)$_POST['testar_real'];
            
            echo "<div style='background: #fff3cd; padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #ffc107;'>";
            echo "<h3>🧪 Testando Exclusão Real - ID: {$id_real}</h3>";
            
            try {
                $linhas_afetadas_real = db_query("DELETE FROM proprietarios WHERE id_proprietario = ?", [$id_real]);
                
                echo "<p><strong>Linhas afetadas:</strong> {$linhas_afetadas_real}</p>";
                
                if ($linhas_afetadas_real > 0) {
                    echo "<p style='color: green; font-weight: bold;'>✅ SUCESSO! Proprietário real foi excluído!</p>";
                } else {
                    echo "<p style='color: red; font-weight: bold;'>❌ FALHA! Proprietário real não foi excluído!</p>";
                }
                
            } catch (Exception $e) {
                echo "<p style='color: red;'>❌ ERRO: " . $e->getMessage() . "</p>";
            }
            
            echo "</div>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>ERRO GERAL:</strong> " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    h1, h2 { color: #333; }
    h2 { border-bottom: 2px solid #007bff; padding-bottom: 5px; }
    pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
    ul { margin-left: 20px; }
</style>

<hr>
<p><a href="private/imoveis/proprietarios-listar.php">← Voltar para listagem</a> | 
   <a href="teste-exclusao-direto.php">Teste Interface</a> | 
   <a href="diagnostico-exclusao-completo.php">Diagnóstico Completo</a></p>