<?php
require_once(__DIR__ . '/private/includes/db.php');

// Script de teste para exclusão de proprietários
echo "<h2>Teste de Exclusão de Proprietário</h2>";

// Primeiro, vamos criar um proprietário de teste para excluir
try {
    // Inserir um proprietário de teste
    $nome_teste = "Teste Exclusão " . date('Y-m-d H:i:s');
    $insert_id = db_query("INSERT INTO proprietarios (nome, created_at) VALUES (?, NOW())", [$nome_teste]);
    
    echo "<p>✅ Proprietário de teste criado com ID: {$insert_id}</p>";
    
    // Verificar se foi inserido
    $proprietario_inserido = db_query("SELECT * FROM proprietarios WHERE id_proprietario = ?", [$insert_id]);
    
    if (empty($proprietario_inserido)) {
        echo "<p>❌ Erro: Proprietário não foi encontrado após inserção</p>";
        exit;
    }
    
    echo "<p>✅ Proprietário encontrado: " . $proprietario_inserido[0]['nome'] . "</p>";
    
    // Verificar se tem imóveis (deve ser 0)
    $imoveis_count = db_query("SELECT COUNT(*) as count FROM imoveis WHERE id_proprietario = ?", [$insert_id])[0]['count'];
    echo "<p>ℹ️ Imóveis associados: {$imoveis_count}</p>";
    
    if ($imoveis_count == 0) {
        // Tentar excluir
        echo "<p>🗑️ Tentando excluir o proprietário...</p>";
        
        $linhas_afetadas = db_query("DELETE FROM proprietarios WHERE id_proprietario = ?", [$insert_id]);
        
        echo "<p>📊 Linhas afetadas: {$linhas_afetadas}</p>";
        
        if ($linhas_afetadas > 0) {
            echo "<p style='color: green;'>✅ SUCESSO! Proprietário excluído com sucesso.</p>";
            
            // Verificar se realmente foi excluído
            $verificar_exclusao = db_query("SELECT COUNT(*) as count FROM proprietarios WHERE id_proprietario = ?", [$insert_id])[0]['count'];
            
            if ($verificar_exclusao == 0) {
                echo "<p style='color: green;'>✅ CONFIRMADO! Proprietário não existe mais no banco.</p>";
            } else {
                echo "<p style='color: red;'>❌ ERRO! Proprietário ainda existe no banco após exclusão.</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ ERRO! Nenhuma linha foi afetada na exclusão.</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ Proprietário tem imóveis associados, não pode ser excluído.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ ERRO: " . $e->getMessage() . "</p>";
}

// Testar a função db_query básica
echo "<hr><h3>Teste de conectividade básica:</h3>";
try {
    $teste_basico = db_query("SELECT 1 as teste");
    echo "<p>✅ Conexão OK - Resultado: " . print_r($teste_basico, true) . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro de conexão: " . $e->getMessage() . "</p>";
}

// Listar alguns proprietários para verificar
echo "<hr><h3>Proprietários no sistema (últimos 5):</h3>";
try {
    $props = db_query("SELECT id_proprietario, nome FROM proprietarios ORDER BY id_proprietario DESC LIMIT 5");
    
    if (empty($props)) {
        echo "<p>Nenhum proprietário encontrado.</p>";
    } else {
        echo "<ul>";
        foreach ($props as $prop) {
            echo "<li>ID: {$prop['id_proprietario']} - Nome: {$prop['nome']}</li>";
        }
        echo "</ul>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro ao listar proprietários: " . $e->getMessage() . "</p>";
}
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    p { margin: 5px 0; }
</style>