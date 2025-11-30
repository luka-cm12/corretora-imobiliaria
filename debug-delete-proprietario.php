<?php
require_once(__DIR__ . '/private/includes/auth.php');
require_login();
require_once(__DIR__ . '/private/includes/db.php');

// Script de debug para testar a exclusão de proprietários

// Verificar se temos proprietários no sistema
echo "<h2>Debug - Exclusão de Proprietários</h2>";

try {
    // Listar proprietários
    $proprietarios = db_query("SELECT * FROM proprietarios ORDER BY id_proprietario ASC");
    
    echo "<h3>Proprietários no sistema:</h3>";
    echo "<table border='1' style='border-collapse: collapse; margin-bottom: 20px;'>";
    echo "<tr><th>ID</th><th>Nome</th><th>CPF</th><th>Email</th><th>Imóveis</th></tr>";
    
    foreach ($proprietarios as $prop) {
        // Contar imóveis de cada proprietário
        $imoveis_count = db_query("SELECT COUNT(*) as count FROM imoveis WHERE id_proprietario = ?", [$prop['id_proprietario']])[0]['count'];
        
        echo "<tr>";
        echo "<td>{$prop['id_proprietario']}</td>";
        echo "<td>{$prop['nome']}</td>";
        echo "<td>" . ($prop['cpf'] ?? 'N/A') . "</td>";
        echo "<td>" . ($prop['email'] ?? 'N/A') . "</td>";
        echo "<td>{$imoveis_count}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Testar exclusão simulada (sem executar)
    if (count($proprietarios) > 0) {
        $primeiro_proprietario = $proprietarios[0];
        $id_teste = $primeiro_proprietario['id_proprietario'];
        
        echo "<h3>Teste de exclusão (simulado) - Proprietário ID: {$id_teste}</h3>";
        
        // Verificar se tem imóveis
        $imoveis_count = db_query("SELECT COUNT(*) as count FROM imoveis WHERE id_proprietario = ?", [$id_teste])[0]['count'];
        
        if ($imoveis_count > 0) {
            echo "<p style='color: orange;'>❌ Este proprietário possui {$imoveis_count} imóvel(is) - NÃO pode ser excluído</p>";
        } else {
            echo "<p style='color: green;'>✅ Este proprietário não possui imóveis - PODE ser excluído</p>";
            
            // Simular a query de exclusão
            echo "<p><strong>Query que seria executada:</strong></p>";
            echo "<code>DELETE FROM proprietarios WHERE id_proprietario = {$id_teste}</code>";
        }
    }
    
    // Verificar estrutura da tabela proprietarios
    echo "<h3>Estrutura da tabela proprietarios:</h3>";
    $colunas = db_query("DESCRIBE proprietarios");
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th><th>Extra</th></tr>";
    
    foreach ($colunas as $coluna) {
        echo "<tr>";
        echo "<td>{$coluna['Field']}</td>";
        echo "<td>{$coluna['Type']}</td>";
        echo "<td>{$coluna['Null']}</td>";
        echo "<td>{$coluna['Key']}</td>";
        echo "<td>" . ($coluna['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . ($coluna['Extra'] ?? '') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Verificar estrutura da tabela imoveis
    echo "<h3>Estrutura da tabela imoveis (relação com proprietários):</h3>";
    $colunas_imoveis = db_query("DESCRIBE imoveis");
    
    $tem_id_proprietario = false;
    foreach ($colunas_imoveis as $coluna) {
        if ($coluna['Field'] === 'id_proprietario') {
            $tem_id_proprietario = true;
            echo "<p style='color: green;'>✅ Campo id_proprietario encontrado na tabela imoveis</p>";
            echo "<p>Tipo: {$coluna['Type']}, Nulo: {$coluna['Null']}, Chave: {$coluna['Key']}</p>";
            break;
        }
    }
    
    if (!$tem_id_proprietario) {
        echo "<p style='color: red;'>❌ Campo id_proprietario NÃO encontrado na tabela imoveis</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>Erro:</strong> " . $e->getMessage() . "</p>";
}

// Teste de conectividade do banco
echo "<h3>Teste de conectividade:</h3>";
try {
    $teste = db_query("SELECT 1 as teste");
    echo "<p style='color: green;'>✅ Conexão com banco OK</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro de conexão: " . $e->getMessage() . "</p>";
}
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { margin: 10px 0; }
    th, td { padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    code { background-color: #f5f5f5; padding: 5px; border-radius: 3px; }
</style>